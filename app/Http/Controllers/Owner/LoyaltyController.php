<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
    /**
     * Get loyalty program settings, reward tiers, and customer analytics.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();

        // Customer analytics grouped by phone
        $driver = DB::connection()->getDriverName();
        $dateExpression = $driver === 'sqlite' ? "strftime('%Y-%m-%d', created_at)" : 'DATE(created_at)';

        $customers = Order::where('restaurant_id', $restaurant->id)
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->where('order_status', '!=', 'cancelled')
            ->select([
                'customer_phone',
                DB::raw('MAX(customer_name) as customer_name'),
                DB::raw('COUNT(id) as total_orders'),
                DB::raw("COUNT(DISTINCT {$dateExpression}) as distinct_visits"),
                DB::raw('SUM(total_amount) as total_spent'),
                DB::raw('SUM(CASE WHEN loyalty_reward_claimed = 1 THEN 1 ELSE 0 END) as rewards_claimed'),
                DB::raw('MAX(created_at) as last_visit'),
            ])
            ->groupBy('customer_phone')
            ->orderByDesc('distinct_visits')
            ->limit(50)
            ->get();

        $stats = [
            'total_customers' => $customers->count(),
            'total_loyalty_claims' => (int) Order::where('restaurant_id', $restaurant->id)->where('loyalty_reward_claimed', true)->count(),
            'total_loyalty_discount' => (float) Order::where('restaurant_id', $restaurant->id)->where('loyalty_reward_claimed', true)->sum('discount_amount'),
        ];

        return response()->json([
            'success' => true,
            'loyalty_enabled' => (bool) ($restaurant->loyalty_enabled ?? true),
            'loyalty_required_visits' => (int) ($restaurant->loyalty_required_visits ?: 5),
            'loyalty_game_type' => $restaurant->loyalty_game_type ?: 'spinner',
            'rewards' => $restaurant->getEffectiveLoyaltyRewards(),
            'customers' => $customers,
            'stats' => $stats,
        ]);
    }

    /**
     * Update loyalty program configuration and custom rewards list.
     */
    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();

        $validated = $request->validate([
            'loyalty_enabled' => 'required|boolean',
            'loyalty_required_visits' => 'required|integer|min:1|max:50',
            'loyalty_game_type' => 'required|string|in:spinner,scratch,both',
            'rewards' => 'required|array|min:2|max:12',
            'rewards.*.id' => 'required',
            'rewards.*.title' => 'required|string|max:100',
            'rewards.*.type' => 'required|string|in:flat,percent,bogo,free_item',
            'rewards.*.value' => 'required|numeric|min:0',
            'rewards.*.color' => 'nullable|string|max:30',
            'rewards.*.icon' => 'nullable|string|max:50',
        ]);

        $restaurant->update([
            'loyalty_enabled' => $validated['loyalty_enabled'],
            'loyalty_required_visits' => $validated['loyalty_required_visits'],
            'loyalty_game_type' => $validated['loyalty_game_type'],
            'loyalty_rewards' => $validated['rewards'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loyalty program settings updated successfully!',
            'restaurant' => $restaurant->fresh(),
            'rewards' => $restaurant->getEffectiveLoyaltyRewards(),
        ]);
    }
}
