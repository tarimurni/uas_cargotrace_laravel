<?php

namespace App\Services;

use App\Exceptions\InvalidStatusTransitionException;
use App\Models\AuditLog;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    protected EtaService $etaService;
    protected NotificationService $notificationService;

    public function __construct(
        ?EtaService $etaService = null,
        ?NotificationService $notificationService = null
    ) {
        $this->etaService = $etaService ?? new EtaService();
        $this->notificationService = $notificationService ?? new NotificationService();
    }

    public const STATUS_CREATED = 'CREATED';
    public const STATUS_BOOKED = 'BOOKED';
    public const STATUS_PICKED_UP = 'PICKED_UP';
    public const STATUS_IN_TRANSIT = 'IN_TRANSIT';
    public const STATUS_DELIVERED = 'DELIVERED';

    /**
     * Map of strictly valid status transitions.
     * Alur: CREATED -> BOOKED -> PICKED_UP -> IN_TRANSIT -> DELIVERED
     */
    protected const VALID_TRANSITIONS = [
        self::STATUS_CREATED => [self::STATUS_BOOKED],
        self::STATUS_BOOKED => [self::STATUS_PICKED_UP],
        self::STATUS_PICKED_UP => [self::STATUS_IN_TRANSIT],
        self::STATUS_IN_TRANSIT => [self::STATUS_DELIVERED],
        self::STATUS_DELIVERED => [], // Terminal: Tidak boleh ada transisi keluar
    ];

    /**
     * Normalize status string to uppercase canonical format.
     */
    public function normalizeStatus(string $status): string
    {
        $upper = strtoupper(trim($status));

        return match ($upper) {
            'PENDING' => self::STATUS_CREATED,
            'CUSTOMS_CLEARANCE', 'DELAYED' => self::STATUS_IN_TRANSIT,
            default => $upper,
        };
    }

    /**
     * Check if a transition between two statuses is allowed.
     */
    public function canTransition(string $currentStatus, string $newStatus): bool
    {
        $current = $this->normalizeStatus($currentStatus);
        $new = $this->normalizeStatus($newStatus);

        if ($current === $new) {
            return true;
        }

        if ($current === self::STATUS_DELIVERED) {
            return false;
        }

        $allowed = self::VALID_TRANSITIONS[$current] ?? [];

        return in_array($new, $allowed, true);
    }

    /**
     * Validate transition and throw exception if invalid.
     *
     * @throws InvalidStatusTransitionException
     */
    public function validateTransition(string $currentStatus, string $newStatus): void
    {
        $current = $this->normalizeStatus($currentStatus);
        $new = $this->normalizeStatus($newStatus);

        if ($current === $new) {
            return;
        }

        if ($current === self::STATUS_DELIVERED) {
            throw new InvalidStatusTransitionException($current, $new);
        }

        if (! $this->canTransition($current, $new)) {
            throw new InvalidStatusTransitionException($current, $new);
        }
    }

    /**
     * Execute a status transition on a shipment.
     *
     * @throws InvalidStatusTransitionException
     */
    public function transitionStatus(Shipment $shipment, string $newStatus, User $user, ?string $notes = null): Shipment
    {
        $targetStatus = $this->normalizeStatus($newStatus);
        $currentStatus = $this->normalizeStatus($shipment->current_status);

        $this->validateTransition($currentStatus, $targetStatus);

        return DB::transaction(function () use ($shipment, $currentStatus, $targetStatus, $user, $notes) {
            $oldValues = $shipment->toArray();

            $updateData = [
                'current_status' => $targetStatus,
            ];

            if ($targetStatus === self::STATUS_DELIVERED && empty($shipment->actual_arrival)) {
                $updateData['actual_arrival'] = now();
            }

            if (in_array($targetStatus, [self::STATUS_PICKED_UP, self::STATUS_IN_TRANSIT]) && empty($shipment->actual_departure)) {
                $updateData['actual_departure'] = now();
            }

            if ($notes) {
                $updateData['notes'] = $notes;
            }

            $shipment->update($updateData);

            // Audit log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'STATUS_TRANSITION',
                'auditable_type' => Shipment::class,
                'auditable_id' => $shipment->id,
                'old_values' => ['current_status' => $currentStatus],
                'new_values' => ['current_status' => $targetStatus, 'notes' => $notes],
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent(),
            ]);

            // In-App Notification to Customer
            $this->notificationService->notifyStatusChange($shipment, $currentStatus, $targetStatus, $notes);

            return $shipment->fresh();
        });
    }

    /**
     * Create a new shipment and its manifest items.
     */
    public function createShipment(array $data, array $items, User $user): Shipment
    {
        return DB::transaction(function () use ($data, $items, $user) {
            // Set initial status to CREATED if not specified
            if (empty($data['current_status'])) {
                $data['current_status'] = self::STATUS_CREATED;
            } else {
                $data['current_status'] = $this->normalizeStatus($data['current_status']);
            }

            $shipment = Shipment::create($data);

            foreach ($items as $item) {
                $shipment->items()->create([
                    'item_name' => $item['item_name'],
                    'item_code' => $item['item_code'] ?? null,
                    'quantity' => $item['quantity'],
                    'weight' => $item['weight'],
                    'volume' => $item['volume'] ?? null,
                    'declared_value' => $item['declared_value'] ?? null,
                ]);
            }

            // Create initial checkpoint
            $shipment->trackingEvents()->create([
                'location_id' => $shipment->origin_id,
                'status' => 'Shipment Booking Created',
                'event_time' => now(),
                'description' => 'Shipment order registered in system by ' . $user->name,
                'created_by' => $user->id,
            ]);

            // Audit log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'CREATE_SHIPMENT',
                'auditable_type' => Shipment::class,
                'auditable_id' => $shipment->id,
                'new_values' => $shipment->load('items')->toArray(),
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent(),
            ]);

            $this->etaService->evaluateAndApply($shipment, $user);

            return $shipment->fresh(['items', 'origin', 'destination', 'carrier', 'customer']);
        });
    }

    /**
     * Update an existing shipment and sync items.
     */
    public function updateShipment(Shipment $shipment, array $data, ?array $items, User $user): Shipment
    {
        // If status is changing, enforce valid transition
        if (isset($data['current_status'])) {
            $normalizedNewStatus = $this->normalizeStatus($data['current_status']);
            $normalizedCurrent = $this->normalizeStatus($shipment->current_status);

            if ($normalizedNewStatus !== $normalizedCurrent) {
                $this->validateTransition($normalizedCurrent, $normalizedNewStatus);
                $data['current_status'] = $normalizedNewStatus;
            }
        }

        return DB::transaction(function () use ($shipment, $data, $items, $user) {
            $oldValues = $shipment->load('items')->toArray();

            $shipment->update($data);

            if (is_array($items)) {
                $submittedIds = collect($items)->pluck('id')->filter()->toArray();
                $shipment->items()->whereNotIn('id', $submittedIds)->delete();

                foreach ($items as $item) {
                    if (! empty($item['id'])) {
                        ShipmentItem::where('id', $item['id'])
                            ->where('shipment_id', $shipment->id)
                            ->update([
                                'item_name' => $item['item_name'],
                                'item_code' => $item['item_code'] ?? null,
                                'quantity' => $item['quantity'],
                                'weight' => $item['weight'],
                                'volume' => $item['volume'] ?? null,
                                'declared_value' => $item['declared_value'] ?? null,
                            ]);
                    } else {
                        $shipment->items()->create([
                            'item_name' => $item['item_name'],
                            'item_code' => $item['item_code'] ?? null,
                            'quantity' => $item['quantity'],
                            'weight' => $item['weight'],
                            'volume' => $item['volume'] ?? null,
                            'declared_value' => $item['declared_value'] ?? null,
                        ]);
                    }
                }
            }

            // Audit log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'UPDATE_SHIPMENT',
                'auditable_type' => Shipment::class,
                'auditable_id' => $shipment->id,
                'old_values' => $oldValues,
                'new_values' => $shipment->fresh()->load('items')->toArray(),
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent(),
            ]);

            $this->etaService->evaluateAndApply($shipment, $user);

            // In-App Notification if status changed
            if (isset($data['current_status']) && ($oldValues['current_status'] ?? '') !== $data['current_status']) {
                $this->notificationService->notifyStatusChange(
                    $shipment,
                    $oldValues['current_status'] ?? 'UNKNOWN',
                    $data['current_status'],
                    $data['notes'] ?? null
                );
            }

            return $shipment->fresh(['items', 'origin', 'destination', 'carrier', 'customer']);
        });
    }

    /**
     * Delete shipment and record audit log.
     */
    public function deleteShipment(Shipment $shipment, User $user): bool
    {
        return DB::transaction(function () use ($shipment, $user) {
            $oldValues = $shipment->load('items')->toArray();
            $shipmentId = $shipment->id;

            $deleted = $shipment->delete();

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'DELETE_SHIPMENT',
                'auditable_type' => Shipment::class,
                'auditable_id' => $shipmentId,
                'old_values' => $oldValues,
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent(),
            ]);

            return $deleted;
        });
    }
}
