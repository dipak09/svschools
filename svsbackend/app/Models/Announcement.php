<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = ['created_by', 'title', 'body', 'audience', 'school_class_id', 'published_at'];
    protected $casts = ['published_at' => 'datetime'];
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class); }
}
