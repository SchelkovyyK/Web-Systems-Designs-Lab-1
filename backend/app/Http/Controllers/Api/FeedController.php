<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FeedController extends Controller
{
    public function index(Request $request): JsonResponse
{
    $userId = 78716; 
    $limit = (int) $request->query('limit', 10);
    $cursor = $request->query('cursor', '');

    $cacheKey = 'feed:' . $userId . ':' . $limit . ':' . $cursor;

    // Кешуємо тільки масив згенерованих даних
    $cachedData = Cache::remember($cacheKey, 60, function () use ($userId, $limit) {
        $followedIds = Follow::query()
            ->where('follower_id', $userId)
            ->pluck('followed_id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        $followedIds[] = (string) $userId;

        // Отримуємо пагінатор
        $feedPaginator = Photo::query()
            ->whereIn('album_number', $followedIds)
            ->orderByDesc('created_at')
            ->cursorPaginate($limit);

        // Перетворюємо його на чистий масив, який Redis 100% зможе зберегти
        return [
            'items' => $feedPaginator->items(),
            'next_cursor' => $feedPaginator->nextCursor()?->encode(),
        ];
    });

    return response()->json([
        'count' => count($cachedData['items']),
        'data' => [
            'data' => $cachedData['items'],
            'next_cursor' => $cachedData['next_cursor'],
        ]
    ], 200);
}

}
