<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WordController;
use App\Http\Controllers\TelegramBotController;


Route::post('/words', [WordController::class, 'store']);
Route::post('/telegram/webhook', [TelegramBotController::class, 'webhook']);
