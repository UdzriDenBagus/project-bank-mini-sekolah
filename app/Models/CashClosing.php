<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashClosing extends Model
{
    protected $fillable = [
        'teller_id',
        'closing_date',
        'opening_balance',
        'total_deposit',
        'total_withdraw',
        'system_balance',
        'physical_balance',
        'difference',
        'status',
        'supervisor_id',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'closing_date' => 'date',
        'opening_balance' => 'integer',
        'total_deposit' => 'integer',
        'total_withdraw' => 'integer',
        'system_balance' => 'integer',
        'physical_balance' => 'integer',
        'difference' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}