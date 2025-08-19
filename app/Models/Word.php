<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    protected $fillable = [
        'user_id',
        'word',
        'translation',
        'example', // додайте це поле
    ];
    public $timestamps = true;
}
