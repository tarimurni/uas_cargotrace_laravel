<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
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
            'shipment_number' => $this->shipment_number,
            'customer_id' => $this->customer_id,
            'carrier_id' => $this->carrier_id,
            'origin_id' => $this->origin_id,
            'destination_id' => $this->destination_id,
            'cargo_type' => $this->cargo_type,
            'weight' => (float) $this->weight,
            'volume' => $this->volume ? (float) $this->volume : null,
            'current_status' => $this->current_status,
            'is_delayed' => (bool) $this->is_delayed,
            'delay_hours' => $this->delay_hours ? (float) $this->delay_hours : 0.0,
            'expected_departure' => $this->expected_departure?->toIso8601String(),
            'actual_departure' => $this->actual_departure?->toIso8601String(),
            'expected_arrival' => $this->expected_arrival?->toIso8601String(),
            'estimated_arrival' => $this->estimated_arrival?->toIso8601String(),
            'actual_arrival' => $this->actual_arrival?->toIso8601String(),
            'notes' => $this->notes,
            'customer' => new UserResource($this->whenLoaded('customer')),
            'carrier' => new CarrierResource($this->whenLoaded('carrier')),
            'origin' => new LocationResource($this->whenLoaded('origin')),
            'destination' => new LocationResource($this->whenLoaded('destination')),
            'items' => ShipmentItemResource::collection($this->whenLoaded('items')),
            'tracking_events' => TrackingEventResource::collection($this->whenLoaded('trackingEvents')),
            'exceptions' => ShipmentExceptionResource::collection($this->whenLoaded('exceptions')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
