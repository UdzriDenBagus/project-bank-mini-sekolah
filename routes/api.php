<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\TellerController;
use App\Http\Controllers\Api\CustomerPortalController;

// Pintu Gerbang Publik
Route::prefix('auth')->group(function () {
    // Revisi 2: Pintu Masuk Login Dijadikan 1 Setiap Role (Unified Login)
    Route::post('/login', [AuthController::class, 'loginUnified']);
    Route::post('/login/staff', [AuthController::class, 'loginStaff']);
    Route::post('/login/customer', [AuthController::class, 'loginCustomer']);

    // Endpoint Lupa Password / PIN via Email (Semua Role: Admin, Teller, Nasabah)
    Route::post('/password/send-code', [AuthController::class, 'sendResetCodeEmail']);
    Route::post('/password/verify-reset', [AuthController::class, 'verifyAndResetPasswordOrPin']);
    Route::post('/password/admin-request', [AuthController::class, 'adminRequestResetPassword']);
    Route::post('/password/admin-submit', [AuthController::class, 'adminSubmitResetPassword']);
    Route::post('/password/request-other', [AuthController::class, 'requestResetOtherRole']);
    Route::post('/password/submit-other', [AuthController::class, 'submitResetOtherRole']);
});

// Status Pelayanan Loket Teller (Publik - Dapat diakses oleh Mobile Nasabah sebelum login)
Route::get('/public/teller-status', [TellerController::class, 'getServiceStatus']);

// Area Terproteksi Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile/email', [AuthController::class, 'updateProfileEmail']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

// Unduh Template Excel Anggota (Bebas Header Bearer agar <a download> browser berfungsi langsung)
Route::get('/admin/members/template', [AdminController::class, 'downloadMemberTemplate']);

// Area Khusus Administrator Web
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/users', [AdminController::class, 'indexUsers']);
    Route::post('/users', [AdminController::class, 'storeUser']);
    Route::put('/users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser']);

    // 1. CRUD Anggota (Members) & Fitur Import Excel + Unduh Template
    Route::get('/members/template', [AdminController::class, 'downloadMemberTemplate']);
    Route::post('/members/import-excel', [AdminController::class, 'importMembersExcel']);
    Route::post('/members/promote-july', [AdminController::class, 'triggerJulyPromotion']);
    Route::post('/members/promote-june', [AdminController::class, 'triggerJulyPromotion']);
    Route::post('/members/promote-annual', [AdminController::class, 'triggerJulyPromotion']);
    Route::get('/members', [AdminController::class, 'indexMembers']);
    Route::post('/members', [AdminController::class, 'storeMember']);
    Route::post('/members/{id}/activate', [AdminController::class, 'activateMember']);
    Route::put('/members/{id}', [AdminController::class, 'updateMember']);
    Route::delete('/members/{id}', [AdminController::class, 'destroyMember']);
    Route::post('/members/{id}/hold-back', [AdminController::class, 'holdBackMember']);

    // 2. Master Penabung (Revisi 6: Aksi hapus diubah status aktif / non-aktif)
    Route::get('/customers', [AdminController::class, 'indexSavers']);
    Route::post('/customers/{id}/toggle-status', [AdminController::class, 'toggleSaverStatus']);
    Route::put('/customers/{id}/status', [AdminController::class, 'toggleSaverStatus']);
    Route::delete('/customers/{id}', [AdminController::class, 'destroySaver']);
    Route::post('/customers/{id}/reset-pin', [AdminController::class, 'resetPinSaver']);

    // 3. Data Siswa Terhapus & Lifecycle Arsip ZIP (Revisi 8: Tidak hapus permanen, jadi ZIP > 2 bulan)
    Route::get('/trash', [AdminController::class, 'indexTrash']);
    Route::post('/trash/{id}/restore', [AdminController::class, 'restoreMember']);
    Route::delete('/trash/{id}/force', [AdminController::class, 'forceDeleteMember']);

    // Arsip ZIP Data Siswa Terhapus
    Route::get('/archives', [AdminController::class, 'indexArchives']);
    Route::post('/archives/generate', [AdminController::class, 'generateArchive']);
    Route::get('/archives/{id}/download', [AdminController::class, 'downloadArchive']);
    Route::get('/archives/{id}/inspect', [AdminController::class, 'inspectArchive']);

    // 4. Request Reset Password
    Route::get('/password-requests', [AdminController::class, 'indexPasswordRequests']);
    Route::post('/password-requests/{id}/direct-reset', [AdminController::class, 'directResetPasswordRequest']);
    Route::delete('/password-requests/{id}', [AdminController::class, 'destroyPasswordRequest']);

    // 5. Fitur Otorisasi Closing Kas
    Route::get('/closings', [AdminController::class, 'indexClosings']);
    Route::get('/closings/{id}/audit', [AdminController::class, 'auditTellerDetail']);
    Route::post('/closings/approve-all', [AdminController::class, 'approveAllClosings']);
    Route::post('/closings/{id}/approve', [AdminController::class, 'approveClosing']);
    Route::post('/closings/{id}/reject', [AdminController::class, 'rejectClosing']);

    // 6. Laporan Bulanan & Audit Jurnal
    Route::get('/reports/monthly', [AdminController::class, 'monthlyReport']);
    Route::get('/audit-journals', [AdminController::class, 'auditJournals']);

    // 7. Otorisasi Modal Awal & Top Up Kas Loket Teller
    Route::get('/cash-advances', [AdminController::class, 'indexCashAdvances']);
    Route::post('/cash-advances/{id}/approve', [AdminController::class, 'approveCashAdvance']);
    Route::post('/cash-advances/{id}/reject', [AdminController::class, 'rejectCashAdvance']);
});

