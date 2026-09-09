<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_code',
        'customer_id',
        'user_id',
        'transaction_date',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function journals()
    {
        return $this->hasMany(AccountingJournal::class, 'transaction_id');
    }
}