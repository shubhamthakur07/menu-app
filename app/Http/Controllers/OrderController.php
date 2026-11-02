<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Customer places a new order (Dine-In, Home Delivery, or Takeaway).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'order_type' => 'nullable|in:dine_in,delivery,takeaway',
            'table_number' => 'nullable|string|max:50',
            'booking_date' => 'nullable|date',
            'booking_time' => 'nullable|string|max:20',
            'chair_or_specialist' => 'nullable|string|max:100',
            'delivery_address' => 'nullable|string|max:500',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'special_notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:upi,card,cash',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $orderType = $validated['order_type'] ?? 'dine_in';

        // Home delivery requires address
        if ($orderType === 'delivery' && empty(trim($validated['delivery_address'] ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide your home delivery address.',
            ], 422);
        }

        $restaurant = Restaurant::findOrFail($validated['restaurant_id']);

        if (!$restaurant->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This restaurant is currently closed.',
            ], 422);
        }

        // Check if Dine-In table already has an active order and restaurant disallows multiple orders
        $existingActiveCount = 0;
        if ($orderType === 'dine_in' && !empty($validated['table_number'])) {
            $existingActiveCount = Order::where('restaurant_id', $restaurant->id)
                ->where('table_number', $validated['table_number'])
                ->whereIn('order_status', ['pending', 'accepted', 'preparing', 'ready'])
                ->count();

            if ($existingActiveCount > 0 && !$restaurant->allow_shared_table_orders) {
                return response()->json([
                    'success' => false,
                    'message' => "{$validated['table_number']} already has an active order and is occupied. Please select another available table.",
                ], 422);
            }
        }

        // Validate payment method against restaurant configuration
        if ($validated['payment_method'] === 'upi' && !$restaurant->enable_online_payment) {
            return response()->json([
                'success' => false,
                'message' => 'Online UPI payment is currently disabled by this restaurant. Please select Pay at Counter.',
            ], 422);
        }

        if ($validated['payment_method'] === 'cash' && !$restaurant->enable_cash_payment && $restaurant->enable_online_payment) {
            return response()->json([
                'success' => false,
                'message' => 'Pay at counter is currently disabled. Please select UPI / Online payment.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate subtotal from DB
            $subtotal = 0;
            $orderItemsData = [];

            foreach ($validated['items'] as $itemData) {
                $menuItem = MenuItem::where('restaurant_id', $restaurant->id)
                    ->where('id', $itemData['id'])
                    ->firstOrFail();

                $itemQty = (int) $itemData['quantity'];
                $itemSubtotal = $menuItem->price * $itemQty;
                $subtotal += $itemSubtotal;

                $orderItemsData[] = [
                    'menu_item_id' => $menuItem->id,
                    'item_name' => $menuItem->name,
                    'item_price' => $menuItem->price,
                    'quantity' => $itemQty,
                    'subtotal' => $itemSubtotal,
                ];
            }

            // Calculate tax and total
            $taxPercentage = $restaurant->tax_percentage ?? 0;
            $taxAmount = round(($subtotal * $taxPercentage) / 100, 2);
            $totalAmount = round($subtotal + $taxAmount, 2);

            $orderCode = 'ORD-' . strtoupper(Str::random(3)) . rand(100, 999);

            $tokenNumber = null;
            $bookingDate = $validated['booking_date'] ?? null;
            $bookingTime = $validated['booking_time'] ?? null;
            $chairOrSpecialist = $validated['chair_or_specialist'] ?? null;

            if ($restaurant->business_type === 'salon' || !empty($bookingDate) || !empty($bookingTime)) {
                $targetDate = $bookingDate ?: date('Y-m-d');
                $tokenNumber = (int) (Order::where('restaurant_id', $restaurant->id)
                    ->whereDate('booking_date', $targetDate)
                    ->max('token_number')) + 1;

                if (!$bookingDate) {
                    $bookingDate = $targetDate;
                }
                if (!$chairOrSpecialist) {
                    $chairOrSpecialist = $validated['table_number'] ?? 'Chair 1';
                }
            }

            $tableLabel = null;
            if ($restaurant->business_type === 'salon') {
                $tableLabel = $chairOrSpecialist ?: ($validated['table_number'] ?? 'Chair 1');
            } elseif ($orderType === 'dine_in') {
                $tableLabel = $validated['table_number'] ?? 'Table 1';
            } elseif ($orderType === 'delivery') {
                $tableLabel = 'Home Delivery';
            } else {
                $tableLabel = 'Takeaway / Self-Pickup';
            }

            $order = Order::create([
                'order_code' => $orderCode,
                'restaurant_id' => $restaurant->id,
                'order_type' => $orderType,
                'table_number' => $tableLabel,
                'booking_date' => $bookingDate,
                'booking_time' => $bookingTime,
                'chair_or_specialist' => $chairOrSpecialist,
                'token_number' => $tokenNumber,
                'delivery_address' => $validated['delivery_address'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'special_notes' => $validated['special_notes'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'cash' ? 'pending' : 'pending',
                'order_status' => 'pending',
            ]);

            foreach ($orderItemsData as $item) {
                $order->items()->create($item);
            }

            // If dine_in: link and mark table as occupied in restaurant_tables
            if ($orderType === 'dine_in' && !empty($validated['table_number'])) {
                $table = $restaurant->tables()->where('name', $validated['table_number'])->first();
                if ($table) {
                    $table->update([
                        'status' => 'occupied',
                        'active_order_id' => $order->id,
                    ]);
                }
            }

            DB::commit();

            // Generate UPI payment intent string if payment method is UPI
            $upiString = null;
            if ($restaurant->upi_id) {
                $upiPayeeName = urlencode($restaurant->upi_name ?: $restaurant->name);
                $upiId = $restaurant->upi_id;
                $note = urlencode("Order " . $order->order_code);
                $upiString = "upi://pay?pa={$upiId}&pn={$upiPayeeName}&am={$totalAmount}&cu=INR&tn={$note}";
            }

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully!',
                'order' => $order->load('items', 'restaurant'),
                'upi_string' => $upiString,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order tracking details by order_code.
     */
    public function show(string $orderCode): JsonResponse
    {
        $order = Order::where('order_code', $orderCode)
            ->with(['items', 'restaurant'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $upiString = null;
        if ($order->restaurant && $order->restaurant->upi_id && $order->payment_status !== 'paid') {
            $upiPayeeName = urlencode($order->restaurant->upi_name ?: $order->restaurant->name);
            $upiId = $order->restaurant->upi_id;
            $note = urlencode("Order " . $order->order_code);
            $upiString = "upi://pay?pa={$upiId}&pn={$upiPayeeName}&am={$order->total_amount}&cu=INR&tn={$note}";
        }

        return response()->json([
            'success' => true,
            'order' => $order,
            'upi_string' => $upiString,
        ]);
    }

    /**
     * Verify/record customer payment transaction.
     */
    public function recordPayment(Request $request, string $orderCode): JsonResponse
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();

        $validated = $request->validate([
            'transaction_id' => 'nullable|string|max:100',
            'payment_status' => 'required|in:paid,pending,failed',
        ]);

        $order->update([
            'payment_status' => $validated['payment_status'],
            'transaction_id' => $validated['transaction_id'] ?? ('TXN-' . strtoupper(Str::random(10))),
            'order_status' => $order->order_status === 'pending' ? 'accepted' : $order->order_status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully!',
            'order' => $order->fresh()->load('items', 'restaurant'),
        ]);
    }

    /**
     * Apply 5% Mind Game Winner discount reward to an order.
     */
    public function applyGameDiscount(Request $request, string $orderCode): JsonResponse
    {
        $order = Order::where('order_code', $orderCode)
            ->with(['items', 'restaurant'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        if ($order->game_reward_claimed) {
            return response()->json([
                'success' => false,
                'message' => '5% Mind Game discount has already been applied to this order.',
                'order' => $order,
            ], 400);
        }

        // Calculate 5% discount on subtotal
        $discountPercent = 5;
        $discountAmount = round(($order->subtotal * $discountPercent) / 100, 2);

        // Recalculate tax and total
        $taxPercentage = $order->restaurant->tax_percentage ?? 0;
        $taxableAmount = max(0, $order->subtotal - $discountAmount);
        $taxAmount = round(($taxableAmount * $taxPercentage) / 100, 2);
        $newTotal = round($taxableAmount + $taxAmount, 2);

        $order->update([
            'discount_amount' => $discountAmount,
            'discount_reason' => 'Mind Game Winner 5% Discount 🏆',
            'tax_amount' => $taxAmount,
            'total_amount' => $newTotal,
            'game_reward_claimed' => true,
        ]);

        $upiString = null;
        if ($order->restaurant && $order->restaurant->upi_id && $order->payment_status !== 'paid') {
            $upiPayeeName = urlencode($order->restaurant->upi_name ?: $order->restaurant->name);
            $upiId = $order->restaurant->upi_id;
            $note = urlencode("Order " . $order->order_code);
            $upiString = "upi://pay?pa={$upiId}&pn={$upiPayeeName}&am={$newTotal}&cu=INR&tn={$note}";
        }

        return response()->json([
            'success' => true,
            'message' => '🎉 5% Mind Game discount applied successfully!',
            'discount_amount' => $discountAmount,
            'new_total' => $newTotal,
            'order' => $order->fresh(['items', 'restaurant']),
            'upi_string' => $upiString,
        ]);
    }
}
