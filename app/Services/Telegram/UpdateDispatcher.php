<?php

namespace App\Services\Telegram;

class UpdateDispatcher
{
    public function dispatch(array $update): void
    {
        // Вхідні випадки: message, callback_query, inline_query і т.д.
        if (isset($update['message'])) {
            $message = $update['message'];

            // Якщо це контакт (коли користувач поділився своїм номером)
            if (isset($message['contact'])) {
                (new Handlers\ContactHandler())->handle($message);
                return;
            }

            // Текстове повідомлення
           if (isset($message['text'])) {
            $text = trim($message['text']);

            if ($text === '/start') {
                (new Handlers\StartHandler())->handle($message);
                return;
            }

            if ($text === '➕ Додати слово' || $text === '/addnewword') {
                // Тут викликаємо спеціальний хендлер або повідомляємо користувача, що треба надіслати слово
                (new Handlers\WordHandler())->promptAddWord($message);
                return;
            }

            if ($text === '📃 Мої слова' || $text === '/mywords' || str_contains($text, '-')) {
                (new Handlers\WordHandler())->handle($message);
                return;
            }

            (new Handlers\StartHandler())->handleUnknown($message);
            return;
        }

        }

        // Можна додати обробку callback_query тут
    }
}
