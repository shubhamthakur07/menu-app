<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LoyaltyProgramTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Restaurant $restaurant;

    protected Category $category;

    protected MenuItem $menuItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->restaurant = Restaurant::create([
            'user_id' => $this->owner->id,
            'name' => 'Biryani Palace',
            'slug' => 'biryani-palace',
            'currency' => '₹',
            'table_count' => 10,
            'loyalty_enabled' => true,
            'loyalty_required_visits' => 5,
            'loyalty_game_type' => 'spinner',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Main Course',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->menuItem = MenuItem::create([
            'restaurant_id' => $this->restaurant->id,
            'category_id' => $this->category->id,
            'name' => 'Chicken Dum Biryani',
            'price' => 300,
            'duration_minutes' => 30,
            'is_available' => true,
        ]);
    }

    public function test_customer_loyalty_status_with_no_orders(): void
    {
        $response = $this->getJson("/api/r/{$this->restaurant->slug}/loyalty-status?phone=9876543210");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('loyalty_enabled', true)
            ->assertJsonPath('required_visits', 5)
            ->assertJsonPath('customer.visit_count', 0)
            ->assertJsonPath('customer.available_tickets', 0)
            ->assertJsonPath('customer.can_play', false)
            ->assertJsonPath('customer.visits_remaining', 5);
    }

    public function test_customer_earns_ticket_after_five_distinct_date_visits(): void
    {
        $phone = '9876543210';

        // Create 5 orders on 5 distinct dates
        for ($i = 1; $i <= 5; $i++) {
            $createdDate = Carbon::now()->subDays(6 - $i);
            $order = Order::create([
                'order_code' => "ORD-00{$i}",
                'restaurant_id' => $this->restaurant->id,
                'customer_name' => 'Rahul Sharma',
                'customer_phone' => $phone,
                'subtotal' => 300,
                'tax_amount' => 15,
                'total_amount' => 315,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'order_status' => 'completed',
            ]);
            $order->created_at = $createdDate;
            $order->saveQuietly();
        }

        $response = $this->getJson("/api/r/{$this->restaurant->slug}/loyalty-status?phone={$phone}");

        $response->assertOk()
            ->assertJsonPath('customer.visit_count', 5)
            ->assertJsonPath('customer.available_tickets', 1)
            ->assertJsonPath('customer.can_play', true)
            ->assertJsonPath('customer.visits_remaining', 0)
            ->assertJsonCount(5, 'customer.visit_dates');
    }

    public function test_customer_claims_reward_on_existing_order(): void
    {
        $phone = '9876543210';

        // 5 past orders
        for ($i = 1; $i <= 5; $i++) {
            $createdDate = Carbon::now()->subDays(6 - $i);
            $pastOrder = Order::create([
                'order_code' => "ORD-00{$i}",
                'restaurant_id' => $this->restaurant->id,
                'customer_name' => 'Rahul Sharma',
                'customer_phone' => $phone,
                'subtotal' => 300,
                'tax_amount' => 15,
                'total_amount' => 315,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'order_status' => 'completed',
            ]);
            $pastOrder->created_at = $createdDate;
            $pastOrder->saveQuietly();
        }

        // Active order today
        $activeOrder = Order::create([
            'order_code' => 'ORD-TODAY-01',
            'restaurant_id' => $this->restaurant->id,
            'customer_name' => 'Rahul Sharma',
            'customer_phone' => $phone,
            'subtotal' => 500,
            'tax_amount' => 25,
            'total_amount' => 525,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ]);

        $response = $this->postJson("/api/r/{$this->restaurant->slug}/claim-loyalty-reward", [
            'phone' => $phone,
            'reward_id' => 1, // Default reward 1 is ₹100 Flat OFF
            'order_code' => $activeOrder->order_code,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('discount_amount', 100)
            ->assertJsonPath('new_total', 420); // (500 - 100) + 5% tax = 420

        $activeOrder->refresh();
        $this->assertTrue($activeOrder->loyalty_reward_claimed);
        $this->assertEquals(100.0, $activeOrder->discount_amount);
    }

    public function test_ineligible_customer_cannot_claim_reward(): void
    {
        $phone = '9999999999';

        // Only 2 visits
        for ($i = 1; $i <= 2; $i++) {
            Order::create([
                'order_code' => "ORD-TEST-{$i}",
                'restaurant_id' => $this->restaurant->id,
                'customer_name' => 'New User',
                'customer_phone' => $phone,
                'subtotal' => 300,
                'total_amount' => 300,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'order_status' => 'completed',
                'created_at' => Carbon::now()->subDays(3 - $i),
            ]);
        }

        $response = $this->postJson("/api/r/{$this->restaurant->slug}/claim-loyalty-reward", [
            'phone' => $phone,
            'reward_id' => 1,
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_owner_can_get_and_update_loyalty_settings(): void
    {
        // Get settings
        $response = $this->actingAs($this->owner)
            ->getJson('/api/owner/loyalty');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('loyalty_required_visits', 5)
            ->assertJsonPath('loyalty_game_type', 'spinner');

        // Update settings: change required visits to 3, game type to scratch, custom rewards
        $customRewards = [
            ['id' => 1, 'title' => 'Flat ₹200 OFF', 'type' => 'flat', 'value' => 200, 'color' => '#10b981', 'icon' => 'fa-solid fa-gift'],
            ['id' => 2, 'title' => '50% Mega Bumper', 'type' => 'percent', 'value' => 50, 'color' => '#ec4899', 'icon' => 'fa-solid fa-percent'],
            ['id' => 3, 'title' => 'Free Biryani (BOGO)', 'type' => 'bogo', 'value' => 300, 'color' => '#f59e0b', 'icon' => 'fa-solid fa-bowl-rice'],
        ];

        $updateResponse = $this->actingAs($this->owner)
            ->putJson('/api/owner/loyalty', [
                'loyalty_enabled' => true,
                'loyalty_required_visits' => 3,
                'loyalty_game_type' => 'scratch',
                'rewards' => $customRewards,
            ]);

        $updateResponse->assertOk()
            ->assertJsonPath('success', true);

        $this->restaurant->refresh();
        $this->assertEquals(3, $this->restaurant->loyalty_required_visits);
        $this->assertEquals('scratch', $this->restaurant->loyalty_game_type);
        $this->assertCount(3, $this->restaurant->loyalty_rewards);
    }
}
