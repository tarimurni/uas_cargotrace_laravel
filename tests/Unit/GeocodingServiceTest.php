<?php

namespace Tests\Unit;

use App\Services\GeocodingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeocodingServiceTest extends TestCase
{
    protected GeocodingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->service = new GeocodingService();
    }

    public function test_reverse_geocode_returns_address_when_api_succeeds(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/reverse*' => Http::response([
                'display_name' => 'Pelabuhan Tanjung Priok, Jakarta Utara, DKI Jakarta, Indonesia',
                'address' => [
                    'city' => 'Jakarta Utara',
                    'country' => 'Indonesia',
                ],
            ], 200),
        ]);

        $result = $this->service->reverseGeocode(-6.1038, 106.8824);

        $this->assertTrue($result['success']);
        $this->assertFalse($result['is_fallback']);
        $this->assertStringContainsString('Tanjung Priok', $result['display_name']);
        $this->assertEquals('Jakarta Utara', $result['city']);
        $this->assertEquals('Indonesia', $result['country']);
        $this->assertEquals('Nominatim OpenStreetMap', $result['source']);
    }

    public function test_reverse_geocode_returns_safe_fallback_when_api_fails(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/reverse*' => Http::response('Gateway Timeout', 504),
        ]);

        $result = $this->service->fetchReverseFromApi(-6.1038, 106.8824);

        $this->assertFalse($result['success']);
        $this->assertTrue($result['is_fallback']);
        $this->assertStringContainsString('-6.1038', $result['display_name']);
        $this->assertNull($result['city']);
        $this->assertEquals('Fallback Offline', $result['source']);
    }

    public function test_reverse_geocode_returns_safe_fallback_when_exception_occurs(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/reverse*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Network unreachable');
            },
        ]);

        $result = $this->service->fetchReverseFromApi(-6.1038, 106.8824);

        $this->assertFalse($result['success']);
        $this->assertTrue($result['is_fallback']);
        $this->assertNotEmpty($result['error']);
    }
}
