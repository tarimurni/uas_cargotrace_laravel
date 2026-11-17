<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use App\Services\ExceptionService;
use Tests\TestCase;

class ExceptionServiceTest extends TestCase
{
    protected ExceptionService $exceptionService;
    protected User $user;
    protected Shipment $shipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionService = app(ExceptionService::class);

        $this->user = User::firstOrCreate(
            ['email' => 'operator_exc@cargotrace.com'],
            ['name' => 'Operator Exc Test', 'password' => bcrypt('password'), 'role' => 'operator']
        );

        $carrier = Carrier::firstOrCreate(
            ['code' => 'EXC_CR'],
            ['name' => 'Exc Test Carrier', 'service_type' => 'sea', 'is_active' => true]
        );

        $origin = Location::firstOrCreate(
            ['code' => 'EXC_ORG'],
            ['name' => 'Origin Exc Test', 'city' => 'Jakarta', 'country' => 'Indonesia', 'latitude' => -6.1, 'longitude' => 106.8]
        );

        $destination = Location::firstOrCreate(
            ['code' => 'EXC_DST'],
            ['name' => 'Dest Exc Test', 'city' => 'Singapore', 'country' => 'Singapore', 'latitude' => 1.2, 'longitude' => 103.8]
        );

        $this->shipment = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-EXC-777'],
            [
                'customer_id' => $this->user->id,
                'carrier_id' => $carrier->id,
                'origin_id' => $origin->id,
                'destination_id' => $destination->id,
                'cargo_type' => 'Electronics',
                'weight' => 1500.00,
                'volume' => 8.00,
                'expected_departure' => now(),
                'expected_arrival' => now()->addDays(2),
                'estimated_arrival' => now()->addDays(2),
                'current_status' => 'IN_TRANSIT',
                'is_delayed' => false,
            ]
        );
    }

    public function test_create_exception_records_exception_and_marks_delayed(): void
    {
        $exception = $this->exceptionService->createException(
            shipment: $this->shipment,
            data: [
                'exception_type' => ShipmentException::TYPE_PORT_CONGESTION,
                'severity' => 'HIGH',
                'status' => ShipmentException::STATUS_OPEN,
                'description' => 'Berth congestion at Singapore terminal',
            ],
            actor: $this->user
        );

        $this->assertEquals(ShipmentException::TYPE_PORT_CONGESTION, $exception->exception_type);
        $this->assertEquals('OPEN', $exception->status);
        $this->assertFalse($exception->is_resolved);
        $this->assertTrue($this->shipment->fresh()->is_delayed);

        // Check audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CREATE_EXCEPTION',
            'auditable_type' => Shipment::class,
            'auditable_id' => $this->shipment->id,
        ]);
    }

    public function test_update_exception_to_resolved_sets_resolved_at(): void
    {
        $exception = $this->exceptionService->createException(
            shipment: $this->shipment,
            data: [
                'exception_type' => ShipmentException::TYPE_CUSTOMS_HOLD,
                'severity' => 'CRITICAL',
                'status' => ShipmentException::STATUS_OPEN,
                'description' => 'Customs hold at checkpoint',
            ],
            actor: $this->user
        );

        $updated = $this->exceptionService->updateException(
            exception: $exception,
            data: [
                'status' => ShipmentException::STATUS_RESOLVED,
                'resolution_notes' => 'Customs documents cleared and approved.',
            ],
            actor: $this->user
        );

        $this->assertEquals('RESOLVED', $updated->status);
        $this->assertTrue($updated->is_resolved);
        $this->assertNotNull($updated->resolved_at);

        // Check audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'UPDATE_EXCEPTION',
            'auditable_type' => Shipment::class,
            'auditable_id' => $this->shipment->id,
        ]);
    }
}
