<?php

namespace App\Exceptions;

use DomainException;

class InvalidStatusTransitionException extends DomainException
{
    public function __construct(string $fromStatus, string $toStatus, string $reason = '')
    {
        $message = "Transisi status tidak valid dari [{$fromStatus}] ke [{$toStatus}].";

        if ($fromStatus === 'DELIVERED') {
            $message = "Pengiriman sudah berstatus DELIVERED dan tidak dapat diubah ke status lain.";
        } elseif (! empty($reason)) {
            $message .= " Alasan: {$reason}";
        } else {
            $message .= " Alur status harus berurutan: CREATED -> BOOKED -> PICKED_UP -> IN_TRANSIT -> DELIVERED.";
        }

        parent::__construct($message, 422);
    }
}
