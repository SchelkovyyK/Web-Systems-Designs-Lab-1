<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\HealthController;

Route::get('/health', [HealthController::class, 'index']);

Route::prefix('78716/v1')->group(function () {
    Route::apiResource('tasks', TaskController::class);
});