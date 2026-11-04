<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Member;
use App\Models\Saver;

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

        return response()->json([
            'success' => true,
            'data'    => [
                'is_active'      => true,
                'account_number' => $user->account_number,
                'nis'            => $user->nis,
                'name'           => $user->name,
                'class_name'     => $user->class_name,
                'email'          => $email,
                'balance'        => (int) $user->balance,
                'status'         => $user->status ?? 'active',
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
}