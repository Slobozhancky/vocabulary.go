<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;

class TelegramBotController extends Controller
{
    public function webhook(Request $request)
    {
        $data = $request->all();

        if (!isset($data['message'])) {
            return response('No message found', 200);
        }

        $message = $data['message'];
        $chatId = $message['chat']['id'];
        $text = trim($message['text'] ?? '');

        // Команда /start
        if ($text === '/start') {
            $keyboard = [
                [['text' => '➕ Додати слово']],
                [['text' => '📃 Мої слова']],
            ];

            $this->sendTelegramMessage(
                $chatId,
                "👋 Вітаю! Надішліть мені слово у форматі:\n<code>apple - яблуко</code>\nАбо скористайтесь кнопками нижче:",
                $keyboard
            );
            return response('OK', 200);
        }

        // Кнопка "Мої слова"
        if ($text === '📃 Мої слова' || $text === '/mywords') {
            $words = Word::where('user_id', $chatId)->get();

            if ($words->isEmpty()) {
                $this->sendTelegramMessage($chatId, "У вас ще немає збережених слів.");
            } else {
                $message = "📚 Ваші слова:\n\n";
                foreach ($words as $word) {
                    $message .= "🔹 <b>{$word->word}</b> — {$word->translation}\n";
                }
                $this->sendTelegramMessage($chatId, $message);
            }

            return response('OK', 200);
        }

        // Кнопка "Додати слово"
        if ($text === '➕ Додати слово' || $text === '/addnewword') {
            $this->sendTelegramMessage($chatId, "✍️ Надішліть мені слово у форматі:\n<code>apple - яблуко</code>");
            return response('OK', 200);
        }

        // Додавання слова (word - translation)
        if (str_contains($text, '-')) {
            [$word, $translation] = array_map('trim', explode('-', $text, 2));

            if (!empty($word) && !empty($translation)) {
                Word::create([
                    'user_id' => $chatId,
                    'word' => $word,
                    'translation' => $translation,
                ]);

                $this->sendTelegramMessage($chatId, "✅ Додано: <b>{$word}</b> — {$translation}");
            } else {
                $this->sendTelegramMessage($chatId, "⚠️ Невірний формат. Приклад:\n<code>apple - яблуко</code>");
            }

            return response('OK', 200);
        }

        // Повідомлення за замовчуванням
        $this->sendTelegramMessage($chatId, "❗ Невідома команда. Надішліть слово у форматі:\n<code>слово - переклад</code>");
        return response('OK', 200);
    }

    private function sendTelegramMessage($chatId, $text, $keyboard = null)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $postData = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($keyboard) {
            $postData['reply_markup'] = json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]);
        }

        file_get_contents($url . '?' . http_build_query($postData));
    }
}
