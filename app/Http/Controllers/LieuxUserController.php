<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LieuxUserController extends Controller
{
    public function search_lieu(Request $request)
{
    try {
        $lieu = $request->query('lieu');

        if (!$lieu) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Paramètre lieu manquant',
            ], 400);
        }

        // 🔹 1. IP utilisateur
        $ip = $request->ip();

        // En local → IP d’Abidjan pour test
        if ($ip === '127.0.0.1' || $ip === '::1') {
            $ip = '196.201.0.1';
        }

        // 🔹 2. Géolocalisation par IP (avec Http au lieu de file_get_contents)
        $geoResponse = Http::get("http://ip-api.com/json/{$ip}");

        if (!$geoResponse->successful()) {
            throw new \Exception("Erreur service de géolocalisation IP");
        }

        $geo = $geoResponse->json();

        if (!isset($geo['status']) || $geo['status'] !== 'success') {
            throw new \Exception("Impossible de déterminer la position de l'utilisateur");
        }

        $userLat = $geo['lat'];
        $userLng = $geo['lon'];

        // 🔹 3. Recherche Google Places (Text Search)
        $apiKey = config('services.google_maps.key');

        if (!$apiKey) {
            throw new \Exception("Clé Google Maps manquante");
        }

        $query = urlencode($lieu . ' Côte d\'Ivoire');

        $url = "https://maps.googleapis.com/maps/api/place/textsearch/json";

        $response = Http::get($url, [
            'query' => $query,
            'location' => "{$userLat},{$userLng}",
            'radius' => 50000,
            'key' => $apiKey,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Erreur Google Maps API");
        }

        $results = $response->json();

        // 🔴 Vérifier le statut Google
        if (!isset($results['status']) || $results['status'] !== 'OK') {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => $results['status'] ?? 'Aucun résultat',
            ], 200);
        }

        // 🔹 4. Format + calcul distance
        $places = collect($results['results'])->map(function ($item) use ($userLat, $userLng) {

            $lat = $item['geometry']['location']['lat'];
            $lng = $item['geometry']['location']['lng'];

            $distance = $this->calculateDistance($userLat, $userLng, $lat, $lng);

            return [
                'id' => $item['place_id'],
                'title' => $item['name'],
                'subtitle' => $item['formatted_address'] ?? $item['name'],
                'distance' => round($distance, 2), // km
                'lat' => $lat,
                'lng' => $lng,
            ];
        })
        ->sortBy('distance')
        ->values();

        return response()->json([
            'success' => true,
            'data' => $places,
            'message' => 'Résultats trouvés',
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la recherche',
            'erreur' => $e->getMessage(),
        ], 500);
    }
}


    private function calculateDistance($lat1, $lon1, $lat2, $lon2){
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

}
