<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Teacher extends Model
{
    protected $fillable = ['user_id', 'employee_id', 'qualification', 'department', 'experience_years', 'joining_date'];
    protected $casts = ['joining_date' => 'date'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function subjects(): BelongsToMany { return $this->belongsToMany(Subject::class, 'class_subject', 'teacher_id', 'subject_id')->withPivot('school_class_id'); }
    public function classes(): BelongsToMany { return $this->belongsToMany(SchoolClass::class, 'class_subject', 'teacher_id', 'school_class_id')->withPivot('subject_id'); }
}
