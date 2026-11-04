<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('members')) {
            Schema::table('members', function (Blueprint $table) {
                if (!Schema::hasColumn('members', 'password')) {
                    $table->string('password')->nullable()->after('graduation_year');
                }
            });

            // Backfill password default 'password123' untuk anggota yang belum punya password
            DB::table('members')->whereNull('password')->update([
                'password' => Hash::make('password123')
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('members') && Schema::hasColumn('members', 'password')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('password');
            });
        }
    }
};
