<?php

namespace Tests\Feature\Api;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MasterAndExceptionApiTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $operator;
    protected User $customer;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;
    protected Shipment $shipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@cargotrace.com'],
            ['name' => 'Admin Test', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $this->operator = User::firstOrCreate(
            ['email' => 'operator@cargotrace.com'],
            ['name' => 'Operator Test', 'password' => bcrypt('password'), 'role' => 'operator']
        );

        $this->customer = User::firstOrCreate(
            ['email' => 'customer@cargotrace.com'],
            ['name' => 'Customer 1', 'password' => bcrypt('password'), 'role' => 'customer']
        );

        $this->carrier = Carrier::firstOrCreate(
            ['code' => 'CR_MST_TEST'],
            ['name' => 'Master Carrier Test', 'service_type' => 'sea', 'is_active' => true]
        );

        $this->origin = Location::firstOrCreate(
            ['code' => 'LOC_MST_ORG'],
            ['name' => 'Master Origin Test', 'type' => 'seaport', 'city' => 'Surabaya', 'country' => 'Indonesia', 'latitude' => -7.2, 'longitude' => 112.7]
        );

        $this->destination = Location::firstOrCreate(
            ['code' => 'LOC_MST_DST'],
            ['name' => 'Master Destination Test', 'type' => 'seaport', 'city' => 'Manila', 'country' => 'Philippines', 'latitude' => 14.5, 'longitude' => 120.9]
        );

        $this->shipment = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-MST-001'],
            [
                'customer_id' => $this->customer->id,
                'carrier_id' => $this->carrier->id,
                'origin_id' => $this->origin->id,
                'destination_id' => $this->destination->id,
                'cargo_type' => 'Auto Parts',
                'weight' => 900.00,
                'volume' => 6.00,
                'expected_departure' => now(),
                'expected_arrival' => now()->addDays(4),
                'estimated_arrival' => now()->addDays(4),
                'current_status' => 'IN_TRANSIT',
            ]
        );
    }

    public function test_admin_can_create_carrier(): void
    {
        $token = $this->admin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.carriers.store'), [
                'code' => 'NEW_AIR',
                'name' => 'Sky Cargo International',
                'service_type' => 'air',
                'contact_email' => 'ops@skycargo.com',
                'contact_phone' => '+62812345678',
                'is_active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.code', 'NEW_AIR')
            ->assertJsonPath('data.name', 'Sky Cargo International');

        $this->assertDatabaseHas('carriers', ['code' => 'NEW_AIR']);
    }

    public function test_operator_and_customer_cannot_create_carrier_returns_403(): void
    {
        $operatorToken = $this->operator->createToken('test')->plainTextToken;
        $customerToken = $this->customer->createToken('test')->plainTextToken;

        $response1 = $this->withHeader('Authorization', 'Bearer ' . $operatorToken)
            ->postJson(route('api.carriers.store'), [
                'code' => 'FAIL_1',
                'name' => 'Fail Carrier',
                'service_type' => 'sea',
            ]);
        $response1->assertStatus(403);

        $response2 = $this->withHeader('Authorization', 'Bearer ' . $customerToken)
            ->postJson(route('api.carriers.store'), [
                'code' => 'FAIL_2',
                'name' => 'Fail Carrier 2',
                'service_type' => 'sea',
            ]);
        $response2->assertStatus(403);
    }

    public function test_admin_and_operator_can_list_carriers(): void
    {
        $token = $this->operator->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.carriers.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_admin_can_create_location(): void
    {
        $token = $this->admin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.locations.store'), [
                'code' => 'LOC_NEW',
                'name' => 'New Logistic Hub',
                'type' => 'hub',
                'city' => 'Bekasi',
                'country' => 'Indonesia',
                'latitude' => -6.23,
                'longitude' => 106.99,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.code', 'LOC_NEW')
            ->assertJsonPath('data.city', 'Bekasi');

        $this->assertDatabaseHas('locations', ['code' => 'LOC_NEW']);
    }

    public function test_customer_cannot_create_location_returns_403(): void
    {
        $token = $this->customer->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.locations.store'), [
                'code' => 'LOC_CUST',
                'name' => 'Customer Hub',
                'type' => 'warehouse',
                'city' => 'Bandung',
                'country' => 'Indonesia',
                'latitude' => -6.9,
                'longitude' => 107.6,
            ]);

        $response->assertStatus(403);
    }

    public function test_operator_can_report_shipment_exception(): void
    {
        $token = $this->operator->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.shipments.exceptions.store', $this->shipment->id), [
                'exception_type' => 'CUSTOMS_HOLD',
                'severity' => 'HIGH',
                'description' => 'Container flagged for physical inspection by customs',
                'status' => 'OPEN',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.exception_type', 'CUSTOMS_HOLD')
            ->assertJsonPath('data.status', 'OPEN')
            ->assertJsonPath('data.severity', 'HIGH');

        $this->assertDatabaseHas('shipment_exceptions', [
            'shipment_id' => $this->shipment->id,
            'exception_type' => 'CUSTOMS_HOLD',
        ]);

        $this->assertTrue((bool) $this->shipment->fresh()->is_delayed);
    }

    public function test_operator_can_update_shipment_exception_status(): void
    {
        $exception = ShipmentException::create([
            'shipment_id' => $this->shipment->id,
            'exception_type' => 'WEATHER_DELAY',
            'severity' => 'MEDIUM',
            'status' => 'OPEN',
            'description' => 'Rough seas delayed departure',
            'detected_at' => now(),
            'occurred_at' => now(),
            'is_resolved' => false,
        ]);

        $token = $this->operator->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson(route('api.exceptions.update', $exception->id), [
                'status' => 'RESOLVED',
                'resolution_notes' => 'Vessel cleared and resumed navigation',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'RESOLVED')
            ->assertJsonPath('data.is_resolved', true);

        $this->assertTrue((bool) $exception->fresh()->is_resolved);
        $this->assertNotNull($exception->fresh()->resolved_at);
    }

    public function test_customer_cannot_report_exception_returns_403(): void
    {
        $token = $this->customer->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.shipments.exceptions.store', $this->shipment->id), [
                'exception_type' => 'DAMAGED',
                'severity' => 'HIGH',
                'description' => 'Unauthorized report',
            ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson(route('api.shipments.index'));
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
