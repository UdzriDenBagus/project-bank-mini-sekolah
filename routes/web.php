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

// 4. Modul Panel Supervisor (Otorisasi Closing, Dispute, Brankas 102)
Route::get('/supervisor/dashboard', function () {
    return view('supervisor.index');
});