<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RestaurantController extends Controller
{
    public function index(): JsonResponse
    {
        $restaurants = Restaurant::query()->latest()->get();
        return response()->json(['data' => $restaurants], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max' => 255],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'category' => ['nullable', 'string', 'max' => 100],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'album_number' => ['required', 'string', 'max' => 50],
        ]);

        $restaurant = Restaurant::create($validated);
        Cache::flush(); 

        return response()->json(['data' => $restaurant], 201);
    }

    public function show(Restaurant $restaurant): JsonResponse
    {
        return response()->json(['data' => $restaurant], 200);
    }

    public function update(Request $request, Restaurant $restaurant): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max' => 255],
            'latitude' => ['sometimes', 'required', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'required', 'numeric', 'between:-180,180'],
            'category' => ['nullable', 'string', 'max' => 100],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'album_number' => ['sometimes', 'required', 'string', 'max' => 50],
        ]);

        $restaurant->update($validated);
        Cache::flush();

        return response()->json(['data' => $restaurant], 200);
    }

    public function destroy(Restaurant $restaurant): JsonResponse
    {
        $restaurant->delete();
        Cache::flush();
        return response()->json(null, 204);
    }

    public function nearby(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'city' => ['required', 'string'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'radius' => ['required', 'numeric'],
        ]);

        $city = strtolower($validated['city']);
        $lat = (float) $validated['lat'];
        $lng = (float) $validated['lng'];
        $radius = (float) $validated['radius'];
        
        // Отримуємо номер поточної сторінки (за замовчуванням 1)
        $page = (int) $request->query('page', 1);

        // Додаємо сторінку в ключ кешу Redis!
        $cacheKey = "nearby:$city:$lat:$lng:$radius:page:$page";

        $restaurantsPaginated = Cache::remember($cacheKey, 3600, function () use ($city, $lat, $lng, $radius) {
            return Restaurant::query()
                ->where('album_number', '78716')
                ->where('city', $city)
                ->selectRaw("
                    id, name, city, latitude, longitude, category, rating, album_number, created_at, updated_at,
                    (
                        6371 * acos(
                            cos(radians(?)) *
                            cos(radians(latitude)) *
                            cos(radians(longitude) - radians(?)) +
                            sin(radians(?)) *
                            sin(radians(latitude))
                        )
                    ) AS distance_km
                ", [$lat, $lng, $lat])
                ->whereRaw("
                    (
                        6371 * acos(
                            cos(radians(?)) *
                            cos(radians(latitude)) *
                            cos(radians(longitude) - radians(?)) +
                            sin(radians(?)) *
                            sin(radians(latitude))
                        )
                    ) <= ?
                ", [$lat, $lng, $lat, $radius])
                ->orderBy('distance_km')
                ->paginate(50); // Замінено ліміт на пагінацію (по 50 на сторінку)
        });

        return response()->json($restaurantsPaginated, 200);
    }

    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;

        $lat1Rad = deg2rad($lat1);
        $lng1Rad = deg2rad($lng1);
        $lat2Rad = deg2rad($lat2);
        $lng2Rad = deg2rad($lng2);

        $latDiff = $lat2Rad - $lat1Rad;
        $lngDiff = $lng2Rad - $lng1Rad;

        $a = sin($latDiff / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($lngDiff / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 3);
    }
}
