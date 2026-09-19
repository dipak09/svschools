<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fee extends Model
{
    protected $fillable = [
        'student_id',
        'fee_type',
        'amount',
        'paid_amount',
        'due_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function getStatusAttribute(): string
    {
        if ((float) $this->paid_amount >= (float) $this->amount) {
            return 'Paid';
        }

        if ($this->due_date && $this->due_date->isPast()) {
            return 'Overdue';
        }

        return (float) $this->paid_amount > 0 ? 'Partial' : 'Pending';
    }
}