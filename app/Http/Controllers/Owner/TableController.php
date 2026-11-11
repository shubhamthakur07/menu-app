<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableController extends Controller
{
    /**
     * Get all tables with live occupancy and all active orders grouped per table.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();

        $tables = $restaurant->tables()
            ->with(['activeOrders' => function ($q) {
                $q->select('id', 'order_code', 'table_number', 'customer_name', 'customer_phone', 'total_amount', 'order_status', 'created_at')
                    ->latest();
            }])
            ->get()
            ->map(function ($table) {
                $activeOrders = $table->activeOrders;
                $isOccupied = $activeOrders->count() > 0 || $table->status === 'occupied';

                return [
                    'id' => $table->id,
                    'restaurant_id' => $table->restaurant_id,
                    'name' => $table->name,
                    'code' => $table->code,
                    'seating_capacity' => $table->seating_capacity,
                    'status' => $isOccupied ? 'occupied' : 'available',
                    'active_orders' => $activeOrders,
                    'orders_count' => $activeOrders->count(),
                    'table_total_amount' => (float) $activeOrders->sum('total_amount'),
                    'is_active' => $table->is_active,
                ];
            });

        $availableCount = $tables->where('status', 'available')->count();
        $occupiedCount = $tables->where('status', 'occupied')->count();

        return response()->json([
            'success' => true,
            'tables' => $tables,
            'stats' => [
                'total' => $tables->count(),
                'available' => $availableCount,
                'occupied' => $occupiedCount,
            ],
        ]);
    }

    /**
     * Create a single table.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'seating_capacity' => 'nullable|integer|min:1|max:50',
        ]);

        $table = $restaurant->tables()->create([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? $validated['name'],
            'seating_capacity' => $validated['seating_capacity'] ?? 4,
            'status' => 'available',
            'is_active' => true,
        ]);

        $restaurant->update(['table_count' => $restaurant->tables()->count()]);

        return response()->json([
            'success' => true,
            'message' => 'Table added successfully!',
            'table' => $table,
        ], 201);
    }

    /**
     * Bulk generate tables.
     */
    public function bulkGenerate(Request $request): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();

        $validated = $request->validate([
            'count' => 'required|integer|min:1|max:100',
            'prefix' => 'nullable|string|max:30',
            'seating_capacity' => 'nullable|integer|min:1|max:50',
        ]);

        $prefix = trim($validated['prefix'] ?? 'Table');
        $capacity = $validated['seating_capacity'] ?? 4;
        $count = $validated['count'];

        $existingCount = $restaurant->tables()->where('name', 'like', "{$prefix} %")->count();

        $created = [];
        for ($i = 1; $i <= $count; $i++) {
            $num = $existingCount + $i;
            $table = $restaurant->tables()->create([
                'name' => "{$prefix} {$num}",
                'code' => strtoupper(substr($prefix, 0, 1)).$num,
                'seating_capacity' => $capacity,
                'status' => 'available',
                'is_active' => true,
            ]);
            $created[] = $table;
        }

        $restaurant->update(['table_count' => $restaurant->tables()->count()]);

        return response()->json([
            'success' => true,
            'message' => "Successfully generated {$count} new tables!",
            'created_count' => count($created),
        ]);
    }

    /**
     * Update table details.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();
        $table = $restaurant->tables()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'seating_capacity' => 'required|integer|min:1|max:50',
            'status' => 'nullable|in:available,occupied,reserved',
        ]);

        $table->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Table updated successfully!',
            'table' => $table,
        ]);
    }

    /**
     * Vacate table: marks table available and marks uncompleted dine-in orders on that table as completed.
     */
    public function vacate(int $id): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();
        $table = $restaurant->tables()->findOrFail($id);

        $table->update([
            'status' => 'available',
            'active_order_id' => null,
        ]);

        // Complete any open dine-in orders on this table
        Order::where('restaurant_id', $restaurant->id)
            ->where('table_number', $table->name)
            ->whereIn('order_status', ['pending', 'accepted', 'preparing', 'ready'])
            ->update(['order_status' => 'completed']);

        return response()->json([
            'success' => true,
            'message' => "{$table->name} is now vacated & ready for new guests!",
            'table' => $table,
        ]);
    }

    /**
     * Delete table.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();
        $table = $restaurant->tables()->findOrFail($id);

        $table->delete();
        $restaurant->update(['table_count' => $restaurant->tables()->count()]);

        return response()->json([
            'success' => true,
            'message' => 'Table deleted successfully!',
        ]);
    }
}
