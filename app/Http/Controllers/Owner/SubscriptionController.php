<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\RestaurantSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Get current restaurant's subscription details and available plans
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $restaurant = $user->restaurants()->first();

        if (! $restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found.',
            ], 404);
        }

        // Available plans for upgrade / selection
        $availablePlans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $activeSubscription = $restaurant->currentSubscription;

        // Calculate days left in trial or active subscription
        $daysRemaining = 0;
        $isTrial = $restaurant->subscription_status === 'trial';

        if ($activeSubscription) {
            $daysRemaining = $activeSubscription->daysRemaining();
            $isTrial = $activeSubscription->status === 'trial' || $activeSubscription->billing_cycle === 'trial';
        } elseif ($restaurant->trial_ends_at) {
            $daysRemaining = max(0, (int) now()->diffInDays($restaurant->trial_ends_at, false));
        }

        // Feature definitions map for comparison table
        $featureDefinitions = SubscriptionPlan::defaultFeatureDefinitions();

        return response()->json([
            'success' => true,
            'restaurant' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'subscription_status' => $restaurant->subscription_status,
                'subscription_plan' => $restaurant->subscription_plan,
                'trial_ends_at' => $restaurant->trial_ends_at?->toIso8601String(),
                'subscription_ends_at' => $restaurant->subscription_ends_at?->toIso8601String(),
                'days_remaining' => $daysRemaining,
                'is_trial' => $isTrial,
                'is_expired' => $daysRemaining <= 0,
            ],
            'active_subscription' => $activeSubscription,
            'available_plans' => $availablePlans,
            'feature_definitions' => $featureDefinitions,
        ]);
    }

    /**
     * Owner selects a plan to upgrade/subscribe
     */
    public function selectPlan(Request $request): JsonResponse
    {
        $user = $request->user();
        $restaurant = $user->restaurants()->first();

        if (! $restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found.',
            ], 404);
        }

        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'cycle' => 'required|string|in:monthly,yearly',
            'payment_method' => 'nullable|string|in:upi,cash,manual',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $cycle = $validated['cycle'];
        $price = $cycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $durationDays = $cycle === 'yearly' ? 365 : 30;

        $startsAt = now();
        $endsAt = now()->addDays($durationDays);

        // Snapshot of features locked into this cycle (Grandfathering)
        $sub = RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'subscription_plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'plan_price' => $price,
            'billing_cycle' => $cycle,
            'features_snapshot' => $plan->features,
            'status' => 'active',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'payment_method' => $validated['payment_method'] ?? 'upi',
            'notes' => 'Subscribed by Restaurant Owner',
        ]);

        $updateData = [
            'subscription_plan' => $plan->slug,
            'subscription_status' => 'active',
            'subscription_ends_at' => $endsAt,
            'current_subscription_id' => $sub->id,
        ];

        // Apply features
        if (isset($plan->features['enable_waiter_call'])) {
            $updateData['enable_waiter_call'] = (bool) $plan->features['enable_waiter_call'];
        }
        if (isset($plan->features['enable_kds'])) {
            $updateData['enable_kds'] = (bool) $plan->features['enable_kds'];
        }
        if (isset($plan->features['enable_google_reviews'])) {
            $updateData['enable_google_reviews'] = (bool) $plan->features['enable_google_reviews'];
        }
        if (isset($plan->features['enable_loyalty'])) {
            $updateData['loyalty_enabled'] = (bool) $plan->features['enable_loyalty'];
        }
        if (isset($plan->features['enable_voice_search'])) {
            $updateData['enable_voice_search'] = (bool) $plan->features['enable_voice_search'];
        }
        if (isset($plan->features['enable_split_bill'])) {
            $updateData['enable_split_bill'] = (bool) $plan->features['enable_split_bill'];
        }

        $restaurant->update($updateData);

        return response()->json([
            'success' => true,
            'message' => "Congratulations! You have upgraded to {$plan->name} ({$cycle}) successfully!",
            'subscription' => $sub,
        ]);
    }
}
