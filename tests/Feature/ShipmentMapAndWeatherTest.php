<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShipmentMapAndWeatherTest extends TestCase
{
    use DatabaseTransactions;

    protected User $operator;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;
    protected Shipment $shipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::firstOrCreate(
            ['email' => 'operator@cargotrace.com'],
            ['name' => 'Operator Test', 'password' => bcrypt('password'), 'role' => 'operator']
        );

        $this->carrier = Carrier::firstOrCreate(
            ['code' => 'CR_MAP_TEST'],
            ['name' => 'Map Carrier Express', 'service_type' => 'sea', 'is_active' => true]
        );

        $this->origin = Location::firstOrCreate(
            ['code' => 'LOC_MAP_ORG'],
            [
                'name' => 'Pelabuhan Tanjung Perak',
                'type' => 'seaport',
                'city' => 'Surabaya',
                'country' => 'Indonesia',
                'latitude' => -7.1983,
                'longitude' => 112.7325,
            ]
        );

        $this->destination = Location::firstOrCreate(
            ['code' => 'LOC_MAP_DST'],
            [
                'name' => 'Pelabuhan Belawan',
                'type' => 'seaport',
                'city' => 'Medan',
                'country' => 'Indonesia',
                'latitude' => 3.7850,
                'longitude' => 98.6833,
            ]
        );

        $this->shipment = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-MAP-001'],
            [
                'customer_id' => $this->operator->id,
                'carrier_id' => $this->carrier->id,
                'origin_id' => $this->origin->id,
                'destination_id' => $this->destination->id,
                'cargo_type' => 'Consumer Goods',
                'weight' => 1500.00,
                'volume' => 12.00,
                'expected_departure' => now(),
                'expected_arrival' => now()->addDays(4),
                'estimated_arrival' => now()->addDays(4),
                'current_status' => 'IN_TRANSIT',
            ]
        );

        $this->shipment->trackingEvents()->create([
            'status' => 'IN_TRANSIT',
            'latitude' => -5.4000,
            'longitude' => 105.3000,
            'description' => 'Kapal melintasi perairan Lampung menuju Selat Bangka',
            'event_time' => now(),
            'created_by' => $this->operator->id,
        ]);
    }

    public function test_shipment_detail_page_renders_leaflet_map_and_osm_attribution(): void
    {
        Http::fake([
            'https://api.open-meteo.com/v1/forecast*' => Http::response([
                'latitude' => 3.78,
                'longitude' => 98.68,
                'current' => [
                    'temperature_2m' => 31.2,
                    'apparent_temperature' => 35.0,
                    'relative_humidity_2m' => 70,
                    'weather_code' => 1,
                    'wind_speed_10m' => 11.0,
                    'is_day' => 1,
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->operator)->get(route('shipments.show', $this->shipment));

        $response->assertStatus(200);

        // Assert map container and OpenStreetMap attribution are present in view
        $response->assertSee('id="shipmentMap"', false);
        $response->assertSee('OpenStreetMap', false);
        $response->assertSee('Visualisasi Peta Rute', false);
        $response->assertSee('Pelabuhan Tanjung Perak', false);
        $response->assertSee('Pelabuhan Belawan', false);

        // Assert weather widget is present in view
        $response->assertSee('Kondisi Cuaca Pelabuhan Tujuan', false);
        $response->assertSee('Kondisi Cuaca Posisi Terkini / Asal', false);
        $response->assertSee('31.2°C', false);
    }

    public function test_shipment_detail_page_renders_gracefully_when_weather_api_fails(): void
    {
        Http::fake([
            'https://api.open-meteo.com/v1/forecast*' => Http::response('Service Unavailable', 503),
        ]);

        $response = $this->actingAs($this->operator)->get(route('shipments.show', $this->shipment));

        $response->assertStatus(200);
        $response->assertSee('id="shipmentMap"', false);
        $response->assertSee('Offline / Fallback', false);
    }
}
