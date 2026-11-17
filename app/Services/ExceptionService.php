<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExceptionService
{
    public function __construct(
        protected TrackingService $trackingService,
        protected EtaService $etaService
    ) {}

    /**
     * Create a new exception for a shipment.
     */
    public function createException(Shipment $shipment, array $data, ?User $actor = null): ShipmentException
    {
        $validTypes = [
            ShipmentException::TYPE_CUSTOMS_HOLD,
            ShipmentException::TYPE_WEATHER_DELAY,
            ShipmentException::TYPE_PORT_CONGESTION,
            ShipmentException::TYPE_DAMAGED,
            ShipmentException::TYPE_MISROUTED,
            ShipmentException::TYPE_DOCUMENTATION_ISSUE,
        ];

        $validStatuses = [
            ShipmentException::STATUS_OPEN,
            ShipmentException::STATUS_INVESTIGATING,
            ShipmentException::STATUS_RESOLVED,
        ];

        $type = strtoupper($data['exception_type']);
        if (! in_array($type, $validTypes, true)) {
            throw new InvalidArgumentException("Tipe exception [{$type}] tidak valid.");
        }

        $status = strtoupper($data['status'] ?? ShipmentException::STATUS_OPEN);
        if (! in_array($status, $validStatuses, true)) {
            throw new InvalidArgumentException("Status exception [{$status}] tidak valid.");
        }

        return DB::transaction(function () use ($shipment, $data, $type, $status, $actor) {
            $detectedAt = isset($data['detected_at']) ? Carbon::parse($data['detected_at']) : now();
            $severity = strtoupper($data['severity'] ?? 'MEDIUM');
            $isResolved = ($status === ShipmentException::STATUS_RESOLVED);

            $exception = $shipment->exceptions()->create([
                'exception_type' => $type,
                'severity' => $severity,
                'status' => $status,
                'description' => $data['description'],
                'detected_at' => $detectedAt,
                'occurred_at' => $detectedAt,
                'is_resolved' => $isResolved,
                'resolved_at' => $isResolved ? now() : null,
                'resolution_notes' => $data['resolution_notes'] ?? null,
            ]);

            // Mark shipment as delayed if exception is active
            if (! $isResolved) {
                $shipment->update(['is_delayed' => true]);
            }

            // Record tracking checkpoint on the timeline
            $this->trackingService->addTrackingEvent(
                shipment: $shipment,
                status: "EXCEPTION: {$type}",
                locationId: $shipment->latestTrackingEvent?->location_id ?? $shipment->origin_id,
                description: "[Status: {$status} | Severity: {$severity}] {$data['description']}",
                eventTime: $detectedAt,
                user: $actor,
                updateShipmentStatus: false
            );

            // Audit log
            AuditLog::create([
                'user_id' => $actor?->id,
                'action' => 'CREATE_EXCEPTION',
                'auditable_type' => Shipment::class,
                'auditable_id' => $shipment->id,
                'new_values' => $exception->toArray(),
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent(),
            ]);

            return $exception->fresh();
        });
    }

    /**
     * Update or resolve an existing exception.
     */
    public function updateException(ShipmentException $exception, array $data, ?User $actor = null): ShipmentException
    {
        return DB::transaction(function () use ($exception, $data, $actor) {
            $shipment = $exception->shipment;
            $oldValues = $exception->toArray();

            $status = isset($data['status']) ? strtoupper($data['status']) : $exception->status;
            $isResolved = ($status === ShipmentException::STATUS_RESOLVED);

            $updateData = [
                'status' => $status,
                'is_resolved' => $isResolved,
            ];

            if (isset($data['severity'])) {
                $updateData['severity'] = strtoupper($data['severity']);
            }

            if (isset($data['description'])) {
                $updateData['description'] = $data['description'];
            }

            if (isset($data['resolution_notes'])) {
                $updateData['resolution_notes'] = $data['resolution_notes'];
            }

            if ($isResolved && empty($exception->resolved_at)) {
                $updateData['resolved_at'] = now();
            } elseif (! $isResolved) {
                $updateData['resolved_at'] = null;
            }

            $exception->update($updateData);

            // Record checkpoint on timeline
            $this->trackingService->addTrackingEvent(
                shipment: $shipment,
                status: "EXCEPTION_UPDATE: {$exception->exception_type}",
                locationId: $shipment->latestTrackingEvent?->location_id ?? $shipment->origin_id,
                description: "Exception [{$exception->exception_type}] status updated to [{$status}]. " . ($data['resolution_notes'] ?? ''),
                eventTime: now(),
                user: $actor,
                updateShipmentStatus: false
            );

            // If resolved, re-evaluate delay condition based on ETA vs Expected
            if ($isResolved) {
                $hasActiveExceptions = $shipment->exceptions()->where('is_resolved', false)->exists();
                if (! $hasActiveExceptions) {
                    $this->etaService->evaluateAndApply($shipment, $actor);
                }
            } else {
                $shipment->update(['is_delayed' => true]);
            }

            // Audit log
            AuditLog::create([
                'user_id' => $actor?->id,
                'action' => 'UPDATE_EXCEPTION',
                'auditable_type' => Shipment::class,
                'auditable_id' => $shipment->id,
                'old_values' => $oldValues,
                'new_values' => $exception->fresh()->toArray(),
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent(),
            ]);

            return $exception->fresh();
        });
    }
}
