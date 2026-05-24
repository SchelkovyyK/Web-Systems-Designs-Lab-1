<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PhotoController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->input('page', 1);

        $paginatedPhotos = Cache::remember("photos.index.page.{$page}", 60, function () {
            $paginated = Photo::latest()->paginate(4);


            $paginated->getCollection()->transform(function ($photo) {
                return [
                    'id'                => $photo->id,
                    'title'             => $photo->title,
                    'caption'           => $photo->caption,
                    'image_url'         => asset('storage/' . $photo->image_path), 
                    'processing_status' => $photo->processing_status,
                    'created_at'        => $photo->created_at,
                    'updated_at'        => $photo->updated_at,
                ];
            });

            return $paginated;
        });

        return response()->json(['success' => true, 'data' => $paginatedPhotos], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'        => 'required|string',
            'caption'      => 'nullable|string',
            'image'        => 'required|image|max:10240', 
            'album_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $file = $request->file('image');
        $path = $file->store('photos', 'public');

        $photo = Photo::create([
            'title'             => $request->input('title'),
            'caption'           => $request->input('caption'),
            'image_path'        => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType(),
            'file_size'         => $file->getSize(),
            'processing_status' => 'uploaded',
            'album_number'      => $request->input('album_number'),
        ]);

        $photo->processing_status = 'processed';
        $photo->save();

        $this->clearPaginationCache();

        return response()->json([
            'success' => true,
            'data' => [
                'id'                => $photo->id,
                'title'             => $photo->title,
                'caption'           => $photo->caption,
                'image_url'         => asset('storage/' . $photo->image_path),
                'processing_status' => $photo->processing_status
            ]
        ], 201);
    }

    public function show($id)
    {
        $photo = Cache::remember("photos.show.{$id}", 60, function () use ($id) {
            return Photo::find($id);
        });

        if (!$photo) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id'                => $photo->id,
                'title'             => $photo->title,
                'caption'           => $photo->caption,
                'image_url'         => asset('storage/' . $photo->image_path),
                'processing_status' => $photo->processing_status
            ]
        ], 200);
    }

    public function destroy($id)
    {
        $photo = Photo::find($id);

        if (!$photo) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        if (Storage::disk('public')->exists($photo->image_path)) {
            Storage::disk('public')->delete($photo->image_path);
        }

        $photo->delete();

        $this->clearPaginationCache();
        Cache::forget("photos.show.{$id}");

        return response()->json(['success' => true, 'message' => 'Deleted'], 200);
    }

    private function clearPaginationCache()
    {
        Cache::flush();
    }
}
