<?php

use App\Http\Controllers\TelegramBotController;
use App\Http\Controllers\WordController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

// Route::post('/words', [WordController::class, 'store']);
// Route::post('/telegram/webhook', [TelegramBotController::class, 'webhook']);
