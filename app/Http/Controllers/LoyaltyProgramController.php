<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LoyaltyProgramController extends Controller
{
    /**
     * Get loyalty program status, visit stamps, and reward ticket availability.
     */
    public function status(Request $request, string $slug): JsonResponse
    {
        $restaurant = Restaurant::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found.',
            ], 404);
        }

        $loyaltyEnabled = (bool) ($restaurant->loyalty_enabled ?? true);
        $requiredVisits = (int) ($restaurant->loyalty_required_visits ?: 5);
        $gameType = $restaurant->loyalty_game_type ?: 'spinner';
        $rewards = $restaurant->getEffectiveLoyaltyRewards();

        $phone = trim((string) $request->input('phone', ''));

        $customerData = null;
        if (! empty($phone)) {
            $customerData = $this->calculateCustomerLoyalty($restaurant, $phone, $requiredVisits);
        }

        return response()->json([
            'success' => true,
            'loyalty_enabled' => $loyaltyEnabled,
            'required_visits' => $requiredVisits,
            'game_type' => $gameType,
            'latitude' => $restaurant->latitude ? (float) $restaurant->latitude : null,
            'longitude' => $restaurant->longitude ? (float) $restaurant->longitude : null,
            'geofence_radius_km' => (float) ($restaurant->geofence_radius_km ?: 5.0),
            'rewards' => $rewards,
            'customer' => $customerData,
        ]);
    }

    /**
     * Claim / Spin loyalty reward ticket.
     */
    public function claim(Request $request, string $slug): JsonResponse
    {
        $restaurant = Restaurant::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        if (! ($restaurant->loyalty_enabled ?? true)) {
            return response()->json([
                'success' => false,
                'message' => 'Loyalty program is currently disabled for this restaurant.',
            ], 400);
        }

        $validated = $request->validate([
            'phone' => 'required|string|max:25',
            'reward_id' => 'required',
            'order_code' => 'nullable|string|max:50',
        ]);

        $phone = trim($validated['phone']);
        $requiredVisits = (int) ($restaurant->loyalty_required_visits ?: 5);
        $customerLoyalty = $this->calculateCustomerLoyalty($restaurant, $phone, $requiredVisits);

        if (($customerLoyalty['available_tickets'] ?? 0) <= 0) {
            return response()->json([
                'success' => false,
                'message' => "You need at least {$requiredVisits} visits to earn a reward ticket. You currently have {$customerLoyalty['visit_count']} visit(s).",
            ], 400);
        }

        // Find the selected/spun reward
        $rewards = $restaurant->getEffectiveLoyaltyRewards();
        $matchedReward = null;
        foreach ($rewards as $r) {
            if ((string) $r['id'] === (string) $validated['reward_id']) {
                $matchedReward = $r;
                break;
            }
        }

        if (! $matchedReward) {
            $matchedReward = $rewards[0];
        }

        // Case 1: Apply to existing active order
        if (! empty($validated['order_code'])) {
            $order = Order::where('order_code', $validated['order_code'])
                ->where('restaurant_id', $restaurant->id)
                ->first();

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.',
                ], 404);
            }

            if ($order->loyalty_reward_claimed) {
                return response()->json([
                    'success' => false,
                    'message' => 'A loyalty reward has already been claimed for this order.',
                ], 400);
            }

            $discountAmount = $this->calculateDiscount($order->subtotal, $matchedReward);

            $taxPercentage = $restaurant->tax_percentage ?? 0;
            $taxableAmount = max(0, $order->subtotal - $discountAmount);
            $taxAmount = round(($taxableAmount * $taxPercentage) / 100, 2);
            $newTotal = round($taxableAmount + $taxAmount, 2);

            $order->update([
                'discount_amount' => $discountAmount,
                'discount_reason' => 'Loyalty Reward: '.$matchedReward['title'].' 🎁',
                'loyalty_reward_claimed' => true,
                'loyalty_reward_details' => $matchedReward,
                'tax_amount' => $taxAmount,
                'total_amount' => $newTotal,
            ]);

            $upiString = null;
            if ($restaurant->upi_id && $order->payment_status !== 'paid') {
                $upiPayeeName = urlencode($restaurant->upi_name ?: $restaurant->name);
                $upiId = $restaurant->upi_id;
                $note = urlencode("Order {$order->order_code}");
                $upiString = "upi://pay?pa={$upiId}&pn={$upiPayeeName}&am={$newTotal}&cu=INR&tn={$note}";
            }

            return response()->json([
                'success' => true,
                'message' => "🎉 Congratulations! {$matchedReward['title']} applied to your order!",
                'reward' => $matchedReward,
                'discount_amount' => $discountAmount,
                'new_total' => $newTotal,
                'order' => $order->fresh(['items']),
                'upi_string' => $upiString,
            ]);
        }

        // Case 2: Claimed before ordering (voucher for checkout)
        $signature = hash_hmac('sha256', "{$restaurant->id}:{$phone}:{$matchedReward['id']}", config('app.key'));

        return response()->json([
            'success' => true,
            'message' => "🎉 Congratulations! You won {$matchedReward['title']}!",
            'reward' => $matchedReward,
            'voucher' => [
                'reward_id' => $matchedReward['id'],
                'title' => $matchedReward['title'],
                'type' => $matchedReward['type'],
                'value' => $matchedReward['value'],
                'phone' => $phone,
                'signature' => $signature,
            ],
        ]);
    }

    /**
     * Calculate visits, distinct dates, and available tickets for a phone number.
     *
     * @return array{visit_count: int, available_tickets: int, can_play: bool, visits_remaining: int, current_cycle_visits: int, visit_dates: array, claimed_count: int}
     */
    protected function calculateCustomerLoyalty(Restaurant $restaurant, string $phone, int $requiredVisits): array
    {
        $driver = DB::connection()->getDriverName();
        $dateExpr = $driver === 'sqlite' ? "strftime('%Y-%m-%d', created_at)" : 'DATE(created_at)';

        // Get distinct visit dates and the latest order on each date
        $orders = Order::where('restaurant_id', $restaurant->id)
            ->where('customer_phone', $phone)
            ->where('order_status', '!=', 'cancelled')
            ->select(['id', 'order_code', 'total_amount', 'created_at', DB::raw("{$dateExpr} as visit_date")])
            ->orderBy('created_at', 'asc')
            ->get();

        $distinctDatesMap = [];
        foreach ($orders as $ord) {
            $vDate = $ord->visit_date ?: Carbon::parse($ord->created_at)->format('Y-m-d');
            if (! isset($distinctDatesMap[$vDate])) {
                $distinctDatesMap[$vDate] = [
                    'date' => $vDate,
                    'formatted_date' => Carbon::parse($vDate)->format('d M Y'),
                    'order_code' => $ord->order_code,
                    'amount' => $ord->total_amount,
                ];
            }
        }

        $visitDates = array_values($distinctDatesMap);
        $visitCount = count($visitDates);

        $claimedCount = (int) Order::where('restaurant_id', $restaurant->id)
            ->where('customer_phone', $phone)
            ->where('loyalty_reward_claimed', true)
            ->count();

        $totalEarnedTickets = (int) floor($visitCount / max(1, $requiredVisits));
        $availableTickets = max(0, $totalEarnedTickets - $claimedCount);

        $currentCycleVisits = $visitCount % max(1, $requiredVisits);
        $visitsRemaining = $availableTickets > 0 ? 0 : max(0, $requiredVisits - $currentCycleVisits);

        return [
            'phone' => $phone,
            'visit_count' => $visitCount,
            'required_visits' => $requiredVisits,
            'available_tickets' => $availableTickets,
            'can_play' => $availableTickets > 0 && ($restaurant->loyalty_enabled ?? true),
            'visits_remaining' => $visitsRemaining,
            'current_cycle_visits' => $currentCycleVisits,
            'visit_dates' => $visitDates,
            'claimed_count' => $claimedCount,
        ];
    }

    /**
     * Calculate discount amount according to reward specification.
     */
    protected function calculateDiscount(float $subtotal, array $reward): float
    {
        $type = $reward['type'] ?? 'flat';
        $val = (float) ($reward['value'] ?? 0);

        if ($type === 'percent') {
            return round(($subtotal * min(100, $val)) / 100, 2);
        }

        if ($type === 'flat') {
            return min($subtotal, $val);
        }

        if ($type === 'bogo' || $type === 'free_item') {
            return min($subtotal, $val ?: 100);
        }

        return 0.0;
    }
}
