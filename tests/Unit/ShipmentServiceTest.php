<?php

namespace Tests\Unit;

use App\Exceptions\InvalidStatusTransitionException;
use App\Services\ShipmentService;
use PHPUnit\Framework\TestCase;

class ShipmentServiceTest extends TestCase
{
    protected ShipmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ShipmentService();
    }

    public function test_valid_sequential_status_transitions(): void
    {
        $this->assertTrue($this->service->canTransition('CREATED', 'BOOKED'));
        $this->assertTrue($this->service->canTransition('BOOKED', 'PICKED_UP'));
        $this->assertTrue($this->service->canTransition('PICKED_UP', 'IN_TRANSIT'));
        $this->assertTrue($this->service->canTransition('IN_TRANSIT', 'DELIVERED'));
    }

    public function test_invalid_skip_forward_transitions(): void
    {
        $this->assertFalse($this->service->canTransition('CREATED', 'IN_TRANSIT'));
        $this->assertFalse($this->service->canTransition('CREATED', 'DELIVERED'));
        $this->assertFalse($this->service->canTransition('BOOKED', 'DELIVERED'));
    }

    public function test_invalid_backward_transitions(): void
    {
        $this->assertFalse($this->service->canTransition('IN_TRANSIT', 'PICKED_UP'));
        $this->assertFalse($this->service->canTransition('PICKED_UP', 'CREATED'));
        $this->assertFalse($this->service->canTransition('DELIVERED', 'IN_TRANSIT'));
        $this->assertFalse($this->service->canTransition('DELIVERED', 'CREATED'));
    }

    public function test_validate_transition_throws_exception_on_invalid_jump(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);
        $this->service->validateTransition('CREATED', 'DELIVERED');
    }

    public function test_validate_transition_throws_exception_when_already_delivered(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);
        $this->service->validateTransition('DELIVERED', 'IN_TRANSIT');
    }
}
