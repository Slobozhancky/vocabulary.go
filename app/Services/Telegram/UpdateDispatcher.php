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

            if ($text === '/help') {
                (new Handlers\StartHandler())->handleHelp($message);
                return;
            }

            // Замість перевірки на '-'
            // Всі текстові повідомлення передаємо у WordHandler
            (new Handlers\WordHandler())->handle($message);
            return;
        }

        }

        // Можна додати обробку callback_query тут

        if (isset($update['callback_query'])) {
            $callback = $update['callback_query'];
            $data = $callback['data'];
            $chatId = $callback['message']['chat']['id'];
            $messageId = $callback['message']['message_id'];

            if (str_starts_with($data, 'delete_')) {
                $wordId = str_replace('delete_', '', $data);
                (new Handlers\WordHandler())->deleteWord($chatId, $wordId, $messageId);
                return;
            }

            if (str_starts_with($data, 'edit_')) {
                $wordId = str_replace('edit_', '', $data);
                (new Handlers\WordHandler())->promptEditWord($chatId, $wordId);
                return;
            }

            if (str_starts_with($data, 'addexample_')) {
                $wordId = str_replace('addexample_', '', $data);
                (new Handlers\WordHandler())->promptAddExample($chatId, $wordId);
                return;
            }
        }
    }
}
