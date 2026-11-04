<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TellerCashAdvance extends Model
{
    protected $fillable = [
        'teller_id',
        'request_date',
        'amount',
        'type',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'request_date' => 'date',
        'amount'       => 'integer',
        'approved_at'  => 'datetime',
    ];

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
