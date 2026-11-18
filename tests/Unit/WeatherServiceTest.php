<?php

namespace Tests\Unit;

use App\Services\WeatherService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherServiceTest extends TestCase
{
    protected WeatherService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->service = new WeatherService();
    }

    public function test_get_weather_returns_successful_data_when_api_succeeds(): void
    {
        Http::fake([
            'https://api.open-meteo.com/v1/forecast*' => Http::response([
                'latitude' => -6.1,
                'longitude' => 106.8,
                'current' => [
                    'temperature_2m' => 29.8,
                    'apparent_temperature' => 33.2,
                    'relative_humidity_2m' => 76,
                    'weather_code' => 2,
                    'wind_speed_10m' => 14.5,
                    'is_day' => 1,
                ],
            ], 200),
        ]);

        $result = $this->service->getWeather(-6.1, 106.8);

        $this->assertTrue($result['success']);
        $this->assertFalse($result['is_fallback']);
        $this->assertEquals(29.8, $result['temperature']);
        $this->assertEquals(33.2, $result['apparent_temperature']);
        $this->assertEquals(76, $result['humidity']);
        $this->assertEquals(14.5, $result['wind_speed']);
        $this->assertEquals('Berawan Sebagian', $result['condition']);
        $this->assertEquals('⛅', $result['icon']);
        $this->assertEquals('Open-Meteo API', $result['source']);
    }

    public function test_get_weather_returns_safe_fallback_when_api_fails(): void
    {
        Http::fake([
            'https://api.open-meteo.com/v1/forecast*' => Http::response('Server Error', 500),
        ]);

        $result = $this->service->fetchFromApi(-6.1, 106.8);

        $this->assertFalse($result['success']);
        $this->assertTrue($result['is_fallback']);
        $this->assertNull($result['temperature']);
        $this->assertEquals('Fallback Offline', $result['source']);
        $this->assertNotEmpty($result['error']);
    }

    public function test_get_weather_returns_safe_fallback_when_timeout_occurs(): void
    {
        Http::fake([
            'https://api.open-meteo.com/v1/forecast*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
            },
        ]);

        $result = $this->service->fetchFromApi(-6.1, 106.8);

        $this->assertFalse($result['success']);
        $this->assertTrue($result['is_fallback']);
        $this->assertNull($result['temperature']);
        $this->assertEquals('Data Cuaca Tidak Tersedia', $result['condition']);
    }

    public function test_interpret_weather_code(): void
    {
        $clear = $this->service->interpretWeatherCode(0);
        $this->assertEquals('Cerah (Clear Sky)', $clear['label']);
        $this->assertEquals('☀️', $clear['icon']);

        $rain = $this->service->interpretWeatherCode(61);
        $this->assertEquals('Hujan (Rain)', $rain['label']);
        $this->assertEquals('🌧️', $rain['icon']);

        $storm = $this->service->interpretWeatherCode(95);
        $this->assertEquals('Badai Petir (Thunderstorm)', $storm['label']);
        $this->assertEquals('⛈️', $storm['icon']);
    }
}
