<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAndChartTest extends TestCase
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
            ['code' => 'CR_DASH_TEST'],
            ['name' => 'Dashboard Carrier', 'service_type' => 'sea', 'is_active' => true]
        );

        $this->origin = Location::firstOrCreate(
            ['code' => 'LOC_DASH_ORG'],
            ['name' => 'Jakarta Seaport', 'type' => 'seaport', 'city' => 'Jakarta', 'country' => 'Indonesia', 'latitude' => -6.1, 'longitude' => 106.8]
        );

        $this->destination = Location::firstOrCreate(
            ['code' => 'LOC_DASH_DST'],
            ['name' => 'Surabaya Seaport', 'type' => 'seaport', 'city' => 'Surabaya', 'country' => 'Indonesia', 'latitude' => -7.2, 'longitude' => 112.7]
        );

        // Create sample shipments with different statuses
        $s1 = Shipment::create([
            'shipment_number' => 'CT-DASH-001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Electronic Parts',
            'weight' => 500,
            'volume' => 5,
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(2),
            'estimated_arrival' => now()->addDays(2),
            'current_status' => 'IN_TRANSIT',
            'is_delayed' => true,
            'delay_hours' => 14,
        ]);

        $s2 = Shipment::create([
            'shipment_number' => 'CT-DASH-002',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Textiles',
            'weight' => 300,
            'volume' => 3,
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(3),
            'estimated_arrival' => now()->addDays(3),
            'current_status' => 'DELIVERED',
            'is_delayed' => false,
            'delay_hours' => 0,
        ]);

        ShipmentException::create([
            'shipment_id' => $s1->id,
            'exception_type' => 'WEATHER_DELAY',
            'severity' => 'HIGH',
            'status' => 'OPEN',
            'description' => 'Storm at sea',
            'detected_at' => now(),
            'occurred_at' => now(),
            'is_resolved' => false,
        ]);
    }

    public function test_dashboard_renders_metrics_and_charts_for_operator(): void
    {
        $response = $this->actingAs($this->operator)->get(route('dashboard'));

        $response->assertStatus(200);

        // Assert view has all required metrics from MySQL
        $response->assertViewHas('totalShipments');
        $response->assertViewHas('inTransitCount');
        $response->assertViewHas('deliveredCount');
        $response->assertViewHas('delayedCount');
        $response->assertViewHas('openExceptionsCount');
        $response->assertViewHas('delayRate');
        $response->assertViewHas('exceptionRate');

        // Assert 3 additional KPIs (Slide 23 Brief Metrics)
        $response->assertViewHas('onTimeDeliveryRate');
        $response->assertViewHas('avgDelayDuration');
        $response->assertViewHas('avgExceptionResolutionTime');

        // Assert Chart.js data is passed
        $response->assertViewHas('statusChartLabels');
        $response->assertViewHas('statusChartValues');
        $response->assertViewHas('carrierChartLabels');
        $response->assertViewHas('carrierChartValues');

        // Assert canvas elements and KPI cards are in HTML
        $response->assertSee('id="statusChart"', false);
        $response->assertSee('id="carrierChart"', false);
        $response->assertSee('cdn.jsdelivr.net/npm/chart.js', false);
        $response->assertSee('On-Time Delivery Rate');
        $response->assertSee('Average Delay Duration');
        $response->assertSee('Average Exception Resolution Time');
    }

    public function test_dashboard_is_scoped_for_customer(): void
    {
        $response = $this->actingAs($this->customer)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Kargo Saya');
    }
}
