<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRecord extends Model
{
    protected $table = 'student_records';

    protected $fillable = [
        'user_id',
        'roll_no',
        'student_name',
        'email',
        'phone',
        'standard',
        'division',
        'gender',
        'dob',
        'city',
        'attendance_percent',
        'marks_math',
        'marks_science',
        'marks_english',
        'total_marks',
        'percentage',
        'grade',
        'fees_total',
        'fees_paid',
        'fees_due',
        'status',
        'admission_date',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date:Y-m-d',
            'admission_date' => 'date:Y-m-d',
            'attendance_percent' => 'integer',
            'marks_math' => 'integer',
            'marks_science' => 'integer',
            'marks_english' => 'integer',
            'total_marks' => 'integer',
            'percentage' => 'float',
            'fees_total' => 'float',
            'fees_paid' => 'float',
            'fees_due' => 'float',
        ];
    }

    /**
     * The user (school account) this record belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
