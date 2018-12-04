<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Telegram extends Model
{
    protected $fillable = [
        'chat_id', 'title', 'first_name', 'last_name', 'type',
    ];
    public function monitors(){
        return $this->belongsToMany('App\Monitor_Telegram','telegram_monitors', 'telegram_id','monitor_id');
    }
}
