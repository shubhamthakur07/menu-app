<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'code',
        'seating_capacity',
        'status',
        'active_order_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'seating_capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function activeOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'active_order_id');
    }

    public function activeOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_number', 'name')
            ->whereIn('order_status', ['pending', 'accepted', 'preparing', 'ready']);
    }
}
