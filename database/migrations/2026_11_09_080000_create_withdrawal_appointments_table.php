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
        if (!Schema::hasTable('withdrawal_appointments')) {
            Schema::create('withdrawal_appointments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('saver_id')->constrained('savers')->onDelete('cascade');
                $table->foreignId('teller_id')->nullable()->constrained('users')->nullOnDelete();
                $table->date('appointment_date'); // Tanggal dibuatnya perjanjian (H-1 atau sebelumnya)
                $table->date('withdrawal_date');  // Tanggal rencana penarikan (minimal appointment_date + 1 hari)
                $table->unsignedBigInteger('amount'); // Nominal penarikan dana besar (> 1.000.000)
                $table->string('status', 20)->default('approved'); // approved, completed, cancelled
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['saver_id', 'status']);
                $table->index('withdrawal_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_appointments');
    }
};
