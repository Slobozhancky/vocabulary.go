<?php

use Illuminate\Support\Facades\Route;

Route::post('/bot', function () {
    $content = file_get_contents("php://input");
    $update = json_decode($content, true);

    if (isset($update['message'])) {
        $chatId = $update['message']['chat']['id'];
        $text = $update['message']['text'] ?? '';

        if ($text === '/start') {
            sendTelegramMessage($chatId, "Привіт! Обери дію:", [
                'inline_keyboard' => [
                    [
                        ['text' => '🗂 Мої слова', 'callback_data' => 'my_words']
                    ]
                ]
            ]);
        }
    }

    if (isset($update['callback_query'])) {
        $callbackData = $update['callback_query']['data'];
        $userId = $update['callback_query']['from']['id'];
        $callbackId = $update['callback_query']['id'];

        if ($callbackData === 'my_words') {
            answerCallbackQuery($callbackId);
            sendTelegramMessage($userId, "Тут мали би бути твої слова");
        }
    }
});

function sendTelegramMessage($chatId, $text, $keyboard = [], $markdown = false) {
    $payload = [
        'chat_id' => $chatId,
        'text' => $text,
    ];

    if (!empty($keyboard)) {
        $payload['reply_markup'] = json_encode($keyboard);
    }

    if ($markdown) {
        $payload['parse_mode'] = 'Markdown';
    }

    file_get_contents("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage?" . http_build_query($payload));
}

function answerCallbackQuery($callbackId) {
    file_get_contents("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/answerCallbackQuery?" . http_build_query([
        'callback_query_id' => $callbackId
    ]));
}
