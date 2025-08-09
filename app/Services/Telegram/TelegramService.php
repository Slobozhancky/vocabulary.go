<?php

namespace App\Services;

class TelegramService
{
    protected $token;
    protected $apiUrl;

    public function __construct()
    {
        $this->token = env('TELEGRAM_BOT_TOKEN');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}/";
    }

    public function sendMessage($chatId, $text)
    {
        $url = $this->apiUrl . "sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text'    => $text,
        ];
        file_get_contents($url . '?' . http_build_query($data));
    }

    public function sendContactRequestButton($chatId)
    {
        $url = $this->apiUrl . "sendMessage";
        $data = [
            'chat_id'      => $chatId,
            'text'         => "Будь ласка, надішліть свій номер телефону:",
            'reply_markup' => json_encode([
                'keyboard' => [
                    [[
                        'text' => 'Поділитися номером',
                        'request_contact' => true
                    ]]
                ],
                'one_time_keyboard' => true,
                'resize_keyboard'   => true
            ])
        ];
        file_get_contents($url . '?' . http_build_query($data));
    }

}
