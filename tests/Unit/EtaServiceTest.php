<?php

namespace Tests\Unit;

use App\Services\EtaService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class EtaServiceTest extends TestCase
{
    protected EtaService $etaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->etaService = new EtaService();
    }

    public function test_calculate_delay_hours_when_on_time(): void
    {
        $expected = Carbon::parse('2026-11-20 10:00:00');
        $estimated = Carbon::parse('2026-11-20 09:30:00');

        $delay = $this->etaService->calculateDelayHours($expected, $estimated);
        $this->assertEquals(0.0, $delay);
    }

    public function test_calculate_delay_hours_when_delayed(): void
    {
        $expected = Carbon::parse('2026-11-20 10:00:00');
        $estimated = Carbon::parse('2026-11-20 18:30:00'); // 8.5 hours delay

        $delay = $this->etaService->calculateDelayHours($expected, $estimated);
        $this->assertEquals(8.5, $delay);
    }

    public function test_determine_severity_levels(): void
    {
        // 0 hours -> NONE
        $this->assertEquals(EtaService::SEVERITY_NONE, $this->etaService->determineSeverity(0));

        // 0-6 hours -> LOW
        $this->assertEquals(EtaService::SEVERITY_LOW, $this->etaService->determineSeverity(2.5));
        $this->assertEquals(EtaService::SEVERITY_LOW, $this->etaService->determineSeverity(6.0));

        // >6 - 24 hours -> MEDIUM
        $this->assertEquals(EtaService::SEVERITY_MEDIUM, $this->etaService->determineSeverity(6.1));
        $this->assertEquals(EtaService::SEVERITY_MEDIUM, $this->etaService->determineSeverity(18.0));
        $this->assertEquals(EtaService::SEVERITY_MEDIUM, $this->etaService->determineSeverity(24.0));

        // > 24 hours -> HIGH
        $this->assertEquals(EtaService::SEVERITY_HIGH, $this->etaService->determineSeverity(24.1));
        $this->assertEquals(EtaService::SEVERITY_HIGH, $this->etaService->determineSeverity(48.0));
    }
}
