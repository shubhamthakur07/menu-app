<?php

namespace App\Http\Controllers;

use App\Models\CustomerFeedback;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerFeedbackController extends Controller
{
    public function store(Request $request, string $slug): JsonResponse
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        if (! $restaurant->enable_google_reviews) {
            return response()->json([
                'success' => false,
                'message' => 'Reviews and feedback are currently disabled for this restaurant.',
            ], 403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'order_code' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:30',
        ]);

        $isPositive = $validated['rating'] >= 4;
        $googleUrl = $restaurant->google_review_url;

        $feedback = CustomerFeedback::create([
            'restaurant_id' => $restaurant->id,
            'order_code' => $validated['order_code'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'redirected_to_google' => $isPositive && ! empty($googleUrl),
        ]);

        return response()->json([
            'success' => true,
            'message' => $isPositive ? 'Thank you so much for the 5-star rating!' : 'Thank you for your valuable feedback. Our management will review it!',
            'redirect_to_google' => $isPositive && ! empty($googleUrl),
            'google_review_url' => $isPositive ? $googleUrl : null,
            'feedback' => $feedback,
        ], 201);
    }
}
