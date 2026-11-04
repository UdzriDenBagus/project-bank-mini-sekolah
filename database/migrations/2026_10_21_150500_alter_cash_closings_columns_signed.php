<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah kolom keuangan pada cash_closings menjadi SIGNED BIGINT agar mendukung nilai minus/negatif
     * (misal system_balance negatif jika penarikan melebihi setoran/saldo awal loket).
     */
    public function up(): void
    {
        if (Schema::hasTable('cash_closings')) {
            DB::statement("ALTER TABLE cash_closings MODIFY COLUMN opening_balance BIGINT NOT NULL DEFAULT 0");
            DB::statement("ALTER TABLE cash_closings MODIFY COLUMN total_deposit BIGINT NOT NULL DEFAULT 0");
            DB::statement("ALTER TABLE cash_closings MODIFY COLUMN total_withdraw BIGINT NOT NULL DEFAULT 0");
            DB::statement("ALTER TABLE cash_closings MODIFY COLUMN system_balance BIGINT NOT NULL DEFAULT 0");
            DB::statement("ALTER TABLE cash_closings MODIFY COLUMN physical_balance BIGINT NOT NULL DEFAULT 0");
            DB::statement("ALTER TABLE cash_closings MODIFY COLUMN difference BIGINT NOT NULL DEFAULT 0");
        }
    }

    public function down(): void
    {
        // Tetap signed demi integritas data
    }
};
