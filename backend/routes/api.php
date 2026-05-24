<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ShortLinkController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\PhotoController; 
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\FeedController;


Route::get('/health', [HealthController::class, 'index']);

Route::prefix('78716/v1')->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('short-links', ShortLinkController::class)->only(['index', 'store', 'show']);
    Route::get('restaurants/nearby', [RestaurantController::class, 'nearby']);
    Route::apiResource('restaurants', RestaurantController::class);
    Route::apiResource('photos', PhotoController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::post('users/{id}/follow', [FollowController::class, 'follow']);
    Route::delete('users/{id}/follow', [FollowController::class, 'unfollow']);
    Route::get('feed', [FeedController::class, 'index']);

    Route::get('users/hints', function () {
    $albums = \App\Models\Photo::query()
        ->whereNotNull('album_number')
        ->where('album_number', '!=', '')
        ->distinct()
        ->pluck('album_number');
        
    return response()->json(['success' => true, 'data' => $albums]);
});
});

