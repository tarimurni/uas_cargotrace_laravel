<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Customer can only view their own shipments (returns 403 Forbidden if not).
     */
    public function view(User $user, Shipment $shipment): bool
    {
        if ($user->isAdmin() || $user->isOperator()) {
            return true;
        }

        return $user->id === $shipment->customer_id;
    }

    /**
     * Determine whether the user can create models.
     * Only Admin and Operator can create shipments.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Determine whether the user can update the model.
     * Only Admin and Operator can update shipments.
     */
    public function update(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin();
    }
}
