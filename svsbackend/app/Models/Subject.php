<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    protected $fillable = ['name', 'code', 'type', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function classes(): BelongsToMany { return $this->belongsToMany(SchoolClass::class, 'class_subject')->withPivot('teacher_id'); }
    public function results(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(ExamResult::class); }
}
