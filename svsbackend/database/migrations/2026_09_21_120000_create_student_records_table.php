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
        Schema::create('student_records', function (Blueprint $table) {
            $table->id();

            // Every record belongs to the user (school account) that owns it.
            // The public API is filtered by this column.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('roll_no', 20);
            $table->string('student_name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();

            $table->string('standard', 20);
            $table->string('division', 5);
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('dob');
            $table->string('city', 100);

            $table->unsignedTinyInteger('attendance_percent');
            $table->unsignedSmallInteger('marks_math');
            $table->unsignedSmallInteger('marks_science');
            $table->unsignedSmallInteger('marks_english');
            $table->unsignedSmallInteger('total_marks');
            $table->decimal('percentage', 5, 2);
            $table->string('grade', 2);

            $table->decimal('fees_total', 10, 2);
            $table->decimal('fees_paid', 10, 2);
            $table->decimal('fees_due', 10, 2);

            $table->enum('status', ['active', 'inactive', 'alumni']);
            $table->date('admission_date');

            $table->timestamps();

            $table->unique(['user_id', 'roll_no']);
            $table->index(['user_id', 'standard']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_records');
    }
};
