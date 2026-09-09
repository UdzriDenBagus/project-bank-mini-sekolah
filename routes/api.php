<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\SupervisorController;
use App\Http\Controllers\Api\TellerController;
use App\Http\Controllers\Api\CustomerPortalController;

// Pintu Gerbang Publik
Route::prefix('auth')->group(function () {
    Route::post('/login/staff', [AuthController::class, 'loginStaff']);
    Route::post('/login/customer', [AuthController::class, 'loginCustomer']);
});

// Area Terproteksi Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
// Area Khusus Administrator Web
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/users', [AdminController::class, 'indexUsers']);
    Route::post('/users', [AdminController::class, 'storeUser']);
    Route::put('/users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser']);

    Route::get('/customers', [AdminController::class, 'indexCustomers']);
    Route::post('/customers', [AdminController::class, 'storeCustomer']);

    Route::get('/audit-journals', [AdminController::class, 'auditJournals']);
});

// Area Khusus Supervisor Web
Route::middleware(['auth:sanctum', 'role:supervisor'])->prefix('supervisor')->group(function () {
    Route::get('/closings', [SupervisorController::class, 'indexClosings']);
    Route::get('/closings/{id}/audit', [SupervisorController::class, 'auditTellerDetail']);
    Route::post('/closings/{id}/approve', [SupervisorController::class, 'approveClosing']);
});

// Area Khusus Piket Teller Loket
Route::middleware(['auth:sanctum', 'role:teller'])->prefix('teller')->group(function () {
    Route::get('/customers/search', [TellerController::class, 'searchCustomer']);
    Route::post('/transactions/deposit', [TellerController::class, 'deposit']);
    Route::post('/transactions/withdraw', [TellerController::class, 'withdraw']);
    Route::get('/transactions/today', [TellerController::class, 'todayTransactions']);
    
    Route::get('/closing/calculation', [TellerController::class, 'getClosingCalculation']);
    Route::post('/closing/submit', [TellerController::class, 'submitClosing']);
});

// Area Khusus Nasabah Siswa
Route::middleware(['auth:sanctum', 'role:customer'])->prefix('customer')->group(function () {
    Route::get('/balance', [CustomerPortalController::class, 'getBalance']);
    Route::get('/mutations', [CustomerPortalController::class, 'getMutations']);
});
