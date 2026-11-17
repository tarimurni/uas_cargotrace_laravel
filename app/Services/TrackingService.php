<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrackingService
{
    public function __construct(
        protected ShipmentService $shipmentService
    ) {}

    /**
     * Add a new tracking event without overwriting older events,
     * and update shipment's current status and timestamps.
     */
    public function addTrackingEvent(
        Shipment $shipment,
        string $status,
        ?int $locationId = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $description = null,
        ?Carbon $eventTime = null,
        ?User $user = null,
        bool $updateShipmentStatus = true
    ): TrackingEvent {
        return DB::transaction(function () use (
            $shipment,
            $status,
            $locationId,
            $latitude,
            $longitude,
            $description,
            $eventTime,
            $user,
            $updateShipmentStatus
        ) {
            // Auto-fill coordinates from Location master data if not provided
            if ($locationId && ($latitude === null || $longitude === null)) {
                $location = Location::find($locationId);
                if ($location) {
                    $latitude ??= (float) $location->latitude;
                    $longitude ??= (float) $location->longitude;
                }
            }

            $eventTimestamp = $eventTime ?? now();

            // Create new tracking event
            $event = $shipment->trackingEvents()->create([
                'location_id' => $locationId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'status' => $status,
                'event_time' => $eventTimestamp,
                'description' => $description,
                'created_by' => $user?->id,
            ]);

            // Update shipment's current status if requested
            if ($updateShipmentStatus) {
                $normalized = $this->shipmentService->normalizeStatus($status);
                $updateData = ['current_status' => $normalized];

                if ($normalized === ShipmentService::STATUS_DELIVERED && empty($shipment->actual_arrival)) {
                    $updateData['actual_arrival'] = $eventTimestamp;
                }

                if (in_array($normalized, [ShipmentService::STATUS_PICKED_UP, ShipmentService::STATUS_IN_TRANSIT]) && empty($shipment->actual_departure)) {
                    $updateData['actual_departure'] = $eventTimestamp;
                }

                $shipment->update($updateData);
            }

            // Record audit log
            AuditLog::create([
                'user_id' => $user?->id,
                'action' => 'RECORD_TRACKING_EVENT',
                'auditable_type' => Shipment::class,
                'auditable_id' => $shipment->id,
                'new_values' => [
                    'tracking_event_id' => $event->id,
                    'status' => $status,
                    'location_id' => $locationId,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'event_time' => $eventTimestamp->toDateTimeString(),
                    'description' => $description,
                    'shipment_current_status' => $shipment->current_status,
                ],
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent(),
            ]);

            return $event->fresh(['location', 'creator']);
        });
    }

    /**
     * Record an operational exception or delay incident for the shipment.
     */
    public function recordException(
        Shipment $shipment,
        string $exceptionType,
        string $severity,
        string $description,
        ?Carbon $occurredAt = null,
        ?User $user = null
    ): ShipmentException {
        return DB::transaction(function () use (
            $shipment,
            $exceptionType,
            $severity,
            $description,
            $occurredAt,
            $user
        ) {
            $occurredTimestamp = $occurredAt ?? now();

            $exception = $shipment->exceptions()->create([
                'exception_type' => $exceptionType,
                'severity' => $severity,
                'description' => $description,
                'occurred_at' => $occurredTimestamp,
                'is_resolved' => false,
            ]);

            // Mark shipment as delayed
            $shipment->update(['is_delayed' => true]);

            // Automatically record an exception tracking event on the timeline
            $this->addTrackingEvent(
                shipment: $shipment,
                status: "EXCEPTION: {$exceptionType}",
                locationId: $shipment->latestTrackingEvent?->location_id ?? $shipment->origin_id,
                description: "[{$severity}] {$description}",
                eventTime: $occurredTimestamp,
                user: $user,
                updateShipmentStatus: false
            );

            // Audit log
            AuditLog::create([
                'user_id' => $user?->id,
                'action' => 'RECORD_SHIPMENT_EXCEPTION',
                'auditable_type' => Shipment::class,
                'auditable_id' => $shipment->id,
                'new_values' => [
                    'exception_id' => $exception->id,
                    'exception_type' => $exceptionType,
                    'severity' => $severity,
                    'description' => $description,
                ],
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent(),
            ]);

            return $exception;
        });
    }

    /**
     * Get a sorted combined chronological timeline including tracking checkpoints and exceptions.
     */
    public function getCombinedTimeline(Shipment $shipment): Collection
    {
        $events = $shipment->trackingEvents()
            ->with(['location', 'creator'])
            ->get()
            ->map(function ($event) {
                return (object) [
                    'type' => str_starts_with($event->status, 'EXCEPTION') ? 'exception' : 'checkpoint',
                    'id' => $event->id,
                    'status' => $event->status,
                    'timestamp' => $event->event_time,
                    'location' => $event->location,
                    'latitude' => $event->latitude ?? $event->location?->latitude,
                    'longitude' => $event->longitude ?? $event->location?->longitude,
                    'description' => $event->description,
                    'creator' => $event->creator,
                    'is_exception' => str_starts_with($event->status, 'EXCEPTION') || str_contains(strtolower($event->status), 'delay') || str_contains(strtolower($event->status), 'hold'),
                ];
            });

        return $events->sortBy('timestamp')->values();
    }
}
