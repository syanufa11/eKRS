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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 12)->unique(); // Wajib unik, 8-12 digit
            $table->string('name', 100); // 3-100 karakter
            $table->string('email')->unique(); // Wajib format email valid & unik
            $table->timestamps();
            $table->softDeletes();

            // Index tambahan untuk pencarian nama
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
