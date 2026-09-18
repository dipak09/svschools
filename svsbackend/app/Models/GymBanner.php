<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymBanner extends Model
{
    protected $table = 'gym_banners';

    /**
     * This table uses camel case timestamp columns.
     */
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'name',
        'gym_id',
        'user_id',
        'banner',
        'link',
        'description',
    ];

    /**
     * The gym this banner belongs to.
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }
}
