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

        // KUNCI EMAS INTEGRITAS AUDIT:
        // Eager loading saver & member dengan withTrashed(), sehingga jika siswa naik kelas atau lulus/arsip,
        // seluruh riwayat mutasi, nama, dan no rekening tetap utuh dan tidak pernah terhapus!
        $transactions = Transaction::with(['saver' => function($q) {
                                       $q->withTrashed()->with(['member' => function($mq) {
                                           $mq->withTrashed();
                                       }]);
                                   }])
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

    /**
     * 3b. Otorisasi Batch / Accept All Seluruh Laporan Closing Kas Pending
     */
    public function approveAllClosings(Request $request)
    {
        $pendingClosings = CashClosing::with('teller')->whereIn('status', ['submitted', 'pending', 'dispute'])->get();

        if ($pendingClosings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada laporan closing berstatus pending / belum disetujui.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($pendingClosings as $closing) {
                $closing->update([
                    'status'        => 'approved',
                    'supervisor_id' => $request->user()->id,
                    'approved_at'   => now(),
                    'notes'         => $request->notes ?? 'Disetujui serentak oleh Administrator (Accept All).',
                ]);

                if ($closing->physical_balance > 0) {
                    AccountingJournal::create([
                        'transaction_id' => null,
                        'account_code'   => '102',
                        'account_name'   => 'Kas Brankas Utama Bank Mini',
                        'position'       => 'debit',
                        'amount'         => $closing->physical_balance,
                    ]);

                    AccountingJournal::create([
                        'transaction_id' => null,
                        'account_code'   => '101',
                        'account_name'   => 'Kas Loket Teller (' . ($closing->teller ? $closing->teller->name : 'Piket') . ')',
                        'position'       => 'credit',
                        'amount'         => $closing->physical_balance,
                    ]);
                }
                $count++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menyetujui seluruh laporan closing ({$count} laporan) dan mengamankan kas fisik ke Brankas Utama (102)!"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui semua closing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 4. Otorisasi Penolakan Closing Kas Teller dengan Catatan/Alasan Mandiri Admin
     */
    public function rejectClosing(Request $request, $id)
    {
        $closing = CashClosing::with('teller')->find($id);

        if (!$closing) {
            return response()->json(['success' => false, 'message' => 'Laporan closing tidak ditemukan.'], 404);
        }

        if ($closing->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Laporan yang telah disetujui tidak dapat ditolak.'], 422);
        }

        // Support both 'reason' and 'rejection_reason' fields from frontend
        $reason = trim($request->rejection_reason ?? $request->reason ?? '');

        if (empty($reason) || strlen($reason) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Catatan/alasan penolakan wajib diisi oleh Admin (minimal 3 karakter).',
                'errors'  => ['rejection_reason' => ['Catatan/alasan penolakan wajib diisi oleh Admin.']]
            ], 422);
        }

        $closing->update([
            'status'           => 'rejected',
            'supervisor_id'    => $request->user()->id,
            'rejection_reason' => $reason,
            'notes'            => 'DITOLAK ADMIN: ' . $reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan Closing Berhasil Ditolak! Catatan alasan penolakan telah diteruskan ke teller yang bersangkutan.',
            'data'    => $closing->load(['teller', 'supervisor'])
        ]);
    }
}