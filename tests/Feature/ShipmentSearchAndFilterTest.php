<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ShipmentSearchAndFilterTest extends TestCase
{
    use DatabaseTransactions;

    protected User $operator;
    protected Carrier $carrierA;
    protected Carrier $carrierB;
    protected Location $originJakarta;
    protected Location $originSurabaya;
    protected Location $destSingapore;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::firstOrCreate(
            ['email' => 'operator@cargotrace.com'],
            ['name' => 'Operator Test', 'password' => bcrypt('password'), 'role' => 'operator']
        );

        $this->carrierA = Carrier::firstOrCreate(
            ['code' => 'CR_FILTER_A'],
            ['name' => 'Alpha Ocean Carrier', 'service_type' => 'sea', 'is_active' => true]
        );

        $this->carrierB = Carrier::firstOrCreate(
            ['code' => 'CR_FILTER_B'],
            ['name' => 'Beta Air Express', 'service_type' => 'air', 'is_active' => true]
        );

        $this->originJakarta = Location::firstOrCreate(
            ['code' => 'LOC_JKT_FLT'],
            ['name' => 'Jakarta Seaport', 'type' => 'seaport', 'city' => 'Jakarta', 'country' => 'Indonesia', 'latitude' => -6.1, 'longitude' => 106.8]
        );

        $this->originSurabaya = Location::firstOrCreate(
            ['code' => 'LOC_SUB_FLT'],
            ['name' => 'Surabaya Seaport', 'type' => 'seaport', 'city' => 'Surabaya', 'country' => 'Indonesia', 'latitude' => -7.2, 'longitude' => 112.7]
        );

        $this->destSingapore = Location::firstOrCreate(
            ['code' => 'LOC_SGP_FLT'],
            ['name' => 'Singapore Port', 'type' => 'seaport', 'city' => 'Singapore', 'country' => 'Singapore', 'latitude' => 1.3, 'longitude' => 103.8]
        );

        // Shipment 1: Jakarta -> Singapore with Alpha Carrier
        Shipment::create([
            'shipment_number' => 'CT-FILTER-ALPHA-01',
            'customer_id' => $this->operator->id,
            'carrier_id' => $this->carrierA->id,
            'origin_id' => $this->originJakarta->id,
            'destination_id' => $this->destSingapore->id,
            'cargo_type' => 'Heavy Machinery Parts',
            'weight' => 2500,
            'volume' => 15,
            'expected_departure' => '2026-11-20 08:00:00',
            'expected_arrival' => '2026-11-25 10:00:00',
            'estimated_arrival' => '2026-11-26 12:00:00',
            'current_status' => 'IN_TRANSIT',
            'is_delayed' => true,
            'delay_hours' => 26,
        ]);

        // Shipment 2: Surabaya -> Singapore with Beta Carrier
        Shipment::create([
            'shipment_number' => 'CT-FILTER-BETA-02',
            'customer_id' => $this->operator->id,
            'carrier_id' => $this->carrierB->id,
            'origin_id' => $this->originSurabaya->id,
            'destination_id' => $this->destSingapore->id,
            'cargo_type' => 'Fresh Seafood Consignment',
            'weight' => 500,
            'volume' => 4,
            'expected_departure' => '2026-11-28 09:00:00',
            'expected_arrival' => '2026-11-29 14:00:00',
            'estimated_arrival' => '2026-11-29 14:00:00',
            'current_status' => 'DELIVERED',
            'is_delayed' => false,
            'delay_hours' => 0,
        ]);
    }

    public function test_search_by_shipment_number(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('shipments.index', ['search' => 'ALPHA-01']));

        $response->assertStatus(200);
        $response->assertSee('CT-FILTER-ALPHA-01');
        $response->assertDontSee('CT-FILTER-BETA-02');
    }

    public function test_search_by_cargo_type(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('shipments.index', ['search' => 'Seafood']));

        $response->assertStatus(200);
        $response->assertSee('CT-FILTER-BETA-02');
        $response->assertDontSee('CT-FILTER-ALPHA-01');
    }

    public function test_filter_by_status(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('shipments.index', ['current_status' => 'DELIVERED']));

        $response->assertStatus(200);
        $response->assertSee('CT-FILTER-BETA-02');
        $response->assertDontSee('CT-FILTER-ALPHA-01');
    }

    public function test_filter_by_carrier(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('shipments.index', ['carrier_id' => $this->carrierA->id]));

        $response->assertStatus(200);
        $response->assertSee('CT-FILTER-ALPHA-01');
        $response->assertDontSee('CT-FILTER-BETA-02');
    }

    public function test_filter_by_origin_and_destination(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('shipments.index', [
                'origin_id' => $this->originJakarta->id,
                'destination_id' => $this->destSingapore->id,
            ]));

        $response->assertStatus(200);
        $response->assertSee('CT-FILTER-ALPHA-01');
        $response->assertDontSee('CT-FILTER-BETA-02');
    }

    public function test_filter_by_date_range(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('shipments.index', [
                'date_from' => '2026-11-19',
                'date_to' => '2026-11-21',
            ]));

        $response->assertStatus(200);
        $response->assertSee('CT-FILTER-ALPHA-01');
        $response->assertDontSee('CT-FILTER-BETA-02');
    }

    public function test_filter_by_delay_status(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('shipments.index', ['is_delayed' => '1']));

        $response->assertStatus(200);
        $response->assertSee('CT-FILTER-ALPHA-01');
        $response->assertDontSee('CT-FILTER-BETA-02');
    }

    public function test_sorting_shipments(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('shipments.index', [
                'sort_by' => 'shipment_number',
                'sort_direction' => 'asc',
            ]));

        $response->assertStatus(200);
        $response->assertSee('CT-FILTER-ALPHA-01');
    }
}
