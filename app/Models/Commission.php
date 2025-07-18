<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    // Table associée (facultatif si suit la convention)
    protected $table = 'commissions';

    // Les champs modifiables en masse
    protected $fillable = [
        'product_percentage',
        'delivery_percentage',
        'min_delivery_fee',
    ];

    
}
