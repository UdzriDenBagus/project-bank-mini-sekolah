<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\AccountingJournal;
use App\Models\CashClosing;

class TellerController extends Controller
{
    /**
     * 1. Pencarian Data Nasabah via QR Code atau Nomor Rekening / NIS
     */
    public function searchCustomer(Request $request)
    {
        $query = $request->query('query');

        if (!$query) {
            return response()->json(['success' => false, 'message' => 'Parameter pencarian wajib diisi'], 422);
        }

        $customer = Customer::where('account_number', $query)
                            ->orWhere('nis', $query)
                            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Nasabah dengan No Rek / NIS [' . $query . '] tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $customer->id,
                'account_number' => $customer->account_number,
                'nis'            => $customer->nis,
                'name'           => $customer->name,
                'class_name'     => $customer->class_name,
                'balance'        => (int) $customer->balance,
                'status'         => $customer->status,
            ]
        ]);
    }

    /**
     * 2. Eksekusi Setor Tunai (Deposit)
     */
    public function deposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'amount'      => 'required|integer|min:1000',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Lock row customer untuk mencegah race condition
            $customer = Customer::where('id', $request->customer_id)->lockForUpdate()->first();

            if ($customer->status !== 'active') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Rekening nasabah ini sedang tidak aktif.'], 403);
            }

            $amount = (int) $request->amount;
            $balanceBefore = (int) $customer->balance;
            $balanceAfter = $balanceBefore + $amount;

            // 1. Simpan Transaksi
            $transCode = 'TRX-DEP-' . date('YmdHis') . '-' . rand(100, 999);
            $transaction = Transaction::create([
                'transaction_code' => $transCode,
                'customer_id'      => $customer->id,
                'user_id'          => $request->user()->id,
                'transaction_date' => now(),
                'transaction_type' => 'deposit',
                'amount'           => $amount,
                'balance_before'   => $balanceBefore,
                'balance_after'    => $balanceAfter,
                'description'      => $request->description ?? 'Setoran Tunai di Loket Teller',
            ]);

            // 2. Update Saldo Nasabah
            $customer->update(['balance' => $balanceAfter]);

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
                    'transaction' => $transaction->load(['customer', 'user']),
                    'receipt'     => [
                        'transaction_code' => $transaction->transaction_code,
                        'date'             => $transaction->transaction_date->format('d/m/Y H:i:s'),
                        'teller'           => $request->user()->name,
                        'account_number'   => $customer->account_number,
                        'name'             => $customer->name,
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
     * 3. Eksekusi Penarikan Tunai (Withdrawal)
     */
    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id'  => 'required|exists:customers,id',
            'amount'       => 'required|integer|min:1000',
            'security_pin' => 'required|digits:6',
            'description'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $customer = Customer::where('id', $request->customer_id)->lockForUpdate()->first();

            if ($customer->status !== 'active') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Rekening nasabah ini sedang diblokir.'], 403);
            }

            // A. Rubrik 3.1: Verifikasi 6-Digit PIN Keamanan Nasabah
            if (!Hash::check($request->security_pin, $customer->security_pin)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Otorisasi Gagal: PIN Keamanan 6-Digit Nasabah Salah!'
                ], 401);
            }

            $amount = (int) $request->amount;
            $balanceBefore = (int) $customer->balance;
            $remainingBalance = $balanceBefore - $amount;

            // B. Rubrik 2.2: Validasi Batas Saldo Mengendap Minimum Rp 10.000
            $minimumBalance = 10000;
            if ($remainingBalance < $minimumBalance) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Penarikan Ditolak: Sisa saldo setelah penarikan (Rp ' . number_format($remainingBalance, 0, ',', '.') . ') tidak boleh di bawah batas saldo mengendap minimum (Rp 10.000). Saldo saat ini: Rp ' . number_format($balanceBefore, 0, ',', '.') . '.'
                ], 422);
            }

            $balanceAfter = $remainingBalance;

            // 1. Simpan Transaksi
            $transCode = 'TRX-WDR-' . date('YmdHis') . '-' . rand(100, 999);
            $transaction = Transaction::create([
                'transaction_code' => $transCode,
                'customer_id'      => $customer->id,
                'user_id'          => $request->user()->id,
                'transaction_date' => now(),
                'transaction_type' => 'withdraw',
                'amount'           => $amount,
                'balance_before'   => $balanceBefore,
                'balance_after'    => $balanceAfter,
                'description'      => $request->description ?? 'Penarikan Tunai di Loket Teller',
            ]);

            // 2. Potong Saldo Nasabah
            $customer->update(['balance' => $balanceAfter]);

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
                    'transaction' => $transaction->load(['customer', 'user']),
                    'receipt'     => [
                        'transaction_code' => $transaction->transaction_code,
                        'date'             => $transaction->transaction_date->format('d/m/Y H:i:s'),
                        'teller'           => $request->user()->name,
                        'account_number'   => $customer->account_number,
                        'name'             => $customer->name,
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
     * 4. Daftar Mutasi Transaksi Loket Hari Ini
     */
    public function todayTransactions(Request $request)
    {
        $transactions = Transaction::with('customer')
                                   ->where('user_id', $request->user()->id)
                                   ->whereDate('transaction_date', today())
                                   ->orderBy('id', 'desc')
                                   ->get();

        return response()->json(['success' => true, 'data' => $transactions]);
    }

    /**
     * 5. Kalkulasi Draf Closing Kas Hari Ini (Rumus Saldo Awal + Setor - Tarik)
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

        // Saldo Awal default 0 (atau dari closing kemarin jika berlanjut)
        $openingBalance = 0;
        $systemBalance = $openingBalance + (int) $totalDeposit - (int) $totalWithdraw;

        $existingClosing = CashClosing::where('teller_id', $tellerId)
                                      ->whereDate('closing_date', $today)
                                      ->first();

        return response()->json([
            'success' => true,
            'data'    => [
                'closing_date'     => $today->format('Y-m-d'),
                'opening_balance'  => (int) $openingBalance,
                'total_deposit'    => (int) $totalDeposit,
                'total_withdraw'   => (int) $totalWithdraw,
                'system_balance'   => (int) $systemBalance,
                'existing_closing' => $existingClosing,
            ]
        ]);
    }

    /**
     * 6. Submit Rekonsiliasi Kas Teller ke Supervisor
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

        $totalDeposit = (int) Transaction::where('user_id', $tellerId)->whereDate('transaction_date', $today)->where('transaction_type', 'deposit')->sum('amount');
        $totalWithdraw = (int) Transaction::where('user_id', $tellerId)->whereDate('transaction_date', $today)->where('transaction_type', 'withdraw')->sum('amount');
        
        $openingBalance = 0;
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
                'notes'            => $request->notes,
            ]
        );

        $message = ($difference === 0) 
            ? 'Rekonsiliasi Kas Berhasil (KLOP Rp 0)! Laporan harian siap diverifikasi Supervisor.'
            : 'PERINGATAN: Terdeteksi Selisih Kas sebesar Rp ' . number_format($difference, 0, ',', '.') . ' (Status: DISPUTE). Harap periksa bundel slip transaksi fisik Anda.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $closing
        ]);
    }
}