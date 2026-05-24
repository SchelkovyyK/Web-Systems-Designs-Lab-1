<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'katowice' => [50.2649, 19.0238],
            'warszawa' => [52.2297, 21.0122],
            'lodz' => [51.7592, 19.4550],
            'krakow' => [50.0647, 19.9450],
        ];

        foreach ($cities as $cityName => [$lat, $lng]) {

            $this->command->info("Loading: $cityName");

            $osmQuery = "
            [out:json];
            (
            node[\"amenity\"=\"restaurant\"](around:5000,$lat,$lng);
            way[\"amenity\"=\"restaurant\"](around:5000,$lat,$lng);
            relation[\"amenity\"=\"restaurant\"](around:5000,$lat,$lng);
            );
            out center tags;
            ";

            $response = Http::timeout(60)
                ->withHeaders([
                    'User-Agent' => 'Laravel Restaurant App'
                ])
                ->asForm()
                ->post('https://overpass-api.de/api/interpreter', [
                    'data' => $osmQuery
                ]);

            $elements = $response->json()['elements'] ?? [];

            foreach (array_slice($elements, 0, 50) as $element) {

                $latR = $element['lat'] ?? $element['center']['lat'] ?? null;
                $lngR = $element['lon'] ?? $element['center']['lon'] ?? null;

                if (!$latR || !$lngR) continue;

                Restaurant::updateOrCreate(
                    [
                        'name' => $element['tags']['name'] ?? 'Restaurant',
                        'latitude' => $latR,
                        'longitude' => $lngR,
                    ],
                    [
                        'city' => $cityName,
                        'category' => $element['tags']['cuisine'] ?? 'general',
                        'rating' => rand(35, 50) / 10,
                        'album_number' => '78716',
                    ]
                );
            }
        }

        $this->command->info("DONE: restaurants loaded per city");
    }
}