<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    protected $fillable = ['exam_id', 'student_id', 'subject_id', 'marks', 'total'];
    protected $casts = ['marks' => 'float', 'total' => 'float'];
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function getPercentageAttribute(): float { return $this->total > 0 ? round(($this->marks / $this->total) * 100, 2) : 0; }
    public function getGradeAttribute(): string { return match (true) { $this->percentage >= 90 => 'A+', $this->percentage >= 80 => 'A', $this->percentage >= 70 => 'B', $this->percentage >= 60 => 'C', $this->percentage >= 50 => 'D', default => 'F' }; }
}
