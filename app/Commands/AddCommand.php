<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Spatie\Url\Url;
use Spatie\UptimeMonitor\Models\Monitor;
use Illuminate\Support\Facades\Validator;
class AddCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "add";

    /**
     * @var string Command Description
     */
    protected $description = "Criar um novo Monitoramento";

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
        
        $validator = $this->validator($data);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('url')) {
                $this->replyWithMessage(['text' => ''.$errors->first('url')]);
            }
            return;
        }

        $telegram_user = \App\Telegram::where('chat_id',$updates['message']['chat']['id'])->first();
        $url = Url::fromString($url);
        $monitor = Monitor::firstOrCreate(
            [
                'url' => trim($url, '/')
            ],
            [
                'look_for_string' => '',
                'uptime_check_method' => 'head',
                'certificate_check_enabled' => $url->getScheme() === 'https',
                'uptime_check_interval_in_minutes' => 1,
            ]);
        $telegram_user->monitors()->syncWithoutDetaching([$monitor->id]);
        $this->replyWithMessage(['text' => 'Monitoramento criado!']);
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        // Trigger another command dynamically from within this command
        // When you want to chain multiple commands within one or process the request further.
        // The method supports second parameter arguments which you can optionally pass, By default
        // it'll pass the same arguments that are received for this command originally.
        //$this->triggerCommand('subscribe');
    }
    
    protected function validator(array $data){
        $messages = [
            'required' => 'Não há argumentos suficientes (falta: ":attribute").',
            'unique' => 'Monitoramento já existe!',
        ];
        return Validator::make($data, [
            'url' => 'required|url'
        ],$messages);
    }
}