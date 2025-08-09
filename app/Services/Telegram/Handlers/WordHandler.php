<?php

namespace App\Services\Telegram\Handlers;

use Illuminate\Support\Facades\Http;
use App\Models\Word;

class WordHandler
{
    public function handle(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = trim($message['text'] ?? '');

        if ($text === '📃 Мої слова' || $text === '/mywords') {
            $words = Word::where('user_id', $chatId)->get();

            if ($words->isEmpty()) {
                $this->sendMessage($chatId, "У вас ще немає слів.");
            } else {
                $msg = "📚 Ваші слова:\n\n";
                foreach ($words as $w) {
                    $msg .= "🔹 {$w->word} — {$w->translation}\n";
                }
                $this->sendMessage($chatId, $msg);
            }
            return;
        }

        // Додавання через формат "word - translation"
        if (str_contains($text, '-')) {
            [$wordText, $translation] = array_map('trim', explode('-', $text, 2));
            if ($wordText !== '') {
                Word::create([
                    'user_id' => $chatId,
                    'word' => $wordText,
                    'translation' => $translation,
                ]);
                $this->sendMessage($chatId, "✅ Додано: {$wordText} — {$translation}");
            } else {
                $this->sendMessage($chatId, "Невірний формат. Приклад: apple - яблуко");
            }
            return;
        }

        // Невідомий текст
        $this->sendMessage($chatId, "Надішліть слово у форматі: слово - переклад або скористайтесь кнопками.");
    }

    public function promptAddWord(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = "Надішліть слово у форматі: <code>слово - переклад</code> або скористайтесь кнопками нижче.";
        $this->sendMessage($chatId, $text);
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
}
