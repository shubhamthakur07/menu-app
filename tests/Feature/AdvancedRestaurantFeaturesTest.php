<?php

namespace Tests\Feature;

use App\Models\CustomerFeedback;
use App\Models\Restaurant;
use App\Models\TableServiceCall;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedRestaurantFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Restaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->restaurant = Restaurant::create([
            'user_id' => $this->owner->id,
            'name' => 'Royal Dine',
            'slug' => 'royal-dine',
            'currency' => '₹',
            'table_count' => 12,
            'enable_waiter_call' => true,
            'enable_google_reviews' => true,
            'google_review_url' => 'https://g.page/r/royaldine/review',
            'enable_whatsapp_bill' => true,
            'enable_split_bill' => true,
            'enable_voice_search' => true,
            'enable_kds' => true,
            'is_active' => true,
        ]);
    }

    public function test_customer_can_submit_table_service_call(): void
    {
        $response = $this->postJson("/api/r/{$this->restaurant->slug}/service-call", [
            'table_number' => 'Table 4',
            'service_type' => 'water',
            'notes' => 'Please bring cold water',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'call' => [
                    'table_number' => 'Table 4',
                    'service_type' => 'water',
                    'status' => 'pending',
                ],
            ]);

        $this->assertDatabaseHas('table_service_calls', [
            'restaurant_id' => $this->restaurant->id,
            'table_number' => 'Table 4',
            'service_type' => 'water',
            'status' => 'pending',
        ]);
    }

    public function test_service_call_fails_when_feature_disabled(): void
    {
        $this->restaurant->update(['enable_waiter_call' => false]);

        $response = $this->postJson("/api/r/{$this->restaurant->slug}/service-call", [
            'table_number' => 'Table 4',
            'service_type' => 'waiter',
        ]);

        $response->assertStatus(403);
    }

    public function test_owner_can_list_and_update_service_call_status(): void
    {
        $call = TableServiceCall::create([
            'restaurant_id' => $this->restaurant->id,
            'table_number' => 'Table 2',
            'service_type' => 'cutlery',
            'status' => 'pending',
        ]);

        $listResponse = $this->actingAs($this->owner)->getJson('/api/owner/service-calls');
        $listResponse->assertOk()
            ->assertJsonPath('pending_count', 1);

        $patchResponse = $this->actingAs($this->owner)->patchJson("/api/owner/service-calls/{$call->id}/status", [
            'status' => 'attended',
        ]);

        $patchResponse->assertOk()
            ->assertJsonPath('call.status', 'attended');

        $this->assertDatabaseHas('table_service_calls', [
            'id' => $call->id,
            'status' => 'attended',
        ]);
    }

    public function test_owner_can_clear_completed_calls(): void
    {
        TableServiceCall::create([
            'restaurant_id' => $this->restaurant->id,
            'table_number' => 'Table 1',
            'service_type' => 'water',
            'status' => 'completed',
        ]);

        TableServiceCall::create([
            'restaurant_id' => $this->restaurant->id,
            'table_number' => 'Table 2',
            'service_type' => 'bill',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->owner)->deleteJson('/api/owner/service-calls/clear-completed');
        $response->assertOk();

        $this->assertDatabaseMissing('table_service_calls', [
            'table_number' => 'Table 1',
        ]);
        $this->assertDatabaseHas('table_service_calls', [
            'table_number' => 'Table 2',
        ]);
    }

    public function test_customer_5_star_feedback_redirects_to_google(): void
    {
        $response = $this->postJson("/api/r/{$this->restaurant->slug}/feedback", [
            'rating' => 5,
            'comment' => 'Amazing food and fast service!',
            'customer_name' => 'Rahul Sharma',
            'customer_phone' => '9876543210',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'redirect_to_google' => true,
                'google_review_url' => 'https://g.page/r/royaldine/review',
            ]);

        $this->assertDatabaseHas('customer_feedbacks', [
            'restaurant_id' => $this->restaurant->id,
            'rating' => 5,
            'redirected_to_google' => true,
        ]);
    }

    public function test_customer_low_rating_feedback_kept_private(): void
    {
        $response = $this->postJson("/api/r/{$this->restaurant->slug}/feedback", [
            'rating' => 2,
            'comment' => 'Paneer was too spicy and soup was cold',
            'customer_name' => 'Amit Kumar',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'redirect_to_google' => false,
                'google_review_url' => null,
            ]);

        $this->assertDatabaseHas('customer_feedbacks', [
            'restaurant_id' => $this->restaurant->id,
            'rating' => 2,
            'redirected_to_google' => false,
        ]);
    }

    public function test_feedback_fails_when_feature_disabled(): void
    {
        $this->restaurant->update(['enable_google_reviews' => false]);

        $response = $this->postJson("/api/r/{$this->restaurant->slug}/feedback", [
            'rating' => 5,
        ]);

        $response->assertStatus(403);
    }

    public function test_owner_can_list_feedbacks_and_view_stats(): void
    {
        CustomerFeedback::create([
            'restaurant_id' => $this->restaurant->id,
            'rating' => 5,
            'comment' => 'Loved it',
            'redirected_to_google' => true,
        ]);

        CustomerFeedback::create([
            'restaurant_id' => $this->restaurant->id,
            'rating' => 1,
            'comment' => 'Slow service',
            'redirected_to_google' => false,
        ]);

        $response = $this->actingAs($this->owner)->getJson('/api/owner/feedbacks');
        $response->assertOk()
            ->assertJsonPath('stats.total', 2)
            ->assertJsonPath('stats.five_star', 1)
            ->assertJsonPath('stats.below_four', 1)
            ->assertJsonPath('stats.average_rating', 3);
    }

    public function test_owner_can_toggle_advanced_features_in_settings(): void
    {
        $response = $this->actingAs($this->owner)->putJson('/api/owner/restaurant', [
            'name' => 'Royal Dine Updated',
            'currency' => '₹',
            'table_count' => 15,
            'tax_percentage' => 5.0,
            'enable_waiter_call' => false,
            'enable_google_reviews' => true,
            'google_review_url' => 'https://maps.app.goo.gl/example',
            'enable_whatsapp_bill' => false,
            'enable_split_bill' => false,
            'enable_voice_search' => true,
            'enable_kds' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('restaurant.enable_waiter_call', false)
            ->assertJsonPath('restaurant.enable_whatsapp_bill', false)
            ->assertJsonPath('restaurant.enable_split_bill', false)
            ->assertJsonPath('restaurant.google_review_url', 'https://maps.app.goo.gl/example');

        $this->assertDatabaseHas('restaurants', [
            'id' => $this->restaurant->id,
            'enable_waiter_call' => false,
            'enable_whatsapp_bill' => false,
        ]);
    }
}
