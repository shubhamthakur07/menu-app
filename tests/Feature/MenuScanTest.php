<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MenuScanTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Restaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'owner',
        ]);

        $this->restaurant = Restaurant::create([
            'user_id' => $this->user->id,
            'name' => 'The Grand Feast',
            'business_type' => 'restaurant',
            'slug' => 'grand-feast',
            'currency' => '₹',
            'table_count' => 10,
        ]);
    }

    public function test_guest_cannot_access_scan_or_import(): void
    {
        $this->postJson('/api/owner/menu/scan-image')->assertUnauthorized();
        $this->postJson('/api/owner/menu/import-scanned')->assertUnauthorized();
    }

    public function test_demo_menu_scan_returns_sample_categories_and_items(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/owner/menu/scan-image', [
                'demo' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('is_demo', true)
            ->assertJsonStructure([
                'success',
                'categories' => [
                    '*' => [
                        'name',
                        'icon',
                        'items' => [
                            '*' => ['name', 'price', 'food_type'],
                        ],
                    ],
                ],
            ]);
    }

    public function test_scan_requires_image_when_not_in_demo_mode(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/owner/menu/scan-image');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_scan_with_gemini_vision_mock(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'categories' => [
                                            [
                                                'name' => 'Appetizers',
                                                'icon' => 'fa-solid fa-bowl-food',
                                                'items' => [
                                                    [
                                                        'name' => 'Crispy Spring Rolls',
                                                        'description' => 'Served with sweet chili sauce',
                                                        'price' => 180,
                                                        'food_type' => 'veg',
                                                    ],
                                                    [
                                                        'name' => 'Chicken Wings',
                                                        'description' => 'BBQ glazed wings',
                                                        'price' => 260,
                                                        'food_type' => 'non-veg',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $file = UploadedFile::fake()->image('menu_card.jpg', 800, 600);

        $response = $this->actingAs($this->user)
            ->postJson('/api/owner/menu/scan-image', [
                'image' => $file,
                'api_key' => 'fake-gemini-key',
                'provider' => 'gemini',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_items', 2)
            ->assertJsonPath('categories.0.name', 'Appetizers')
            ->assertJsonPath('categories.0.items.0.name', 'Crispy Spring Rolls')
            ->assertJsonPath('categories.0.items.0.price', 180);
    }

    public function test_import_scanned_creates_categories_and_menu_items(): void
    {
        $payload = [
            'categories' => [
                [
                    'name' => 'Pizza & Pasta',
                    'icon' => 'fa-solid fa-pizza-slice',
                    'description' => 'Italian specials',
                    'items' => [
                        [
                            'name' => 'Margherita Pizza',
                            'price' => 299,
                            'food_type' => 'veg',
                            'description' => 'Classic cheese & basil',
                        ],
                        [
                            'name' => 'Chicken Penne Alfredo',
                            'price' => 349,
                            'food_type' => 'non-veg',
                            'description' => 'Creamy parmesan pasta',
                        ],
                    ],
                ],
                [
                    'name' => 'Drinks',
                    'icon' => 'fa-solid fa-mug-hot',
                    'description' => 'Cold & Hot',
                    'items' => [
                        [
                            'name' => 'Fresh Lime Soda',
                            'price' => 90,
                            'food_type' => null,
                            'description' => 'Sweet and salted',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/owner/menu/import-scanned', $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('categories_created', 2)
            ->assertJsonPath('items_created', 3);

        $this->assertDatabaseHas('categories', [
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Pizza & Pasta',
        ]);

        $this->assertDatabaseHas('menu_items', [
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Margherita Pizza',
            'price' => 299.0,
            'food_type' => 'veg',
        ]);

        $this->assertDatabaseHas('menu_items', [
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Fresh Lime Soda',
            'price' => 90.0,
        ]);
    }

    public function test_import_scanned_reuses_existing_category(): void
    {
        $existingCategory = Category::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Beverages',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $payload = [
            'categories' => [
                [
                    'name' => 'beverages', // lowercase to test case insensitivity
                    'items' => [
                        [
                            'name' => 'Iced Americano',
                            'price' => 150,
                            'food_type' => 'veg',
                            'description' => 'Espresso with cold water & ice',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/owner/menu/import-scanned', $payload);

        $response->assertOk()
            ->assertJsonPath('categories_created', 0) // Should reuse existing
            ->assertJsonPath('items_created', 1);

        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseHas('menu_items', [
            'category_id' => $existingCategory->id,
            'name' => 'Iced Americano',
        ]);
    }
}
