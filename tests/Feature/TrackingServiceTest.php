<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\TrackingService;
use Tests\TestCase;

class TrackingServiceTest extends TestCase
{
    protected TrackingService $trackingService;
    protected User $user;
    protected Shipment $shipment;
    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trackingService = app(TrackingService::class);

        $this->user = User::firstOrCreate(
            ['email' => 'operator@cargotrace.com'],
            ['name' => 'Operator Tracking Test', 'password' => bcrypt('password'), 'role' => 'operator']
        );

        $carrier = Carrier::firstOrCreate(
            ['code' => 'TEST_TRK_CR'],
            ['name' => 'Tracking Test Carrier', 'service_type' => 'sea', 'is_active' => true]
        );

        $origin = Location::firstOrCreate(
            ['code' => 'TEST_TRK_ORG'],
            ['name' => 'Tanjung Priok Test', 'city' => 'Jakarta', 'country' => 'Indonesia', 'latitude' => -6.1017400, 'longitude' => 106.8837700]
        );

        $destination = Location::firstOrCreate(
            ['code' => 'TEST_TRK_DST'],
            ['name' => 'PSA Singapore Test', 'city' => 'Singapore', 'country' => 'Singapore', 'latitude' => 1.2644000, 'longitude' => 103.8400000]
        );

        $this->location = $origin;

        $this->shipment = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-TRACK-999'],
            [
                'customer_id' => $this->user->id,
                'carrier_id' => $carrier->id,
                'origin_id' => $origin->id,
                'destination_id' => $destination->id,
                'cargo_type' => 'General Machinery',
                'weight' => 2000.00,
                'volume' => 10.00,
                'expected_departure' => now(),
                'expected_arrival' => now()->addDays(2),
                'estimated_arrival' => now()->addDays(2),
                'current_status' => 'CREATED',
            ]
        );
    }

    public function test_add_tracking_event_persists_without_overwriting_older_events(): void
    {
        $initialCount = $this->shipment->trackingEvents()->count();

        $event1 = $this->trackingService->addTrackingEvent(
            shipment: $this->shipment,
            status: 'BOOKED',
            locationId: $this->location->id,
            description: 'Booking confirmed by carrier',
            user: $this->user
        );

        $event2 = $this->trackingService->addTrackingEvent(
            shipment: $this->shipment,
            status: 'PICKED_UP',
            locationId: $this->location->id,
            description: 'Cargo picked up from shipper warehouse',
            user: $this->user
        );

        $this->assertEquals($initialCount + 2, $this->shipment->trackingEvents()->count());
        $this->assertEquals('PICKED_UP', $this->shipment->fresh()->current_status);
        $this->assertEquals(-6.1017400, (float) $event1->latitude);
        $this->assertEquals(106.8837700, (float) $event1->longitude);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'RECORD_TRACKING_EVENT',
            'auditable_type' => Shipment::class,
            'auditable_id' => $this->shipment->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_combined_timeline_differentiates_normal_and_exception_events(): void
    {
        $this->trackingService->addTrackingEvent(
            shipment: $this->shipment,
            status: 'IN_TRANSIT',
            locationId: $this->location->id,
            description: 'Vessel underway',
            user: $this->user
        );

        $this->trackingService->recordException(
            shipment: $this->shipment,
            exceptionType: 'Weather Delay',
            severity: 'high',
            description: 'Tropical storm warning, vessel anchored',
            user: $this->user
        );

        $timeline = $this->trackingService->getCombinedTimeline($this->shipment);

        $this->assertTrue($timeline->contains(fn ($item) => $item->is_exception === true));
        $this->assertTrue($timeline->contains(fn ($item) => $item->is_exception === false));
    }
}
