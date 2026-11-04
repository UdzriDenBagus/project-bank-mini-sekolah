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
        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'promoted_years_count')) {
                $table->unsignedTinyInteger('promoted_years_count')->default(0)->after('graduation_year');
            }
            if (!Schema::hasColumn('members', 'last_promoted_at')) {
                $table->timestamp('last_promoted_at')->nullable()->after('promoted_years_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasColumn('members', 'promoted_years_count')) {
                $table->dropColumn('promoted_years_count');
            }
            if (Schema::hasColumn('members', 'last_promoted_at')) {
                $table->dropColumn('last_promoted_at');
            }
        });
    }
};
