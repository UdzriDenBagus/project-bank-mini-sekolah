<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetRequest extends Model
{
    protected $fillable = [
        'user_id',
        'saver_id',
        'role',
        'reset_type',
        'nis',
        'name',
        'class_name',
        'reset_code',
        'email',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function saver()
    {
        return $this->belongsTo(Saver::class, 'saver_id')->withTrashed();
    }
}
