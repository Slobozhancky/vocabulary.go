<?php

namespace App\Services\Telegram\Handlers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Word;

class WordHandler
{
    public function handle(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = trim($message['text'] ?? '');

        // Перевірка на режим редагування
        $editWordId = Cache::get("edit_word_{$chatId}");
        if ($editWordId) {
            if ($text === '') {
                $this->sendMessage($chatId, "❗️ Переклад не може бути порожнім. Введіть переклад.");
                return;
            }
            $word = Word::find($editWordId);
            if ($word) {
                $word->translation = $text;
                $word->save();
                $this->sendMessage($chatId, "✅ Переклад для слова <b>{$word->word}</b> оновлено на: <b>{$text}</b>", null, 'HTML');
            } else {
                $this->sendMessage($chatId, "Слово не знайдено.");
            }
            Cache::forget("edit_word_{$chatId}");
            return;
        }

        $exampleWordId = Cache::get("add_example_{$chatId}");
        if ($exampleWordId) {
            if (mb_strlen($text) < 5) {
                $this->sendMessage($chatId, "❗️ Приклад має бути зрозумілим реченням. Спробуйте ще раз.");
                return;
            }
            $word = Word::find($exampleWordId);
            if ($word) {
                $word->example = $text;
                $word->save();
                $this->sendMessage($chatId, "✅ Приклад для слова <b>{$word->word}</b> збережено!", null, 'HTML');
            } else {
                $this->sendMessage($chatId, "Слово не знайдено.");
            }
            Cache::forget("add_example_{$chatId}");
            return;
        }

        if ($text === '📃 Мої слова' || $text === '/mywords') {
            $words = Word::where('user_id', $chatId)->get();

            if ($words->isEmpty()) {
                $this->sendMessage($chatId, "У вас ще немає слів.");
            } else {
                // Надсилаємо кожне слово окремо з кнопками
                $this->sendWordsList($chatId, $words);
            }
            return;
        }

        // Додавання через формат "word - translation"
        if (str_contains($text, '-')) {
            [$wordText, $translation] = array_map('trim', explode('-', $text, 2));
            if ($wordText === '' || $translation === '') {
                $this->sendMessage($chatId, "❗️ Формат невірний. Приклад: <code>apple - яблуко</code>", null, 'HTML');
                return;
            }
            Word::create([
                'user_id' => $chatId,
                'word' => $wordText,
                'translation' => $translation,
            ]);
            $this->sendMessage($chatId, "✅ Додано: {$wordText} — {$translation}");
            return;
        }

        // Невідомий текст
        $this->sendMessage($chatId, "Надішліть слово у форматі: слово - переклад або скористайтесь кнопками.");
    }

    public function promptAddWord(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = "Надішліть слово у форматі: ✅слово - переклад✅ або скористайтесь кнопками нижче.";
        $this->sendMessage($chatId, $text);
    }

    public function sendWordsList($chatId, $words)
    {
        foreach ($words as $word) {
            $this->sendWordWithButtons($chatId, $word);
        }
    }

    private function sendWordWithButtons($chatId, $word)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $text = "<b>{$word->word}</b> — {$word->translation}";
        if ($word->example) {
            $text .= "\n<i>Приклад:</i> {$word->example}";
        }

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '✏️ Редагувати',
                        'callback_data' => "edit_{$word->id}"
                    ],
                    [
                        'text' => '🗑️ Видалити',
                        'callback_data' => "delete_{$word->id}"
                    ],
                    [
                        'text' => '➕ Додати приклад',
                        'callback_data' => "addexample_{$word->id}"
                    ]
                ]
            ]
        ];

        Http::post($url, [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($replyMarkup),
        ]);
    }

    public function deleteWord($chatId, $wordId, $messageId)
    {
        $word = Word::find($wordId);
        if ($word) {
            $word->delete();
            $this->editMessage($chatId, $messageId, "Слово видалено.");
        } else {
            $this->editMessage($chatId, $messageId, "Слово не знайдено.");
        }
    }

    public function promptEditWord($chatId, $wordId)
    {
        $word = Word::find($wordId);
        if ($word) {
            $text = "Відправте новий переклад для слова <b>{$word->word}</b>:";
            $this->sendMessage($chatId, $text, null, 'HTML');
            // Зберігаємо ID слова для редагування
            Cache::put("edit_word_{$chatId}", $wordId, now()->addMinutes(5));
        } else {
            $this->sendMessage($chatId, "Слово не знайдено.");
        }
    }

    public function promptAddExample($chatId, $wordId)
    {
        $word = Word::find($wordId);
        if ($word) {
            $text = "Відправте приклад речення для слова <b>{$word->word}</b>:";
            $this->sendMessage($chatId, $text, null, 'HTML');
            Cache::put("add_example_{$chatId}", $wordId, now()->addMinutes(5));
        } else {
            $this->sendMessage($chatId, "Слово не знайдено.");
        }
    }

    private function editMessage($chatId, $messageId, $text)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $url = "https://api.telegram.org/bot{$token}/editMessageText";

        Http::post($url, [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    private function sendMessage($chatId, $text, $keyboard = null, $parseMode = null)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        if ($keyboard) {
            $payload['reply_markup'] = json_encode($keyboard);
        }
        if ($parseMode) {
            $payload['parse_mode'] = $parseMode;
        }

        Http::post($url, $payload);
    }
}
