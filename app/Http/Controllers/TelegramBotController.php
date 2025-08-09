<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Telegram\UpdateDispatcher;
use App\Models\UserContact;


class TelegramBotController extends Controller
{
    protected UpdateDispatcher $dispatcher;

    public function __construct(UpdateDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function webhook(Request $request)
    {
        $update = $request->all();

        // Відправляємо в диспетчер, він сам розбере тип оновлення
        $this->dispatcher->dispatch($update);

        return response('OK', 200);
    }
}
