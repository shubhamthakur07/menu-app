<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'business_type',
        'slug',
        'tagline',
        'description',
        'address',
        'latitude',
        'longitude',
        'geofence_radius_km',
        'phone',
        'email',
        'currency',
        'logo_url',
        'cover_url',
        'upi_id',
        'upi_name',
        'table_count',
        'slot_duration_minutes',
        'opening_time',
        'closing_time',
        'tax_percentage',
        'enable_online_payment',
        'enable_cash_payment',
        'enable_home_delivery',
        'enable_takeaway',
        'allow_shared_table_orders',
        'loyalty_enabled',
        'loyalty_required_visits',
        'loyalty_game_type',
        'loyalty_rewards',
        'enable_waiter_call',
        'enable_google_reviews',
        'google_review_url',
        'enable_whatsapp_bill',
        'enable_split_bill',
        'enable_voice_search',
        'enable_kds',
        'subscription_plan',
        'subscription_status',
        'trial_ends_at',
        'subscription_ends_at',
        'current_subscription_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'enable_online_payment' => 'boolean',
            'enable_cash_payment' => 'boolean',
            'enable_home_delivery' => 'boolean',
            'enable_takeaway' => 'boolean',
            'allow_shared_table_orders' => 'boolean',
            'loyalty_enabled' => 'boolean',
            'loyalty_required_visits' => 'integer',
            'loyalty_rewards' => 'array',
            'enable_waiter_call' => 'boolean',
            'enable_google_reviews' => 'boolean',
            'enable_whatsapp_bill' => 'boolean',
            'enable_split_bill' => 'boolean',
            'enable_voice_search' => 'boolean',
            'enable_kds' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'geofence_radius_km' => 'float',
            'is_active' => 'boolean',
            'tax_percentage' => 'float',
            'table_count' => 'integer',
            'slot_duration_minutes' => 'integer',
        ];
    }

    public function currentSubscription(): BelongsTo
    {
        return $this->belongsTo(RestaurantSubscription::class, 'current_subscription_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(RestaurantSubscription::class)->latest();
    }

    /**
     * Check if a feature is enabled under the restaurant's active subscription.
     * Uses the grandfathered snapshot of the current active cycle.
     */
    public function hasFeature(string $featureKey): bool
    {
        $sub = $this->currentSubscription;
        if ($sub && ! $sub->isExpired()) {
            return $sub->isFeatureEnabled($featureKey);
        }

        // Fallback: If restaurant is in trial mode, all features remain unlocked
        if ($this->subscription_status === 'trial') {
            if ($this->trial_ends_at === null || now()->isBefore($this->trial_ends_at)) {
                return true;
            }
        }

        return false;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('sort_order');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function tables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class)->orderBy('name');
    }

    public function serviceCalls(): HasMany
    {
        return $this->hasMany(TableServiceCall::class)->latest();
    }

    public function customerFeedbacks(): HasMany
    {
        return $this->hasMany(CustomerFeedback::class)->latest();
    }

    public function getEffectiveLoyaltyRewards(): array
    {
        if (! empty($this->loyalty_rewards) && is_array($this->loyalty_rewards)) {
            return $this->loyalty_rewards;
        }

        $currency = $this->currency ?: '₹';

        return [
            ['id' => 1, 'title' => "{$currency}100 Flat OFF", 'type' => 'flat', 'value' => 100, 'color' => '#f59e0b', 'icon' => 'fa-solid fa-gift'],
            ['id' => 2, 'title' => '40% Mega Discount', 'type' => 'percent', 'value' => 40, 'color' => '#10b981', 'icon' => 'fa-solid fa-percent'],
            ['id' => 3, 'title' => 'Buy 1 Get 1 Free (BOGO)', 'type' => 'bogo', 'value' => 100, 'color' => '#8b5cf6', 'icon' => 'fa-solid fa-bag-shopping'],
            ['id' => 4, 'title' => "{$currency}50 Instant OFF", 'type' => 'flat', 'value' => 50, 'color' => '#ec4899', 'icon' => 'fa-solid fa-wallet'],
            ['id' => 5, 'title' => 'Free Dessert / Drink', 'type' => 'free_item', 'value' => 80, 'color' => '#06b6d4', 'icon' => 'fa-solid fa-mug-hot'],
            ['id' => 6, 'title' => '25% Special Treat', 'type' => 'percent', 'value' => 25, 'color' => '#f97316', 'icon' => 'fa-solid fa-tag'],
        ];
    }
}
