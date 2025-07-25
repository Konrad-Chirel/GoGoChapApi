<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;
    protected $table = 'order_items';
    protected $fillable = [
        'order_id',
        'meal_id',
        'quantity',
        'price'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class, 'meal_id');
    }
}
