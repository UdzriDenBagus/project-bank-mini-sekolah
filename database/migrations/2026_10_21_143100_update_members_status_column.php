<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('members')) {
            // Ubah tipe kolom status menjadi varchar(30) agar fleksibel mendukung active_account, inactive_account, graduated
            DB::statement("ALTER TABLE `members` MODIFY `status` VARCHAR(30) NOT NULL DEFAULT 'inactive_account'");
        }
    }

    public function down(): void
    {
    }
};
