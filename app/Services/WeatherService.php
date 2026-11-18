<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WeatherService
{
    /**
     * Get live or cached weather conditions for specified coordinates.
     *
     * @return array<string, mixed>
     */
    public function getWeather(float $latitude, float $longitude): array
    {
        $cacheKey = sprintf('weather_%.3f_%.3f', $latitude, $longitude);

        return Cache::remember($cacheKey, 900, function () use ($latitude, $longitude) {
            return $this->fetchFromApi($latitude, $longitude);
        });
    }

    /**
     * Fetch weather data directly from Open-Meteo external API with timeout and error fallback.
     *
     * @return array<string, mixed>
     */
    public function fetchFromApi(float $latitude, float $longitude): array
    {
        $url = config('services.open_meteo.url', 'https://api.open-meteo.com/v1/forecast');
        $timeout = (int) config('services.open_meteo.timeout', 3);
        $apiKey = config('services.open_meteo.api_key');

        $params = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,is_day,precipitation,weather_code,wind_speed_10m',
            'timezone' => 'auto',
        ];

        if (! empty($apiKey)) {
            $params['apikey'] = $apiKey;
        }

        try {
            $response = Http::timeout($timeout)
                ->retry(1, 100)
                ->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                $current = $data['current'] ?? [];

                $weatherCode = $current['weather_code'] ?? null;
                $interpretation = $this->interpretWeatherCode($weatherCode);

                return [
                    'success' => true,
                    'is_fallback' => false,
                    'latitude' => (float) ($data['latitude'] ?? $latitude),
                    'longitude' => (float) ($data['longitude'] ?? $longitude),
                    'temperature' => isset($current['temperature_2m']) ? (float) $current['temperature_2m'] : null,
                    'apparent_temperature' => isset($current['apparent_temperature']) ? (float) $current['apparent_temperature'] : null,
                    'condition' => $interpretation['label'],
                    'condition_code' => $weatherCode,
                    'icon' => $interpretation['icon'],
                    'humidity' => isset($current['relative_humidity_2m']) ? (int) $current['relative_humidity_2m'] : null,
                    'wind_speed' => isset($current['wind_speed_10m']) ? (float) $current['wind_speed_10m'] : null,
                    'is_day' => (bool) ($current['is_day'] ?? true),
                    'source' => 'Open-Meteo API',
                    'fetched_at' => now()->toDateTimeString(),
                ];
            }

            Log::warning('Weather API returned unsuccessful response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::notice('Weather API request failed (falling back gracefully)', [
                'error' => $e->getMessage(),
                'coordinates' => "{$latitude},{$longitude}",
            ]);
        }

        return $this->getFallbackWeather($latitude, $longitude);
    }

    /**
     * Safe fallback response when external Weather service is unreachable or errors.
     *
     * @return array<string, mixed>
     */
    public function getFallbackWeather(float $latitude, float $longitude): array
    {
        return [
            'success' => false,
            'is_fallback' => true,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'error' => 'Layanan cuaca eksternal sedang offline atau mengalami timeout.',
            'temperature' => null,
            'apparent_temperature' => null,
            'condition' => 'Data Cuaca Tidak Tersedia',
            'condition_code' => null,
            'icon' => '☁️',
            'humidity' => null,
            'wind_speed' => null,
            'is_day' => true,
            'source' => 'Fallback Offline',
            'fetched_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Interpret standard WMO Weather interpretation codes.
     *
     * @return array{label: string, icon: string}
     */
    public function interpretWeatherCode(?int $code): array
    {
        return match ($code) {
            0 => ['label' => 'Cerah (Clear Sky)', 'icon' => '☀️'],
            1 => ['label' => 'Sebagian Besar Cerah', 'icon' => '🌤️'],
            2 => ['label' => 'Berawan Sebagian', 'icon' => '⛅'],
            3 => ['label' => 'Mendung (Overcast)', 'icon' => '☁️'],
            45, 48 => ['label' => 'Berkabut (Fog)', 'icon' => '🌫️'],
            51, 53, 55 => ['label' => 'Gerimis Ringan', 'icon' => '🌦️'],
            61, 63, 65 => ['label' => 'Hujan (Rain)', 'icon' => '🌧️'],
            71, 73, 75 => ['label' => 'Hujan Salju (Snow)', 'icon' => '🌨️'],
            80, 81, 82 => ['label' => 'Hujan Deras / Badai Hujan', 'icon' => '🌧️'],
            95, 96, 99 => ['label' => 'Badai Petir (Thunderstorm)', 'icon' => '⛈️'],
            default => ['label' => 'Kondisi Normal', 'icon' => '🌤️'],
        };
    }
}
