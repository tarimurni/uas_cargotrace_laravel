<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exception\StoreShipmentExceptionRequest;
use App\Http\Requests\Exception\UpdateShipmentExceptionRequest;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use Illuminate\Http\RedirectResponse;

class ShipmentExceptionController extends Controller
{
    public function __construct(
        protected ExceptionService $exceptionService
    ) {}

    /**
     * Store a newly created exception for a shipment.
     */
    public function store(StoreShipmentExceptionRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        $this->exceptionService->createException(
            shipment: $shipment,
            data: $request->validated(),
            actor: $request->user()
        );

        return redirect()->route('shipments.show', $shipment)->with('success', 'Exception kargo berhasil dicatat.');
    }

    /**
     * Update or resolve an existing exception.
     */
    public function update(UpdateShipmentExceptionRequest $request, ShipmentException $exception): RedirectResponse
    {
        $this->authorize('update', $exception->shipment);

        $this->exceptionService->updateException(
            exception: $exception,
            data: $request->validated(),
            actor: $request->user()
        );

        return redirect()->route('shipments.show', $exception->shipment)->with('success', 'Status exception berhasil diperbarui.');
    }
}
