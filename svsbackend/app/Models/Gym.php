<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gym extends Model
{
    /**
     * The table is singular, so it has to be named explicitly.
     */
    protected $table = 'gym';

    /**
     * This table uses camel case timestamp columns.
     */
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'user_id',
        'name',
        'uniq_id',
        'address',
        'phone1',
        'phone2',
        'email',
        'map_url',
        'website',
        'notification_key',
        'logo',
        'add_by',
        'is_default',
    ];

    /**
     * The banners that belong to this gym.
     */
    public function banners(): HasMany
    {
        return $this->hasMany(GymBanner::class, 'gym_id');
    }

    function getNameAttribute($value)
    {
        return ucfirst($value);
    }

}
