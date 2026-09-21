<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = ['student_id', 'attendance_date', 'status', 'marked_by'];
    protected $casts = ['attendance_date' => 'date'];
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function marker(): BelongsTo { return $this->belongsTo(User::class, 'marked_by'); }
}
