<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Spatie\Url\Url;
use Spatie\UptimeMonitor\Models\Monitor;
use Illuminate\Support\Facades\Validator;
class StatusCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "status";

    /**
     * @var string Command Description
     */
    protected $description = "Exibe o Status atual da Url Monitorada";

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
        $telegram_user = \App\Telegram::where('chat_id',$updates['message']['chat']['id'])
        ->with('monitors')
        ->first();

        $monitor = $telegram_user->monitors->where('url',$url)->first();
        if($monitor == null){
            $this->replyWithMessage(['text' => 'A Url informada não esta sendo monitorada.']);
            return;
        }
        $response = title_case($monitor->uptime_status)." - ".$monitor->url."\n"."Ultima Checagem: ".$monitor->uptime_last_check_date->format('d/m/Y H:i:s');
        if($monitor->uptime_status == 'down'){
            $response .= "\n";
            $response .= "Erro Informado: ".$monitor->uptime_check_failure_reason;
        }
        $this->replyWithMessage(['text' => $response]);
    }
    
    protected function validator(array $data){
        $messages = [
            'required' => 'Não há argumentos suficientes (falta: ":attribute").',
            'exist' => 'A Url informada não esta sendo monitorada.',
        ];
        return Validator::make($data, [
            'url' => 'required|url|exists:monitors,url'
        ],$messages);
    }
}