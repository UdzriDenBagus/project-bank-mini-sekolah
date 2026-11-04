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

// 1. Halaman Login Petugas (Multi-Role: Admin, Teller, Supervisor)
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
