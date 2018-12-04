<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'telegram_id', 'from_id', 'first_name', 'last_name', 'is_bot', 'language_code', 'text'
    ];
}
