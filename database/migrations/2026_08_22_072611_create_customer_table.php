<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('account_number', 30)->unique();
            $table->string('nis', 30)->unique();
            $table->string('name', 100);
            $table->string('class_name', 50);
            $table->string('security_pin'); // 6-Digit Hashed PIN
            $table->unsignedBigInteger('balance')->default(0);
            $table->enum('status', ['active', 'blocked'])->default('active');
            $table->timestamps();

            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};