<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shipment_id' => $this->shipment_id,
            'item_name' => $this->item_name,
            'item_code' => $this->item_code,
            'quantity' => (int) $this->quantity,
            'weight' => (float) $this->weight,
            'volume' => $this->volume ? (float) $this->volume : null,
            'declared_value' => $this->declared_value ? (float) $this->declared_value : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
