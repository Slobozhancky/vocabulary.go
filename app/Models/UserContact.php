<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserContact extends Model
{
    protected $fillable = [
        'telegram_id',
        'phone',
        'first_name',
        'last_name',
    ];
}
