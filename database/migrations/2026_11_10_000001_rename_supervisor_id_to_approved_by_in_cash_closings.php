<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migrasi: Hapus kolom supervisor_id dan ganti dengan approved_by
 * karena role supervisor sudah dihapus dari sistem.
 * Kolom approved_by menunjuk ke user (Admin) yang menyetujui closing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_closings', function (Blueprint $table) {
            // Hapus foreign key & kolom supervisor_id lama
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn('supervisor_id');

            // Tambahkan kolom approved_by (nullable, menunjuk ke users Admin yang menyetujui)
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cash_closings', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn('approved_by');

            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('restrict');
        });
    }
};

