<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_on');
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->string('room')->nullable();
            $table->timestamps();
            $table->unique(['exam_id', 'subject_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('exam_schedules'); }
};