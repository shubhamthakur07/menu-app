<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'restaurant_id',
        'order_type',
        'table_number',
        'booking_date',
        'booking_time',
        'chair_or_specialist',
        'token_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_address',
        'special_notes',
        'subtotal',
        'discount_amount',
        'discount_reason',
        'game_reward_claimed',
        'loyalty_reward_claimed',
        'loyalty_reward_details',
        'tax_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'transaction_id',
        'order_status',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'token_number' => 'integer',
            'subtotal' => 'float',
            'discount_amount' => 'float',
            'game_reward_claimed' => 'boolean',
            'loyalty_reward_claimed' => 'boolean',
            'loyalty_reward_details' => 'array',
            'tax_amount' => 'float',
            'total_amount' => 'float',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
