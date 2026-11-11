<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestaurantController extends Controller
{
    /**
     * Get owner's current restaurant with live stats.
     */
    public function show(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $restaurant = $user->restaurants()
            ->withCount(['categories', 'menuItems', 'orders'])
            ->first();

        if (! $restaurant) {
            return response()->json(['message' => 'No restaurant found for this account.'], 404);
        }

        // Live stats
        $todayOrders = $restaurant->orders()->whereDate('created_at', today())->get();
        $stats = [
            'total_sales' => (float) $restaurant->orders()->where('payment_status', 'paid')->sum('total_amount'),
            'today_sales' => (float) $todayOrders->where('payment_status', 'paid')->sum('total_amount'),
            'today_orders_count' => $todayOrders->count(),
            'pending_orders_count' => $restaurant->orders()->whereIn('order_status', ['pending', 'preparing'])->count(),
        ];

        return response()->json([
            'success' => true,
            'restaurant' => $restaurant,
            'stats' => $stats,
        ]);
    }

    /**
     * Update restaurant settings (UPI payment info, tables, profile, tax).
     */
    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'business_type' => 'nullable|in:food,salon,retail',
            'tagline' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'currency' => 'required|string|max:10',
            'logo_url' => 'nullable|string',
            'cover_url' => 'nullable|string',
            'upi_id' => 'nullable|string|max:100',
            'upi_name' => 'nullable|string|max:150',
            'table_count' => 'required|integer|min:1|max:200',
            'slot_duration_minutes' => 'nullable|integer|min:10|max:120',
            'opening_time' => 'nullable|string|max:10',
            'closing_time' => 'nullable|string|max:10',
            'tax_percentage' => 'required|numeric|min:0|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'geofence_radius_km' => 'nullable|numeric|min:0.5|max:100',
            'enable_online_payment' => 'boolean',
            'enable_cash_payment' => 'boolean',
            'enable_home_delivery' => 'boolean',
            'enable_takeaway' => 'boolean',
            'allow_shared_table_orders' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $restaurant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Restaurant settings updated successfully!',
            'restaurant' => $restaurant->fresh(),
        ]);
    }
}
