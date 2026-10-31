<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();

        $query = $restaurant->menuItems()->with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('food_type')) {
            $query->where('food_type', $request->food_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'food_type' => 'nullable|string|max:50',
            'image_url' => 'nullable|string',
            'is_available' => 'boolean',
            'is_recommended' => 'boolean',
            'prep_time_minutes' => 'nullable|integer|min:1',
            'duration_minutes' => 'nullable|integer|min:5|max:240',
            'target_audience' => 'nullable|string|max:50',
        ]);

        if ($restaurant->business_type === 'salon') {
            $validated['food_type'] = null;
            $validated['duration_minutes'] = $validated['duration_minutes'] ?? 30;
        } elseif ($restaurant->business_type === 'retail') {
            $validated['food_type'] = null;
        } else {
            $validated['food_type'] = $validated['food_type'] ?? 'veg';
        }

        // Verify category belongs to this restaurant
        $restaurant->categories()->findOrFail($validated['category_id']);

        $item = $restaurant->menuItems()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Menu item created successfully!',
            'item' => $item->load('category'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();
        $item = $restaurant->menuItems()->findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'food_type' => 'nullable|string|max:50',
            'image_url' => 'nullable|string',
            'is_available' => 'boolean',
            'is_recommended' => 'boolean',
            'prep_time_minutes' => 'nullable|integer|min:1',
            'duration_minutes' => 'nullable|integer|min:5|max:240',
            'target_audience' => 'nullable|string|max:50',
        ]);

        if ($restaurant->business_type === 'salon') {
            $validated['food_type'] = null;
            $validated['duration_minutes'] = $validated['duration_minutes'] ?? 30;
        } elseif ($restaurant->business_type === 'retail') {
            $validated['food_type'] = null;
        } else {
            $validated['food_type'] = $validated['food_type'] ?? 'veg';
        }

        $restaurant->categories()->findOrFail($validated['category_id']);
        $item->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Menu item updated successfully!',
            'item' => $item->fresh()->load('category'),
        ]);
    }

    public function toggleAvailability(int $id): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();
        $item = $restaurant->menuItems()->findOrFail($id);

        $item->update([
            'is_available' => !$item->is_available,
        ]);

        return response()->json([
            'success' => true,
            'message' => $item->is_available ? 'Item marked as Available' : 'Item marked as Sold Out',
            'item' => $item,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();
        $item = $restaurant->menuItems()->findOrFail($id);

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu item deleted successfully!',
        ]);
    }
}
