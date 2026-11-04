<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_code',
        'saver_id',
        'saver_name_snapshot',
        'account_number_snapshot',
        'user_id',
        'transaction_date',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
    ];

    protected $appends = [
        'saver_name',
        'account_number',
    ];

    public function getSaverNameAttribute()
    {
        return $this->saver_name_snapshot ?? ($this->saver ? $this->saver->name : 'Siswa Terhapus Permanen');
    }

    public function getAccountNumberAttribute()
    {
        return $this->account_number_snapshot ?? ($this->saver ? $this->saver->account_number : '-');
    }

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount'           => 'integer',
        'balance_before'   => 'integer',
        'balance_after'    => 'integer',
    ];

    /**
     * KUNCI INTEGRITAS DATA:
     * Dengan withTrashed(), transaksi dari penabung yang akunnya telah dihapus
     * TETAP DAPAT TERBACA UTUH di laporan closing kas dan PDF!
     */
    public function saver()
    {
        return $this->belongsTo(Saver::class, 'saver_id')->withTrashed();
    }

    // Kompatibilitas jika ada kode lama yang memanggil ->customer
    public function customer()
    {
        return $this->saver();
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