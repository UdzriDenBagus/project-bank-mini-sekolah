<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\Member;
use App\Models\Saver;
use App\Models\Transaction;
use App\Models\AccountingJournal;
use App\Models\CashClosing;
use App\Models\TellerCashAdvance;

class TellerController extends Controller
{
    /**
     * 1. Cari Anggota Siswa Belum Aktif untuk Aktivasi di Loket
     */
    public function searchUnactivatedMember(Request $request)
    {
        $query = trim($request->query('query', ''));
        $qBuilder = Member::where('status', 'inactive_account')->orderBy('id', 'desc');

        if (!empty($query)) {
            $qBuilder->where(function ($q) use ($query) {
                $q->where('nis', 'LIKE', "%{$query}%")
                  ->orWhere('name', 'LIKE', "%{$query}%")
                  ->orWhere('class_name', 'LIKE', "%{$query}%");
            });
        }

        $members = $qBuilder->take(30)->get();

        return response()->json(['success' => true, 'data' => $members]);
    }

    /**
     * Cek apakah Teller telah melakukan closing rekonsiliasi kas hari ini
     * Jika sudah (status: submitted, approved, atau dispute), transaksi dinonaktifkan hingga hari berikutnya.
     */
    private function checkTodayClosingStatus($tellerId)
    {
        return CashClosing::where('teller_id', $tellerId)
                          ->whereDate('closing_date', today())
                          ->whereIn('status', ['submitted', 'approved', 'dispute'])
                          ->first();
    }

    /**
     * 2. Generator Nomor Rekening Otomatis
     * Format: [2 Digit Tahun][2 Digit Kode Sekolah '01'][5 Digit Nomor Urut]
     */
    private function generateAccountNumber()
    {
        $prefix = date('y') . '01'; // Contoh: '2601'
        $latest = Saver::withTrashed()
                       ->where('account_number', 'LIKE', $prefix . '%')
                       ->orderBy('account_number', 'desc')
                       ->first();

        if (!$latest) {
            $nextSeq = 1;
        } else {
            $lastSeq = (int) substr($latest->account_number, strlen($prefix));
            $nextSeq = $lastSeq + 1;
        }

        return $prefix . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
    }

