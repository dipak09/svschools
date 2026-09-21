<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSchedule extends Model
{
    protected $fillable = ['exam_id', 'subject_id', 'scheduled_on', 'starts_at', 'ends_at', 'room'];
    protected $casts = ['scheduled_on' => 'date'];
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
}