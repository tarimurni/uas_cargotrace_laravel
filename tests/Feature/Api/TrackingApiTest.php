<?php

namespace Tests\Feature\Api;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected User $customer1;
    protected User $customer2;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;
    protected Shipment $shipment;
    protected Shipment $otherShipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::firstOrCreate(
            ['email' => 'operator@cargotrace.com'],
            ['name' => 'Operator Test', 'password' => bcrypt('password'), 'role' => 'operator']
        );

        $this->customer1 = User::firstOrCreate(
            ['email' => 'customer@cargotrace.com'],
            ['name' => 'Customer 1', 'password' => bcrypt('password'), 'role' => 'customer']
        );

        $this->customer2 = User::firstOrCreate(
            ['email' => 'customer2@cargotrace.com'],
            ['name' => 'Customer 2', 'password' => bcrypt('password'), 'role' => 'customer']
        );

        $this->carrier = Carrier::firstOrCreate(
            ['code' => 'CR_TRACK_TEST'],
            ['name' => 'Carrier Track Test', 'service_type' => 'sea', 'is_active' => true]
        );

        $this->origin = Location::firstOrCreate(
            ['code' => 'LOC_TRK_ORG'],
            ['name' => 'Port of Tanjung Priok', 'type' => 'seaport', 'city' => 'Jakarta', 'country' => 'Indonesia', 'latitude' => -6.10, 'longitude' => 106.88]
        );

        $this->destination = Location::firstOrCreate(
            ['code' => 'LOC_TRK_DST'],
            ['name' => 'Port of Singapore', 'type' => 'seaport', 'city' => 'Singapore', 'country' => 'Singapore', 'latitude' => 1.29, 'longitude' => 103.85]
        );

        $this->shipment = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-TRK-001'],
            [
                'customer_id' => $this->customer1->id,
                'carrier_id' => $this->carrier->id,
                'origin_id' => $this->origin->id,
                'destination_id' => $this->destination->id,
                'cargo_type' => 'General Merchandise',
                'weight' => 750.00,
                'volume' => 5.00,
                'expected_departure' => now(),
                'expected_arrival' => now()->addDays(3),
                'estimated_arrival' => now()->addDays(3),
                'current_status' => 'CREATED',
            ]
        );

        $this->otherShipment = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-TRK-002'],
            [
                'customer_id' => $this->customer2->id,
                'carrier_id' => $this->carrier->id,
                'origin_id' => $this->origin->id,
                'destination_id' => $this->destination->id,
                'cargo_type' => 'Chemicals',
                'weight' => 300.00,
                'volume' => 3.00,
                'expected_departure' => now(),
                'expected_arrival' => now()->addDays(5),
                'estimated_arrival' => now()->addDays(5),
                'current_status' => 'CREATED',
            ]
        );
    }

    public function test_operator_can_record_tracking_event(): void
    {
        $token = $this->operator->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.shipments.tracking.store', $this->shipment->id), [
                'status' => 'IN_TRANSIT',
                'location_id' => $this->origin->id,
                'latitude' => -6.1038,
                'longitude' => 106.8824,
                'description' => 'Vessel departed Tanjung Priok container terminal',
                'event_time' => now()->toDateTimeString(),
                'update_shipment_status' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'IN_TRANSIT')
            ->assertJsonPath('data.description', 'Vessel departed Tanjung Priok container terminal');

        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $this->shipment->id,
            'status' => 'IN_TRANSIT',
        ]);

        $this->assertEquals('IN_TRANSIT', $this->shipment->fresh()->current_status);
    }

    public function test_customer_cannot_record_tracking_event_returns_403(): void
    {
        $token = $this->customer1->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.shipments.tracking.store', $this->shipment->id), [
                'status' => 'DELIVERED',
            ]);

        $response->assertStatus(403);
    }

    public function test_customer_can_view_tracking_timeline_of_own_shipment(): void
    {
        $token = $this->customer1->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.shipments.tracking.index', $this->shipment->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_customer_cannot_view_tracking_timeline_of_other_shipment_returns_403(): void
    {
        $token = $this->customer1->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.shipments.tracking.index', $this->otherShipment->id));

        $response->assertStatus(403);
    }

    public function test_recording_tracking_event_validates_input_returns_422(): void
    {
        $token = $this->operator->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.shipments.tracking.store', $this->shipment->id), []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['status'],
            ]);
    }
}
