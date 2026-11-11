<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'subscription_plan_id',
        'plan_name',
        'plan_price',
        'billing_cycle',
        'features_snapshot',
        'status',
        'starts_at',
        'ends_at',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'plan_price' => 'decimal:2',
            'features_snapshot' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isFeatureEnabled(string $featureKey): bool
    {
        // Check exact snapshot from the active subscription cycle
        if (is_array($this->features_snapshot)) {
            return (bool) ($this->features_snapshot[$featureKey] ?? false);
        }

        return false;
    }

    public function isExpired(): bool
    {
        if (! $this->ends_at) {
            return false;
        }

        return now()->isAfter($this->ends_at);
    }

    public function daysRemaining(): int
    {
        if (! $this->ends_at) {
            return 30;
        }

        if ($this->isExpired()) {
            return 0;
        }

        return (int) now()->diffInDays($this->ends_at, false);
    }
}