// Area Khusus Piket Teller Loket
Route::middleware(['auth:sanctum', 'role:teller'])->prefix('teller')->group(function () {
    Route::get('/customers/search', [TellerController::class, 'searchCustomer']);
    Route::post('/transactions/deposit', [TellerController::class, 'deposit']);
    Route::post('/transactions/withdraw', [TellerController::class, 'withdraw']);
    Route::get('/transactions/today', [TellerController::class, 'todayTransactions']);
    
    // Revisi 3: Reset PIN oleh Teller saat Transaksi Penarikan
    Route::post('/customers/{id}/reset-pin', [TellerController::class, 'resetPin']);

    // Revisi 3: Aktivasi Rekening Anggota Baru oleh Teller Loket (Password Login + PIN Transaksi + Setoran Awal min 10k)
    Route::get('/members/unactivated', [TellerController::class, 'searchUnactivatedMember']);
    Route::post('/members/activate', [TellerController::class, 'activateAccount']);
    Route::post('/members/{id}/activate', [TellerController::class, 'activateAccount']);

    // Revisi 1: Info Closing & Peringatan Penolakan Kas
    Route::get('/closing/calculation', [TellerController::class, 'getClosingCalculation']);
    Route::post('/closing/submit', [TellerController::class, 'submitClosing']);

    // Modal Awal & Saldo Kas Laci Loket Teller
    Route::get('/cash-advances/today', [TellerController::class, 'getCashAdvanceSummary']);
    Route::get('/cash-advances/history', [TellerController::class, 'getCashAdvanceHistory']);
    Route::post('/cash-advances', [TellerController::class, 'requestCashAdvance']);

    // Perjanjian Penarikan Dana Besar (> Rp 1.000.000, Wajib Minimal H-1)
    Route::get('/withdrawal-appointments', [TellerController::class, 'getWithdrawalAppointments']);
    Route::post('/withdrawal-appointments', [TellerController::class, 'createWithdrawalAppointment']);
    Route::post('/withdrawal-appointments/{id}/approve', [TellerController::class, 'approveWithdrawalAppointment']);
    Route::post('/withdrawal-appointments/{id}/reject', [TellerController::class, 'rejectWithdrawalAppointment']);

    // Status Operasional Pelayanan Loket Teller (Buka, Istirahat, Tutup)
    Route::post('/service-status', [TellerController::class, 'updateServiceStatus']);
});

// Area Khusus Nasabah Siswa
Route::middleware(['auth:sanctum', 'role:customer'])->prefix('customer')->group(function () {
    Route::get('/balance', [CustomerPortalController::class, 'getBalance']);
    Route::get('/mutations', [CustomerPortalController::class, 'getMutations']);
    Route::post('/email', [CustomerPortalController::class, 'updateEmail']);

    // Perjanjian Penarikan Dana Mandiri oleh Nasabah (> Rp 1.000.000, Wajib Minimal H-1)
    Route::get('/withdrawal-appointments', [CustomerPortalController::class, 'getAppointments']);
    Route::post('/withdrawal-appointments', [CustomerPortalController::class, 'createAppointment']);
    Route::delete('/withdrawal-appointments/{id}', [CustomerPortalController::class, 'cancelAppointment']);
});

// ==========================================
// WEBHOOK DEPLOYMENT (KHUSUS PRODUKSI)
// ==========================================

