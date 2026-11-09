<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalAppointment extends Model
{
    protected $fillable = [
        'saver_id',
        'teller_id',
        'appointment_date',
        'withdrawal_date',
        'amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'withdrawal_date'  => 'date',
        'amount'           => 'integer',
    ];

    public function saver()
    {
        return $this->belongsTo(Saver::class, 'saver_id');
    }

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }
}
