<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeliveryEnterprise extends Model
{
    use HasFactory;
    protected $table = 'delivery_enterprises';
    protected $fillable = [
        'name',
        'address',
        'phone_number',
        'email',
        'password',
        'solde',
        'numero_IFU'
    ];

    public function deliveryPersons(): HasMany
    {
        return $this->hasMany(DeliveryPerson::class, 'delivery_enterprise_id');
    }

    public function user(): HasOne
{
    return $this->hasOne(User::class, 'delivery_enterprise_id');
}
}
