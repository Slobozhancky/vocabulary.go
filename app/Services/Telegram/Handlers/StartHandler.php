<?php

namespace App\Services\Telegram\Handlers;

use Illuminate\Support\Facades\Http;
use App\Models\UserContact;

class StartHandler
{
    public function handle(array $message): void
    {
        $chatId = $message['chat']['id'];

        $hasContact = UserContact::where('telegram_id', $chatId)->exists();

        if (!$hasContact) {
            // Якщо контакту нема — просимо поділитись номером
            $text = "Привіт! Щоб почати, будь ласка, поділіться своїм номером телефону.";
            $keyboard = [
                [
                    ['text' => '☎️ Поділитись номером', 'request_contact' => true],
                ],
            ];
            $this->sendMessage($chatId, $text, $keyboard);
        } else {
            // Якщо контакт є — пропонуємо вводити слова
            $text = "Вітаю знову! Надішліть слово у форматі: <code>слово - переклад</code> або скористайтесь кнопками нижче.";
            $keyboard = [
                [
                    ['text' => '➕ Додати слово'],
                ],
                [
                    ['text' => '📃 Мої слова'],
                ],
            ];
            $this->sendMessage($chatId, $text, $keyboard);
        }
    }

    private function sendMessage($chatId, $text, $keyboard = null)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($keyboard) {
            $replyMarkup = ['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => false];
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        Http::post($url, $payload);
    }

    // обробка невідомих команд
    public function handleUnknown(array $message): void
    {
        $chatId = $message['chat']['id'];
        $this->sendMessage($chatId, "Невідома команда. Надішліть слово у форматі: <code>слово - переклад</code>");
    }

    public function handleHelp(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = "ℹ️ <b>Інструкція користування ботом:</b>\n\n"
            . "• Додавайте слова у форматі: <code>слово - переклад</code>\n"
            . "• Переглянути свої слова: 📃 Мої слова або /mywords\n"
            . "• Додати нове слово: ➕ Додати слово або /addnewword\n"
            . "• Редагувати чи видалити слово — натисніть відповідну кнопку під словом\n"
            . "• Для старту: /start\n";
        $this->sendMessage($chatId, $text, null, 'HTML');
    }
}
