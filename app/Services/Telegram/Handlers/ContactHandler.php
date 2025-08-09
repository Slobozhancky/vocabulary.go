<?php

namespace App\Services\Telegram\Handlers;

use Illuminate\Support\Facades\Http;
use App\Models\UserContact;

class ContactHandler
{
    public function handle(array $message): void
    {
        $chatId = $message['chat']['id'];
        $contact = $message['contact'];

        $phone = $contact['phone_number'] ?? null;
        $firstName = $contact['first_name'] ?? null;
        $lastName = $contact['last_name'] ?? null;

        if ($phone) {
            UserContact::updateOrCreate(
                ['telegram_id' => $chatId],
                [
                    'phone' => $phone,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]
            );

            $this->sendMessage($chatId, "Дякую що поділились номером :)");
            $this->hideKeyboard($chatId);
        } else {
            $this->sendMessage($chatId, "Не вдалося отримати номер. Спробуйте ще раз.");
        }
    }

    private function sendMessage($chatId, $text)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        Http::post($url, [
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }

    private function hideKeyboard($chatId)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        Http::post($url, [
            'chat_id' => $chatId,
            // 'text' => 'Клавіатуру сховано.',
            'reply_markup' => json_encode([
                'remove_keyboard' => true,
                'selective' => true,
            ]),
        ]);
    }
}
