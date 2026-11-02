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
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'enable_online_payment' => 'boolean',
            'enable_cash_payment' => 'boolean',
            'enable_home_delivery' => 'boolean',
            'enable_takeaway' => 'boolean',
            'allow_shared_table_orders' => 'boolean',
            'is_active' => 'boolean',
            'tax_percentage' => 'float',
            'table_count' => 'integer',
            'slot_duration_minutes' => 'integer',
        ];
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
}
