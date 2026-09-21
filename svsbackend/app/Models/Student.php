<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = ['user_id', 'admission_number', 'date_of_birth', 'gender', 'address', 'admission_date', 'roll_number', 'school_class_id', 'section_id'];
    protected $casts = ['date_of_birth' => 'date', 'admission_date' => 'date'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function parents(): BelongsToMany { return $this->belongsToMany(ParentModel::class, 'parent_student')->withPivot('relationship'); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function results(): HasMany { return $this->hasMany(ExamResult::class); }
}
