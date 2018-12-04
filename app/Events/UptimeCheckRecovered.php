<?php

namespace App\Events;

use Telegram\Bot\Api;
use App\Monitor_Telegram;
use Spatie\UptimeMonitor\Helpers\Period;
use Spatie\UptimeMonitor\Models\Monitor;
use Illuminate\Contracts\Queue\ShouldQueue;

class UptimeCheckRecovered implements ShouldQueue
{
    /** @var \Spatie\UptimeMonitor\Models\Monitor */
    public $monitor;

    /** @var \Spatie\UptimeMonitor\Helpers\Period */
    public $downtimePeriod;

    public function __construct(Monitor_Telegram $monitor, Period $downtimePeriod)
    {
        $this->monitor = $monitor;

        $this->downtimePeriod = $downtimePeriod;

        $this->telegram = new Api();
    }

    public function setEvent($event){
        $this->monitor = $event->monitor;
        $telegrams = $this->getTelegrams();
        foreach($telegrams as $telegram){
            $this->telegram->sendMessage([
            'chat_id' => $telegram->chat_id, 
            'text' => $this->getMessageText()
            ]);
        }
        return;
    }
    public function getTelegrams(){
        return Monitor_Telegram::find($this->monitor->id)->telegrams()->get();
    }
    public function getMessageText(): string
    {
        return "{$this->monitor->url} has recovered after {$this->downtimePeriod->duration()}";
    }
}
