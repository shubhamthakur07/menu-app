<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicMenuController extends Controller
{
    /**
     * Get restaurant details and menu by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $restaurant = Restaurant::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'tables' => function ($tQuery) {
                    $tQuery->where('is_active', true)->orderBy('name');
                },
                'categories' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order')
                        ->with(['menuItems' => function ($itemQuery) {
                            $itemQuery->orderBy('is_recommended', 'desc')
                                ->orderBy('name');
                        }]);
                }
            ])
            ->first();

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found or currently inactive.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'restaurant' => $restaurant,
        ]);
    }

    /**
     * List all active restaurants (for explore / directory).
     */
    public function list(Request $request): JsonResponse
    {
        $query = Restaurant::where('is_active', true);

        if ($request->filled('business_type') && $request->business_type !== 'all') {
            $query->where('business_type', $request->business_type);
        }

        $restaurants = $query->withCount(['menuItems' => function ($q) {
                $q->where('is_available', true);
            }])
            ->get();

        return response()->json([
            'success' => true,
            'restaurants' => $restaurants,
        ]);
    }

    /**
     * Get available booking slots for a business (Salon / Clinic / Services).
     */
    public function getSlots(Request $request, string $slug): JsonResponse
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();
        $date = $request->query('date', date('Y-m-d'));

        $openTimeStr = $restaurant->opening_time ?: '09:00';
        $closeTimeStr = $restaurant->closing_time ?: '21:00';
        $slotDuration = $restaurant->slot_duration_minutes ?: 30;

        // Generate intervals between openTime and closeTime
        $slots = [];
        $current = strtotime("$date $openTimeStr");
        $end = strtotime("$date $closeTimeStr");

        // Existing bookings for this date
        $existingBookings = $restaurant->orders()
            ->whereDate('booking_date', $date)
            ->whereNotIn('order_status', ['cancelled'])
            ->get();

        // Total chairs / service points
        $chairs = $restaurant->tables()->where('is_active', true)->get();
        $totalChairs = max($chairs->count(), 1);

        while ($current < $end) {
            $timeSlotStr = date('H:i', $current);
            $displayTime = date('h:i A', $current);

            // Count bookings at this timeSlot
            $bookedAtSlot = $existingBookings->where('booking_time', $timeSlotStr);
            $bookedCount = $bookedAtSlot->count();
            $isAvailable = $bookedCount < $totalChairs;

            // If date is today and time has already passed
            if ($date === date('Y-m-d') && $current < time()) {
                $isAvailable = false;
                $isPast = true;
            } else {
                $isPast = false;
            }

            $slots[] = [
                'time' => $timeSlotStr,
                'display_time' => $displayTime,
                'is_available' => $isAvailable,
                'is_past' => $isPast,
                'booked_count' => $bookedCount,
                'available_chairs' => max(0, $totalChairs - $bookedCount),
            ];

            $current += ($slotDuration * 60);
        }

        // Live Queue Token Status for today
        $todayTokens = $restaurant->orders()
            ->whereDate('booking_date', $date)
            ->whereNotNull('token_number')
            ->orderBy('token_number')
            ->get();

        $completedCount = $todayTokens->whereIn('order_status', ['completed', 'served'])->count();
        $inProgressToken = $todayTokens->whereIn('order_status', ['preparing', 'ready'])->first();

        return response()->json([
            'success' => true,
            'date' => $date,
            'slot_duration_minutes' => $slotDuration,
            'total_chairs' => $totalChairs,
            'chairs' => $chairs,
            'slots' => $slots,
            'queue' => [
                'total_booked_today' => $todayTokens->count(),
                'completed_count' => $completedCount,
                'now_serving_token' => $inProgressToken ? $inProgressToken->token_number : ($completedCount > 0 ? $completedCount + 1 : 1),
            ],
        ]);
    }
}
