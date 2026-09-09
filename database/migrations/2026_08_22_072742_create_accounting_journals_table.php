<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('restrict');
            $table->string('account_code', 20); // '101' Kas Teller, '201' Tabungan Siswa, '102' Brankas
            $table->string('account_name', 100);
            $table->enum('position', ['debit', 'credit']);
            $table->unsignedBigInteger('amount');
            $table->timestamps();

            $table->engine = 'InnoDB';
        });

        // Rubrik 1.3: Check Constraint Jumlah > 0
        DB::statement('ALTER TABLE accounting_journals ADD CONSTRAINT chk_journal_amount CHECK (amount > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_journals');
    }
};