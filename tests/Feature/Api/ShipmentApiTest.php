<?php

namespace Tests\Feature\Api;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentApiTest extends TestCase
{
    use RefreshDatabase;

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
            ['code' => 'CARRIER_API_TEST'],
            ['name' => 'Carrier API Test', 'service_type' => 'sea', 'is_active' => true]
        );

        $this->origin = Location::firstOrCreate(
            ['code' => 'LOC_ORG_TEST'],
            ['name' => 'Origin Port Test', 'type' => 'seaport', 'city' => 'Jakarta', 'country' => 'Indonesia', 'latitude' => -6.1, 'longitude' => 106.8]
        );

        $this->destination = Location::firstOrCreate(
            ['code' => 'LOC_DST_TEST'],
            ['name' => 'Destination Port Test', 'type' => 'seaport', 'city' => 'Singapore', 'country' => 'Singapore', 'latitude' => 1.2, 'longitude' => 103.8]
        );

        $this->shipment1 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-API-001'],
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
                'current_status' => 'CREATED',
            ]
        );

        $this->shipment2 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-API-002'],
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
                'current_status' => 'BOOKED',
            ]
        );
    }

    public function test_operator_can_list_all_shipments_with_pagination(): void
    {
        $token = $this->operator->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.shipments.index', ['per_page' => 10]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'shipment_number', 'cargo_type', 'current_status'],
                ],
                'links' => ['first', 'last'],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_customer_only_sees_own_shipments_in_list(): void
    {
        $token = $this->customer1->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.shipments.index'));

        $response->assertStatus(200);
        $data = $response->json('data');

        foreach ($data as $shipment) {
            $this->assertEquals($this->customer1->id, $shipment['customer_id']);
        }
    }

    public function test_customer_can_view_own_shipment_detail(): void
    {
        $token = $this->customer1->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.shipments.show', $this->shipment1->id));

        $response->assertStatus(200)
            ->assertJsonPath('data.shipment_number', 'CT-API-001')
            ->assertJsonPath('data.customer_id', $this->customer1->id);
    }

    public function test_customer_cannot_view_another_customers_shipment_detail_returns_403(): void
    {
        $token = $this->customer1->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.shipments.show', $this->shipment2->id));

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'This action is unauthorized.',
            ]);
    }

    public function test_viewing_nonexistent_shipment_returns_404(): void
    {
        $token = $this->operator->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.shipments.show', 999999));

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Resource not found.',
            ]);
    }

    public function test_operator_can_create_shipment_with_items(): void
    {
        $token = $this->operator->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.shipments.store'), [
                'customer_id' => $this->customer1->id,
                'carrier_id' => $this->carrier->id,
                'origin_id' => $this->origin->id,
                'destination_id' => $this->destination->id,
                'cargo_type' => 'Medical Equipment',
                'weight' => 250.75,
                'volume' => 3.20,
                'expected_departure' => now()->toDateTimeString(),
                'expected_arrival' => now()->addDays(4)->toDateTimeString(),
                'estimated_arrival' => now()->addDays(4)->toDateTimeString(),
                'notes' => 'Handle with care',
                'items' => [
                    [
                        'item_name' => 'Oxygen Concentrator',
                        'item_code' => 'OXY-01',
                        'quantity' => 10,
                        'weight' => 150.00,
                    ],
                    [
                        'item_name' => 'Diagnostic Monitor',
                        'item_code' => 'MON-02',
                        'quantity' => 5,
                        'weight' => 100.75,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.cargo_type', 'Medical Equipment')
            ->assertJsonPath('data.current_status', 'CREATED');

        $this->assertDatabaseHas('shipments', [
            'cargo_type' => 'Medical Equipment',
            'customer_id' => $this->customer1->id,
        ]);

        $this->assertDatabaseHas('shipment_items', [
            'item_name' => 'Oxygen Concentrator',
        ]);
    }

    public function test_customer_cannot_create_shipment_returns_403(): void
    {
        $token = $this->customer1->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.shipments.store'), [
                'customer_id' => $this->customer1->id,
                'carrier_id' => $this->carrier->id,
                'origin_id' => $this->origin->id,
                'destination_id' => $this->destination->id,
                'cargo_type' => 'Medical Equipment',
                'weight' => 250.75,
                'expected_departure' => now()->toDateTimeString(),
                'expected_arrival' => now()->addDays(4)->toDateTimeString(),
            ]);

        $response->assertStatus(403);
    }

    public function test_create_shipment_validates_input_returns_422(): void
    {
        $token = $this->operator->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.shipments.store'), []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['customer_id', 'carrier_id', 'origin_id', 'destination_id', 'cargo_type', 'weight'],
            ]);
    }

    public function test_operator_can_update_shipment(): void
    {
        $token = $this->operator->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson(route('api.shipments.update', $this->shipment1->id), [
                'cargo_type' => 'Updated Electronics',
                'current_status' => 'BOOKED',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.cargo_type', 'Updated Electronics')
            ->assertJsonPath('data.current_status', 'BOOKED');
    }

    public function test_invalid_status_transition_returns_422(): void
    {
        $token = $this->operator->createToken('test')->plainTextToken;

        // Transition from CREATED straight to DELIVERED is illegal (state machine rule)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson(route('api.shipments.update', $this->shipment1->id), [
                'current_status' => 'DELIVERED',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message']);
    }

    public function test_customer_cannot_update_shipment_returns_403(): void
    {
        $token = $this->customer1->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson(route('api.shipments.update', $this->shipment1->id), [
                'cargo_type' => 'Hacked Cargo',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_shipment(): void
    {
        $token = $this->admin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson(route('api.shipments.destroy', $this->shipment1->id));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Shipment deleted successfully.']);

        $this->assertDatabaseMissing('shipments', ['id' => $this->shipment1->id]);
    }

    public function test_operator_cannot_delete_shipment_returns_403(): void
    {
        $token = $this->operator->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson(route('api.shipments.destroy', $this->shipment2->id));

        $response->assertStatus(403);
    }
}
