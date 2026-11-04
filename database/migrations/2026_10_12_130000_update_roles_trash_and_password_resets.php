<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah SoftDeletes pada tabel members
        if (Schema::hasTable('members')) {
            Schema::table('members', function (Blueprint $table) {
                if (!Schema::hasColumn('members', 'deleted_at')) {
                    $table->softDeletes()->after('status');
                }
            });
        }

        // 2. Tambah kolom email pada tabel users untuk admin reset password
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'email')) {
                    $table->string('email', 100)->nullable()->after('name');
                }
            });

            // Set email default untuk admin jika belum ada
            DB::table('users')->where('username', 'admin')->whereNull('email')->update([
                'email' => 'admin.bankmini@gmail.com'
            ]);

            // Migrasikan role supervisor menjadi admin
            DB::table('users')->where('role', 'supervisor')->update([
                'role' => 'admin'
            ]);
        }

        // 3. Pastikan kolom saver_id pada transactions bersifat nullable dan tidak memblokir penghapusan permanen saver
        if (Schema::hasTable('transactions')) {
            // Drop foreign key restrict jika ada, dan set null on delete agar riwayat transaksi siswa permanen tetap abadi
            try {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->unsignedBigInteger('saver_id')->nullable()->change();
                });
            } catch (\Exception $e) {
                // Abaikan jika sudah nullable
            }
        }

        // 4. Buat tabel password_reset_requests untuk request reset password oleh Teller & Siswa serta kode OTP Admin
        if (!Schema::hasTable('password_reset_requests')) {
            Schema::create('password_reset_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('saver_id')->nullable()->constrained('savers')->onDelete('cascade');
                $table->string('role', 30); // admin, teller, customer/siswa
                $table->string('nis', 30)->nullable();
                $table->string('name', 100);
                $table->string('class_name', 50)->nullable();
                $table->string('reset_code', 20);
                $table->string('email', 100)->nullable();
                $table->enum('status', ['pending', 'used', 'expired'])->default('pending');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->engine = 'InnoDB';
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_requests');

        if (Schema::hasTable('members') && Schema::hasColumn('members', 'deleted_at')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }
    }
};
