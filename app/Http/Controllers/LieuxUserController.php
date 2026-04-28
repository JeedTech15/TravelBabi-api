<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class LieuxUserController extends Controller
{
    private string $baseUrl = 'https://places.googleapis.com';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google.places_key');
    }

    public function search_lieu(Request $request)
    {
        try {

            $query = $request->query('query', '');

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le champ query est requis'
                ], 400);
            }

            // 📍 Position utilisateur (fallback Abidjan)
            $lat = (float) $request->query('lat', 5.359951);
            $lng = (float) $request->query('lng', -4.008256);

            $response = Http::timeout(5)
                ->withHeaders([
                    'X-Goog-Api-Key' => $this->apiKey,
                    'X-Goog-FieldMask' => 'suggestions.placePrediction.placeId,suggestions.placePrediction.structuredFormat.mainText.text,suggestions.placePrediction.structuredFormat.secondaryText.text,suggestions.placePrediction.distanceMeters',
                ])
                ->post($this->baseUrl . '/v1/places:autocomplete', [
                    'input' => $query,
                    'includedRegionCodes' => ['ci'],
                    'origin' => [
                        'latitude' => $lat,
                        'longitude' => $lng,
                    ],
                ]);

            // ❌ Si erreur Google API
            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur API Google',
                    // 'status' => $response->status(),
                    // 'details' => $response->body()
                ], 500);
            }

            $suggestions = $response->json('suggestions', []);

            $results = [];

            foreach ($suggestions as $s) {

                if (!isset($s['placePrediction'])) continue;

                $place = $s['placePrediction'];

                $results[] = [
                    'id' => $place['placeId'] ?? null,
                    'title' => $place['structuredFormat']['mainText']['text'] ?? null,
                    'subtitle' => $place['structuredFormat']['secondaryText']['text'] ?? null,
                    'distance' => isset($place['distanceMeters'])
                        ? round($place['distanceMeters'] / 1000, 1)
                        : null,
                ];
            }

            return response()->json([
                "success" => true,
                "data" => $results,
                "message" => "Lieux affichés ave succès"
            ]);

        } catch (RequestException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Erreur requête HTTP',
                'erreur' => $e->getMessage()
            ], 500);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }


    public function positionUser(Request $request){
        try {

            $lat = $request->query('lat');
            $lng = $request->query('lng');

            if (!$lat || !$lng) {
                return response()->json([
                    'success' => false,
                    'message' => 'lat et lng sont requis'
                ], 400);
            }

            // 🔁 Reverse Geocoding
            $response = Http::timeout(5)->get(
                'https://maps.googleapis.com/maps/api/geocode/json',
                [
                    'latlng' => $lat . ',' . $lng,
                    'key' => $this->apiKey,
                    'language' => 'fr'
                ]
            );

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur API Google'
                ], 500);
            }

            $results = $response->json('results', []);

            $ville = null;
            $commune = null;

            foreach ($results as $result) {

                foreach ($result['address_components'] as $component) {

                    if (in_array('locality', $component['types'])) {
                        $ville = $component['long_name'];
                    }

                    if (in_array('sublocality_level_1', $component['types'])) {
                        $commune = $component['long_name'];
                    }

                    // fallback si sublocality pas trouvé
                    if (in_array('administrative_area_level_2', $component['types']) && !$commune) {
                        $commune = $component['long_name'];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Localisation récupérée',
                'data' => [
                    'ville' => $ville,
                    'commune' => $commune
                ]
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }


    public function positionUserFormatted(Request $request){
        try {

            $lat = $request->query('lat');
            $lng = $request->query('lng');

            if (!$lat || !$lng) {
                return response()->json([
                    'success' => false,
                    'message' => 'lat et lng sont requis'
                ], 400);
            }

            // 🔁 Reverse Geocoding
            $response = Http::timeout(5)->get(
                'https://maps.googleapis.com/maps/api/geocode/json',
                [
                    'latlng' => $lat . ',' . $lng,
                    'key' => $this->apiKey,
                    'language' => 'fr'
                ]
            );

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur API Google'
                ], 500);
            }

            $results = $response->json('results', []);

            if (empty($results)) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Aucun lieu trouvé'
                ]);
            }

            $first = $results[0];

            $title = null;
            $subtitle = null;

            foreach ($first['address_components'] as $component) {

                if (in_array('sublocality_level_1', $component['types'])) {
                    $title = $component['long_name'];
                }

                if (in_array('locality', $component['types'])) {
                    $subtitle = $component['long_name'];
                }

                if (in_array('administrative_area_level_2', $component['types']) && !$title) {
                    $title = $component['long_name'];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $first['place_id'] ?? null,
                    'title' => $title ?? $first['formatted_address'],
                    'subtitle' => $subtitle,
                    'distance' => 0
                ],
                'message' => 'Lieu trouvé'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }
}