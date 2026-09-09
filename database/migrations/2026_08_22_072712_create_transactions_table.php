<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); // Piket Teller
            $table->dateTime('transaction_date');
            $table->enum('transaction_type', ['deposit', 'withdraw']);
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
        });

        // Rubrik 1.3: Check Constraint Nominal > 0 Level Database
        DB::statement('ALTER TABLE transactions ADD CONSTRAINT chk_transaction_amount CHECK (amount > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};