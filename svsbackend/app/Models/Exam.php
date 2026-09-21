<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $fillable = ['name', 'academic_year_id', 'starts_on', 'ends_on'];
    protected $casts = ['starts_on' => 'date', 'ends_on' => 'date'];
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function results(): HasMany { return $this->hasMany(ExamResult::class); }
    public function schedules(): HasMany { return $this->hasMany(ExamSchedule::class); }
}
