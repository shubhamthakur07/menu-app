<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();

        $categories = $restaurant->categories()
            ->withCount('menuItems')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $maxOrder = $restaurant->categories()->max('sort_order') ?? 0;
        $validated['sort_order'] = $validated['sort_order'] ?? ($maxOrder + 1);

        $category = $restaurant->categories()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully!',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();
        $category = $restaurant->categories()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully!',
            'category' => $category,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();
        $category = $restaurant->categories()->findOrFail($id);

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully!',
        ]);
    }
}
