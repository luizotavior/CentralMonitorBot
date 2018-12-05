<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\UptimeMonitor\Models\Enums\UptimeStatus;
use Illuminate\Notifications\Messages\SlackAttachment;
use Spatie\UptimeMonitor\Notifications\BaseNotification;
use Spatie\UptimeMonitor\Events\UptimeCheckFailed as MonitorFailedEvent;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;
use Telegram\Bot\Api;
use App\Monitor_Telegram;

class UptimeCheckFailed extends BaseNotification
{
    /** @var \Spatie\UptimeMonitor\Events\UptimeCheckFailed */
    public $event;

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $telegram_api = new Api();
        $telegrams = $this->getTelegrams();
        foreach($telegrams as $telegram){
            $telegram_api->sendMessage([
                'chat_id' => $telegram->chat_id,
                'text' => $this->getMessageText()
            ]);
        }
        $mailMessage = (new MailMessage)
            ->error()
            ->subject($this->getMessageText())
            ->line($this->getMessageText())
            ->line($this->getLocationDescription());

        foreach ($this->getMonitorProperties() as $name => $value) {
            $mailMessage->line($name.': '.$value);
        }

        return $mailMessage;
    }


    public function getTelegrams(){
        return Monitor_Telegram::find($this->getMonitor()->id)->telegrams()->get();
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->error()
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title($this->getMessageText())
                    ->content($this->getMonitor()->uptime_check_failure_reason)
                    ->fallback($this->getMessageText())
                    ->footer($this->getLocationDescription())
                    ->timestamp(Carbon::now());
            });
    }

    public function getMonitorProperties($extraProperties = []): array
    {
        $since = "Since {$this->event->downtimePeriod->startDateTime->format('H:i')}";
        $date = $this->event->monitor->formattedLastUpdatedStatusChangeDate();

        $extraProperties = [
            $since => $date,
            'Failure reason' => $this->getMonitor()->uptime_check_failure_reason,
        ];

        return parent::getMonitorProperties($extraProperties);
    }

    public function isStillRelevant(): bool
    {
        return $this->getMonitor()->uptime_status == UptimeStatus::DOWN;
    }

    public function setEvent(MonitorFailedEvent $event)
    {
        $this->event = $event;

        return $this;
    }

    protected function getMessageText(): string
    {
        return "{$this->getMonitor()->url} parece estar fora do ar";
    }
}
