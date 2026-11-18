<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;

class NotificationService
{
    /**
     * Notify customer when shipment status changes.
     */
    public function notifyStatusChange(
        Shipment $shipment,
        string $oldStatus,
        string $newStatus,
        ?string $notes = null
    ): ?Notification {
        if (! $shipment->customer_id) {
            return null;
        }

        $formattedStatus = str_replace('_', ' ', strtoupper($newStatus));
        $notesText = $notes ? " Catatan: {$notes}" : '';

        return Notification::create([
            'user_id' => $shipment->customer_id,
            'shipment_id' => $shipment->id,
            'title' => "Status Kargo {$shipment->shipment_number}: {$formattedStatus}",
            'message' => "Pengiriman {$shipment->shipment_number} kini berstatus {$formattedStatus}.{$notesText}",
            'type' => 'status_update',
            'is_read' => false,
        ]);
    }

    /**
     * Notify customer when shipment is marked delayed.
     */
    public function notifyDelay(
        Shipment $shipment,
        float $delayHours,
        string $severity
    ): ?Notification {
        if (! $shipment->customer_id) {
            return null;
        }

        return Notification::create([
            'user_id' => $shipment->customer_id,
            'shipment_id' => $shipment->id,
            'title' => "Peringatan Keterlambatan: {$shipment->shipment_number}",
            'message' => "Pengiriman {$shipment->shipment_number} terdeteksi mengalami estimasi keterlambatan +{$delayHours} jam [Tingkat: {$severity}]. Tim operasional sedang mengupayakan percepatan.",
            'type' => 'delay_alert',
            'is_read' => false,
        ]);
    }

    /**
     * Notify customer when an exception is reported for their shipment.
     */
    public function notifyException(
        Shipment $shipment,
        ShipmentException $exception
    ): ?Notification {
        if (! $shipment->customer_id) {
            return null;
        }

        $typeFormatted = str_replace('_', ' ', strtoupper($exception->exception_type));

        return Notification::create([
            'user_id' => $shipment->customer_id,
            'shipment_id' => $shipment->id,
            'title' => "Kendala Operasional ({$typeFormatted}): {$shipment->shipment_number}",
            'message' => "Terdeteksi kendala [{$typeFormatted} - {$exception->severity}] pada pengiriman {$shipment->shipment_number}: {$exception->description}",
            'type' => 'exception_alert',
            'is_read' => false,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Notification $notification): Notification
    {
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $notification;
    }

    /**
     * Mark all notifications of a user as read.
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}
