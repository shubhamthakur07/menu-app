<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFeedback extends Model
{
    use HasFactory;

    protected $table = 'customer_feedbacks';

    protected $fillable = [
        'restaurant_id',
        'order_code',
        'customer_name',
        'customer_phone',
        'rating',
        'comment',
        'redirected_to_google',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'redirected_to_google' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
