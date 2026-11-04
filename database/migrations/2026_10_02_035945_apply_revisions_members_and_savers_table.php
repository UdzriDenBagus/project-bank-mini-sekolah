<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat Tabel 'members' (Anggota Siswa yang terdaftar)
        if (!Schema::hasTable('members')) {
            Schema::create('members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('nis', 30)->unique();
                $table->string('name', 100);
                $table->string('class_name', 50);
                $table->unsignedSmallInteger('graduation_year')->default((int)date('Y'));
                $table->enum('status', ['inactive_account', 'active_account'])->default('inactive_account');
                $table->timestamps();

                $table->engine = 'InnoDB';
            });
        }

        // 2. Rename Tabel 'customers' Menjadi 'savers' (Jika masih bernama customers)
        if (Schema::hasTable('customers') && !Schema::hasTable('savers')) {
            Schema::rename('customers', 'savers');
        }

        // 3. Tambahkan SoftDeletes & Relasi Member ke Tabel 'savers'
        if (Schema::hasTable('savers')) {
            Schema::table('savers', function (Blueprint $table) {
                if (!Schema::hasColumn('savers', 'member_id')) {
                    $table->foreignId('member_id')->nullable()->after('id')->constrained('members')->onDelete('restrict');
                }
                if (!Schema::hasColumn('savers', 'deleted_at')) {
                    $table->softDeletes()->after('status');
                }
            });
        }

        // 4. Tambahkan Snapshot Audit & Kolom saver_id pada Tabel 'transactions'
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                // Tambahkan saver_id jika belum ada (atau rename customer_id menjadi saver_id)
                if (Schema::hasColumn('transactions', 'customer_id') && !Schema::hasColumn('transactions', 'saver_id')) {
                    // Di MySQL InnoDB, renameColumn aman dilakukan
                    $table->renameColumn('customer_id', 'saver_id');
                } elseif (!Schema::hasColumn('transactions', 'saver_id')) {
                    $table->foreignId('saver_id')->nullable()->after('transaction_code')->constrained('savers')->onDelete('restrict');
                }

                // Kolom Snapshot agar data nama & no rekening abadi di laporan audit walau akun di-soft delete
                if (!Schema::hasColumn('transactions', 'saver_name_snapshot')) {
                    $table->string('saver_name_snapshot', 100)->nullable()->after('saver_id');
                }
                if (!Schema::hasColumn('transactions', 'account_number_snapshot')) {
                    $table->string('account_number_snapshot', 30)->nullable()->after('saver_name_snapshot');
                }
            });
        }

        // 5. DATA BACKFILL OTOMATIS: Migrasikan data Nasabah lama ke tabel Members agar sinkron 100%
        $existingSavers = DB::table('savers')->whereNull('member_id')->get();
        foreach ($existingSavers as $saver) {
            // Masukkan data siswa ke tabel members sebagai akun aktif
            $memberId = DB::table('members')->insertGetId([
                'nis'             => $saver->nis,
                'name'            => $saver->name,
                'class_name'      => $saver->class_name,
                'graduation_year' => (int)date('Y'),
                'status'          => 'active_account',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // Hubungkan kembali saver_id ke member_id
            DB::table('savers')->where('id', $saver->id)->update(['member_id' => $memberId]);
        }

        // 6. Isi snapshot transaksi lama dari data penabung yang ada
        DB::statement("
            UPDATE transactions t
            JOIN savers s ON t.saver_id = s.id
            SET t.saver_name_snapshot = s.name,
                t.account_number_snapshot = s.account_number
            WHERE t.saver_name_snapshot IS NULL
        ");
    }

    public function down(): void
    {
        // Rollback aman jika diperlukan
        if (Schema::hasTable('savers') && !Schema::hasTable('customers')) {
            Schema::rename('savers', 'customers');
        }
    }
};