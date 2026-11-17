<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CargoTraceAuthorizationTest extends TestCase
{
    protected User $admin;
    protected User $operator;
    protected User $customer1;
    protected User $customer2;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;
    protected Shipment $shipment1;
    protected Shipment $shipment2;

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

        $this->customer1 = User::firstOrCreate(
            ['email' => 'customer@cargotrace.com'],
            ['name' => 'Customer 1', 'password' => bcrypt('password'), 'role' => 'customer']
        );

        $this->customer2 = User::firstOrCreate(
            ['email' => 'customer2@cargotrace.com'],
            ['name' => 'Customer 2', 'password' => bcrypt('password'), 'role' => 'customer']
        );

        $this->carrier = Carrier::firstOrCreate(
            ['code' => 'TEST_CR'],
            ['name' => 'Carrier Test', 'service_type' => 'sea', 'is_active' => true]
        );

        $this->origin = Location::firstOrCreate(
            ['code' => 'TEST_ORG'],
            ['name' => 'Origin Test', 'type' => 'seaport', 'city' => 'Jakarta', 'country' => 'Indonesia', 'latitude' => -6.1, 'longitude' => 106.8]
        );

        $this->destination = Location::firstOrCreate(
            ['code' => 'TEST_DST'],
            ['name' => 'Destination Test', 'type' => 'seaport', 'city' => 'Singapore', 'country' => 'Singapore', 'latitude' => 1.2, 'longitude' => 103.8]
        );

        $this->shipment1 = Shipment::firstOrCreate(
            ['shipment_number' => 'TEST-001'],
            [
                'customer_id' => $this->customer1->id,
                'carrier_id' => $this->carrier->id,
                'origin_id' => $this->origin->id,
                'destination_id' => $this->destination->id,
                'cargo_type' => 'Electronics',
                'weight' => 500.00,
                'volume' => 5.00,
                'expected_departure' => now(),
                'expected_arrival' => now()->addDays(2),
                'estimated_arrival' => now()->addDays(2),
                'current_status' => 'in_transit',
            ]
        );

        $this->shipment2 = Shipment::firstOrCreate(
            ['shipment_number' => 'TEST-002'],
            [
                'customer_id' => $this->customer2->id,
                'carrier_id' => $this->carrier->id,
                'origin_id' => $this->origin->id,
                'destination_id' => $this->destination->id,
                'cargo_type' => 'Textile',
                'weight' => 800.00,
                'volume' => 8.00,
                'expected_departure' => now(),
                'expected_arrival' => now()->addDays(3),
                'estimated_arrival' => now()->addDays(3),
                'current_status' => 'pending',
            ]
        );
    }

    public function test_customer_can_view_own_shipment(): void
    {
        $response = $this->actingAs($this->customer1)->get(route('shipments.show', $this->shipment1));
        $response->assertStatus(200);
    }

    public function test_customer_cannot_view_other_customers_shipment_returns_403(): void
    {
        $response = $this->actingAs($this->customer1)->get(route('shipments.show', $this->shipment2));
        $response->assertStatus(403);
    }

    public function test_customer_cannot_create_shipment_returns_403(): void
    {
        $response = $this->actingAs($this->customer1)->get(route('shipments.create'));
        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_carriers_crud_returns_403(): void
    {
        $response = $this->actingAs($this->customer1)->get(route('carriers.index'));
        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_locations_crud_returns_403(): void
    {
        $response = $this->actingAs($this->customer1)->get(route('locations.index'));
        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_audit_logs_returns_403(): void
    {
        $response = $this->actingAs($this->customer1)->get(route('audit-logs.index'));
        $response->assertStatus(403);
    }

    public function test_operator_can_view_any_shipment(): void
    {
        $response = $this->actingAs($this->operator)->get(route('shipments.show', $this->shipment1));
        $response->assertStatus(200);

        $response2 = $this->actingAs($this->operator)->get(route('shipments.show', $this->shipment2));
        $response2->assertStatus(200);
    }

    public function test_operator_can_access_create_shipment_page(): void
    {
        $response = $this->actingAs($this->operator)->get(route('shipments.create'));
        $response->assertStatus(200);
    }

    public function test_operator_cannot_access_carriers_crud_returns_403(): void
    {
        $response = $this->actingAs($this->operator)->get(route('carriers.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_all_management_modules(): void
    {
        $response1 = $this->actingAs($this->admin)->get(route('carriers.index'));
        $response1->assertStatus(200);

        $response2 = $this->actingAs($this->admin)->get(route('locations.index'));
        $response2->assertStatus(200);

        $response3 = $this->actingAs($this->admin)->get(route('audit-logs.index'));
        $response3->assertStatus(200);

        $response4 = $this->actingAs($this->admin)->get(route('shipments.index'));
        $response4->assertStatus(200);
    }
}
