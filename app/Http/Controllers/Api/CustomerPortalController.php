<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Member;
use App\Models\Saver;
use App\Models\WithdrawalAppointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class CustomerPortalController extends Controller
{
    /**
     * 1. Cek Saldo Mandiri Real-Time & Profil Siswa
     */
    public function getBalance(Request $request)
    {
        $user = $request->user();

        // Jika user adalah Member
        if ($user instanceof Member) {
            $saver = $user->saver;
            if (!$saver || $user->status !== 'active_account') {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'is_active'      => false,
                        'account_number' => null,
                        'nis'            => $user->nis,
                        'name'           => $user->name,
                        'class_name'     => $user->class_name,
                        'balance'        => null,
                        'status'         => 'inactive_account',
                    ]
                ]);
            }
            $user = $saver;
        }

        $email = null;
        if ($user instanceof Saver && $user->member) {
            $email = $user->member->email;
        } elseif ($user instanceof Member) {
            $email = $user->email;
        }

        $balance = (int) $user->balance;
        $heldBalance = 10000;
        $maxAvailable = max(0, $balance - $heldBalance);
        $maxDirect = min(1000000, $maxAvailable);

        return response()->json([
            'success' => true,
            'data'    => [
                'is_active'              => true,
                'account_number'         => $user->account_number,
                'nis'                    => $user->nis,
                'name'                   => $user->name,
                'class_name'             => $user->class_name,
                'email'                  => $email,
                'balance'                => $balance,
                'held_balance'           => $heldBalance,
                'max_available_balance'  => $maxAvailable,
                'max_direct_withdrawal'  => $maxDirect,
                'withdrawal_notice'      => 'Penarikan di atas Rp 1.000.000 wajib melakukan perjanjian dengan pihak teller minimal 1 hari sebelum penarikan (H-1).',
                'status'                 => $user->status ?? 'active',
            ]
        ]);
    }

    /**
     * 2. Riwayat Mutasi Buku Tabungan Digital Siswa
     */
    public function getMutations(Request $request)
    {
        $user = $request->user();

        if ($user instanceof Member) {
            $saver = $user->saver;
            if (!$saver || $user->status !== 'active_account') {
                return response()->json([
                    'success'   => true,
                    'is_active' => false,
                    'data'      => []
                ]);
            }
            $user = $saver;
        }

        $transactions = Transaction::where('saver_id', $user->id)
                                   ->orderBy('transaction_date', 'desc')
                                   ->paginate(20);

        return response()->json([
            'success'   => true,
            'is_active' => true,
            'data'      => $transactions
        ]);
    }

    /**
     * 3. Perbarui Alamat Gmail Nasabah
     */
    public function updateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:100',
        ], [
            'email.required' => 'Alamat Gmail wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $user = $request->user();
        $member = null;

        if ($user instanceof Saver) {
            $member = $user->member;
        } elseif ($user instanceof Member) {
            $member = $user;
        }

        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Data Siswa tidak ditemukan'], 404);
        }

        $member->update(['email' => trim($request->email)]);

        return response()->json([
            'success' => true,
            'message' => 'Alamat Gmail berhasil diperbarui! Kode reset password dan PIN akan dikirim ke ' . $member->email,
            'email'   => $member->email,
        ]);
    }

    /**
     * Helper untuk mendapatkan instans Saver aktif dari user terautentikasi
     */
    private function getAuthenticatedSaver(Request $request)
    {
        $user = $request->user();
        if ($user instanceof Saver) {
            return $user;
        }
        if ($user instanceof Member) {
            return $user->saver;
        }
        return null;
    }

    /**
     * 4. Riwayat & Daftar Perjanjian Penarikan Dana Mandiri Nasabah
     */
    public function getAppointments(Request $request)
    {
        $saver = $this->getAuthenticatedSaver($request);
        if (!$saver) {
            return response()->json([
                'success' => false,
                'message' => 'Akun nasabah tidak valid atau belum aktif.'
            ], 403);
        }

        $appointments = WithdrawalAppointment::with(['teller'])
            ->where('saver_id', $saver->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($apt) {
                $statusLabel = 'Menunggu ACC';
                $statusBadge = 'bg-amber-100 text-amber-800 border-amber-300';
                if ($apt->status === 'approved') {
                    $statusLabel = 'Di-ACC (Disetujui)';
                    $statusBadge = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                } elseif ($apt->status === 'rejected') {
                    $statusLabel = 'Ditolak';
                    $statusBadge = 'bg-rose-100 text-rose-800 border-rose-300';
                } elseif ($apt->status === 'completed') {
                    $statusLabel = 'Selesai Dicairkan';
                    $statusBadge = 'bg-blue-100 text-blue-800 border-blue-300';
                } elseif ($apt->status === 'cancelled') {
                    $statusLabel = 'Dibatalkan';
                    $statusBadge = 'bg-slate-100 text-slate-800 border-slate-300';
                }

                return [
                    'id'               => $apt->id,
                    'appointment_date' => $apt->appointment_date ? Carbon::parse($apt->appointment_date)->format('d/m/Y') : '-',
                    'withdrawal_date'  => $apt->withdrawal_date ? Carbon::parse($apt->withdrawal_date)->format('d/m/Y') : '-',
                    'amount'           => (int) $apt->amount,
                    'amount_formatted' => 'Rp ' . number_format($apt->amount, 0, ',', '.'),
                    'status'           => $apt->status,
                    'status_label'     => $statusLabel,
                    'status_badge'     => $statusBadge,
                    'teller_name'      => $apt->teller ? $apt->teller->name : null,
                    'notes'            => $apt->notes,
                    'created_at'       => $apt->created_at ? $apt->created_at->format('d/m/Y H:i') : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $appointments,
        ]);
    }

    /**
     * 5. Nasabah Mengajukan Perjanjian Penarikan Dana Besar (> Rp 1.000.000, Wajib H-1)
     */
    public function createAppointment(Request $request)
    {
        $saver = $this->getAuthenticatedSaver($request);
        if (!$saver) {
            return response()->json([
                'success' => false,
                'message' => 'Akun nasabah tidak valid atau belum aktif.'
            ], 403);
        }

        if ($saver->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tabungan Anda sedang dinonaktifkan atau diblokir.'
            ], 403);
        }

        $balance = (int) $saver->balance;
        $heldBalance = 10000;
        $maxAvailable = max(0, $balance - $heldBalance);

        // Aturan: Jika saldo nasabah di bawah 1 juta maka TIDAK BISA melakukan pengajuan penarikan
        if ($balance <= 1000000 || $maxAvailable <= 1000000) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan Tidak Diizinkan: Saldo tabungan Anda saat ini Rp ' . number_format($balance, 0, ',', '.') . '. Pengajuan perjanjian penarikan hanya dapat dilakukan jika saldo tabungan di atas Rp 1.000.000 (tersedia di atas Rp 1.000.000 setelah saldo mengendap minimum Rp 10.000).'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'amount'          => 'required|integer|min:1000001',
            'withdrawal_date' => 'required|date|after:today',
            'notes'           => 'nullable|string|max:255',
        ], [
            'amount.required'          => 'Nominal penarikan wajib diisi.',
            'amount.min'               => 'Perjanjian penarikan khusus untuk nominal penarikan dana di atas Rp 1.000.000.',
            'withdrawal_date.required' => 'Tanggal rencana penarikan wajib diisi.',
            'withdrawal_date.after'    => 'Perjanjian penarikan wajib dilakukan minimal 1 hari sebelum penarikan (H-1 / tanggal besok ke atas).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $amount = (int) $request->amount;

        // Validasi Saldo Mengendap Rp 10.000
        if ($amount > $maxAvailable) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan Gagal: Nominal penarikan (Rp ' . number_format($amount, 0, ',', '.') . ') melebihi batas saldo yang dapat ditarik. Saldo tabungan Anda saat ini Rp ' . number_format($balance, 0, ',', '.') . ' dengan saldo mengendap minimum Rp 10.000, sehingga maksimal dana yang dapat ditarik adalah Rp ' . number_format($maxAvailable, 0, ',', '.') . '.'
            ], 422);
        }

        // Simpan permohonan dengan status pending (Menunggu ACC Teller)
        $appointment = WithdrawalAppointment::create([
            'saver_id'         => $saver->id,
            'teller_id'        => null, // Menunggu di-ACC Teller
            'appointment_date' => now()->toDateString(),
            'withdrawal_date'  => $request->withdrawal_date,
            'amount'           => $amount,
            'status'           => 'pending', // Menunggu ACC dari Teller
            'notes'            => $request->notes ?? 'Pengajuan mandiri via akun nasabah',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan perjanjian penarikan dana sebesar Rp ' . number_format($amount, 0, ',', '.') . ' untuk tanggal ' . Carbon::parse($request->withdrawal_date)->translatedFormat('d F Y') . ' berhasil dikirim! Menunggu persetujuan (ACC) dari Petugas Teller.',
            'data'    => $appointment,
        ], 201);
    }

    /**
     * 6. Batalkan Perjanjian Penarikan oleh Nasabah
     */
    public function cancelAppointment(Request $request, $id)
    {
        $saver = $this->getAuthenticatedSaver($request);
        if (!$saver) {
            return response()->json([
                'success' => false,
                'message' => 'Akun nasabah tidak valid.'
            ], 403);
        }

        $appointment = WithdrawalAppointment::where('id', $id)
            ->where('saver_id', $saver->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Perjanjian penarikan tidak ditemukan atau sudah selesai/dibatalkan.'
            ], 404);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Perjanjian penarikan dana berhasil dibatalkan.'
        ]);
    }
}