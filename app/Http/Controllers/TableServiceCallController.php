<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\TableServiceCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableServiceCallController extends Controller
{
    public function store(Request $request, string $slug): JsonResponse
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        if (! $restaurant->enable_waiter_call) {
            return response()->json([
                'success' => false,
                'message' => 'Table service calls are currently disabled for this restaurant.',
            ], 403);
        }

        $validated = $request->validate([
            'table_number' => 'required|string|max:50',
            'service_type' => 'required|string|in:waiter,water,cutlery,bill,cleaning,other',
            'notes' => 'nullable|string|max:255',
        ]);

        $call = TableServiceCall::create([
            'restaurant_id' => $restaurant->id,
            'table_number' => $validated['table_number'],
            'service_type' => $validated['service_type'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Staff notified! A waiter will attend your table shortly.',
            'call' => $call,
        ], 201);
    }
}
