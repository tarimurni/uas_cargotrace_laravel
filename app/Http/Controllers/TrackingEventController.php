<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tracking\StoreTrackingEventRequest;
use App\Models\Shipment;
use App\Services\TrackingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

class TrackingEventController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService
    ) {}

    /**
     * Store a new tracking checkpoint event for a shipment.
     */
    public function store(StoreTrackingEventRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        $validated = $request->validated();

        $this->trackingService->addTrackingEvent(
            shipment: $shipment,
            status: $validated['status'],
            locationId: $validated['location_id'] ?? null,
            latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            description: $validated['description'] ?? null,
            eventTime: isset($validated['event_time']) ? Carbon::parse($validated['event_time']) : now(),
            user: $request->user(),
            updateShipmentStatus: $request->boolean('update_shipment_status', true)
        );

        return redirect()->route('shipments.show', $shipment)->with('success', 'Event tracking checkpoint berhasil ditambahkan.');
    }
}
