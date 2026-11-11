<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\CustomerFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerFeedbackController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $restaurant = $request->user()->restaurants()->first();

        if (! $restaurant) {
            return response()->json(['data' => []]);
        }

        $feedbacks = CustomerFeedback::where('restaurant_id', $restaurant->id)
            ->latest()
            ->take(50)
            ->get();

        $stats = [
            'total' => CustomerFeedback::where('restaurant_id', $restaurant->id)->count(),
            'average_rating' => round(CustomerFeedback::where('restaurant_id', $restaurant->id)->avg('rating') ?? 0, 1),
            'five_star' => CustomerFeedback::where('restaurant_id', $restaurant->id)->where('rating', 5)->count(),
            'four_star' => CustomerFeedback::where('restaurant_id', $restaurant->id)->where('rating', 4)->count(),
            'below_four' => CustomerFeedback::where('restaurant_id', $restaurant->id)->where('rating', '<', 4)->count(),
        ];

        return response()->json([
            'data' => $feedbacks,
            'stats' => $stats,
        ]);
    }
}
