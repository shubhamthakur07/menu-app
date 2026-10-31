<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. First Owner: Royal Spice Restro
        $owner1 = User::create([
            'name' => 'Rajesh Sharma',
            'email' => 'rajesh@royalspice.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'phone' => '+91 98765 43210',
        ]);

        $restaurant1 = Restaurant::create([
            'user_id' => $owner1->id,
            'name' => 'Royal Spice Palace',
            'slug' => 'royal-spice',
            'tagline' => 'Authentic Mughlai & North Indian Fine Dining',
            'description' => 'Experience the rich aromas and royal flavors of North Indian and Tandoori delicacies.',
            'address' => 'Plot 42, Civil Lines, Near City Mall, New Delhi',
            'phone' => '+91 98765 43210',
            'email' => 'contact@royalspice.com',
            'currency' => '₹',
            'logo_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=200&h=200&fit=crop',
            'cover_url' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1200&h=400&fit=crop',
            'upi_id' => 'royalspice@upi',
            'upi_name' => 'Royal Spice Palace',
            'table_count' => 15,
            'tax_percentage' => 5.0,
            'enable_online_payment' => true,
            'enable_cash_payment' => true,
            'is_active' => true,
        ]);

        $starters1 = Category::create([
            'restaurant_id' => $restaurant1->id,
            'name' => 'Starters & Tandoor',
            'icon' => 'flame',
            'description' => 'Freshly char-grilled tandoori delicacies and crisp appetizers',
            'sort_order' => 1,
        ]);

        $mainCourse1 = Category::create([
            'restaurant_id' => $restaurant1->id,
            'name' => 'Main Course Curries',
            'icon' => 'utensils',
            'description' => 'Rich creamy gravies cooked with aromatic spices',
            'sort_order' => 2,
        ]);

        $biryani1 = Category::create([
            'restaurant_id' => $restaurant1->id,
            'name' => 'Biryani & Breads',
            'icon' => 'bowl',
            'description' => 'Dum cooked basmati rice biryanis and clay oven breads',
            'sort_order' => 3,
        ]);

        $beverages1 = Category::create([
            'restaurant_id' => $restaurant1->id,
            'name' => 'Drinks & Beverages',
            'icon' => 'cup-soda',
            'description' => 'Chilled mocktails, thick lassi, and cold sodas',
            'sort_order' => 4,
        ]);

        // Menu Items for Royal Spice
        MenuItem::create([
            'restaurant_id' => $restaurant1->id,
            'category_id' => $starters1->id,
            'name' => 'Paneer Tikka Angara',
            'description' => 'Fresh cottage cheese cubes marinated in spicy red yogurt sauce and charred to perfection.',
            'price' => 280.00,
            'food_type' => 'veg',
            'image_url' => 'https://images.unsplash.com/photo-1567188040759-fb8a883dc6d8?w=500&fit=crop',
            'is_available' => true,
            'is_recommended' => true,
            'prep_time_minutes' => 15,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant1->id,
            'category_id' => $starters1->id,
            'name' => 'Tandoori Chicken Tikka',
            'description' => 'Boneless juicy chicken thighs infused with tandoori spices and smoked over charcoal.',
            'price' => 340.00,
            'food_type' => 'non-veg',
            'image_url' => 'https://images.unsplash.com/photo-1599488615731-7e5c2823ff28?w=500&fit=crop',
            'is_available' => true,
            'is_recommended' => true,
            'prep_time_minutes' => 20,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant1->id,
            'category_id' => $mainCourse1->id,
            'name' => 'Dal Makhani Bukhara',
            'description' => 'Black lentils slow-cooked overnight with creamy butter, tomatoes and rich spices.',
            'price' => 290.00,
            'food_type' => 'veg',
            'image_url' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=500&fit=crop',
            'is_available' => true,
            'is_recommended' => true,
            'prep_time_minutes' => 15,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant1->id,
            'category_id' => $mainCourse1->id,
            'name' => 'Butter Chicken Special',
            'description' => 'Tender tandoori chicken cooked in silky smooth buttery tomato gravy.',
            'price' => 390.00,
            'food_type' => 'non-veg',
            'image_url' => 'https://images.unsplash.com/photo-1603894584373-5ac82b2ae398?w=500&fit=crop',
            'is_available' => true,
            'is_recommended' => true,
            'prep_time_minutes' => 20,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant1->id,
            'category_id' => $biryani1->id,
            'name' => 'Hyderabadi Dum Biryani',
            'description' => 'Long grain basmati rice and marinated chicken cooked under sealed dum with saffron.',
            'price' => 360.00,
            'food_type' => 'non-veg',
            'image_url' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=500&fit=crop',
            'is_available' => true,
            'is_recommended' => true,
            'prep_time_minutes' => 25,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant1->id,
            'category_id' => $biryani1->id,
            'name' => 'Butter Garlic Naan',
            'description' => 'Soft leavened bread brushed with melted butter and fresh minced garlic.',
            'price' => 70.00,
            'food_type' => 'veg',
            'image_url' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=500&fit=crop',
            'is_available' => true,
            'is_recommended' => false,
            'prep_time_minutes' => 10,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant1->id,
            'category_id' => $beverages1->id,
            'name' => 'Royal Mango Lassi',
            'description' => 'Thick yogurt blend with Alphonso mango pulp, cardamom and pistachio slivers.',
            'price' => 120.00,
            'food_type' => 'veg',
            'image_url' => 'https://images.unsplash.com/photo-1546173159-315724a31696?w=500&fit=crop',
            'is_available' => true,
            'is_recommended' => true,
            'prep_time_minutes' => 5,
        ]);

        // Sample Order for Royal Spice
        $order1 = Order::create([
            'order_code' => 'ORD-1001',
            'restaurant_id' => $restaurant1->id,
            'table_number' => 'Table 4',
            'customer_name' => 'Amit Verma',
            'customer_phone' => '+91 98111 22233',
            'special_notes' => 'Please keep it less spicy for starters.',
            'subtotal' => 670.00,
            'tax_amount' => 33.50,
            'total_amount' => 703.50,
            'payment_method' => 'upi',
            'payment_status' => 'paid',
            'transaction_id' => 'UPI984729184912',
            'order_status' => 'preparing',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'item_name' => 'Paneer Tikka Angara',
            'item_price' => 280.00,
            'quantity' => 1,
            'subtotal' => 280.00,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'item_name' => 'Butter Chicken Special',
            'item_price' => 390.00,
            'quantity' => 1,
            'subtotal' => 390.00,
        ]);


        // 2. Second Owner: Green Garden Bistro
        $owner2 = User::create([
            'name' => 'Pooja Patel',
            'email' => 'pooja@greengarden.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'phone' => '+91 91234 56789',
        ]);

        $restaurant2 = Restaurant::create([
            'user_id' => $owner2->id,
            'name' => 'Green Garden Cafe & Bakery',
            'slug' => 'green-garden',
            'tagline' => '100% Pure Vegetarian European & Continental Cafe',
            'description' => 'Artisanal sourdough pizzas, hand-crafted pasta, specialty brews and fresh desserts.',
            'address' => '12 MG Road, Bandra West, Mumbai',
            'phone' => '+91 91234 56789',
            'email' => 'hello@greengardencafe.com',
            'currency' => '₹',
            'logo_url' => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?w=200&h=200&fit=crop',
            'cover_url' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=1200&h=400&fit=crop',
            'upi_id' => 'greengarden@icici',
            'upi_name' => 'Green Garden Bistro',
            'table_count' => 10,
            'tax_percentage' => 5.0,
            'enable_online_payment' => true,
            'enable_cash_payment' => true,
            'is_active' => true,
        ]);

        $pizzas = Category::create([
            'restaurant_id' => $restaurant2->id,
            'name' => 'Wood-Fired Pizzas',
            'icon' => 'pizza',
            'description' => 'Crisp crust sourdough pizzas with fresh Italian mozzarella',
            'sort_order' => 1,
        ]);

        $pastas = Category::create([
            'restaurant_id' => $restaurant2->id,
            'name' => 'Artisan Pastas',
            'icon' => 'utensils',
            'description' => 'Handmade fettuccine and penne in authentic sauces',
            'sort_order' => 2,
        ]);

        $coffee = Category::create([
            'restaurant_id' => $restaurant2->id,
            'name' => 'Coffee & Desserts',
            'icon' => 'coffee',
            'description' => 'Specialty single origin coffees and molten chocolate cakes',
            'sort_order' => 3,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant2->id,
            'category_id' => $pizzas->id,
            'name' => 'Margherita Basilico Pizza',
            'description' => 'San Marzano tomato sauce, fresh buffalo mozzarella, virgin olive oil, and sweet basil leaves.',
            'price' => 380.00,
            'food_type' => 'veg',
            'image_url' => 'https://images.unsplash.com/photo-1604382354936-07c5d9983bd3?w=500&fit=crop',
            'is_available' => true,
            'is_recommended' => true,
            'prep_time_minutes' => 18,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant2->id,
            'category_id' => $pastas->id,
            'name' => 'Creamy Truffle Penne Pasta',
            'description' => 'Penne tossed with sautéed mushrooms in creamy black truffle oil parmesan sauce.',
            'price' => 420.00,
            'food_type' => 'veg',
            'image_url' => 'https://images.unsplash.com/photo-1621996346565-e3d5d62816f1?w=500&fit=crop',
            'is_available' => true,
            'is_recommended' => true,
            'prep_time_minutes' => 15,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant2->id,
            'category_id' => $coffee->id,
            'name' => 'Spanish Iced Latte',
            'description' => 'Double espresso shot with condensed milk and whole milk over cracked ice.',
            'price' => 220.00,
            'food_type' => 'beverage',
            'image_url' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=500&fit=crop',
            'is_available' => true,
            'is_recommended' => true,
            'prep_time_minutes' => 5,
        ]);
    }
}
