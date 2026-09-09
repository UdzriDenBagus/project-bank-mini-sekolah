<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teller_id')->constrained('users')->onDelete('restrict');
            $table->date('closing_date');
            $table->unsignedBigInteger('opening_balance')->default(0);
            $table->unsignedBigInteger('total_deposit')->default(0);
            $table->unsignedBigInteger('total_withdraw')->default(0);
            $table->unsignedBigInteger('system_balance')->default(0);
            $table->unsignedBigInteger('physical_balance')->default(0);
            $table->bigInteger('difference')->default(0); // Selisih Kas
            $table->enum('status', ['draft', 'submitted', 'approved', 'dispute'])->default('draft');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->dateTime('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_closings');
    }
};