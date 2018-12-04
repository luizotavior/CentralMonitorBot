<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Monitor_Telegram extends Model
{
    protected $table = 'monitors';

    protected $guarded = [];

    protected $dates = [
        'uptime_last_check_date',
        'uptime_status_last_change_date',
        'uptime_check_failed_event_fired_on_date',
        'certificate_expiration_date',
    ];

    protected $casts = [
        'uptime_check_enabled' => 'boolean',
        'certificate_check_enabled' => 'boolean',
    ];
    
    public function telegrams(){
        return $this->belongsToMany('App\Telegram','telegram_monitors', 'monitor_id','telegram_id');
    }
}