    /**
     * 3. Eksekusi Aktivasi Rekening & Setoran Awal (Minimal Rp 10.000) oleh Teller
     * Memerlukan: Setoran Awal (min 10k), PIN Transaksi (6 digit), dan Password Login (min 6 karakter)
     */
    public function activateAccount(Request $request, $id = null)
    {
        $memberId = $id ?: $request->member_id ?: $request->id;
        $amount = (int) ($request->amount ?? $request->initial_deposit ?? 0);

        $request->merge([
            'member_id' => $memberId,
            'amount'    => $amount,
        ]);

        $validator = Validator::make($request->all(), [
            'member_id'    => 'required|exists:members,id',
            'amount'       => 'required|integer|min:10000',
            'security_pin' => 'required|digits:6',
            'password'     => 'required|string|min:6',
        ], [
            'amount.min'            => 'Setoran awal minimal Rp 10.000 untuk dapat diaktivasi sebagai penabung.',
            'security_pin.required' => 'PIN transaksi 6-digit wajib diisi!',
            'security_pin.digits'   => 'PIN transaksi harus tepat 6 digit angka!',
            'password.required'     => 'Kata sandi (password) login wajib diisi!',
            'password.min'          => 'Kata sandi (password) login minimal 6 karakter!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $todayClosing = $this->checkTodayClosingStatus($request->user()->id);
        if ($todayClosing) {
            $statusLabel = ($todayClosing->status === 'approved') ? 'DISETUJUI' : 'SELESAI DIREKONSILIASI';
            return response()->json([
                'success' => false,
                'message' => 'Kas hari ini sudah ditutup (' . $statusLabel . '). Anda tidak dapat melakukan aktivasi rekening lagi hari ini. Transaksi baru dapat dilakukan pada hari kerja berikutnya.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $member = Member::where('id', $memberId)->lockForUpdate()->first();

            if ($member->status === 'active_account') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Anggota ini sudah memiliki rekening aktif.'], 422);
            }

            $accountNumber = $this->generateAccountNumber();

            // Cek apakah sudah pernah ada Saver (misal soft-deleted)
            $existingSaver = Saver::withTrashed()->where('member_id', $member->id)->first();
            if ($existingSaver) {
                $existingSaver->restore();
                $existingSaver->update([
                    'account_number' => $accountNumber,
                    'nis'            => $member->nis,
                    'name'           => $member->name,
                    'class_name'     => $member->class_name,
                    'security_pin'   => Hash::make($request->security_pin),
                    'balance'        => $amount,
                    'status'         => 'active',
                ]);
                $saver = $existingSaver;
            } else {
                $saver = Saver::create([
                    'member_id'      => $member->id,
                    'account_number' => $accountNumber,
                    'nis'            => $member->nis,
                    'name'           => $member->name,
                    'class_name'     => $member->class_name,
                    'security_pin'   => Hash::make($request->security_pin),
                    'balance'        => $amount,
                    'status'         => 'active',
                ]);
            }

            // Update status & password login anggota
            $member->update([
                'password' => Hash::make($request->password),
                'status'   => 'active_account',
            ]);

            // Catat Transaksi Setoran Perdana dengan Snapshot Kolom
            $transCode = 'TRX-ACT-' . date('YmdHis') . '-' . rand(100, 999);
            $transaction = Transaction::create([
                'transaction_code'        => $transCode,
                'saver_id'                => $saver->id,
                'saver_name_snapshot'     => $saver->name,
                'account_number_snapshot' => $saver->account_number,
                'user_id'                 => $request->user()->id,
                'transaction_date'        => now(),
                'transaction_type'        => 'deposit',
                'amount'                  => $amount,
                'balance_before'          => 0,
                'balance_after'           => $amount,
                'description'             => 'Setoran Awal & Aktivasi Rekening di Loket Teller',
            ]);

            // Jurnal Double-Entry: Debet 101 Kas Loket / Kredit 201 Simpanan Siswa
            AccountingJournal::create([
                'transaction_id' => $transaction->id,
                'account_code'   => '101',
                'account_name'   => 'Kas Loket Teller',
                'position'       => 'debit',
                'amount'         => $amount,
            ]);

            AccountingJournal::create([
                'transaction_id' => $transaction->id,
                'account_code'   => '201',
                'account_name'   => 'Simpanan Tabungan Siswa',
                'position'       => 'credit',
                'amount'         => $amount,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rekening Siswa [' . $member->name . '] Berhasil Diaktifkan! No Rek: ' . $saver->account_number,
                'data'    => [
                    'saver'       => $saver,
                    'transaction' => $transaction,
                    'receipt'     => [
                        'transaction_code' => $transaction->transaction_code,
                        'date'             => $transaction->transaction_date->format('d/m/Y H:i:s'),
                        'teller'           => $request->user()->name,
                        'account_number'   => $saver->account_number,
                        'name'             => $saver->name,
                        'type'             => 'AKTIVASI & SETORAN AWAL',
                        'amount'           => $amount,
                        'balance'          => $amount,
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal aktivasi rekening: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 4. Reset PIN Siswa di Loket Teller (Fitur Lupa PIN)
     */
    public function resetPin(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'new_pin' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'PIN harus 6 digit angka!', 'errors' => $validator->errors()], 422);
        }

        $saver = Saver::find($id);
        if (!$saver) {
            return response()->json(['success' => false, 'message' => 'Penabung tidak ditemukan'], 404);
        }

        $saver->update(['security_pin' => Hash::make($request->new_pin)]);

        return response()->json(['success' => true, 'message' => 'PIN Keamanan berhasil direset oleh Teller!']);
    }

    /**
     * 5. Pencarian Data Penabung via QR Code atau Nomor Rekening / NIS
     */
    public function searchCustomer(Request $request)
    {
        $query = $request->query('query');

        if (!$query) {
            return response()->json(['success' => false, 'message' => 'Parameter pencarian wajib diisi'], 422);
        }

        // 1. Cari di tabel Savers (Nasabah)
        $saver = Saver::where('account_number', $query)
                      ->orWhere('nis', $query)
                      ->first();

        if ($saver) {
            // Cek apakah data anggota induknya belum berstatus aktif
            if ($saver->member && $saver->member->status === 'inactive_account') {
                return response()->json([
                    'success'               => false,
                    'is_unactivated_member' => true,
                    'member'                => [
                        'id'         => $saver->member->id,
                        'nis'        => $saver->nis,
                        'name'       => $saver->name,
                        'class_name' => $saver->class_name,
                    ],
                    'message'               => 'Peringatan: Anggota Siswa [' . $saver->name . '] (NIS: ' . $saver->nis . ') belum aktif sebagai nasabah! Silakan aktivasi rekening nasabah anggota ini langsung di loket teller.'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'             => $saver->id,
                    'account_number' => $saver->account_number,
                    'nis'            => $saver->nis,
                    'name'           => $saver->name,
                    'class_name'     => $saver->class_name,
                    'balance'        => (int) $saver->balance,
                    'status'         => $saver->status,
                    'email'          => ($saver->member && $saver->member->email) ? $saver->member->email : null,
                ]
            ]);
        }

        // 2. Jika tidak ada di savers, cari di tabel Members (Anggota Siswa Sekolah)
        $member = Member::where('nis', $query)
                        ->orWhere('name', 'LIKE', "%{$query}%")
                        ->first();

        if ($member) {
            if ($member->status === 'inactive_account') {
                return response()->json([
                    'success'               => false,
                    'is_unactivated_member' => true,
                    'member'                => [
                        'id'         => $member->id,
                        'nis'        => $member->nis,
                        'name'       => $member->name,
                        'class_name' => $member->class_name,
                    ],
                    'message'               => 'Peringatan: Anggota Siswa [' . $member->name . '] (NIS: ' . $member->nis . ', Kelas: ' . $member->class_name . ') belum aktif sebagai nasabah! Silakan aktivasi rekening nasabah anggota ini langsung di loket teller.'
                ], 422);
            }

            // Jika statusnya active_account tapi tadi dicari via NIS atau nama dan ada saver
            if ($member->saver) {
                $saver = $member->saver;
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'id'             => $saver->id,
                        'account_number' => $saver->account_number,
                        'nis'            => $saver->nis,
                        'name'           => $saver->name,
                        'class_name'     => $saver->class_name,
                        'balance'        => (int) $saver->balance,
                        'status'         => $saver->status,
                    ]
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Penabung atau Anggota dengan kata kunci [' . $query . '] tidak ditemukan.'
        ], 404);
    }

    /**
     * 6. Eksekusi Setor Tunai (Deposit)
     */
    public function deposit(Request $request)
    {
        $targetId = $request->saver_id ?? $request->customer_id;
        $request->merge(['saver_id' => $targetId]);

        $validator = Validator::make($request->all(), [
            'saver_id'    => 'required|exists:savers,id',
            'amount'      => 'required|integer|min:1000',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $todayClosing = $this->checkTodayClosingStatus($request->user()->id);
        if ($todayClosing) {
            $statusLabel = ($todayClosing->status === 'approved') ? 'DISETUJUI' : 'SELESAI DIREKONSILIASI';
            return response()->json([
                'success' => false,
                'message' => 'Kas hari ini sudah ditutup (' . $statusLabel . '). Anda tidak dapat melakukan transaksi setoran lagi hari ini. Transaksi baru dapat dilakukan pada hari kerja berikutnya.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Lock row saver untuk mencegah race condition
            $saver = Saver::where('id', $targetId)->lockForUpdate()->first();

            if ($saver->status !== 'active') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Rekening nasabah ini sedang tidak aktif.'], 403);
            }

            $amount = (int) $request->amount;
            $balanceBefore = (int) $saver->balance;

            // Validasi Setoran Awal: Jika nasabah belum memiliki saldo (saldo Rp 0), setoran awal minimal Rp 10.000
            if ($balanceBefore === 0 && $amount < 10000) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Setoran Awal Ditolak: Rekening nasabah baru belum memiliki saldo mengendap. Setoran awal perdana wajib minimal Rp 10.000!'
                ], 422);
            }

            $balanceAfter = $balanceBefore + $amount;

            // 1. Simpan Transaksi dengan Snapshot Kolom
            $transCode = 'TRX-DEP-' . date('YmdHis') . '-' . rand(100, 999);
            $transaction = Transaction::create([
                'transaction_code'        => $transCode,
                'saver_id'                => $saver->id,
                'saver_name_snapshot'     => $saver->name,
                'account_number_snapshot' => $saver->account_number,
                'user_id'                 => $request->user()->id,
                'transaction_date'        => now(),
                'transaction_type'        => 'deposit',
                'amount'                  => $amount,
                'balance_before'          => $balanceBefore,
                'balance_after'           => $balanceAfter,
                'description'             => $request->description ?? 'Setoran Tunai di Loket Teller',
            ]);

            // 2. Update Saldo Penabung
            $saver->update(['balance' => $balanceAfter]);

            // 3. Rubrik 2.3: Double-Entry Jurnal Akuntansi (Debet 101 Kas / Kredit 201 Tabungan)
            // Baris 1: Debet Kas Loket Teller (101)
            AccountingJournal::create([
                'transaction_id' => $transaction->id,
                'account_code'   => '101',
                'account_name'   => 'Kas Loket Teller',
                'position'       => 'debit',
                'amount'         => $amount,
            ]);

            // Baris 2: Kredit Tabungan Nasabah (201)
            AccountingJournal::create([
                'transaction_id' => $transaction->id,
                'account_code'   => '201',
                'account_name'   => 'Simpanan Tabungan Siswa',
                'position'       => 'credit',
                'amount'         => $amount,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Setoran Tunai Sebesar Rp ' . number_format($amount, 0, ',', '.') . ' Berhasil!',
                'data'    => [
                    'transaction' => $transaction->load(['saver', 'user']),
                    'receipt'     => [
                        'transaction_code' => $transaction->transaction_code,
                        'date'             => $transaction->transaction_date->format('d/m/Y H:i:s'),
                        'teller'           => $request->user()->name,
                        'account_number'   => $saver->account_number,
                        'name'             => $saver->name,
                        'type'             => 'SETORAN TUNAI',
                        'amount'           => $amount,
                        'balance'          => $balanceAfter,
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memproses setoran: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 7. Eksekusi Penarikan Tunai (Withdrawal)
     */
    public function withdraw(Request $request)
    {
        $targetId = $request->saver_id ?? $request->customer_id;
        $request->merge(['saver_id' => $targetId]);

        $validator = Validator::make($request->all(), [
            'saver_id'     => 'required|exists:savers,id',
            'amount'       => 'required|integer|min:1000',
            'security_pin' => 'required|digits:6',
            'description'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $todayClosing = $this->checkTodayClosingStatus($request->user()->id);
        if ($todayClosing) {
            $statusLabel = ($todayClosing->status === 'approved') ? 'DISETUJUI' : 'SELESAI DIREKONSILIASI';
            return response()->json([
                'success' => false,
                'message' => 'Kas hari ini sudah ditutup (' . $statusLabel . '). Anda tidak dapat melakukan transaksi penarikan lagi hari ini. Transaksi baru dapat dilakukan pada hari kerja berikutnya.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $saver = Saver::where('id', $targetId)->lockForUpdate()->first();

            if ($saver->status !== 'active') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Rekening nasabah ini sedang diblokir.'], 403);
            }

            // A. Rubrik 3.1: Verifikasi 6-Digit PIN Keamanan Nasabah
            if (!Hash::check($request->security_pin, $saver->security_pin)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Otorisasi Gagal: PIN Keamanan 6-Digit Nasabah Salah! Silakan periksa kembali PIN nasabah.'
                ], 422);
            }

            $amount = (int) $request->amount;
            $balanceBefore = (int) $saver->balance;
            $minimumBalance = 10000;

            // B. Validasi Batas Saldo Mengendap Minimum Rp 10.000
            if ($balanceBefore === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Penarikan Ditolak: Saldo rekening nasabah saat ini Rp 0. Tidak ada dana yang dapat ditarik.'
                ], 422);
            }

            if ($balanceBefore <= $minimumBalance) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Penarikan Ditolak: Saldo saat ini (Rp ' . number_format($balanceBefore, 0, ',', '.') . ') merupakan saldo mengendap minimum (Rp 10.000) yang tidak dapat ditarik.'
                ], 422);
            }

            $remainingBalance = $balanceBefore - $amount;

            if ($remainingBalance < $minimumBalance) {
                $maxWithdraw = $balanceBefore - $minimumBalance;
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Penarikan Ditolak: Sisa saldo setelah penarikan tidak boleh di bawah saldo mengendap minimum (Rp 10.000). Maksimal penarikan yang diizinkan adalah Rp ' . number_format($maxWithdraw, 0, ',', '.') . '.'
                ], 422);
            }

            $balanceAfter = $remainingBalance;

            // C. Validasi Saldo Kas Fisik Laci Loket Teller (Mencegah Saldo Minus & Kasir Kehabisan Uang Fisik)
            $tellerId = $request->user()->id;
            $today = today();
            $openingCash = (int) TellerCashAdvance::where('teller_id', $tellerId)
                ->whereDate('request_date', $today)
                ->where('status', 'approved')
                ->sum('amount');
            $depositCash = (int) Transaction::where('user_id', $tellerId)
                ->whereDate('transaction_date', $today)
                ->where('transaction_type', 'deposit')
                ->sum('amount');
            $withdrawCash = (int) Transaction::where('user_id', $tellerId)
                ->whereDate('transaction_date', $today)
                ->where('transaction_type', 'withdraw')
                ->sum('amount');
            $currentDrawerCash = $openingCash + $depositCash - $withdrawCash;

            if ($currentDrawerCash < $amount) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Kas Laci Loket Tidak Mencukupi: Saldo kas loket Anda saat ini hanya Rp ' . number_format($currentDrawerCash, 0, ',', '.') . ', sedangkan penarikan sebesar Rp ' . number_format($amount, 0, ',', '.') . '. Harap ajukan Top Up Saldo Modal ke Admin terlebih dahulu!'
                ], 422);
            }

            // 1. Simpan Transaksi dengan Snapshot Kolom
            $transCode = 'TRX-WDR-' . date('YmdHis') . '-' . rand(100, 999);
            $transaction = Transaction::create([
                'transaction_code'        => $transCode,
                'saver_id'                => $saver->id,
                'saver_name_snapshot'     => $saver->name,
                'account_number_snapshot' => $saver->account_number,
                'user_id'                 => $request->user()->id,
                'transaction_date'        => now(),
                'transaction_type'        => 'withdraw',
                'amount'                  => $amount,
                'balance_before'          => $balanceBefore,
                'balance_after'           => $balanceAfter,
                'description'             => $request->description ?? 'Penarikan Tunai di Loket Teller',
            ]);

            // 2. Potong Saldo Penabung
            $saver->update(['balance' => $balanceAfter]);

            // 3. Rubrik 2.3: Double-Entry Jurnal Akuntansi (Debet 201 Tabungan / Kredit 101 Kas)
            // Baris 1: Debet Tabungan Nasabah (201)
            AccountingJournal::create([
                'transaction_id' => $transaction->id,
                'account_code'   => '201',
                'account_name'   => 'Simpanan Tabungan Siswa',
                'position'       => 'debit',
                'amount'         => $amount,
            ]);

            // Baris 2: Kredit Kas Loket Teller (101)
            AccountingJournal::create([
                'transaction_id' => $transaction->id,
                'account_code'   => '101',
                'account_name'   => 'Kas Loket Teller',
                'position'       => 'credit',
                'amount'         => $amount,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penarikan Tunai Sebesar Rp ' . number_format($amount, 0, ',', '.') . ' Berhasil!',
                'data'    => [
                    'transaction' => $transaction->load(['saver', 'user']),
                    'receipt'     => [
                        'transaction_code' => $transaction->transaction_code,
                        'date'             => $transaction->transaction_date->format('d/m/Y H:i:s'),
                        'teller'           => $request->user()->name,
                        'account_number'   => $saver->account_number,
                        'name'             => $saver->name,
                        'type'             => 'PENARIKAN TUNAI',
                        'amount'           => $amount,
                        'balance'          => $balanceAfter,
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memproses penarikan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 8. Daftar Mutasi Transaksi Loket Hari Ini
     */
    public function todayTransactions(Request $request)
    {
        $transactions = Transaction::with('saver')
                                   ->where('user_id', $request->user()->id)
                                   ->whereDate('transaction_date', today())
                                   ->orderBy('id', 'desc')
                                   ->get();

        return response()->json(['success' => true, 'data' => $transactions]);
    }

    /**
     * 9. Kalkulasi Draf Closing Kas Hari Ini (Rumus Saldo Awal + Setor - Tarik)
     */
    public function getClosingCalculation(Request $request)
    {
        $tellerId = $request->user()->id;
        $today = today();

        $totalDeposit = Transaction::where('user_id', $tellerId)
                                   ->whereDate('transaction_date', $today)
                                   ->where('transaction_type', 'deposit')
                                   ->sum('amount');

        $totalWithdraw = Transaction::where('user_id', $tellerId)
                                    ->whereDate('transaction_date', $today)
                                    ->where('transaction_type', 'withdraw')
                                    ->sum('amount');

        // Saldo Awal dari akumulasi Modal Awal & Top Up Teller yang disetujui Admin hari ini
        $openingBalance = (int) TellerCashAdvance::where('teller_id', $tellerId)
                                                 ->whereDate('request_date', $today)
                                                 ->where('status', 'approved')
                                                 ->sum('amount');
        $systemBalance = $openingBalance + (int) $totalDeposit - (int) $totalWithdraw;

        $existingClosing = CashClosing::with(['supervisor'])
                                      ->where('teller_id', $tellerId)
                                      ->whereDate('closing_date', $today)
                                      ->first();

        $isClosed = $existingClosing && in_array($existingClosing->status, ['submitted', 'approved', 'dispute']);

        return response()->json([
            'success' => true,
            'data'    => [
                'closing_date'     => $today->format('Y-m-d'),
                'opening_balance'  => (int) $openingBalance,
                'total_deposit'    => (int) $totalDeposit,
                'total_withdraw'   => (int) $totalWithdraw,
                'system_balance'   => (int) $systemBalance,
                'existing_closing' => $existingClosing,
                'is_closed'        => $isClosed,
            ]
        ]);
    }

    /**
     * 10. Submit Rekonsiliasi Kas Teller ke Administrator
     */
    public function submitClosing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'physical_balance' => 'required|integer|min:0',
            'notes'            => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $tellerId = $request->user()->id;
        $today = today();

        $existing = CashClosing::where('teller_id', $tellerId)
                               ->whereDate('closing_date', $today)
                               ->first();

        if ($existing && $existing->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Rekonsiliasi kas hari ini telah disetujui (Approved) oleh Administrator dan tidak dapat diubah lagi.'
            ], 422);
        }

        $totalDeposit = (int) Transaction::where('user_id', $tellerId)->whereDate('transaction_date', $today)->where('transaction_type', 'deposit')->sum('amount');
        $totalWithdraw = (int) Transaction::where('user_id', $tellerId)->whereDate('transaction_date', $today)->where('transaction_type', 'withdraw')->sum('amount');
        
        // Saldo Awal dari akumulasi Modal Awal & Top Up yang disetujui hari ini
        $openingBalance = (int) TellerCashAdvance::where('teller_id', $tellerId)
                                                 ->whereDate('request_date', $today)
                                                 ->where('status', 'approved')
                                                 ->sum('amount');
        $systemBalance = $openingBalance + $totalDeposit - $totalWithdraw;
        $physicalBalance = (int) $request->physical_balance;
        $difference = $physicalBalance - $systemBalance;

        // Rubrik 2.4: Jika selisih != 0 maka status DISPUTE, jika 0 maka SUBMITTED
        $status = ($difference === 0) ? 'submitted' : 'dispute';

        $closing = CashClosing::updateOrCreate(
            [
                'teller_id'    => $tellerId,
                'closing_date' => $today->format('Y-m-d'),
            ],
            [
                'opening_balance'  => $openingBalance,
                'total_deposit'    => $totalDeposit,
                'total_withdraw'   => $totalWithdraw,
                'system_balance'   => $systemBalance,
                'physical_balance' => $physicalBalance,
                'difference'       => $difference,
                'status'           => $status,
                'rejection_reason' => null,
                'notes'            => $request->notes,
            ]
        );

        $message = ($difference === 0) 
            ? 'Rekonsiliasi Kas Berhasil (Balance Rp 0)! Laporan harian siap diverifikasi Administrator.'
            : 'PERINGATAN: Terdeteksi Selisih Kas sebesar Rp ' . number_format($difference, 0, ',', '.') . ' (Status: DISPUTE). Harap periksa bundel slip transaksi fisik Anda.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $closing
        ]);
    }

    /**
     * 11. Ambil Ringkasan Saldo Kas Loket & Status Modal Awal Teller Hari Ini
     */
    public function getCashAdvanceSummary(Request $request)
    {
        $tellerId = $request->user()->id;
        $today = today();

        $openingApproved = (int) TellerCashAdvance::where('teller_id', $tellerId)
            ->whereDate('request_date', $today)
            ->where('status', 'approved')
            ->sum('amount');

        $totalDeposit = (int) Transaction::where('user_id', $tellerId)
            ->whereDate('transaction_date', $today)
            ->where('transaction_type', 'deposit')
            ->sum('amount');

        $totalWithdraw = (int) Transaction::where('user_id', $tellerId)
            ->whereDate('transaction_date', $today)
            ->where('transaction_type', 'withdraw')
            ->sum('amount');

        $currentBalance = $openingApproved + $totalDeposit - $totalWithdraw;

        $requests = TellerCashAdvance::with(['approvedBy'])
            ->where('teller_id', $tellerId)
            ->whereDate('request_date', $today)
            ->orderBy('id', 'desc')
            ->get();

        $pendingRequest = $requests->firstWhere('status', 'pending');
        $hasApproved = $requests->where('status', 'approved')->count() > 0;

        $existingClosing = CashClosing::where('teller_id', $tellerId)
            ->whereDate('closing_date', $today)
            ->first();
        $isClosed = $existingClosing && in_array($existingClosing->status, ['submitted', 'approved', 'dispute']);

        return response()->json([
            'success' => true,
            'data'    => [
                'date'              => $today->format('Y-m-d'),
                'opening_balance'   => $openingApproved,
                'total_deposit'     => $totalDeposit,
                'total_withdraw'    => $totalWithdraw,
                'current_balance'   => $currentBalance,
                'pending_request'   => $pendingRequest,
                'has_approved'      => $hasApproved,
                'requests_today'    => $requests,
                'is_closed'         => $isClosed,
            ]
        ]);
    }

    /**
     * 12. Ajukan Modal Awal / Top Up Kas Loket Teller ke Admin
     */
    public function requestCashAdvance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:10000',
            'type'   => 'nullable|string|in:initial,topup',
            'notes'  => 'nullable|string|max:255',
        ], [
            'amount.min' => 'Nominal pengajuan saldo modal minimal Rp 10.000.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $tellerId = $request->user()->id;
        $today = today();

        // Cek apakah kas teller hari ini sudah closing
        $existingClosing = CashClosing::where('teller_id', $tellerId)
            ->whereDate('closing_date', $today)
            ->first();
        if ($existingClosing && in_array($existingClosing->status, ['submitted', 'approved', 'dispute'])) {
            return response()->json([
                'success' => false,
                'message' => 'Kas loket hari ini telah ditutup (Closing). Tidak dapat mengajukan modal awal lagi hari ini.'
            ], 422);
        }

        // Cek jika masih ada pengajuan yang berstatus pending
        $pending = TellerCashAdvance::where('teller_id', $tellerId)
            ->whereDate('request_date', $today)
            ->where('status', 'pending')
            ->first();

        if ($pending) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih memiliki permohonan saldo modal sebesar Rp ' . number_format($pending->amount, 0, ',', '.') . ' yang sedang menunggu persetujuan (Pending) dari Admin!'
            ], 422);
        }

