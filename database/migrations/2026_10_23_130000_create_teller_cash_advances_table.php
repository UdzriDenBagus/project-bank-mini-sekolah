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
        Schema::create('teller_cash_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teller_id')->constrained('users')->onDelete('cascade');
            $table->date('request_date');
            $table->bigInteger('amount');
            $table->string('type', 20)->default('initial'); // 'initial' (modal awal), 'topup' (suntikan modal)
            $table->string('status', 20)->default('pending'); // 'pending', 'approved', 'rejected'
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['teller_id', 'request_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teller_cash_advances');
    }
};
