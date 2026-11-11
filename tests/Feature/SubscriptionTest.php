<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SubscriptionPlanSeeder::class);
    }

    public function test_admin_can_list_plans_and_feature_definitions(): void
    {
        $response = $this->getJson('/api/admin/plans');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'plans',
                'feature_definitions',
            ]);

        $this->assertCount(3, $response->json('plans'));
    }

    public function test_admin_can_create_custom_plan_with_feature_toggles(): void
    {
        $payload = [
            'name' => 'Custom Cafe Special',
            'price_monthly' => 299,
            'price_yearly' => 2999,
            'trial_days' => 14,
            'description' => 'Custom special plan for small cafes',
            'badge_text' => 'New Special',
            'features' => [
                'enable_qr_menu' => true,
                'enable_pdf_download' => true,
                'enable_waiter_call' => true,
                'enable_kds' => false,
            ],
            'is_active' => true,
        ];

        $response = $this->postJson('/api/admin/plans', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('plan.name', 'Custom Cafe Special');

        $this->assertDatabaseHas('subscription_plans', [
            'name' => 'Custom Cafe Special',
            'price_monthly' => 299,
        ]);
    }

    public function test_grandfathering_existing_subscribers_keep_features_when_plan_is_modified(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        $restaurant = Restaurant::create([
            'user_id' => $user->id,
            'name' => 'Royal Dine',
            'slug' => 'royal-dine',
            'currency' => '₹',
            'table_count' => 12,
            'enable_kds' => true,
            'is_active' => true,
        ]);

        $proPlan = SubscriptionPlan::where('slug', 'vip')->first();

        // 1. Assign VIP plan to restaurant with initial snapshot (KDS = true)
        $sub = RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'subscription_plan_id' => $proPlan->id,
            'plan_name' => $proPlan->name,
            'plan_price' => $proPlan->price_monthly,
            'billing_cycle' => 'monthly',
            'features_snapshot' => ['enable_kds' => true, 'enable_waiter_call' => true],
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);
        $restaurant->update(['current_subscription_id' => $sub->id]);

        $this->assertTrue($restaurant->hasFeature('enable_kds'));

        // 2. Admin drops KDS feature from VIP plan template
        $updatedFeatures = $proPlan->features;
        $updatedFeatures['enable_kds'] = false; // Admin drops KDS
        $proPlan->update(['features' => $updatedFeatures]);

        // 3. Existing subscriber still has KDS enabled (Grandfathered!)
        $restaurant->refresh();
        $this->assertTrue($restaurant->hasFeature('enable_kds'));
    }

    public function test_owner_can_view_subscription_status_and_upgrade_plan(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        $restaurant = Restaurant::create([
            'user_id' => $user->id,
            'name' => 'Trial Bistro',
            'slug' => 'trial-bistro',
            'currency' => '₹',
            'table_count' => 10,
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        $proPlan = SubscriptionPlan::where('slug', 'pro')->first();

        $response = $this->actingAs($user)->getJson('/api/owner/subscription');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('restaurant.subscription_status', 'trial');

        // Owner upgrades to Pro Plan
        $upgradeResponse = $this->actingAs($user)->postJson('/api/owner/subscription/select-plan', [
            'plan_id' => $proPlan->id,
            'cycle' => 'yearly',
            'payment_method' => 'upi',
        ]);

        $upgradeResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $restaurant->refresh();
        $this->assertEquals('active', $restaurant->subscription_status);
        $this->assertEquals('pro', $restaurant->subscription_plan);
    }
}
