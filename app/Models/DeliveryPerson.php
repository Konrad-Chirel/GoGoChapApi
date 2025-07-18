<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeliveryPerson extends Model
{
    use HasFactory; 
    protected $table = 'delivery_persons';
    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'password',
        'files',
        'solde',
        'position',
        'status',
        'delivery_enterprise_id'
    ];

    public function deliveryEnterprise(): BelongsTo
    {
        return $this->belongsTo(DeliveryEnterprise::class, 'delivery_enterprise_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'delivery_person_id');
    }

    public function user(): HasOne
{
    return $this->hasOne(User::class, 'delivery_person_id');
}
}
