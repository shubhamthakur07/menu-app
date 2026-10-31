<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerOrderController extends Controller
{
    /**
     * List all orders for the owner's restaurant.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();

        $query = $restaurant->orders()->with('items');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('order_status', $request->status);
        }

        if ($request->filled('order_type') && $request->order_type !== 'all') {
            $query->where('order_type', $request->order_type);
        }

        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('booking_date')) {
            $query->whereDate('booking_date', $request->booking_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('table_number', 'like', "%{$search}%")
                    ->orWhere('chair_or_specialist', 'like', "%{$search}%")
                    ->orWhere('token_number', 'like', "%{$search}%")
                    ->orWhere('delivery_address', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(30);

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    /**
     * Update order status (accepted, preparing, ready, served, completed, cancelled).
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();
        $order = $restaurant->orders()->findOrFail($id);

        $validated = $request->validate([
            'order_status' => 'nullable|in:pending,accepted,preparing,ready,served,completed,cancelled',
            'payment_status' => 'nullable|in:pending,paid,failed',
        ]);

        $order->update(array_filter($validated));

        // When order is completed or cancelled, automatically free any occupied table!
        if (in_array($order->order_status, ['completed', 'cancelled'])) {
            $restaurant->tables()->where('active_order_id', $order->id)->update([
                'status' => 'available',
                'active_order_id' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully!',
            'order' => $order->fresh()->load('items'),
        ]);
    }
}
