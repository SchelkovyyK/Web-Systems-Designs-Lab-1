<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class VideoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $genre = $request->genre ?? 'all';
        $page = $request->page ?? 1;
        
        $cacheKey = "videos_catalog:genre:{$genre}:page:{$page}";

        $videos = Cache::remember($cacheKey, 60, function () use ($request) {
            return Video::query()
                ->when($request->genre, function ($query, $genre) {
                    return $query->where('genre', $genre);
                })
                ->orderByDesc('rating')
                ->paginate(10); 
        });

        return response()->json($videos, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'genre' => 'nullable|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'thumbnail_url' => 'nullable|string',
            'video_url' => 'required|string',
            'album_number' => 'required|string', 
        ]);

        $video = Video::create($validated);

        Cache::flush(); 

        return response()->json([
            'message' => 'Video created successfully',
            'data' => $video
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $video = Cache::remember("video_show:{$id}", 60, function () use ($id) {
            return Video::findOrFail($id);
        });
        
        return response()->json(['data' => $video], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $video = Video::findOrFail($id);
        $video->delete();

        Cache::flush(); 

        return response()->json([
            'message' => 'Video deleted successfully'
        ], 200);
    }
}
