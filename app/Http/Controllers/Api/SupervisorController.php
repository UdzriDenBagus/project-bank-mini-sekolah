<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CashClosing;
use App\Models\Transaction;
use App\Models\AccountingJournal;

class SupervisorController extends Controller
{
    /**
     * 1. Daftar Laporan Closing Harian Teller
     */
    public function indexClosings()
    {
        $closings = CashClosing::with(['teller', 'supervisor'])
                               ->orderBy('id', 'desc')
                               ->paginate(15);

        return response()->json(['success' => true, 'data' => $closings]);
    }

    /**
     * 2. Detail Audit Mutasi & Log Teller (Dispute Resolution)
     */
    public function auditTellerDetail($id)
    {
        $closing = CashClosing::with(['teller'])->find($id);

        if (!$closing) {
            return response()->json(['success' => false, 'message' => 'Laporan closing tidak ditemukan'], 404);
        }

        $transactions = Transaction::with(['customer'])
                                   ->where('user_id', $closing->teller_id)
                                   ->whereDate('transaction_date', $closing->closing_date)
                                   ->orderBy('id', 'asc')
                                   ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'closing'      => $closing,
                'transactions' => $transactions,
            ]
        ]);
    }

    /**
     * 3. Otorisasi Approval Closing & Pemindahan Kas ke Brankas Utama (102)
     */
    public function approveClosing(Request $request, $id)
    {
        $closing = CashClosing::find($id);

        if (!$closing) {
            return response()->json(['success' => false, 'message' => 'Laporan closing tidak ditemukan'], 404);
        }

        if ($closing->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Laporan ini sudah disetujui sebelumnya.'], 422);
        }

        DB::beginTransaction();
        try {
            // Update status Closing menjadi Approved
            $closing->update([
                'status'        => 'approved',
                'supervisor_id' => $request->user()->id,
                'approved_at'   => now(),
                'notes'         => $request->notes ?? 'Disetujui dan kas fisik telah diamankan ke Brankas Utama.',
            ]);

            // Jika ada uang fisik yang disetor ke Brankas, catat Jurnal Pemindahan Kas
            if ($closing->physical_balance > 0) {
                // Debet Kas Brankas Utama (102)
                AccountingJournal::create([
                    'transaction_id' => null,
                    'account_code'   => '102',
                    'account_name'   => 'Kas Brankas Utama Bank Mini',
                    'position'       => 'debit',
                    'amount'         => $closing->physical_balance,
                ]);

                // Kredit Kas Loket Teller (101)
                AccountingJournal::create([
                    'transaction_id' => null,
                    'account_code'   => '101',
                    'account_name'   => 'Kas Loket Teller (' . $closing->teller->name . ')',
                    'position'       => 'credit',
                    'amount'         => $closing->physical_balance,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Laporan Closing Berhasil Disetujui & Kas Dipindahkan ke Brankas Utama (102)!',
                'data'    => $closing
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui closing: ' . $e->getMessage()
            ], 500);
        }
    }
}