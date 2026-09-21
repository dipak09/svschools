<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ParentModel extends Model
{
    protected $table = 'parents';
    protected $fillable = ['user_id', 'address'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function students(): BelongsToMany { return $this->belongsToMany(Student::class, 'parent_student')->withPivot('relationship'); }
}
