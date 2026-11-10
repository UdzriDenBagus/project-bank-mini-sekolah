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
use App\Models\WithdrawalAppointment;
use Carbon\Carbon;

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

            $balance = (int) $saver->balance;
            $heldBalance = 10000;
            $maxAvailable = max(0, $balance - $heldBalance);
            $maxDirect = min(1000000, $maxAvailable);

            $activeH1 = WithdrawalAppointment::where('saver_id', $saver->id)
                ->where('status', 'approved')
                ->whereDate('appointment_date', '<=', today()->subDay())
                ->whereDate('withdrawal_date', '<=', today())
                ->orderBy('id', 'desc')
                ->first();

            $pendingApt = WithdrawalAppointment::where('saver_id', $saver->id)
                ->where('status', 'pending')
                ->orderBy('id', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'                     => $saver->id,
                    'account_number'         => $saver->account_number,
                    'nis'                    => $saver->nis,
                    'name'                   => $saver->name,
                    'class_name'             => $saver->class_name,
                    'balance'                => $balance,
                    'held_balance'           => $heldBalance,
                    'max_available_balance'  => $maxAvailable,
                    'max_direct_withdrawal'  => $maxDirect,
                    'has_active_appointment' => !is_null($activeH1),
                    'active_appointment'     => $activeH1,
                    'has_pending_appointment'=> !is_null($pendingApt),
                    'pending_appointment'    => $pendingApt,
                    'status'                 => $saver->status,
                    'email'                  => ($saver->member && $saver->member->email) ? $saver->member->email : null,
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
    /**
     * =========================================================================
     * MODUL TRANSAKSI 1: SETORAN TUNAI DI LOKET TELLER (DEPOSIT)
     * =========================================================================
     * Alur Kerja Sistem Saat Presentasi:
     * 1. Validasi Input: Pastikan rekening nasabah terdaftar & nominal >= Rp 1.000.
     * 2. Proteksi Closing: Tolak setoran jika teller sudah melakukan closing kas hari ini.
     * 3. Row Locking (Pessimistic Lock): 'lockForUpdate()' untuk mencegah saldo ganda (race condition).
     * 4. Validasi Setoran Awal: Jika rekening baru (saldo Rp 0), wajib setoran awal minimal Rp 10.000.
     * 5. Snapshot Data: Simpan nama & no. rekening pada tabel transaksi agar riwayat tetap valid 
     *    meskipun data siswa suatu saat diubah/dihapus.
     * 6. Double-Entry Akuntansi (Rubrik UKK):
     *    - Debet  101 (Kas Loket Teller)      -> Uang tunai fisik teller bertambah (+)
     *    - Kredit 201 (Simpanan Siswa)        -> Kewajiban simpanan bank kepada nasabah bertambah (+)
     * =========================================================================
     */
    public function deposit(Request $request)
    {
        $targetId = $request->saver_id ?? $request->customer_id;
        $request->merge(['saver_id' => $targetId]);

        // Langkah 1: Validasi form request
        $validator = Validator::make($request->all(), [
            'saver_id'    => 'required|exists:savers,id',
            'amount'      => 'required|integer|min:1000',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Langkah 2: Cek status closing harian teller
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
            // Langkah 3: Kunci baris data penabung (Pessimistic Locking)
            $saver = Saver::where('id', $targetId)->lockForUpdate()->first();

            if ($saver->status !== 'active') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Rekening nasabah ini sedang tidak aktif.'], 403);
            }

            $amount = (int) $request->amount;
            $balanceBefore = (int) $saver->balance;

            // Langkah 4: Validasi saldo mengendap perdana (Minimal Rp 10.000)
            if ($balanceBefore === 0 && $amount < 10000) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Setoran Awal Ditolak: Rekening nasabah baru belum memiliki saldo mengendap. Setoran awal perdana wajib minimal Rp 10.000!'
                ], 422);
            }

            $balanceAfter = $balanceBefore + $amount;

            // Langkah 5: Simpan Riwayat Transaksi Buku Tabungan (Dengan Snapshot Historis)
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

            // Langkah 6: Mutasi Tambah Saldo Nasabah
            $saver->update(['balance' => $balanceAfter]);

            // Langkah 7: Jurnal Akuntansi Berpasangan (Double-Entry Bookkeeping)
            // Baris 1: Debet Kas Loket Teller (Akun 101) - Aset Kas Bertambah
            AccountingJournal::create([
                'transaction_id' => $transaction->id,
                'account_code'   => '101',
                'account_name'   => 'Kas Loket Teller',
                'position'       => 'debit',
                'amount'         => $amount,
            ]);

            // Baris 2: Kredit Simpanan Tabungan Siswa (Akun 201) - Kewajiban Bertambah
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
     * =========================================================================
     * MODUL TRANSAKSI 2: PENARIKAN TUNAI DI LOKET TELLER (WITHDRAWAL)
     * =========================================================================
     * Alur Kerja Sistem Saat Presentasi:
     * 1. Validasi Input: Memeriksa saver_id, nominal penarikan, dan PIN 6-digit nasabah.
     * 2. Proteksi Closing: Mencegah penarikan jika teller telah closing kas hari ini.
     * 3. Row Locking: 'lockForUpdate()' mengunci record saldo nasabah selama proses transaksi.
     * 4. Otorisasi PIN (Rubrik 3.1): Validasi hash 6-digit PIN nasabah (bukan password login).
     * 5. Saldo Mengendap Rp 10.000: Saldo nasabah wajib bersisa minimal Rp 10.000.
     *    Contoh: Saldo Rp 1.000.000 -> Maksimal tarik tunai adalah Rp 990.000.
     * 6. Batas Langsung vs Perjanjian H-1:
     *    - Penarikan <= Rp 1.000.000: Langsung diproses tanpa syarat perjanjian.
     *    - Penarikan > Rp 1.000.000 : Wajib memiliki perjanjian H-1 yang sudah di-ACC teller.
     * 7. Validasi Kas Fisik Laci Teller:
     *    - Fisik Kas = Modal Awal (Cash Advance) + Setoran Hari Ini - Penarikan Hari Ini.
     *    - Mencegah teller mengeluarkan uang melebihi ketersediaan fisik laci.
     * 8. Double-Entry Akuntansi (Rubrik 2.3):
     *    - Debet  201 (Simpanan Siswa)        -> Kewajiban bank kepada nasabah berkurang (-)
     *    - Kredit 101 (Kas Loket Teller)      -> Uang tunai fisik teller berkurang (-)
     * =========================================================================
     */
    public function withdraw(Request $request)
    {
        $targetId = $request->saver_id ?? $request->customer_id;
        $request->merge(['saver_id' => $targetId]);

        // Langkah 1: Validasi form penarikan & PIN keamanan
        $validator = Validator::make($request->all(), [
            'saver_id'     => 'required|exists:savers,id',
            'amount'       => 'required|integer|min:1000',
            'security_pin' => 'required|digits:6',
            'description'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Langkah 2: Cek status closing harian teller
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
            // Langkah 3: Kunci baris saldo penabung (Concurrency Control)
            $saver = Saver::where('id', $targetId)->lockForUpdate()->first();

            if ($saver->status !== 'active') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Rekening nasabah ini sedang diblokir.'], 403);
            }

            // Langkah 4 (Rubrik 3.1): Otorisasi 6-Digit PIN Keamanan Nasabah (Menggunakan Hash Bcrypt)
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
            $maxDirectLimit = 1000000; // Batas penarikan langsung maksimal Rp 1.000.000

            // Langkah 5: Validasi Batas Saldo Mengendap Minimum Rp 10.000
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

            $maxAvailableBalance = $balanceBefore - $minimumBalance;

            // Saldo mengendap 10k: jika saldo nasabah hanya 1 juta, maksimal tarik hanya 990k
            if ($amount > $maxAvailableBalance) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Penarikan Ditolak: Sisa saldo setelah penarikan tidak boleh di bawah saldo mengendap minimum (Rp 10.000). Saldo nasabah saat ini Rp ' . number_format($balanceBefore, 0, ',', '.') . ', sehingga maksimal dana yang dapat ditarik adalah Rp ' . number_format($maxAvailableBalance, 0, ',', '.') . '.'
                ], 422);
            }

            // Langkah 6: Validasi Penarikan Dana Besar (> Rp 1.000.000) Wajib Perjanjian H-1
            if ($amount > $maxDirectLimit) {
                $hasValidAppointment = false;
                $appointment = null;

                // 6a. Cek dari appointment_id jika dipilih di form loket
                if ($request->filled('appointment_id')) {
                    $apt = WithdrawalAppointment::where('id', $request->appointment_id)
                        ->where('saver_id', $saver->id)
                        ->where('status', 'approved')
                        ->first();
                    if ($apt && Carbon::parse($apt->appointment_date)->startOfDay()->lte(today()->subDay()->endOfDay())) {
                        $hasValidAppointment = true;
                        $appointment = $apt;
                    }
                }

                // 6b. Cek otomatis apakah ada janji di database yang sudah di-ACC minimal H-1
                if (!$hasValidAppointment) {
                    $apt = WithdrawalAppointment::where('saver_id', $saver->id)
                        ->where('status', 'approved')
                        ->whereDate('appointment_date', '<=', today()->subDay())
                        ->whereDate('withdrawal_date', '<=', today())
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($apt) {
                        $hasValidAppointment = true;
                        $appointment = $apt;
                    }
                }

                // 6c. Cek konfirmasi manual teller di loket (jika janji dibuat secara lisan kemarin)
                if (!$hasValidAppointment && $request->boolean('appointment_confirmed')) {
                    $manualDate = $request->appointment_date ? Carbon::parse($request->appointment_date)->startOfDay() : null;
                    if ($manualDate && $manualDate->lte(today()->subDay()->endOfDay())) {
                        $hasValidAppointment = true;
                        $appointment = WithdrawalAppointment::create([
                            'saver_id'         => $saver->id,
                            'teller_id'        => $request->user()->id,
                            'appointment_date' => $manualDate,
                            'withdrawal_date'  => today(),
                            'amount'           => $amount,
                            'status'           => 'completed',
                            'notes'            => 'Perjanjian penarikan H-1 dikonfirmasi manual oleh teller di loket',
                        ]);
                    }
                }

                // Tolak jika penarikan > 1 Juta tidak memiliki janji H-1 yang sah
                if (!$hasValidAppointment) {
                    $maxDirectPossible = min($maxDirectLimit, $maxAvailableBalance);
                    DB::rollBack();
                    return response()->json([
                        'success'       => false,
                        'is_over_limit' => true,
                        'max_direct'    => $maxDirectPossible,
                        'message'       => 'Penarikan Ditolak: Penarikan dana di atas Rp 1.000.000 wajib melakukan perjanjian dengan pihak teller minimal 1 hari sebelum penarikan (H-1). Saldo nasabah mencukupi, namun untuk penarikan langsung di loket tanpa perjanjian maksimal adalah Rp ' . number_format($maxDirectPossible, 0, ',', '.') . ' (dengan saldo mengendap Rp 10.000).'
                    ], 422);
                }

                if ($appointment && $appointment->status !== 'completed') {
                    $appointment->update(['status' => 'completed']);
                }
            }

            // Hitung saldo akhir setelah penarikan
            $balanceAfter = $balanceBefore - $amount;

            // Langkah 7: Validasi Saldo Kas Fisik Laci Loket Teller (Mencegah Kasir Kehabisan Uang Tunai)
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

            // Langkah 8: Simpan Transaksi Penarikan (Dengan Snapshot Data Historis)
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

            // Langkah 9: Potong Saldo Rekening Penabung
            $saver->update(['balance' => $balanceAfter]);

            // Langkah 10 (Rubrik 2.3): Double-Entry Jurnal Akuntansi (Debet 201 Simpanan / Kredit 101 Kas)
            // Baris 1: Debet Tabungan Nasabah (Akun 201) - Kewajiban Berkurang
            AccountingJournal::create([
                'transaction_id' => $transaction->id,
                'account_code'   => '201',
                'account_name'   => 'Simpanan Tabungan Siswa',
                'position'       => 'debit',
                'amount'         => $amount,
            ]);

            // Baris 2: Kredit Kas Loket Teller (Akun 101) - Aset Kas Keluar
            AccountingJournal::create([
                'transaction_id' => $transaction->id,
                'account_code'   => '101',
                'account_name'   => 'Kas Loket Teller',
                'position'       => 'credit',
                'amount'         => $amount,
            ]);

            // Jika penarikan menggunakan perjanjian (appointment), ubah status menjadi completed
            if (isset($appointment) && $appointment && $appointment->status !== 'completed') {
                $appointment->update(['status' => 'completed']);
            }

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

        $existingClosing = CashClosing::with(['approvedBy'])
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
     * 11b. Riwayat Seluruh Pengajuan Modal Awal & Top Up Kas Teller Ini
     */
    public function getCashAdvanceHistory(Request $request)
    {
        $tellerId = $request->user()->id;

        $advances = TellerCashAdvance::with(['approvedBy'])
            ->where('teller_id', $tellerId)
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $advances,
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

        if (!$statusData || !is_array($statusData) || empty($statusData['updated_at_iso'])) {
            $status = (is_array($statusData) && !empty($statusData['status'])) ? $statusData['status'] : 'buka';
            $meta = [
                'buka' => ['label' => 'BUKA', 'color' => 'emerald', 'description' => 'Loket Buka - Siap melayani setoran & penarikan'],
                'istirahat' => ['label' => 'ISTIRAHAT', 'color' => 'amber', 'description' => 'Loket Sedang Istirahat - Pelayanan dijeda sementara'],
                'tutup' => ['label' => 'TUTUP', 'color' => 'rose', 'description' => 'Loket Tutup - Pelayanan loket telah berakhir'],
            ];
            $currentStatus = in_array($status, ['buka', 'istirahat', 'tutup']) ? $status : 'buka';
            $statusData = [
                'status'         => $currentStatus,
                'label'          => $meta[$currentStatus]['label'],
                'color'          => $meta[$currentStatus]['color'],
                'description'    => $meta[$currentStatus]['description'],
                'updated_at'     => now()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB',
                'updated_at_iso' => now()->setTimezone('Asia/Jakarta')->toIso8601String(),
                'teller_name'    => 'Petugas Loket',
            ];
            Cache::forever('teller_service_status', $statusData);
            try {
                @file_put_contents(storage_path('app/teller_status.json'), json_encode($statusData));
            } catch (\Exception $e) {}
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

    /**
     * 14. Ambil Daftar Perjanjian Penarikan Dana Besar Nasabah
     */
    public function getWithdrawalAppointments(Request $request)
    {
        $saverId = $request->saver_id ?? $request->customer_id;

        $query = WithdrawalAppointment::with(['saver', 'teller']);
        if ($saverId) {
            $query->where('saver_id', $saverId);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderByRaw("CASE WHEN status = 'pending' THEN 1 WHEN status = 'approved' THEN 2 ELSE 3 END")
                              ->orderBy('withdrawal_date', 'asc')
                              ->orderBy('id', 'desc')
                              ->get();

        $activeH1 = $saverId ? $appointments->first(function ($apt) {
            return $apt->status === 'approved' && Carbon::parse($apt->appointment_date)->startOfDay()->lte(today()->subDay()->endOfDay());
        }) : null;

        $pendingCount = WithdrawalAppointment::where('status', 'pending')->count();

        return response()->json([
            'success'       => true,
            'data'          => $appointments,
            'active_h1'     => $activeH1,
            'pending_count' => $pendingCount,
        ]);
    }

    /**
     * 15. Buat Perjanjian Penarikan Dana Besar (> Rp 1.000.000, Minimal H-1) oleh Teller
     */
    public function createWithdrawalAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'saver_id'        => 'required|exists:savers,id',
            'amount'          => 'required|integer|min:1000001',
            'withdrawal_date' => 'required|date|after_or_equal:tomorrow',
            'notes'           => 'nullable|string|max:255',
        ], [
            'amount.min'                     => 'Nominal perjanjian penarikan harus di atas Rp 1.000.000.',
            'withdrawal_date.after_or_equal' => 'Tanggal rencana penarikan wajib minimal 1 hari ke depan (H-1 dari hari ini).',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $saver = Saver::findOrFail($request->saver_id);
        $minimumBalance = 10000;
        $maxAvailable = $saver->balance - $minimumBalance;

        if ($request->amount > $maxAvailable) {
            return response()->json([
                'success' => false,
                'message' => 'Rencana Penarikan Melebihi Saldo: Saldo nasabah saat ini Rp ' . number_format($saver->balance, 0, ',', '.') . '. Batas maksimal penarikan setelah memperhitungkan saldo mengendap (Rp 10.000) adalah Rp ' . number_format(max(0, $maxAvailable), 0, ',', '.') . '.'
            ], 422);
        }

        $appointment = WithdrawalAppointment::create([
            'saver_id'         => $saver->id,
            'teller_id'        => $request->user()->id,
            'appointment_date' => today(),
            'withdrawal_date'  => $request->withdrawal_date,
            'amount'           => $request->amount,
            'status'           => 'approved',
            'notes'            => $request->notes ?? 'Perjanjian penarikan dana besar di loket teller',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perjanjian penarikan dana besar sebesar Rp ' . number_format($request->amount, 0, ',', '.') . ' untuk tanggal ' . Carbon::parse($request->withdrawal_date)->translatedFormat('d F Y') . ' berhasil dicatat!',
            'data'    => $appointment,
        ]);
    }

    /**
     * 16. ACC / Setujui Perjanjian Penarikan Dana Nasabah oleh Teller
     */
    public function approveWithdrawalAppointment(Request $request, $id)
    {
        $appointment = WithdrawalAppointment::with('saver')->findOrFail($id);

        if ($appointment->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Perjanjian ini sudah selesai dicairkan sebelumnya.'
            ], 422);
        }

        $appointment->update([
            'status'    => 'approved',
            'teller_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perjanjian penarikan nasabah [' . $appointment->saver->name . '] sebesar Rp ' . number_format($appointment->amount, 0, ',', '.') . ' BERHASIL DI-ACC (Disetujui)! Status di akun nasabah kini berubah menjadi Di-ACC.',
            'data'    => $appointment,
        ]);
    }

    /**
     * 17. Tolak Perjanjian Penarikan Dana Nasabah oleh Teller
     */
    public function rejectWithdrawalAppointment(Request $request, $id)
    {
        $appointment = WithdrawalAppointment::with('saver')->findOrFail($id);

        if ($appointment->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Perjanjian ini sudah selesai dicairkan dan tidak dapat ditolak.'
            ], 422);
        }

        $reason = $request->reason ? trim($request->reason) : 'Ditolak oleh Petugas Teller';
        $notes = $appointment->notes ? ($appointment->notes . ' | Alasan Ditolak: ' . $reason) : ('Alasan Ditolak: ' . $reason);

        $appointment->update([
            'status'    => 'rejected',
            'teller_id' => $request->user()->id,
            'notes'     => $notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perjanjian penarikan nasabah [' . $appointment->saver->name . '] berhasil ditolak.',
            'data'    => $appointment,
        ]);
    }
}