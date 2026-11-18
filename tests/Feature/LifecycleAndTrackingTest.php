<?php

namespace Tests\Feature;

use App\Exceptions\InvalidStatusTransitionException;
use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\TrackingEvent;
use App\Models\User;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LifecycleAndTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected ShipmentService $shipmentService;
    protected TrackingService $trackingService;
    protected User $operator;
    protected User $customer;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shipmentService = app(ShipmentService::class);
        $this->trackingService = app(TrackingService::class);

        $this->operator = User::factory()->create([
            'email' => 'operator_lifecycle@cargotrace.com',
            'role' => 'operator',
        ]);

        $this->customer = User::factory()->create([
            'email' => 'customer_lifecycle@cargotrace.com',
            'role' => 'customer',
        ]);

        $this->carrier = Carrier::create([
            'code' => 'CR_LC',
            'name' => 'Lifecycle Carrier',
            'service_type' => 'sea',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'LOC_ORG_LC',
            'name' => 'Tanjung Priok',
            'type' => 'seaport',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'latitude' => -6.10,
            'longitude' => 106.88,
        ]);

        $this->destination = Location::create([
            'code' => 'LOC_DST_LC',
            'name' => 'Jurong Port',
            'type' => 'seaport',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'latitude' => 1.30,
            'longitude' => 103.72,
        ]);
    }

    protected function createBaseShipment(string $status = 'CREATED'): Shipment
    {
        return Shipment::create([
            'shipment_number' => 'SHP-LC-' . uniqid(),
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Industrial Equipment',
            'weight' => 500,
            'volume' => 20,
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(5),
            'estimated_arrival' => now()->addDays(5),
            'current_status' => $status,
            'is_delayed' => false,
            'delay_hours' => 0,
        ]);
    }

    public function test_valid_status_lifecycle_transitions_succeed_sequentially(): void
    {
        $shipment = $this->createBaseShipment('CREATED');

        // 1. CREATED -> BOOKED
        $shipment = $this->shipmentService->transitionStatus($shipment, 'BOOKED', $this->operator);
        $this->assertEquals('BOOKED', $shipment->current_status);

        // 2. BOOKED -> PICKED_UP
        $shipment = $this->shipmentService->transitionStatus($shipment, 'PICKED_UP', $this->operator);
        $this->assertEquals('PICKED_UP', $shipment->current_status);
        $this->assertNotNull($shipment->actual_departure);

        // 3. PICKED_UP -> IN_TRANSIT
        $shipment = $this->shipmentService->transitionStatus($shipment, 'IN_TRANSIT', $this->operator);
        $this->assertEquals('IN_TRANSIT', $shipment->current_status);

        // 4. IN_TRANSIT -> DELIVERED
        $shipment = $this->shipmentService->transitionStatus($shipment, 'DELIVERED', $this->operator);
        $this->assertEquals('DELIVERED', $shipment->current_status);
        $this->assertNotNull($shipment->actual_arrival);

        // Verify audit logs were created for each transition
        $logs = AuditLog::where('auditable_type', Shipment::class)
            ->where('auditable_id', $shipment->id)
            ->where('action', 'STATUS_TRANSITION')
            ->get();

        $this->assertCount(4, $logs);
    }

    public function test_invalid_status_transition_jumping_forward_is_rejected(): void
    {
        $shipment = $this->createBaseShipment('CREATED');

        // Service level: expect InvalidStatusTransitionException
        $this->expectException(InvalidStatusTransitionException::class);
        $this->shipmentService->transitionStatus($shipment, 'DELIVERED', $this->operator);
    }

    public function test_invalid_status_transition_via_api_returns_422(): void
    {
        $shipment = $this->createBaseShipment('CREATED');

        // API level: attempting to jump directly to IN_TRANSIT returns 422
        $response = $this->actingAs($this->operator, 'sanctum')
            ->putJson(route('api.shipments.update', $shipment), [
                'current_status' => 'IN_TRANSIT',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message']);

        $this->assertEquals('CREATED', $shipment->fresh()->current_status);
    }

    public function test_invalid_status_transition_backward_is_rejected(): void
    {
        $shipment = $this->createBaseShipment('IN_TRANSIT');

        $this->expectException(InvalidStatusTransitionException::class);
        $this->shipmentService->transitionStatus($shipment, 'BOOKED', $this->operator);
    }

    public function test_transition_from_terminal_delivered_status_is_rejected(): void
    {
        $shipment = $this->createBaseShipment('DELIVERED');

        $this->expectException(InvalidStatusTransitionException::class);
        $this->shipmentService->transitionStatus($shipment, 'IN_TRANSIT', $this->operator);
    }

    public function test_tracking_events_are_persisted_historically_without_overwriting(): void
    {
        $shipment = $this->createBaseShipment('BOOKED');

        // Add Event 1: Cargo picked up
        $event1 = $this->trackingService->addTrackingEvent(
            $shipment,
            'PICKED_UP',
            $this->origin->id,
            -6.10,
            106.88,
            'Cargo picked up from shipper warehouse in Jakarta',
            now()->subHours(5),
            $this->operator
        );

        $this->assertEquals('PICKED_UP', $shipment->fresh()->current_status);

        // Add Event 2: In Transit through Sunda Strait
        $event2 = $this->trackingService->addTrackingEvent(
            $shipment,
            'IN_TRANSIT',
            null,
            -5.90,
            105.80,
            'Vessel transiting Sunda Strait',
            now()->subHours(2),
            $this->operator
        );

        $this->assertEquals('IN_TRANSIT', $shipment->fresh()->current_status);

        // Verify both events persist and neither was overwritten
        $allEvents = TrackingEvent::where('shipment_id', $shipment->id)->get();
        $this->assertCount(2, $allEvents);

        $this->assertDatabaseHas('tracking_events', [
            'id' => $event1->id,
            'status' => 'PICKED_UP',
            'description' => 'Cargo picked up from shipper warehouse in Jakarta',
        ]);

        $this->assertDatabaseHas('tracking_events', [
            'id' => $event2->id,
            'status' => 'IN_TRANSIT',
            'description' => 'Vessel transiting Sunda Strait',
        ]);

        // Verify audit logs for both tracking event creations
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'RECORD_TRACKING_EVENT',
            'auditable_type' => Shipment::class,
            'auditable_id' => $shipment->id,
        ]);
    }

    public function test_combined_timeline_differentiates_normal_and_exception_events(): void
    {
        $shipment = $this->createBaseShipment('IN_TRANSIT');

        // 1. Normal tracking event
        $this->trackingService->addTrackingEvent(
            $shipment,
            'IN_TRANSIT',
            null,
            -6.0,
            106.0,
            'Vessel en route',
            now()->subHours(4),
            $this->operator
        );

        // 2. Exception event
        $this->trackingService->recordException(
            shipment: $shipment,
            exceptionType: 'Weather Delay',
            severity: 'high',
            description: 'Tropical storm warning, vessel anchored',
            user: $this->operator
        );

        $timeline = $this->trackingService->getCombinedTimeline($shipment);

        $this->assertCount(2, $timeline);

        $normalItem = $timeline->firstWhere('is_exception', false);
        $exceptionItem = $timeline->firstWhere('is_exception', true);

        $this->assertNotNull($normalItem);
        $this->assertEquals('IN_TRANSIT', $normalItem->status);

        $this->assertNotNull($exceptionItem);
        $this->assertTrue($exceptionItem->is_exception);
    }
}