        // Tentukan apakah initial atau topup
        $hasApproved = TellerCashAdvance::where('teller_id', $tellerId)
            ->whereDate('request_date', $today)
            ->where('status', 'approved')
            ->exists();

        $type = $request->type ?? ($hasApproved ? 'topup' : 'initial');

        $advance = TellerCashAdvance::create([
            'teller_id'    => $tellerId,
            'request_date' => $today,
            'amount'       => (int) $request->amount,
            'type'         => $type,
            'status'       => 'pending',
            'notes'        => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan ' . ($type === 'initial' ? 'Modal Awal' : 'Top Up Kas') . ' sebesar Rp ' . number_format($advance->amount, 0, ',', '.') . ' berhasil dikirim ke Admin! Menunggu persetujuan.',
            'data'    => $advance
        ]);
    }

    /**
     * 10. Ambil Status Pelayanan Loket Teller Saat Ini (Publik - Dapat diakses Mobile App)
     */
    public function getServiceStatus()
    {
        $statusData = Cache::get('teller_service_status');

        if (!$statusData) {
            $filePath = storage_path('app/teller_status.json');
            if (file_exists($filePath)) {
                $statusData = json_decode(file_get_contents($filePath), true);
            }
        }

        if (!$statusData || !is_array($statusData)) {
            $statusData = [
                'status'      => 'buka',
                'label'       => 'BUKA',
                'color'       => 'emerald',
                'description' => 'Loket Buka - Siap melayani setoran & penarikan',
                'updated_at'     => now()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB',
                'updated_at_iso' => now()->setTimezone('Asia/Jakarta')->toIso8601String(),
                'teller_name'    => 'Petugas Loket',
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $statusData,
        ]);
    }

    /**
     * 11. Perbarui Status Pelayanan Loket Teller (Opsi: buka, istirahat, tutup)
     */
    public function updateServiceStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:buka,istirahat,tutup',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Status pelayanan loket tidak valid! Pilihan: buka, istirahat, tutup.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $status = strtolower($request->status);
        $meta = [
            'buka' => [
                'label'       => 'BUKA',
                'color'       => 'emerald',
                'description' => 'Loket Buka - Siap melayani setoran & penarikan',
            ],
            'istirahat' => [
                'label'       => 'ISTIRAHAT',
                'color'       => 'amber',
                'description' => 'Loket Sedang Istirahat - Pelayanan dijeda sementara',
            ],
            'tutup' => [
                'label'       => 'TUTUP',
                'color'       => 'rose',
                'description' => 'Loket Tutup - Pelayanan loket telah berakhir',
            ],
        ];

        $user = $request->user();
        $payload = [
            'status'      => $status,
            'label'       => $meta[$status]['label'],
            'color'       => $meta[$status]['color'],
            'description' => $meta[$status]['description'],
            'updated_at'     => now()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB',
            'updated_at_iso' => now()->setTimezone('Asia/Jakarta')->toIso8601String(),
            'teller_name'    => $user ? $user->name : 'Petugas Teller',
        ];

        Cache::forever('teller_service_status', $payload);

        try {
            @file_put_contents(storage_path('app/teller_status.json'), json_encode($payload));
        } catch (\Exception $e) {
            // Ignore filesystem write error if permission issue
        }

        return response()->json([
            'success' => true,
            'message' => 'Status loket teller berhasil diperbarui menjadi: ' . $meta[$status]['label'],
            'data'    => $payload,
        ]);
    }
}