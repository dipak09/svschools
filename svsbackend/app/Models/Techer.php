<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Techer extends Model
{
     protected $fillable = [
        'name',
        'email',
        'std',
    ];

}
