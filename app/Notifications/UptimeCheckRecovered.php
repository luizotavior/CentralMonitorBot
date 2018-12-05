<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\UptimeMonitor\Models\Enums\UptimeStatus;
use Illuminate\Notifications\Messages\SlackAttachment;
use Spatie\UptimeMonitor\Notifications\BaseNotification;
use Spatie\UptimeMonitor\Events\UptimeCheckRecovered as MonitorRecoveredEvent;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;
use Telegram\Bot\Api;
use App\Monitor_Telegram;

class UptimeCheckRecovered extends BaseNotification
{
    /** @var \Spatie\UptimeMonitor\Events\UptimeCheckRecovered */
    public $event;

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $telegram = new Api();
        $telegrams = $this->getTelegrams();
        foreach($telegrams as $telegram){
            $telegram->sendMessage([
            'chat_id' => $telegram->chat_id,
            'text' => $this->getMessageText()
            ]);
        }

        $mailMessage = (new MailMessage)
            ->success()
            ->subject($this->getMessageText())
            ->line($this->getMessageText())
            ->line($this->getLocationDescription());

        foreach ($this->getMonitorProperties() as $name => $value) {
            $mailMessage->line($name.': '.$value);
        }

        return $mailMessage;
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->success()
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title($this->getMessageText())
                    ->fallback($this->getMessageText())
                    ->footer($this->getLocationDescription())
                    ->timestamp(Carbon::now());
            });
    }

    public function getTelegrams(){
        return Monitor_Telegram::find($this->getMonitor()->id)->telegrams()->get();
    }
    
    public function getMonitorProperties($extraProperties = []): array
    {
        $extraProperties = [
            "Downtime: {$this->event->downtimePeriod->duration()}" => $this->event->downtimePeriod->toText(),
        ];

        return parent::getMonitorProperties($extraProperties);
    }

    public function isStillRelevant(): bool
    {
        return $this->getMonitor()->uptime_status == UptimeStatus::UP;
    }

    public function setEvent(MonitorRecoveredEvent $event)
    {
        $this->event = $event;

        return $this;
    }

    public function getMessageText(): string
    {
        return "{$this->getMonitor()->url} has recovered after {$this->event->downtimePeriod->duration()}";
    }
}
