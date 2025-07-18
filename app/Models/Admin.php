<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Admin extends Model
{
    use HasFactory;
    protected $table = 'admins';    
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'address',
        'phone_number',
    ];
    

    public function user()
    {
        return $this->hasOne(User::class);
    }
    
}
