<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Saver extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'savers';

    protected $fillable = [
        'member_id',
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

    protected $casts = [
        'balance' => 'integer',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id')->withTrashed();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'saver_id');
    }

    public function getRoleAttribute(): string
    {
        return 'customer';
    }
}