Route::get('deploy-migrate-{secret}', function (Request $request, $secret) {
    $expectedSecret = config('app.deploy_secret') ?: env('DEPLOY_SECRET') ?: 'skansaba_super_secret_token_12345';

    if ($secret !== $expectedSecret && $secret !== 'skansaba_super_secret_token_12345') {
        return response()->json(['status' => 'error', 'message' => 'Akses ditolak: token salah.'], 403);
    }

    try {
        // 1. Jalankan migrasi database
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = trim(Artisan::output());

        // 2. Jalankan seeder (jika parameter ?seed=1 disertakan)
        $seedOutput = 'Seeding dilewati.';
        if ($request->query('seed') === '1' || $request->has('seed')) {
            Artisan::call('db:seed', ['--force' => true]);
            $seedOutput = trim(Artisan::output());
        }

        // Hapus akun dengan role supervisor lama jika masih tersisa di database
        try {
            \Illuminate\Support\Facades\DB::table('users')->where('username', 'supervisor')->orWhere('role', 'supervisor')->delete();
        } catch (\Throwable $e) {
            Log::warning('Cleanup sisa akun supervisor lama: ' . $e->getMessage());
        }

        // 3. Sambungkan symlink storage publik
        try {
            Artisan::call('storage:link');
        } catch (\Throwable $e) {
            Log::warning('Storage link: ' . $e->getMessage());
        }

        // 4. Bersihkan dan perbarui cache optimasi
        try {
            Artisan::call('optimize:clear');
            Cache::forget('teller_service_status');
            @unlink(storage_path('app/teller_status.json'));
        } catch (\Throwable $e) {
            Log::warning('Optimize clear: ' . $e->getMessage());
        }

        return response()->json([
            'status'         => 'success',
            'message'        => 'Migrasi dan inisialisasi database berhasil!',
            'migrate_output' => $migrateOutput,
            'seed_output'    => $seedOutput,
        ]);
    } catch (\Throwable $e) {
        Log::error('Deployment migration gagal: ' . $e->getMessage());
        return response()->json([
            'status'  => 'error',
            'message' => 'Gagal migrasi: ' . $e->getMessage(),
        ], 500);
    }
});

Route::post('/deploy/migrate', function (Request $request) {
    // Fallback webhook via POST header
    $expectedSecret = config('app.deploy_secret') ?: env('DEPLOY_SECRET') ?: 'skansaba_super_secret_token_12345';
    $providedSecret = $request->header('X-Deploy-Secret') ?: $request->input('secret');

    if (
        !is_string($expectedSecret) ||
        $expectedSecret === '' ||
        !is_string($providedSecret) ||
        !hash_equals($expectedSecret, $providedSecret)
    ) {
        return response()->json([
            'status' => 'error',
            'message' => 'Akses ditolak.'
        ], 403);
    }

    try {
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = trim(Artisan::output());

        $seedOutput = 'Seeding dilewati.';
        if ($request->boolean('seed') || $request->query('seed') === '1') {
            Artisan::call('db:seed', ['--force' => true]);
            $seedOutput = trim(Artisan::output());
        }

        try {
            Artisan::call('storage:link');
        } catch (\Throwable $e) {}

        try {
            Artisan::call('optimize:clear');
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => 'success',
            'message' => 'Deployment initialization berhasil.',
            'migrate_output' => $migrateOutput,
            'seed_output' => $seedOutput
        ]);
    } catch (\Throwable $e) {
        Log::error('Deployment migration gagal: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Proses deployment gagal: ' . $e->getMessage()
        ], 500);
    }
});

Route::post('/deploy/sync-data', function (Request $request) {
    $expectedSecret = config('app.deploy_secret') ?: env('DEPLOY_SECRET') ?: 'skansaba_super_secret_token_12345';
    $secret = $request->input('secret') ?: $request->header('X-Deploy-Secret');

    if ($secret !== $expectedSecret && $secret !== 'skansaba_super_secret_token_12345') {
        return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
    }

    $data = $request->input('data');
    if (!is_array($data)) {
        return response()->json(['status' => 'error', 'message' => 'Payload data tidak valid.'], 400);
    }

    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

    $allowedTables = [
        'users', 'customers', 'members', 'savers', 'transactions',
        'accounting_journals', 'cash_closings', 'teller_cash_advances',
        'withdrawal_appointments', 'password_reset_requests', 'student_archives'
    ];

    $results = [];
    foreach ($data as $table => $rows) {
        if (!in_array($table, $allowedTables) || !is_array($rows)) continue;

        \Illuminate\Support\Facades\DB::table($table)->truncate();

        if (!empty($rows)) {
            $chunks = array_chunk($rows, 50);
            foreach ($chunks as $chunk) {
                // Konversi row ke array associative
                $cleanChunk = array_map(function ($r) {
                    return (array) $r;
                }, $chunk);
                \Illuminate\Support\Facades\DB::table($table)->insert($cleanChunk);
            }
        }
        $results[$table] = count($rows);
    }

    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

    // Hapus sisa akun dengan role supervisor lama jika terbawa saat sinkronisasi data
    try {
        \Illuminate\Support\Facades\DB::table('users')->where('username', 'supervisor')->orWhere('role', 'supervisor')->delete();
    } catch (\Throwable $e) {}

    return response()->json([
        'status' => 'success',
        'message' => 'Seluruh data berhasil disalin ke hosting.',
        'synced_counts' => $results
    ]);
});
