<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('members')) {
            Schema::table('members', function (Blueprint $table) {
                if (!Schema::hasColumn('members', 'email')) {
                    $table->string('email', 100)->nullable()->after('name');
                }
            });
        }

        if (Schema::hasTable('password_reset_requests')) {
            Schema::table('password_reset_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('password_reset_requests', 'reset_type')) {
                    $table->string('reset_type', 20)->default('password')->after('role'); // 'password' or 'pin'
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('members')) {
            Schema::table('members', function (Blueprint $table) {
                if (Schema::hasColumn('members', 'email')) {
                    $table->dropColumn('email');
                }
            });
        }

        if (Schema::hasTable('password_reset_requests')) {
            Schema::table('password_reset_requests', function (Blueprint $table) {
                if (Schema::hasColumn('password_reset_requests', 'reset_type')) {
                    $table->dropColumn('reset_type');
                }
            });
        }
    }
};
