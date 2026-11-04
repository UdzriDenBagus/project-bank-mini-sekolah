<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Portal Petugas Bank Mini Sekolah
|--------------------------------------------------------------------------
| Seluruh autentikasi dan otorisasi data ditangani oleh token Sanctum
| melalui REST API (/api/*) secara dinamis menggunakan JavaScript Fetch.
*/

// Redirect root ke halaman login
Route::get('/', function () {
    return redirect('/login');
});

// 1. Halaman Login Petugas (Multi-Role: Admin & Teller)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// 2. Modul Loket Piket Teller & Scanner QR
Route::get('/teller/loket', function () {
    return view('teller.index');
});

// 3. Modul Panel Administrator (Users, Customers, Audit Jurnal)
Route::get('/admin/dashboard', function () {
    return view('admin.index');
});

// 4. Modul Panel Supervisor dialihkan ke Panel Administrator
Route::get('/supervisor/dashboard', function () {
    return redirect('/admin/dashboard');
});

// 5. Rute Tampilan Cetak Laporan Bulanan (Opsi A: Browser Print / PDF)
Route::get('/reports/monthly/print', function () {
    return view('reports.monthly_print');
});

// 6. Rute Tampilan Cetak Laporan Closing Kas Harian
Route::get('/reports/closing/{id}/print', function ($id) {
    return view('reports.closing_print', ['closingId' => $id]);
});

// 7. Akses Nasabah Hanya Melalui Aplikasi Mobile (Halaman Desktop Dinonaktifkan)
Route::get('/customer/portal', function () {
    return redirect('/login');
});

// 8. Unduh Berkas Template Excel Anggota (Direct Download)
Route::get('/admin/members/template/download', [App\Http\Controllers\Api\AdminController::class, 'downloadMemberTemplate']);
Route::get('/admin/members/template', [App\Http\Controllers\Api\AdminController::class, 'downloadMemberTemplate']);

// 9. Modul Sinkronisasi Data Lokal ke Hosting (1-Klik)
Route::get('/sync-data', function () {
    $allowedTables = [
        'users', 'customers', 'members', 'savers', 'transactions',
        'accounting_journals', 'cash_closings', 'teller_cash_advances',
        'password_reset_requests', 'student_archives'
    ];

    $counts = [];
    foreach ($allowedTables as $table) {
        try {
            $counts[$table] = \Illuminate\Support\Facades\DB::table($table)->count();
        } catch (\Throwable $e) {
            $counts[$table] = 0;
        }
    }

    return view('sync_data', compact('counts'));
});

Route::post('/sync-data/push', function (\Illuminate\Http\Request $request) {
    $allowedTables = [
        'users', 'customers', 'members', 'savers', 'transactions',
        'accounting_journals', 'cash_closings', 'teller_cash_advances',
        'password_reset_requests', 'student_archives'
    ];

    $payloadData = [];
    foreach ($allowedTables as $table) {
        try {
            $payloadData[$table] = \Illuminate\Support\Facades\DB::table($table)->get()->map(function ($row) {
                return (array) $row;
            })->toArray();
        } catch (\Throwable $e) {
            $payloadData[$table] = [];
        }
    }

    $targetUrl = 'https://bank-mini-udzri.web.id/api/deploy/sync-data';
    $secret = config('app.deploy_secret') ?: env('DEPLOY_SECRET') ?: 'skansaba_super_secret_token_12345';

    try {
        $response = \Illuminate\Support\Facades\Http::timeout(60)->withoutVerifying()->post($targetUrl, [
            'secret' => $secret,
            'data' => $payloadData
        ]);

        if ($response->successful()) {
            $totalRows = collect($payloadData)->map(fn($r) => count($r))->sum();
            return back()->with('success', "Hebat! Seluruh data lokal ({$totalRows} baris data) berhasil disalin ke https://bank-mini-udzri.web.id!");
        } else {
            return back()->with('error', 'Gagal sinkronisasi: ' . ($response->json('message') ?? $response->body()));
        }
    } catch (\Throwable $e) {
        return back()->with('error', 'Gagal terhubung ke hosting: ' . $e->getMessage());
    }
});

Route::get('/sync-data/download-sql', function () {
    $allowedTables = [
        'users', 'customers', 'members', 'savers', 'transactions',
        'accounting_journals', 'cash_closings', 'teller_cash_advances',
        'password_reset_requests', 'student_archives'
    ];

    $sql = "-- Dump Data Bersih Bank Mini (Tanpa Definisi Procedure / Definisi Root)\n";
    $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    foreach ($allowedTables as $table) {
        try {
            $rows = \Illuminate\Support\Facades\DB::table($table)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Tabel: {$table}\n";
                $sql .= "TRUNCATE TABLE `{$table}`;\n";
                foreach ($rows as $row) {
                    $columns = array_keys((array)$row);
                    $values = array_map(function ($val) {
                        if ($val === null) return 'NULL';
                        return "'" . addslashes((string)$val) . "'";
                    }, array_values((array)$row));

                    $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        } catch (\Throwable $e) {}
    }
    $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    return response($sql, 200, [
        'Content-Type' => 'application/sql',
        'Content-Disposition' => 'attachment; filename="data_bersih_bank_mini.sql"'
    ]);
});
