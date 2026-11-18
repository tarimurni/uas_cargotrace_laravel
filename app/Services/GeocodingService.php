<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeocodingService
{
    /**
     * Reverse geocode latitude and longitude to human-readable address.
     *
     * @return array<string, mixed>
     */
    public function reverseGeocode(float $latitude, float $longitude): array
    {
        $cacheKey = sprintf('geocode_rev_%.4f_%.4f', $latitude, $longitude);

        return Cache::remember($cacheKey, 86400, function () use ($latitude, $longitude) {
            return $this->fetchReverseFromApi($latitude, $longitude);
        });
    }

    /**
     * Fetch reverse geocoding from Nominatim OpenStreetMap API.
     *
     * @return array<string, mixed>
     */
    public function fetchReverseFromApi(float $latitude, float $longitude): array
    {
        $baseUrl = rtrim(config('services.nominatim.url', 'https://nominatim.openstreetmap.org'), '/');
        $timeout = (int) config('services.nominatim.timeout', 3);
        $userAgent = config('services.nominatim.user_agent', 'CargoTrace-Logistics/1.0 (contact@cargotrace.com)');
        $apiKey = config('services.nominatim.api_key');

        $params = [
            'lat' => $latitude,
            'lon' => $longitude,
            'format' => 'json',
            'zoom' => 14,
            'addressdetails' => 1,
        ];

        if (! empty($apiKey)) {
            $params['key'] = $apiKey;
        }

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'User-Agent' => $userAgent,
                    'Accept' => 'application/json',
                ])
                ->retry(1, 100)
                ->get("{$baseUrl}/reverse", $params);

            if ($response->successful()) {
                $data = $response->json();
                $address = $data['address'] ?? [];

                $city = $address['city'] ?? $address['town'] ?? $address['county'] ?? $address['state'] ?? null;
                $country = $address['country'] ?? null;

                return [
                    'success' => true,
                    'is_fallback' => false,
                    'display_name' => $data['display_name'] ?? "{$latitude}, {$longitude}",
                    'city' => $city,
                    'country' => $country,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'source' => 'Nominatim OpenStreetMap',
                    'fetched_at' => now()->toDateTimeString(),
                ];
            }

            Log::warning('Nominatim API returned unsuccessful response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::notice('Nominatim reverse geocode request failed (falling back)', [
                'error' => $e->getMessage(),
                'coordinates' => "{$latitude},{$longitude}",
            ]);
        }

        return $this->getFallbackGeocode($latitude, $longitude);
    }

    /**
     * Safe fallback response for geocoding.
     *
     * @return array<string, mixed>
     */
    public function getFallbackGeocode(float $latitude, float $longitude): array
    {
        return [
            'success' => false,
            'is_fallback' => true,
            'display_name' => sprintf('Koordinat %.5f, %.5f', $latitude, $longitude),
            'city' => null,
            'country' => null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'error' => 'Layanan geocoding eksternal tidak dapat dihubungi atau timeout.',
            'source' => 'Fallback Offline',
            'fetched_at' => now()->toDateTimeString(),
        ];
    }
}
