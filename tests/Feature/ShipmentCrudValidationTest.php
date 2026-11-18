<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentCrudValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customer;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin_crud@cargotrace.com',
            'role' => 'admin',
        ]);

        $this->operator = User::factory()->create([
            'email' => 'operator_crud@cargotrace.com',
            'role' => 'operator',
        ]);

        $this->customer = User::factory()->create([
            'email' => 'customer_crud@cargotrace.com',
            'role' => 'customer',
        ]);

        $this->carrier = Carrier::create([
            'code' => 'CR_CRUD',
            'name' => 'Fast Carrier',
            'service_type' => 'sea',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'LOC_ORG_CRUD',
            'name' => 'Surabaya Seaport',
            'type' => 'seaport',
            'city' => 'Surabaya',
            'country' => 'Indonesia',
            'latitude' => -7.2,
            'longitude' => 112.7,
        ]);

        $this->destination = Location::create([
            'code' => 'LOC_DST_CRUD',
            'name' => 'Singapore Seaport',
            'type' => 'seaport',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'latitude' => 1.28,
            'longitude' => 103.85,
        ]);
    }

    public function test_api_create_shipment_returns_422_when_required_fields_are_missing(): void
    {
        $response = $this->actingAs($this->operator, 'sanctum')
            ->postJson(route('api.shipments.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'customer_id',
                'carrier_id',
                'origin_id',
                'destination_id',
                'cargo_type',
                'weight',
                'expected_departure',
                'expected_arrival',
            ]);
    }

    public function test_api_create_shipment_returns_422_when_inputs_are_invalid(): void
    {
        // 1. Same origin and destination
        $responseSameLocation = $this->actingAs($this->operator, 'sanctum')
            ->postJson(route('api.shipments.store'), [
                'customer_id' => $this->customer->id,
                'carrier_id' => $this->carrier->id,
                'origin_id' => $this->origin->id,
                'destination_id' => $this->origin->id, // identical!
                'cargo_type' => 'Perishables',
                'weight' => 100,
                'expected_departure' => now()->toDateTimeString(),
                'expected_arrival' => now()->addDays(2)->toDateTimeString(),
            ]);

        $responseSameLocation->assertStatus(422)
            ->assertJsonValidationErrors(['destination_id']);

        // 2. Negative weight
        $responseNegativeWeight = $this->actingAs($this->operator, 'sanctum')
            ->postJson(route('api.shipments.store'), [
                'customer_id' => $this->customer->id,
                'carrier_id' => $this->carrier->id,
                'origin_id' => $this->origin->id,
                'destination_id' => $this->destination->id,
                'cargo_type' => 'Heavy Metal',
                'weight' => -10, // negative!
                'expected_departure' => now()->toDateTimeString(),
                'expected_arrival' => now()->addDays(2)->toDateTimeString(),
            ]);

        $responseNegativeWeight->assertStatus(422)
            ->assertJsonValidationErrors(['weight']);

        // 3. Non-existent foreign keys
        $responseNonExistentFk = $this->actingAs($this->operator, 'sanctum')
            ->postJson(route('api.shipments.store'), [
                'customer_id' => 999999, // invalid user
                'carrier_id' => 999999,  // invalid carrier
                'origin_id' => $this->origin->id,
                'destination_id' => $this->destination->id,
                'cargo_type' => 'Standard Cargo',
                'weight' => 50,
                'expected_departure' => now()->toDateTimeString(),
                'expected_arrival' => now()->addDays(2)->toDateTimeString(),
            ]);

        $responseNonExistentFk->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id', 'carrier_id']);
    }

    public function test_api_create_shipment_returns_422_when_shipment_number_is_duplicate(): void
    {
        // First shipment
        Shipment::create([
            'shipment_number' => 'SHP-DUP-001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Electronics',
            'weight' => 100,
            'volume' => 5,
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(3),
            'estimated_arrival' => now()->addDays(3),
            'current_status' => 'CREATED',
        ]);

        // Attempt second shipment with identical shipment_number
        $response = $this->actingAs($this->operator, 'sanctum')
            ->postJson(route('api.shipments.store'), [
                'shipment_number' => 'SHP-DUP-001',
                'customer_id' => $this->customer->id,
                'carrier_id' => $this->carrier->id,
                'origin_id' => $this->origin->id,
                'destination_id' => $this->destination->id,
                'cargo_type' => 'Textiles',
                'weight' => 50,
                'expected_departure' => now()->toDateTimeString(),
                'expected_arrival' => now()->addDays(3)->toDateTimeString(),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shipment_number']);
    }

    public function test_web_create_shipment_validates_required_fields(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('shipments.store'), []);

        $response->assertSessionHasErrors([
            'shipment_number',
            'customer_id',
            'carrier_id',
            'origin_id',
            'destination_id',
            'cargo_type',
            'weight',
            'volume',
            'expected_departure',
            'expected_arrival',
            'items',
        ]);
    }

    public function test_operator_can_successfully_create_shipment_via_api(): void
    {
        $payload = [
            'shipment_number' => 'SHP-CRUDSUCCESS-01',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Machinery Spareparts',
            'weight' => 350.5,
            'volume' => 12.0,
            'expected_departure' => now()->addDay()->toDateTimeString(),
            'expected_arrival' => now()->addDays(5)->toDateTimeString(),
            'items' => [
                [
                    'item_name' => 'Hydraulic Pump',
                    'item_code' => 'HP-01',
                    'quantity' => 2,
                    'weight' => 175.25,
                    'volume' => 6.0,
                    'declared_value' => 5000000,
                ],
            ],
        ];

        $response = $this->actingAs($this->operator, 'sanctum')
            ->postJson(route('api.shipments.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.shipment_number', 'SHP-CRUDSUCCESS-01')
            ->assertJsonPath('data.current_status', 'CREATED');

        $this->assertDatabaseHas('shipments', [
            'shipment_number' => 'SHP-CRUDSUCCESS-01',
            'cargo_type' => 'Machinery Spareparts',
        ]);

        $this->assertDatabaseHas('shipment_items', [
            'item_name' => 'Hydraulic Pump',
            'quantity' => 2,
        ]);
    }

    public function test_operator_can_successfully_update_shipment_via_api(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'SHP-TO-UPDATE-01',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Initial Cargo',
            'weight' => 120,
            'volume' => 5,
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(4),
            'estimated_arrival' => now()->addDays(4),
            'current_status' => 'CREATED',
        ]);

        $response = $this->actingAs($this->operator, 'sanctum')
            ->putJson(route('api.shipments.update', $shipment), [
                'cargo_type' => 'Updated Electronics Cargo',
                'weight' => 180,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.cargo_type', 'Updated Electronics Cargo');

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'cargo_type' => 'Updated Electronics Cargo',
            'weight' => 180,
        ]);
    }

    public function test_admin_can_delete_shipment(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'SHP-TO-DELETE-01',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Cargo to Delete',
            'weight' => 50,
            'volume' => 2,
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(2),
            'estimated_arrival' => now()->addDays(2),
            'current_status' => 'CREATED',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson(route('api.shipments.destroy', $shipment));

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Shipment deleted successfully.',
            ]);

        $this->assertDatabaseMissing('shipments', [
            'id' => $shipment->id,
        ]);
    }
}
