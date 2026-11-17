<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;

class EtaService
{
    public const SEVERITY_NONE = 'NONE';
    public const SEVERITY_LOW = 'LOW';
    public const SEVERITY_MEDIUM = 'MEDIUM';
    public const SEVERITY_HIGH = 'HIGH';

    /**
     * Get the delay threshold in hours from configuration.
     */
    public function getThresholdHours(): int
    {
        return (int) config('cargotrace.delay_threshold_hours', 12);
    }

    /**
     * Calculate delay hours by comparing estimated_arrival with expected_arrival.
     */
    public function calculateDelayHours(?Carbon $expectedArrival, ?Carbon $estimatedArrival): float
    {
        if (! $expectedArrival || ! $estimatedArrival) {
            return 0.0;
        }

        if ($estimatedArrival->lessThanOrEqualTo($expectedArrival)) {
            return 0.0;
        }

        $minutes = $expectedArrival->diffInMinutes($estimatedArrival, false);

        return max(0.0, round($minutes / 60, 2));
    }

    /**
     * Determine severity level based on delay duration:
     * - LOW: 0–6 jam
     * - MEDIUM: >6–24 jam
     * - HIGH: >24 jam
     */
    public function determineSeverity(float $delayHours): string
    {
        if ($delayHours <= 0) {
            return self::SEVERITY_NONE;
        }

        if ($delayHours <= 6.0) {
            return self::SEVERITY_LOW;
        }

        if ($delayHours <= 24.0) {
            return self::SEVERITY_MEDIUM;
        }

        return self::SEVERITY_HIGH;
    }

    /**
     * Analyze delay metrics for a given shipment.
     */
    public function analyzeShipment(Shipment $shipment): array
    {
        $delayHours = $this->calculateDelayHours(
            $shipment->expected_arrival,
            $shipment->estimated_arrival
        );

        $threshold = $this->getThresholdHours();
        $severity = $this->determineSeverity($delayHours);
        $isDelayed = ($delayHours >= $threshold);

        return [
            'expected_arrival' => $shipment->expected_arrival,
            'estimated_arrival' => $shipment->estimated_arrival,
            'delay_hours' => $delayHours,
            'threshold_hours' => $threshold,
            'is_delayed' => $isDelayed,
            'severity' => $severity,
        ];
    }

    /**
     * Evaluate shipment arrival dates and deterministically apply delay flags.
     */
    public function evaluateAndApply(Shipment $shipment, ?User $actor = null): Shipment
    {
        $analysis = $this->analyzeShipment($shipment);

        $oldDelayed = $shipment->is_delayed;
        $oldHours = $shipment->delay_hours;

        $newDelayed = $analysis['is_delayed'];
        $newHours = (int) ceil($analysis['delay_hours']);

        if ($oldDelayed !== $newDelayed || $oldHours !== $newHours) {
            $shipment->update([
                'is_delayed' => $newDelayed,
                'delay_hours' => $newHours,
            ]);

            // Audit log
            AuditLog::create([
                'user_id' => $actor?->id,
                'action' => 'EVALUATE_ETA_DELAY',
                'auditable_type' => Shipment::class,
                'auditable_id' => $shipment->id,
                'old_values' => [
                    'is_delayed' => $oldDelayed,
                    'delay_hours' => $oldHours,
                ],
                'new_values' => [
                    'is_delayed' => $newDelayed,
                    'delay_hours' => $newHours,
                    'severity' => $analysis['severity'],
                    'threshold_hours' => $analysis['threshold_hours'],
                ],
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent(),
            ]);
        }

        return $shipment->fresh();
    }
}
