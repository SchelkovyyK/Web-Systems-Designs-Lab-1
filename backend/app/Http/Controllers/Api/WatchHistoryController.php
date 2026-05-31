<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WatchHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class WatchHistoryController extends Controller
{
    public function continueWatching(): JsonResponse
    {
        $userId = 1; 
        $cacheKey = "watch_history:user:{$userId}";

        $history = Cache::remember($cacheKey, 60, function () use ($userId) {
            return WatchHistory::query()
                ->with('video')
                ->where('user_id', $userId)
                ->where('completed', false) 
                ->orderByDesc('watched_at')
                ->limit(10)
                ->get();
        });

        return response()->json([
            'data' => $history,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'video_id' => 'required|exists:videos,id',
            'progress_seconds' => 'required|integer|min:0',
            'completed' => 'required|boolean',
        ]);

        $userId = 1;

        $history = WatchHistory::updateOrCreate(
            ['user_id' => $userId, 'video_id' => $validated['video_id']],
            [
                'progress_seconds' => $validated['progress_seconds'],
                'completed' => $validated['completed'],
                'watched_at' => now(),
            ]
        );

        Cache::forget("watch_history:user:{$userId}");
        Cache::forget("recommendations:{$userId}");

        return response()->json([
            'message' => 'Playback progress synchronized successfully',
            'data' => $history
        ], 200);
    }
}
