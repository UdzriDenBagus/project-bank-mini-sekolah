<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah dukungan status 'rejected' dan kolom 'rejection_reason' pada cash_closings
        if (Schema::hasTable('cash_closings')) {
            try {
                // Ubah status menjadi string atau perbarui enum
                DB::statement("ALTER TABLE cash_closings MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'draft'");
            } catch (\Exception $e) {
                // Abaikan jika database tidak mendukung statement di atas
            }

            Schema::table('cash_closings', function (Blueprint $table) {
                if (!Schema::hasColumn('cash_closings', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('notes');
                }
            });
        }

        // 2. Tambah kolom arsip pada tabel members
        if (Schema::hasTable('members')) {
            Schema::table('members', function (Blueprint $table) {
                if (!Schema::hasColumn('members', 'is_archived')) {
                    $table->boolean('is_archived')->default(false)->after('status');
                }
                if (!Schema::hasColumn('members', 'archived_at')) {
                    $table->timestamp('archived_at')->nullable()->after('is_archived');
                }
            });
        }

        // 3. Tabel pencatatan arsip ZIP data siswa terhapus
        if (!Schema::hasTable('student_archives')) {
            Schema::create('student_archives', function (Blueprint $table) {
                $table->id();
                $table->string('filename', 150);
                $table->string('original_name', 150);
                $table->string('file_path', 255);
                $table->unsignedBigInteger('file_size')->default(0);
                $table->integer('student_count')->default(0);
                $table->text('student_summary')->nullable(); // Ringkasan daftar nama/NIS
                $table->timestamps();

                $table->engine = 'InnoDB';
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_archives');

        if (Schema::hasTable('members')) {
            Schema::table('members', function (Blueprint $table) {
                if (Schema::hasColumn('members', 'is_archived')) {
                    $table->dropColumn('is_archived');
                }
                if (Schema::hasColumn('members', 'archived_at')) {
                    $table->dropColumn('archived_at');
                }
            });
        }

        if (Schema::hasTable('cash_closings')) {
            Schema::table('cash_closings', function (Blueprint $table) {
                if (Schema::hasColumn('cash_closings', 'rejection_reason')) {
                    $table->dropColumn('rejection_reason');
                }
            });
        }
    }
};
