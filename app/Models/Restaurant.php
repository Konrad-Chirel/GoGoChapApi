<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Restaurant extends Model
{
    use HasFactory;
    protected $table = 'restaurants';
    protected $fillable = [
        'name',
        'address',
        'phone_number',
        'email',
        'password',
        'solde'
    ];

    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class, 'restaurant_id');
    }

    public function user(): HasOne
{
    return $this->hasOne(User::class, 'restaurant_id');
}

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'restaurant_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'restaurant_id');
    }
}
