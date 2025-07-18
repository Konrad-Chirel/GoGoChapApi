<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tarif extends Model
{
    use HasFactory;
    protected $fillable = ['titre', 'description', 'montant'];

    protected $casts = [
        'montant' => 'integer',
    ];
}
