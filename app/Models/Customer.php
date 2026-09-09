<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'account_number',
        'nis',
        'name',
        'class_name',
        'security_pin',
        'balance',
        'status',
    ];

    protected $hidden = [
        'security_pin',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }
}