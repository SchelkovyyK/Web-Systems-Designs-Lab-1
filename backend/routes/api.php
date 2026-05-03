<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EchoController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\TaskController;

Route::get('/echo', [EchoController::class, 'echo']);
Route::post('/echo', [EchoController::class, 'echo']);
Route::get('/health', [HealthController::class, 'index']);

// Додаємо групу з версіюванням та вашим ID
Route::prefix('123456/v1')->group(function () {
    Route::apiResource('tasks', TaskController::class);
});
