<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingJournal extends Model
{
    protected $fillable = [
        'transaction_id',
        'account_code',
        'account_name',
        'position',
        'amount',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}