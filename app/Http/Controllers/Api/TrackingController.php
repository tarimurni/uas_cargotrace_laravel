<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTrackingEventRequest;
use App\Http\Resources\TrackingEventResource;
use App\Models\Shipment;
use App\Services\TrackingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TrackingController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService
    ) {}

    /**
     * Display tracking checkpoints for a shipment.
     */
    public function index(Request $request, int $id): AnonymousResourceCollection
    {
        $shipment = Shipment::findOrFail($id);

        $this->authorize('view', $shipment);

        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $events = $shipment->trackingEvents()
            ->with(['location', 'creator'])
            ->latest('event_time')
            ->paginate($perPage);

        return TrackingEventResource::collection($events);
    }

    /**
     * Record a new tracking checkpoint for a shipment.
     */
    public function store(StoreTrackingEventRequest $request, int $id): JsonResponse
    {
        $shipment = Shipment::findOrFail($id);

        $this->authorize('update', $shipment);

        $validated = $request->validated();
        $eventTime = isset($validated['event_time']) ? Carbon::parse($validated['event_time']) : now();
        $updateStatus = $request->boolean('update_shipment_status', true);

        $event = $this->trackingService->addTrackingEvent(
            shipment: $shipment,
            status: $validated['status'],
            locationId: $validated['location_id'] ?? null,
            latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            description: $validated['description'] ?? null,
            eventTime: $eventTime,
            user: $request->user(),
            updateShipmentStatus: $updateStatus
        );

        return (new TrackingEventResource($event))
            ->response()
            ->setStatusCode(201);
    }
}
