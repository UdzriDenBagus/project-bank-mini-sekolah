<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Customer;
use App\Models\AccountingJournal;

class AdminController extends Controller
{
    // ==========================================
    // 1. MANAJEMEN PENGGUNA SISTEM (USERS CRUD)
    // ==========================================

    public function indexUsers()
    {
        $users = User::orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'data' => $users]);
    }

    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username|max:50',
            'password' => 'required|string|min:6',
            'name'     => 'required|string|max:100',
            'role'     => 'required|in:admin,teller,supervisor',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name'     => $request->name,
            'role'     => $request->role,
            'status'   => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun Petugas [' . strtoupper($user->role) . '] berhasil dibuat!',
            'data'    => $user
        ], 201);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Petugas tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'name'     => 'required|string|max:100',
            'role'     => 'required|in:admin,teller,supervisor',
            'status'   => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $dataUpdate = [
            'username' => $request->username,
            'name'     => $request->name,
            'role'     => $request->role,
            'status'   => $request->status,
        ];

        if ($request->filled('password')) {
            $dataUpdate['password'] = Hash::make($request->password);
        }

        $user->update($dataUpdate);

        return response()->json([
            'success' => true,
            'message' => 'Data Petugas berhasil diperbarui!',
            'data'    => $user
        ]);
    }

    public function destroyUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Petugas tidak ditemukan'], 404);
        }

        // Proteksi Rubrik 1.2: Cek Relasi Transaksi (ON DELETE RESTRICT)
        if ($user->transactions()->exists() || $user->closings()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Petugas tidak dapat dihapus karena memiliki riwayat audit transaksi/closing! Silakan nonaktifkan (status inactive).'
            ], 422);
        }

        $user->delete();
        return response()->json(['success' => true, 'message' => 'Akun Petugas berhasil dihapus.']);
    }

    // ==========================================
    // 2. MASTER DATA REKENING & GENERATOR NOMOR
    // ==========================================

    public function indexCustomers()
    {
        $customers = Customer::orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'data' => $customers]);
    }

    /**
     * Algoritma Generator Nomor Rekening Otomatis:
     * Format: [2 Digit Tahun][2 Digit Sekolah][5 Digit Nomor Urut]
     * Contoh 2026 -> Prefix: '2601' -> '260100001'
     */
    private function generateAccountNumber()
    {
        $prefix = date('y') . '01'; // '2601'
        $latest = Customer::where('account_number', 'LIKE', $prefix . '%')
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

    public function storeCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nis'          => 'required|string|unique:customers,nis|max:30',
            'name'         => 'required|string|max:100',
            'class_name'   => 'required|string|max:50',
            'security_pin' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $accountNumber = $this->generateAccountNumber();

        $customer = Customer::create([
            'account_number' => $accountNumber,
            'nis'            => $request->nis,
            'name'           => $request->name,
            'class_name'     => $request->class_name,
            'security_pin'   => Hash::make($request->security_pin),
            'balance'        => 0,
            'status'         => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Buku Rekening Tabungan Siswa Berhasil Diterbitkan!',
            'data'    => [
                'customer'   => $customer,
                'qr_payload' => $customer->account_number
            ]
        ], 201);
    }

    // ==========================================
    // 3. AUDIT JURNAL AKUNTANSI (BACK-END)
    // ==========================================

    public function auditJournals(Request $request)
    {
        $query = AccountingJournal::with(['transaction.customer', 'transaction.user'])
                                  ->orderBy('id', 'desc');

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $journals = $query->paginate(20);

        $totalDebit  = AccountingJournal::where('position', 'debit')->sum('amount');
        $totalCredit = AccountingJournal::where('position', 'credit')->sum('amount');

        return response()->json([
            'success' => true,
            'summary' => [
                'total_debit'  => (int) $totalDebit,
                'total_credit' => (int) $totalCredit,
                'is_balanced'  => ($totalDebit === $totalCredit),
            ],
            'data'    => $journals
        ]);
    }
}