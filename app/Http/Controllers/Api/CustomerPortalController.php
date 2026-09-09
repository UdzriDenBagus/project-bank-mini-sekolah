<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Transaction;

class CustomerPortalController extends Controller
{
    /**
     * 1. Cek Saldo Mandiri Real-Time
     */
    public function getBalance(Request $request)
    {
        $customer = $request->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'account_number' => $customer->account_number,
                'nis'            => $customer->nis,
                'name'           => $customer->name,
                'class_name'     => $customer->class_name,
                'balance'        => (int) $customer->balance,
            ]
        ]);
    }

    /**
     * 2. Riwayat Mutasi Buku Tabungan Digital Siswa
     */
    public function getMutations(Request $request)
    {
        $customer = $request->user();

        $transactions = Transaction::where('customer_id', $customer->id)
                                   ->orderBy('transaction_date', 'desc')
                                   ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $transactions
        ]);
    }
}