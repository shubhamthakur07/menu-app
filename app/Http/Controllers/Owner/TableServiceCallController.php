<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\TableServiceCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableServiceCallController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $restaurant = $request->user()->restaurants()->first();

        if (! $restaurant) {
            return response()->json(['data' => []]);
        }

        $query = TableServiceCall::where('restaurant_id', $restaurant->id)->latest();

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $calls = $query->take(50)->get();

        return response()->json([
            'data' => $calls,
            'pending_count' => TableServiceCall::where('restaurant_id', $restaurant->id)->where('status', 'pending')->count(),
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $restaurant = $request->user()->restaurants()->first();

        if (! $restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }

        $call = TableServiceCall::where('restaurant_id', $restaurant->id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:pending,attended,completed',
        ]);

        $call->status = $validated['status'];
        if ($validated['status'] === 'attended' && ! $call->attended_at) {
            $call->attended_at = now();
        }
        $call->save();

        return response()->json([
            'success' => true,
            'message' => 'Service call updated',
            'call' => $call,
        ]);
    }

    public function clearCompleted(Request $request): JsonResponse
    {
        $restaurant = $request->user()->restaurants()->first();

        if (! $restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }

        TableServiceCall::where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['attended', 'completed'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Completed calls cleared',
        ]);
    }
}
