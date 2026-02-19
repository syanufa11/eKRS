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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade'); //
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); //
            $table->string('academic_year', 9); // Format YYYY/YYYY
            $table->enum('semester', ['1', '2']); // Ganjil/Genap
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED']); //
            $table->timestamps();

            // Unique Constraint agar mahasiswa tidak ambil MK yang sama di tahun/semester yang sama
            $table->unique(['student_id', 'course_id', 'academic_year', 'semester'], 'enrollment_unique_idx');

            // Index untuk performa filtering & sorting (Server-side)
            $table->index('academic_year');
            $table->index('semester');
            $table->index('status');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
