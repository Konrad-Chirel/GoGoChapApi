<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notifications';
    protected $fillable = [
        'user_id',
        'user_type',     // 'admin', 'client', 'livreur', 'entreprise'
        'type',          // 'commande', 'alerte', 'plainte'...
        'message',
        'order_id',
        'read'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->morphTo(null, 'user_type', 'user_id');
    }
}
