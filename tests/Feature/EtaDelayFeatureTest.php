<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\User;
use App\Services\EtaService;
use App\Services\ShipmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EtaDelayFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected EtaService $etaService;
    protected ShipmentService $shipmentService;
    protected User $operator;
    protected User $customer;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->etaService = app(EtaService::class);
        $this->shipmentService = app(ShipmentService::class);

        $this->operator = User::factory()->create([
            'email' => 'operator_eta@cargotrace.com',
            'role' => 'operator',
        ]);

        $this->customer = User::factory()->create([
            'email' => 'customer_eta@cargotrace.com',
            'role' => 'customer',
        ]);

        $this->carrier = Carrier::create([
            'code' => 'CR_ETA',
            'name' => 'ETA Carrier',
            'service_type' => 'sea',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'LOC_ORG_ETA',
            'name' => 'Tanjung Perak',
            'type' => 'seaport',
            'city' => 'Surabaya',
            'country' => 'Indonesia',
            'latitude' => -7.2,
            'longitude' => 112.7,
        ]);

        $this->destination = Location::create([
            'code' => 'LOC_DST_ETA',
            'name' => 'Port of Singapore',
            'type' => 'seaport',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'latitude' => 1.28,
            'longitude' => 103.85,
        ]);
    }

    protected function createShipment(Carbon $expected, Carbon $estimated): Shipment
    {
        return Shipment::create([
            'shipment_number' => 'SHP-ETA-' . uniqid(),
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Automotive Parts',
            'weight' => 750,
            'volume' => 30,
            'expected_departure' => now(),
            'expected_arrival' => $expected,
            'estimated_arrival' => $estimated,
            'current_status' => 'IN_TRANSIT',
            'is_delayed' => false,
            'delay_hours' => 0,
        ]);
    }

    public function test_eta_service_marks_shipment_on_time_when_estimated_arrival_within_schedule(): void
    {
        $expected = Carbon::parse('2026-11-20 12:00:00');
        $estimated = Carbon::parse('2026-11-20 10:00:00'); // arrives early!

        $shipment = $this->createShipment($expected, $estimated);
        $updated = $this->etaService->evaluateAndApply($shipment, $this->operator);

        $this->assertFalse($updated->is_delayed);
        $this->assertEquals(0, $updated->delay_hours);

        $analysis = $this->etaService->analyzeShipment($shipment);
        $this->assertEquals(0.0, $analysis['delay_hours']);
        $this->assertEquals(EtaService::SEVERITY_NONE, $analysis['severity']);
        $this->assertFalse($analysis['is_delayed']);
    }

    public function test_eta_service_does_not_mark_delayed_when_delay_is_below_threshold(): void
    {
        config(['cargotrace.delay_threshold_hours' => 12]);

        $expected = Carbon::parse('2026-11-20 12:00:00');
        $estimated = Carbon::parse('2026-11-20 18:00:00'); // delay 6 hours (<= 12 threshold)

        $shipment = $this->createShipment($expected, $estimated);
        $updated = $this->etaService->evaluateAndApply($shipment, $this->operator);

        $this->assertFalse($updated->is_delayed);
        $this->assertEquals(6, $updated->delay_hours);

        $analysis = $this->etaService->analyzeShipment($shipment);
        $this->assertEquals(6.0, $analysis['delay_hours']);
        $this->assertEquals(EtaService::SEVERITY_LOW, $analysis['severity']);
        $this->assertFalse($analysis['is_delayed']);
    }

    public function test_eta_service_marks_delayed_and_notifies_customer_when_delay_exceeds_threshold(): void
    {
        config(['cargotrace.delay_threshold_hours' => 12]);

        $expected = Carbon::parse('2026-11-20 12:00:00');
        $estimated = Carbon::parse('2026-11-21 06:00:00'); // delay 18 hours (> 12 threshold)

        $shipment = $this->createShipment($expected, $estimated);
        $updated = $this->etaService->evaluateAndApply($shipment, $this->operator);

        $this->assertTrue($updated->is_delayed);
        $this->assertEquals(18, $updated->delay_hours);

        $analysis = $this->etaService->analyzeShipment($shipment);
        $this->assertEquals(18.0, $analysis['delay_hours']);
        $this->assertEquals(EtaService::SEVERITY_MEDIUM, $analysis['severity']);
        $this->assertTrue($analysis['is_delayed']);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'EVALUATE_ETA_DELAY',
            'auditable_type' => Shipment::class,
            'auditable_id' => $shipment->id,
        ]);

        // Verify in-app notification sent to customer
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->customer->id,
            'type' => 'delay_alert',
        ]);
    }

    public function test_eta_service_severity_calculation_accurately_maps_thresholds(): void
    {
        // 0 hours -> NONE
        $this->assertEquals(EtaService::SEVERITY_NONE, $this->etaService->determineSeverity(0.0));

        // 0 < hours <= 6 -> LOW
        $this->assertEquals(EtaService::SEVERITY_LOW, $this->etaService->determineSeverity(2.5));
        $this->assertEquals(EtaService::SEVERITY_LOW, $this->etaService->determineSeverity(6.0));

        // > 6 and <= 24 -> MEDIUM
        $this->assertEquals(EtaService::SEVERITY_MEDIUM, $this->etaService->determineSeverity(6.1));
        $this->assertEquals(EtaService::SEVERITY_MEDIUM, $this->etaService->determineSeverity(12.0));
        $this->assertEquals(EtaService::SEVERITY_MEDIUM, $this->etaService->determineSeverity(24.0));

        // > 24 -> HIGH
        $this->assertEquals(EtaService::SEVERITY_HIGH, $this->etaService->determineSeverity(24.1));
        $this->assertEquals(EtaService::SEVERITY_HIGH, $this->etaService->determineSeverity(48.0));
    }

    public function test_eta_service_integration_with_shipment_service_update(): void
    {
        config(['cargotrace.delay_threshold_hours' => 12]);

        $expected = Carbon::parse('2026-11-20 12:00:00');
        $initialEstimated = Carbon::parse('2026-11-20 12:00:00');

        $shipment = $this->createShipment($expected, $initialEstimated);
        $this->assertFalse($shipment->is_delayed);

        // Update estimated_arrival via ShipmentService to 30 hours later (> 24h -> HIGH severity delay)
        $delayedEstimated = Carbon::parse('2026-11-21 18:00:00');

        $this->shipmentService->updateShipment($shipment, [
            'estimated_arrival' => $delayedEstimated,
        ], null, $this->operator);

        $fresh = $shipment->fresh();
        $this->assertTrue($fresh->is_delayed);
        $this->assertEquals(30, $fresh->delay_hours);

        // Customer receives delay_alert notification
        $notification = Notification::where('user_id', $this->customer->id)
            ->where('type', 'delay_alert')
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString('30', $notification->message);
    }
}
