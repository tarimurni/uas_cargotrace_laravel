<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreShipmentExceptionRequest;
use App\Http\Requests\Api\UpdateShipmentExceptionRequest;
use App\Http\Resources\ShipmentExceptionResource;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShipmentExceptionController extends Controller
{
    public function __construct(
        protected ExceptionService $exceptionService
    ) {}

    /**
     * Display a listing of exceptions for a shipment.
     */
    public function index(Request $request, int $id): AnonymousResourceCollection
    {
        $shipment = Shipment::findOrFail($id);

        $this->authorize('view', $shipment);

        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $exceptions = $shipment->exceptions()->latest()->paginate($perPage);

        return ShipmentExceptionResource::collection($exceptions);
    }

    /**
     * Store a newly created exception for a shipment.
     */
    public function store(StoreShipmentExceptionRequest $request, int $id): JsonResponse
    {
        $shipment = Shipment::findOrFail($id);

        $this->authorize('update', $shipment);

        $exception = $this->exceptionService->createException(
            shipment: $shipment,
            data: $request->validated(),
            actor: $request->user()
        );

        return (new ShipmentExceptionResource($exception))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update an existing shipment exception.
     */
    public function update(UpdateShipmentExceptionRequest $request, int $id): ShipmentExceptionResource
    {
        $exception = ShipmentException::with('shipment')->findOrFail($id);

        $this->authorize('update', $exception->shipment);

        $updated = $this->exceptionService->updateException(
            exception: $exception,
            data: $request->validated(),
            actor: $request->user()
        );

        return new ShipmentExceptionResource($updated);
    }
}
