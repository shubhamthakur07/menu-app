<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    /**
     * List all subscription plans and feature definitions
     */
    public function index(): JsonResponse
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        $definitions = SubscriptionPlan::defaultFeatureDefinitions();

        return response()->json([
            'success' => true,
            'plans' => $plans,
            'feature_definitions' => $definitions,
        ]);
    }

    /**
     * Create a new custom subscription plan
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:255',
            'badge_text' => 'nullable|string|max:50',
            'features' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (SubscriptionPlan::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $plan = SubscriptionPlan::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'price_monthly' => $validated['price_monthly'],
            'price_yearly' => $validated['price_yearly'],
            'trial_days' => $validated['trial_days'] ?? 30,
            'description' => $validated['description'] ?? null,
            'badge_text' => $validated['badge_text'] ?? null,
            'features' => $validated['features'],
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => SubscriptionPlan::max('sort_order') + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan created successfully!',
            'plan' => $plan,
        ], 201);
    }

    /**
     * Update an existing plan template.
     * Note: Grandfathering ensures existing active subscriptions KEEP their current snapshot!
     * Only new subscriptions or renewals will receive these updated features/pricing.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'price_monthly' => 'sometimes|numeric|min:0',
            'price_yearly' => 'sometimes|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:255',
            'badge_text' => 'nullable|string|max:50',
            'features' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $plan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Plan updated! Existing subscribers will continue on their current plan snapshot until their next renewal cycle.',
            'plan' => $plan,
        ]);
    }

    /**
     * Delete or deactivate a plan
     */
    public function destroy(int $id): JsonResponse
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan deleted.',
        ]);
    }

    /**
     * View all restaurant subscribers, trial status, and days remaining
     */
    public function subscribers(): JsonResponse
    {
        $restaurants = Restaurant::with(['currentSubscription', 'user'])
            ->latest()
            ->get()
            ->map(function ($r) {
                $sub = $r->currentSubscription;

                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'slug' => $r->slug,
                    'owner_name' => $r->user?->name,
                    'owner_email' => $r->user?->email,
                    'owner_phone' => $r->user?->phone,
                    'subscription_status' => $r->subscription_status,
                    'subscription_plan' => $r->subscription_plan,
                    'trial_ends_at' => $r->trial_ends_at?->toIso8601String(),
                    'subscription_ends_at' => $r->subscription_ends_at?->toIso8601String(),
                    'days_remaining' => $sub ? $sub->daysRemaining() : ($r->trial_ends_at ? (int) now()->diffInDays($r->trial_ends_at, false) : 0),
                    'active_subscription' => $sub,
                    'created_at' => $r->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'restaurants' => $restaurants,
        ]);
    }

    /**
     * Manually assign, extend, or activate a subscription for a restaurant
     */
    public function assignPlan(Request $request, int $restaurantId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $validated = $request->validate([
            'plan_id' => 'nullable|exists:subscription_plans,id',
            'action' => 'required|string|in:assign,extend_trial,mark_paid',
            'cycle' => 'nullable|string|in:monthly,yearly,trial',
            'custom_days' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $action = $validated['action'];

        if ($action === 'extend_trial') {
            $days = $validated['custom_days'] ?? 30;
            $currentEnd = $restaurant->trial_ends_at && $restaurant->trial_ends_at->isFuture()
                ? $restaurant->trial_ends_at
                : now();
            $newEnd = $currentEnd->copy()->addDays($days);

            $restaurant->update([
                'subscription_status' => 'trial',
                'trial_ends_at' => $newEnd,
            ]);

            if ($restaurant->currentSubscription) {
                $restaurant->currentSubscription->update([
                    'ends_at' => $newEnd,
                    'status' => 'trial',
                    'notes' => 'Trial extended by '.$days.' days by Admin. '.($validated['notes'] ?? ''),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Trial extended by {$days} days successfully!",
                'trial_ends_at' => $newEnd->toIso8601String(),
            ]);
        }

        // For Assign or Mark Paid
        $plan = isset($validated['plan_id'])
            ? SubscriptionPlan::findOrFail($validated['plan_id'])
            : SubscriptionPlan::first();

        $cycle = $validated['cycle'] ?? 'monthly';
        $durationDays = $cycle === 'yearly' ? 365 : 30;
        $price = $cycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $startsAt = now();
        $endsAt = now()->addDays($durationDays);

        // Create new Subscription cycle snapshot (Grandfathering)
        $sub = RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'subscription_plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'plan_price' => $price,
            'billing_cycle' => $cycle,
            'features_snapshot' => $plan->features, // Exact snapshot saved!
            'status' => 'active',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'payment_method' => $action === 'mark_paid' ? 'manual' : 'admin_assigned',
            'notes' => $validated['notes'] ?? 'Activated by Admin',
        ]);

        // Sync individual restaurant feature toggles with the plan snapshot
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
            'message' => "Restaurant successfully assigned to {$plan->name} ({$cycle}) until ".$endsAt->format('d M Y'),
            'subscription' => $sub,
        ]);
    }
}
