<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $table = 'school_classes';
    protected $fillable = ['name', 'academic_year_id', 'class_teacher_id'];

    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'class_teacher_id'); }
    public function sections(): HasMany { return $this->hasMany(Section::class); }
    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function subjects(): BelongsToMany { return $this->belongsToMany(Subject::class, 'class_subject')->withPivot('teacher_id'); }
}
