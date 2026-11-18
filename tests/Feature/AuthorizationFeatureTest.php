<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationFeatureTest extends TestCase
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

        $this->admin = User::factory()->create([
            'email' => 'admin_authz@cargotrace.com',
            'role' => 'admin',
        ]);

        $this->operator = User::factory()->create([
            'email' => 'operator_authz@cargotrace.com',
            'role' => 'operator',
        ]);

        $this->customer1 = User::factory()->create([
            'email' => 'customer1_authz@cargotrace.com',
            'role' => 'customer',
        ]);

        $this->customer2 = User::factory()->create([
            'email' => 'customer2_authz@cargotrace.com',
            'role' => 'customer',
        ]);

        $this->carrier = Carrier::create([
            'code' => 'CR_AUTHZ',
            'name' => 'Authz Carrier',
            'service_type' => 'sea',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'LOC_ORG_AUTHZ',
            'name' => 'Port of Jakarta',
            'type' => 'seaport',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'latitude' => -6.10,
            'longitude' => 106.88,
        ]);

        $this->destination = Location::create([
            'code' => 'LOC_DST_AUTHZ',
            'name' => 'Port of Singapore',
            'type' => 'seaport',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'latitude' => 1.28,
            'longitude' => 103.85,
        ]);

        $this->shipment1 = Shipment::create([
            'shipment_number' => 'SHP-AUTHZ-001',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Electronics',
            'weight' => 200,
            'volume' => 10,
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(5),
            'estimated_arrival' => now()->addDays(5),
            'current_status' => 'CREATED',
            'is_delayed' => false,
            'delay_hours' => 0,
        ]);

        $this->shipment2 = Shipment::create([
            'shipment_number' => 'SHP-AUTHZ-002',
            'customer_id' => $this->customer2->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Textiles',
            'weight' => 450,
            'volume' => 25,
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(4),
            'estimated_arrival' => now()->addDays(4),
            'current_status' => 'BOOKED',
            'is_delayed' => false,
            'delay_hours' => 0,
        ]);
    }

    public function test_customer_cannot_view_another_customer_shipment_via_web_returns_403(): void
    {
        $response = $this->actingAs($this->customer1)
            ->get(route('shipments.show', $this->shipment2));

        $response->assertStatus(403);
    }

    public function test_customer_cannot_edit_or_update_another_customer_shipment_via_web_returns_403(): void
    {
        // Try edit page
        $editResponse = $this->actingAs($this->customer1)
            ->get(route('shipments.edit', $this->shipment2));
        $editResponse->assertStatus(403);

        // Try update action
        $updateResponse = $this->actingAs($this->customer1)
            ->put(route('shipments.update', $this->shipment2), [
                'customer_id' => $this->customer2->id,
                'carrier_id' => $this->carrier->id,
                'origin_id' => $this->origin->id,
                'destination_id' => $this->destination->id,
                'cargo_type' => 'Hacked Cargo',
                'weight' => 999,
                'volume' => 99,
                'expected_departure' => now()->toDateTimeString(),
                'expected_arrival' => now()->addDays(3)->toDateTimeString(),
                'current_status' => 'BOOKED',
            ]);
        $updateResponse->assertStatus(403);
    }

    public function test_customer_cannot_view_or_update_another_customer_shipment_via_api_returns_403(): void
    {
        // View via API
        $viewResponse = $this->actingAs($this->customer1, 'sanctum')
            ->getJson(route('api.shipments.show', $this->shipment2));
        $viewResponse->assertStatus(403);

        // Update via API
        $updateResponse = $this->actingAs($this->customer1, 'sanctum')
            ->putJson(route('api.shipments.update', $this->shipment2), [
                'cargo_type' => 'Malicious Update',
            ]);
        $updateResponse->assertStatus(403);

        // Delete via API
        $deleteResponse = $this->actingAs($this->customer1, 'sanctum')
            ->deleteJson(route('api.shipments.destroy', $this->shipment2));
        $deleteResponse->assertStatus(403);
    }

    public function test_customer_can_view_own_shipment_via_web_and_api(): void
    {
        // Web
        $webResponse = $this->actingAs($this->customer1)
            ->get(route('shipments.show', $this->shipment1));
        $webResponse->assertStatus(200);

        // API
        $apiResponse = $this->actingAs($this->customer1, 'sanctum')
            ->getJson(route('api.shipments.show', $this->shipment1));
        $apiResponse->assertStatus(200)
            ->assertJsonPath('data.shipment_number', 'SHP-AUTHZ-001');
    }

    public function test_customer_cannot_access_carriers_crud_returns_403(): void
    {
        $this->actingAs($this->customer1)->get(route('carriers.index'))->assertStatus(403);
        $this->actingAs($this->customer1)->get(route('carriers.create'))->assertStatus(403);
        $this->actingAs($this->customer1)->post(route('carriers.store'), [
            'code' => 'CR_NEW',
            'name' => 'New Carrier',
            'service_type' => 'sea',
        ])->assertStatus(403);

        // API
        $this->actingAs($this->customer1, 'sanctum')->postJson(route('api.carriers.store'), [
            'code' => 'CR_API_NEW',
            'name' => 'New Carrier API',
            'service_type' => 'air',
        ])->assertStatus(403);
    }

    public function test_customer_cannot_access_locations_crud_returns_403(): void
    {
        $this->actingAs($this->customer1)->get(route('locations.index'))->assertStatus(403);
        $this->actingAs($this->customer1)->get(route('locations.create'))->assertStatus(403);
        $this->actingAs($this->customer1)->post(route('locations.store'), [
            'code' => 'LOC_NEW',
            'name' => 'New Location',
            'type' => 'seaport',
            'city' => 'Surabaya',
            'country' => 'Indonesia',
            'latitude' => -7.2,
            'longitude' => 112.7,
        ])->assertStatus(403);

        // API
        $this->actingAs($this->customer1, 'sanctum')->postJson(route('api.locations.store'), [
            'code' => 'LOC_API_NEW',
            'name' => 'New Location API',
            'type' => 'airport',
            'city' => 'Bali',
            'country' => 'Indonesia',
            'latitude' => -8.7,
            'longitude' => 115.1,
        ])->assertStatus(403);
    }

    public function test_customer_cannot_access_audit_logs_returns_403(): void
    {
        $this->actingAs($this->customer1)->get(route('audit-logs.index'))->assertStatus(403);
    }

    public function test_customer_cannot_create_shipment_returns_403(): void
    {
        // Web
        $this->actingAs($this->customer1)->get(route('shipments.create'))->assertStatus(403);
        $this->actingAs($this->customer1)->post(route('shipments.store'), [
            'shipment_number' => 'SHP-CUST-UNAUTH',
        ])->assertStatus(403);

        // API
        $this->actingAs($this->customer1, 'sanctum')->postJson(route('api.shipments.store'), [
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Unauthorized Cargo',
            'weight' => 150,
            'volume' => 5,
            'expected_departure' => now()->toDateTimeString(),
            'expected_arrival' => now()->addDays(3)->toDateTimeString(),
        ])->assertStatus(403);
    }

    public function test_operator_can_manage_shipments_but_cannot_access_carriers_locations_audit_logs(): void
    {
        // Operator can manage shipments
        $this->actingAs($this->operator)->get(route('shipments.index'))->assertStatus(200);
        $this->actingAs($this->operator)->get(route('shipments.create'))->assertStatus(200);
        $this->actingAs($this->operator)->get(route('shipments.show', $this->shipment1))->assertStatus(200);

        // Operator cannot manage carriers/locations/audit-logs
        $this->actingAs($this->operator)->get(route('carriers.index'))->assertStatus(403);
        $this->actingAs($this->operator)->get(route('locations.index'))->assertStatus(403);
        $this->actingAs($this->operator)->get(route('audit-logs.index'))->assertStatus(403);
    }

    public function test_admin_has_full_access_to_all_management_modules(): void
    {
        $this->actingAs($this->admin)->get(route('carriers.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('locations.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('audit-logs.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('shipments.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('shipments.show', $this->shipment1))->assertStatus(200);
    }
}
