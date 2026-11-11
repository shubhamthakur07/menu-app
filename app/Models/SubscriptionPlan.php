<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price_monthly',
        'price_yearly',
        'trial_days',
        'description',
        'badge_text',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'trial_days' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(RestaurantSubscription::class);
    }

    /**
     * Default list of customizable feature definitions and their human labels
     */
    public static function defaultFeatureDefinitions(): array
    {
        return [
            'enable_qr_menu' => [
                'label' => 'Digital QR Menu & Categories',
                'description' => 'Unlimited menu dishes, photos, and categories',
                'default' => true,
            ],
            'enable_pdf_download' => [
                'label' => 'PDF Menu & Rate Card Download',
                'description' => '1-tap high quality printable PDF for menu or salon rate card',
                'default' => true,
            ],
            'enable_waiter_call' => [
                'label' => 'Table Service Bell ("वेटर बुलाएं")',
                'description' => 'Instant 1-tap table call for water, waiter, bill, and cleaning',
                'default' => false,
            ],
            'enable_kds' => [
                'label' => 'Kitchen Display System (KDS)',
                'description' => 'Live real-time screen for chef with sound alerts and 1-tap order bump',
                'default' => false,
            ],
            'enable_google_reviews' => [
                'label' => 'Smart Google Review 5-Star Booster',
                'description' => 'Smart review gating that routes 4-5 stars to Google Maps and bad reviews to owner',
                'default' => false,
            ],
            'enable_loyalty' => [
                'label' => 'Royalty Rewards Club (Spinner & 5km Alerts)',
                'description' => 'Customer visit stamp cards, Lucky Wheel, scratch cards, and nearby geofence alerts',
                'default' => false,
            ],
            'enable_voice_search' => [
                'label' => 'Multi-Language (हिन्दी/Eng) & Voice Search',
                'description' => 'Speech-to-text dish search and dynamic instant full Hindi menu translation',
                'default' => false,
            ],
            'enable_split_bill' => [
                'label' => 'Split Bill with Friends & WhatsApp Bill',
                'description' => 'Split bill evenly among friends with UPI links and 1-tap WhatsApp receipt sharing',
                'default' => false,
            ],
            'enable_online_payment' => [
                'label' => 'Direct UPI QR Payments',
                'description' => 'Direct QR and online UPI intent payments into restaurant account',
                'default' => true,
            ],
        ];
    }
}
