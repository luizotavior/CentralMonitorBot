<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use function GuzzleHttp\json_encode;

class RegisterCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "register";

    /**
     * @var string Command Description
     */
    protected $description = "Registre-se";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $updates = $this->getUpdate();
        $user = \App\Telegram::where('chat_id',$updates['message']['chat']['id'])->first();
        if($user != null){
            $this->replyWithMessage(['text' => 'Você já esta Registrado.']);
            return;
        }

        $telegram_chat = \App\Telegram::updateOrCreate(
            ['chat_id' => $updates['message']['chat']['id']],
            [
                'title' => isset($updates['message']['chat']['title']) ? $updates['message']['chat']['title'] : '',
                'first_name' => isset($updates['message']['chat']['first_name']) ? $updates['message']['chat']['first_name'] : '',
                'last_name' => isset($updates['message']['chat']['last_name']) ? $updates['message']['chat']['last_name'] : '',
                'type' => isset($updates['message']['chat']['type']) ? $updates['message']['chat']['type'] : '',
            ]
         );
         $this->replyWithMessage(['text' => 'Registrado !!']);
         //$this->replyWithChatAction(['text' => 'Registrado !!','action' => Actions::TYPING]);
    }
}