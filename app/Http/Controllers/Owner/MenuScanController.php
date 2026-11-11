<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\MenuScanService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MenuScanController extends Controller
{
    public function __construct(
        protected MenuScanService $scanService
    ) {}

    /**
     * Scan an uploaded menu photo or base64 image using AI vision.
     */
    public function scan(Request $request): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();

        // Check for quick demo mode
        if ($request->boolean('demo')) {
            return response()->json($this->getDemoScannedMenu($restaurant->business_type));
        }

        $request->validate([
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:10240', // up to 10MB
            'image_base64' => 'nullable|string',
            'api_key' => 'nullable|string|max:255',
            'provider' => 'nullable|string|in:gemini,openai',
        ]);

        $image = $request->file('image') ?: $request->input('image_base64');

        if (! $image) {
            return response()->json([
                'success' => false,
                'message' => 'Please upload or capture a menu photo.',
            ], 422);
        }

        $provider = $request->input('provider', 'gemini');
        $customApiKey = $request->input('api_key');

        $result = $this->scanService->scanImage(
            $image,
            $customApiKey,
            $provider,
            $restaurant->business_type
        );

        if (! ($result['success'] ?? false)) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * Import reviewed categories and dishes into the restaurant menu.
     */
    public function import(Request $request): JsonResponse
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrFail();

        $validated = $request->validate([
            'categories' => 'required|array|min:1',
            'categories.*.name' => 'required|string|max:100',
            'categories.*.icon' => 'nullable|string|max:50',
            'categories.*.description' => 'nullable|string|max:255',
            'categories.*.items' => 'required|array|min:1',
            'categories.*.items.*.name' => 'required|string|max:150',
            'categories.*.items.*.price' => 'required|numeric|min:0',
            'categories.*.items.*.description' => 'nullable|string',
            'categories.*.items.*.food_type' => 'nullable|string|in:veg,non-veg',
            'categories.*.items.*.duration_minutes' => 'nullable|integer|min:5|max:240',
        ]);

        DB::beginTransaction();

        try {
            $categoriesCreated = 0;
            $itemsCreated = 0;
            $createdItems = [];

            $currentMaxOrder = (int) ($restaurant->categories()->max('sort_order') ?? 0);

            foreach ($validated['categories'] as $catData) {
                $trimmedCatName = trim($catData['name']);

                // Find existing category with matching name (case-insensitive)
                $category = $restaurant->categories()
                    ->whereRaw('LOWER(name) = ?', [strtolower($trimmedCatName)])
                    ->first();

                if (! $category) {
                    $currentMaxOrder++;
                    $category = $restaurant->categories()->create([
                        'name' => $trimmedCatName,
                        'icon' => $catData['icon'] ?? 'fa-solid fa-utensils',
                        'description' => $catData['description'] ?? null,
                        'sort_order' => $currentMaxOrder,
                        'is_active' => true,
                    ]);
                    $categoriesCreated++;
                }

                // Add items for this category
                foreach ($catData['items'] as $itemData) {
                    $foodType = $itemData['food_type'] ?? null;
                    if ($restaurant->business_type === 'salon' || $restaurant->business_type === 'retail') {
                        $foodType = null;
                    } elseif ($foodType === null) {
                        $foodType = 'veg';
                    }

                    $durationMinutes = $itemData['duration_minutes'] ?? 30;

                    $menuItem = $restaurant->menuItems()->create([
                        'category_id' => $category->id,
                        'name' => trim($itemData['name']),
                        'description' => ! empty($itemData['description']) ? trim($itemData['description']) : null,
                        'price' => (float) $itemData['price'],
                        'food_type' => $foodType,
                        'duration_minutes' => $durationMinutes,
                        'is_available' => true,
                        'is_recommended' => false,
                    ]);

                    $itemsCreated++;
                    $createdItems[] = $menuItem;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$itemsCreated} menu item(s) across {$categoriesCreated} new category(ies)!",
                'categories_created' => $categoriesCreated,
                'items_created' => $itemsCreated,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to import menu items: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Provide sample scanned data for instant demo/testing.
     */
    protected function getDemoScannedMenu(?string $businessType): array
    {
        if ($businessType === 'salon') {
            return [
                'success' => true,
                'is_demo' => true,
                'categories' => [
                    [
                        'name' => 'Hair Care & Styling',
                        'icon' => 'fa-solid fa-scissors',
                        'description' => 'Professional hair cuts and styling',
                        'items' => [
                            ['name' => 'Classic Haircut & Wash', 'price' => 250, 'food_type' => null, 'duration_minutes' => 30, 'description' => 'Haircut with shampoo & blow dry'],
                            ['name' => 'Beard Grooming & Shape', 'price' => 150, 'food_type' => null, 'duration_minutes' => 20, 'description' => 'Precision beard trimming and hot towel treatment'],
                            ['name' => 'Keratin Hair Spa', 'price' => 800, 'food_type' => null, 'duration_minutes' => 45, 'description' => 'Deep conditioning hair restoration spa'],
                        ],
                    ],
                    [
                        'name' => 'Skin & Facial Care',
                        'icon' => 'fa-solid fa-sparkles',
                        'description' => 'Glow facials and cleansing',
                        'items' => [
                            ['name' => 'Gold Radiance Facial', 'price' => 1200, 'food_type' => null, 'duration_minutes' => 60, 'description' => 'Luxurious glowing facial treatment'],
                            ['name' => 'O3+ D-Tan Treatment', 'price' => 500, 'food_type' => null, 'duration_minutes' => 30, 'description' => 'Sun tan removal mask and pack'],
                        ],
                    ],
                ],
                'total_items' => 5,
            ];
        }

        return [
            'success' => true,
            'is_demo' => true,
            'categories' => [
                [
                    'name' => 'Starters & Appetizers',
                    'icon' => 'fa-solid fa-bowl-food',
                    'description' => 'Crispy and savory starters',
                    'items' => [
                        ['name' => 'Paneer Tikka', 'price' => 240, 'food_type' => 'veg', 'duration_minutes' => null, 'description' => 'Cottage cheese cubes marinated in tandoori spices and grilled'],
                        ['name' => 'Crispy Corn & Pepper', 'price' => 190, 'food_type' => 'veg', 'duration_minutes' => null, 'description' => 'Golden fried corn tossed with peppers and herbs'],
                        ['name' => 'Chicken Tikka', 'price' => 280, 'food_type' => 'non-veg', 'duration_minutes' => null, 'description' => 'Succulent boneless chicken roasted in clay oven'],
                        ['name' => 'Tandoori Chicken (Half)', 'price' => 320, 'food_type' => 'non-veg', 'duration_minutes' => null, 'description' => 'Classic clay oven roasted bone-in chicken'],
                    ],
                ],
                [
                    'name' => 'Main Course',
                    'icon' => 'fa-solid fa-plate-wheat',
                    'description' => 'Rich curries and entrees',
                    'items' => [
                        ['name' => 'Paneer Butter Masala', 'price' => 280, 'food_type' => 'veg', 'duration_minutes' => null, 'description' => 'Paneer in rich tomato, butter, and cashew gravy'],
                        ['name' => 'Dal Makhani', 'price' => 230, 'food_type' => 'veg', 'duration_minutes' => null, 'description' => 'Slow cooked black lentils with fresh cream'],
                        ['name' => 'Butter Chicken', 'price' => 340, 'food_type' => 'non-veg', 'duration_minutes' => null, 'description' => 'Tandoori chicken in velvety tomato butter sauce'],
                    ],
                ],
                [
                    'name' => 'Breads & Rice',
                    'icon' => 'fa-solid fa-bowl-rice',
                    'description' => 'Fresh tandoori breads & aromatic biryani',
                    'items' => [
                        ['name' => 'Butter Naan', 'price' => 50, 'food_type' => 'veg', 'duration_minutes' => null, 'description' => 'Soft leavened flatbread brushed with butter'],
                        ['name' => 'Garlic Naan', 'price' => 70, 'food_type' => 'veg', 'duration_minutes' => null, 'description' => 'Fresh tandoori naan topped with chopped roasted garlic'],
                        ['name' => 'Hyderabadi Chicken Biryani', 'price' => 320, 'food_type' => 'non-veg', 'duration_minutes' => null, 'description' => 'Fragrant basmati rice dum cooked with spiced chicken'],
                    ],
                ],
                [
                    'name' => 'Beverages & Desserts',
                    'icon' => 'fa-solid fa-mug-hot',
                    'description' => 'Refreshing coolers and sweet treats',
                    'items' => [
                        ['name' => 'Cold Coffee with Ice Cream', 'price' => 120, 'food_type' => null, 'duration_minutes' => null, 'description' => 'Thick creamy blended coffee'],
                        ['name' => 'Gulab Jamun (2 pcs)', 'price' => 80, 'food_type' => 'veg', 'duration_minutes' => null, 'description' => 'Warm milk solid dumplings in cardamom syrup'],
                    ],
                ],
            ],
            'total_items' => 11,
        ];
    }
}
