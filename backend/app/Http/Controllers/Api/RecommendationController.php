<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\WatchHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class RecommendationController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = 1; 
        $cacheKey = 'recommendations:' . $userId;

        $recommendations = Cache::remember($cacheKey, 60, function () use ($userId) {
            $favoriteGenres = WatchHistory::query()
                ->join('videos', 'videos.id', '=', 'watch_histories.video_id')
                ->where('watch_histories.user_id', $userId)
                ->select('videos.genre')
                ->groupBy('videos.genre')
                ->pluck('videos.genre');

            return Video::query()
                ->whereIn('genre', $favoriteGenres)
                ->orderByDesc('rating')
                ->limit(10)
                ->get();
        });

        return response()->json([
            'data' => $recommendations,
        ], 200);
    }
}
