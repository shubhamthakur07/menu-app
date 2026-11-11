<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter Plan',
                'slug' => 'starter',
                'price_monthly' => 499,
                'price_yearly' => 3999,
                'trial_days' => 30,
                'description' => 'Ideal for small cafes, juice bars, and quick takeaway joints.',
                'badge_text' => 'Essential',
                'sort_order' => 1,
                'features' => [
                    'enable_qr_menu' => true,
                    'enable_pdf_download' => true,
                    'enable_online_payment' => true,
                    'enable_waiter_call' => false,
                    'enable_split_bill' => false,
                    'enable_voice_search' => false,
                    'enable_kds' => false,
                    'enable_google_reviews' => false,
                    'enable_loyalty' => false,
                ],
            ],
            [
                'name' => 'Pro Growth Plan',
                'slug' => 'pro',
                'price_monthly' => 799,
                'price_yearly' => 5999,
                'trial_days' => 30,
                'description' => 'Best for busy family restaurants & cafes needing table calls & split billing.',
                'badge_text' => 'Most Popular ⭐',
                'sort_order' => 2,
                'features' => [
                    'enable_qr_menu' => true,
                    'enable_pdf_download' => true,
                    'enable_online_payment' => true,
                    'enable_waiter_call' => true,
                    'enable_split_bill' => true,
                    'enable_voice_search' => true,
                    'enable_kds' => false,
                    'enable_google_reviews' => false,
                    'enable_loyalty' => false,
                ],
            ],
            [
                'name' => 'All-in-One VIP',
                'slug' => 'vip',
                'price_monthly' => 1299,
                'price_yearly' => 9999,
                'trial_days' => 30,
                'description' => 'Complete powerhouse with Kitchen KDS, 5-Star Reviews & Lucky Wheel Loyalty.',
                'badge_text' => 'Best Value 👑',
                'sort_order' => 3,
                'features' => [
                    'enable_qr_menu' => true,
                    'enable_pdf_download' => true,
                    'enable_online_payment' => true,
                    'enable_waiter_call' => true,
                    'enable_split_bill' => true,
                    'enable_voice_search' => true,
                    'enable_kds' => true,
                    'enable_google_reviews' => true,
                    'enable_loyalty' => true,
                ],
            ],
        ];

        foreach ($plans as $p) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $p['slug']],
                $p
            );
        }
    }
}
