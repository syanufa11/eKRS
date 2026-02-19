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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // Contoh: IF101
            $table->string('name', 120); // 3-120 karakter
            $table->integer('credits'); // 1-6
            $table->timestamps();
            $table->softDeletes();

            // Index untuk pencarian kode mata kuliah
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
