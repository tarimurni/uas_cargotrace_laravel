<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingEventResource extends JsonResource
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
            'location_id' => $this->location_id,
            'status' => $this->status,
            'latitude' => $this->latitude ? (float) $this->latitude : ($this->location?->latitude ? (float) $this->location->latitude : null),
            'longitude' => $this->longitude ? (float) $this->longitude : ($this->location?->longitude ? (float) $this->location->longitude : null),
            'event_time' => $this->event_time?->toIso8601String(),
            'description' => $this->description,
            'created_by' => $this->created_by,
            'location' => new LocationResource($this->whenLoaded('location')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
