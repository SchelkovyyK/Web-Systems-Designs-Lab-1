<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShortLink;
use App\Support\Base62;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShortLinkController extends Controller
{
    private const ALBUM_NUMBER = '78716';
    private const CACHE_KEY = 'short_links_78716';

    public function index(Request $request): JsonResponse
    {
        $links = Cache::remember(self::CACHE_KEY, 600, function () {
            return ShortLink::where('album_number', self::ALBUM_NUMBER)->get();
        });

        return response()->json([
            'status' => 'success',
            'data' => $links
        ]);
    }
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'original_url' => 'required|url|max:2048',
            'album_number' => 'required|string',
        ]);

        $shortLink = ShortLink::create([
            'original_url' => $validated['original_url'],
            'album_number' => $validated['album_number'],
            'click_count' => 0,
        ]);

        $code = Base62::encode($shortLink->id);

        $shortLink->update([
            'short_code' => $code,
        ]);

        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'data' => $shortLink
        ], 201);
    }
    public function show(ShortLink $shortLink): JsonResponse
{
    return response()->json([
        'status' => 'success',
        'data' => $shortLink
    ]);
}
}
