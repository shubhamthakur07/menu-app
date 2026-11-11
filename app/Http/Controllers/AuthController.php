<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\RestaurantTable;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Owner Registration (Registers Owner + Initial Restaurant Setup).
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'business_type' => 'nullable|in:food,salon,retail',
            'restaurant_name' => 'required|string|max:150',
            'restaurant_address' => 'nullable|string|max:255',
            'upi_id' => 'nullable|string|max:100',
        ]);

        $businessType = $validated['business_type'] ?? 'food';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'owner',
            'phone' => $validated['phone'] ?? null,
        ]);

        $baseSlug = Str::slug($validated['restaurant_name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Restaurant::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $restaurant = Restaurant::create([
            'user_id' => $user->id,
            'name' => $validated['restaurant_name'],
            'business_type' => $businessType,
            'slug' => $slug,
            'tagline' => $businessType === 'salon' ? 'Professional Hair & Grooming Lounge' : 'Welcome to '.$validated['restaurant_name'],
            'description' => $businessType === 'salon' ? 'Expert haircuts, beard styling and grooming treatments without the waiting queue.' : 'Fresh delicious food, served with love and passion.',
            'address' => $validated['restaurant_address'] ?? 'Main Market',
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'],
            'currency' => '₹',
            'logo_url' => $businessType === 'salon'
                ? 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=200&h=200&fit=crop'
                : 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=200&h=200&fit=crop',
            'cover_url' => $businessType === 'salon'
                ? 'https://images.unsplash.com/photo-1585747860715-2ba37e788b70?w=1200&h=400&fit=crop'
                : 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&h=400&fit=crop',
            'upi_id' => $validated['upi_id'] ?? null,
            'upi_name' => $validated['restaurant_name'],
            'table_count' => $businessType === 'salon' ? 4 : 12,
            'slot_duration_minutes' => 30,
            'opening_time' => '09:00',
            'closing_time' => '21:00',
            'tax_percentage' => 5.0,
            'enable_online_payment' => true,
            'enable_cash_payment' => true,
            'is_active' => true,
            'subscription_plan' => 'vip',
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(30),
            'subscription_ends_at' => now()->addDays(30),
        ]);

        // Create initial 30-day Free Trial subscription record with full features snapshot
        $vipPlan = SubscriptionPlan::where('slug', 'vip')->first();
        $trialFeatures = $vipPlan ? $vipPlan->features : [
            'enable_qr_menu' => true,
            'enable_pdf_download' => true,
            'enable_online_payment' => true,
            'enable_waiter_call' => true,
            'enable_split_bill' => true,
            'enable_voice_search' => true,
            'enable_kds' => true,
            'enable_google_reviews' => true,
            'enable_loyalty' => true,
        ];

        $trialSub = RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'subscription_plan_id' => $vipPlan?->id,
            'plan_name' => '30-Day Free Trial (All VIP Features)',
            'plan_price' => 0,
            'billing_cycle' => 'trial',
            'features_snapshot' => $trialFeatures,
            'status' => 'trial',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'payment_method' => 'free_trial',
            'notes' => 'Automatic 30-day free trial on registration',
        ]);

        $restaurant->update(['current_subscription_id' => $trialSub->id]);

        // Create sample default categories & chairs/tables based on business type
        if ($businessType === 'salon') {
            for ($i = 1; $i <= 4; $i++) {
                RestaurantTable::create([
                    'restaurant_id' => $restaurant->id,
                    'name' => "Chair {$i}",
                    'code' => "chair-{$i}",
                    'seating_capacity' => 1,
                    'status' => 'available',
                    'is_active' => true,
                ]);
            }

            $cat1 = Category::create([
                'restaurant_id' => $restaurant->id,
                'name' => 'Hair Cuts & Styling',
                'icon' => 'scissors',
                'sort_order' => 1,
            ]);

            MenuItem::create([
                'restaurant_id' => $restaurant->id,
                'category_id' => $cat1->id,
                'name' => 'Classic Haircut & Wash',
                'description' => 'Precision scissor cut with refreshing head wash and styling.',
                'price' => 150.00,
                'duration_minutes' => 30,
                'food_type' => 'veg',
                'image_url' => 'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?w=500&fit=crop',
                'is_available' => true,
                'is_recommended' => true,
            ]);

            MenuItem::create([
                'restaurant_id' => $restaurant->id,
                'category_id' => $cat1->id,
                'name' => 'Beard Grooming & Shape',
                'description' => 'Beard trim, hot towel treatment and razor sharp edge lining.',
                'price' => 100.00,
                'duration_minutes' => 20,
                'food_type' => 'veg',
                'image_url' => 'https://images.unsplash.com/photo-1621605815971-fbc98d665033?w=500&fit=crop',
                'is_available' => true,
                'is_recommended' => true,
            ]);
        } else {
            for ($i = 1; $i <= 6; $i++) {
                RestaurantTable::create([
                    'restaurant_id' => $restaurant->id,
                    'name' => "Table {$i}",
                    'code' => "tbl-{$i}",
                    'seating_capacity' => 4,
                    'status' => 'available',
                    'is_active' => true,
                ]);
            }

            $cat1 = Category::create([
                'restaurant_id' => $restaurant->id,
                'name' => 'Popular Specials',
                'icon' => 'flame',
                'sort_order' => 1,
            ]);

            MenuItem::create([
                'restaurant_id' => $restaurant->id,
                'category_id' => $cat1->id,
                'name' => 'Chef Special Dish',
                'description' => 'Prepared fresh with special house spice blends.',
                'price' => 250.00,
                'duration_minutes' => 20,
                'food_type' => 'veg',
                'image_url' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=500&fit=crop',
                'is_available' => true,
                'is_recommended' => true,
            ]);
        }

        Auth::login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration successful! Welcome to QR Menu.',
            'user' => $user->load('restaurants'),
            'restaurant' => $restaurant,
        ], 201);
    }

    /**
     * Owner Login.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, true)) {
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }
            $user = Auth::user()->load('restaurants');

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user,
                'restaurant' => $user->restaurants()->first(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'The provided credentials do not match our records.',
        ], 422);
    }

    /**
     * Get Current Authenticated Owner Profile.
     */
    public function user(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'authenticated' => false,
                'user' => null,
            ]);
        }

        $user = Auth::user()->load('restaurants');
        $activeRestaurant = $user->restaurants()->first();

        return response()->json([
            'authenticated' => true,
            'user' => $user,
            'restaurant' => $activeRestaurant,
        ]);
    }

    /**
     * Logout.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
