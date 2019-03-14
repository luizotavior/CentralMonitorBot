<?php

namespace App\Http\Controllers;

use Mail;
use Illuminate\Http\Request;
use Telegram\Bot\Api;
use function GuzzleHttp\json_encode;
use App\Conversation;
class TelegramController extends Controller
{
    protected $telegram;
    protected $chat_id;
    protected $user_id;
    protected $text;


    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    public function getMe()
    {
        $response = $this->telegram->getMe();
        return $response;
    }
    public function setWebHook()
    {
        $url = 'https://monitor.luizotavior.com.br/' . env('TELEGRAM_BOT_TOKEN') . '/webhook';
        $response = $this->telegram->setWebhook(['url' => $url]);

        return $response == true ? redirect()->back() : dd($response);
    }

    public function removeWebHook(){
        $response = $this->telegram->removeWebhook();
    }

    public function handleRequest(Request $request)
    {
        $updates = $this->telegram->getWebhookUpdates();
        if(isset($updates['parameters']['migrate_to_chat_id']) && isset($updates['message']['chat']['id'])){
            $user = \App\Telegram::where('chat_id',$this->chat_id)->first();
            $user->chat_id = $updates['parameters']['migrate_to_chat_id'];
            $user->save();
        }
        if(!isset($updates['message']['chat']['id']) or !isset($updates['message']['text'])){
              return;
        }
        $this->chat_id = $updates['message']['chat']['id'];
        //$this->telegram->triggerCommand('register');
        //$this->processCommand($update);
        if($updates['message']['text'] == '/register' or $updates['message']['text'] == '\/register'){
            $this->telegram->getCommandBus()->handler('/register', $updates);
            return;
        }

        $user = \App\Telegram::where('chat_id',$this->chat_id)->first();
        if($user == null){
            $this->sendMessage('Você não esta registrado. Se registre em "/register"');
            return;
        }
        $conversation = Conversation::create([
            'telegram_id' => $user->id,
            'from_id' => isset($updates['message']['from']['id']) ? $updates['message']['from']['id'] : '',
            'first_name' => isset($updates['message']['from']['first_name']) ? $updates['message']['from']['first_name'] : '',
            'last_name' => isset($updates['message']['from']['last_name']) ? $updates['message']['from']['last_name'] : '',
            'is_bot' => isset($updates['message']['from']['is_bot']) ? $updates['message']['from']['is_bot'] : '',
            'language_code' => isset($updates['message']['from']['language_code']) ? $updates['message']['from']['language_code'] : '',
            'text' => $updates['message']['text'],
        ]);
        $update = $this->telegram->commandsHandler(true);
        //$this->chat = $request['message']['chat']['id'];
        //$this->chat = $request['message']['chat']['id'];
        //$this->user_id = $request['message']['from']['id'];
        //$this->first_name = $request['message']['from']['first_name'];
        //$this->last_name = $request['message']['from']['last_name'];
        //$message = $update->getMessage();
        //$type = $update->detectType();
        //$chat = $message->getChat();
        //$chat_id = $chat->id;
        //$this->text = $request['message']['text'];
        return response()->json(['success' => 'success'], 200);
    }

    protected function sendMessage($message, $parse_html = false)
    {
        $data = [
            'chat_id' => $this->chat_id,
            'text' => $message,
        ];

        if ($parse_html) $data['parse_mode'] = 'HTML';

        $this->telegram->sendMessage($data);
    }
}
