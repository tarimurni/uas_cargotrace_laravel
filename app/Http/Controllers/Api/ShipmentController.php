<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreShipmentRequest;
use App\Http\Requests\Api\UpdateShipmentRequest;
use App\Http\Resources\ShipmentResource;
use App\Models\Shipment;
use App\Services\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class ShipmentController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService
    ) {}

    /**
     * Display a paginated listing of shipments with policy filtering.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Shipment::class);

        $user = $request->user();
        $query = Shipment::query()->with(['customer', 'carrier', 'origin', 'destination']);

        // Policy rule: Customer can only view their own shipments
        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        } elseif ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->input('carrier_id'));
        }

        if ($request->filled('current_status')) {
            $query->where('current_status', $request->input('current_status'));
        }

        if ($request->filled('is_delayed')) {
            $query->where('is_delayed', $request->boolean('is_delayed'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('cargo_type', 'like', "%{$search}%");
            });
        }

        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $shipments = $query->latest()->paginate($perPage);

        return ShipmentResource::collection($shipments);
    }

    /**
     * Store a newly created shipment in storage.
     */
    public function store(StoreShipmentRequest $request): JsonResponse
    {
        $this->authorize('create', Shipment::class);

        $validated = $request->validated();
        $items = $validated['items'] ?? [];
        unset($validated['items']);

        if (empty($validated['shipment_number'])) {
            $validated['shipment_number'] = 'CT-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        }

        $shipment = $this->shipmentService->createShipment(
            data: $validated,
            items: $items,
            user: $request->user()
        );

        return (new ShipmentResource($shipment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified shipment.
     */
    public function show(int $id): ShipmentResource
    {
        $shipment = Shipment::with([
            'customer',
            'carrier',
            'origin',
            'destination',
            'items',
            'trackingEvents.location',
            'exceptions',
        ])->findOrFail($id);

        $this->authorize('view', $shipment);

        return new ShipmentResource($shipment);
    }

    /**
     * Update the specified shipment.
     */
    public function update(UpdateShipmentRequest $request, int $id): ShipmentResource
    {
        $shipment = Shipment::findOrFail($id);

        $this->authorize('update', $shipment);

        $validated = $request->validated();
        $items = $validated['items'] ?? null;
        unset($validated['items']);

        $updated = $this->shipmentService->updateShipment(
            shipment: $shipment,
            data: $validated,
            items: $items,
            user: $request->user()
        );

        return new ShipmentResource($updated);
    }

    /**
     * Remove the specified shipment from storage.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $shipment = Shipment::findOrFail($id);

        $this->authorize('delete', $shipment);

        $this->shipmentService->deleteShipment($shipment, $request->user());

        return response()->json([
            'message' => 'Shipment deleted successfully.',
        ], 200);
    }
}
