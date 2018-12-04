<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Spatie\Url\Url;
use Spatie\UptimeMonitor\Models\Monitor;
use Illuminate\Support\Facades\Validator;
class ListCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "list";

    /**
     * @var string Command Description
     */
    protected $description = "Exibe todos os monitoramentos criados";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $updates = $this->getUpdate();
        $data = explode(' ',$arguments);
        $url = $data[0];
        $data = [
            'url' => $url,
        ];
        
        $telegram_user = \App\Telegram::where('chat_id',$updates['message']['chat']['id'])
        ->with('monitors')
        ->first();
        $response = '';
        foreach($telegram_user->monitors as $monitor){
            $response .= $monitor->url."\n";
        }
        $this->replyWithMessage(['text' => $response]);
    }
}