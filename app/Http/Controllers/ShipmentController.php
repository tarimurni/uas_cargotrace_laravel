<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidStatusTransitionException;
use App\Http\Requests\Shipment\StoreShipmentRequest;
use App\Http\Requests\Shipment\UpdateShipmentRequest;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService,
        protected TrackingService $trackingService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Shipment::class);

        $user = $request->user();
        $query = Shipment::query()->with(['customer', 'carrier', 'origin', 'destination', 'items']);

        // Customer can only view their own shipments
        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        } elseif ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('cargo_type', 'like', "%{$search}%")
                  ->orWhereHas('items', function ($itemQ) use ($search) {
                      $itemQ->where('item_name', 'like', "%{$search}%")
                            ->orWhere('item_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('current_status')) {
            $query->where('current_status', $request->input('current_status'));
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->input('carrier_id'));
        }

        if ($request->filled('is_delayed')) {
            $query->where('is_delayed', $request->boolean('is_delayed'));
        }

        $shipments = $query->latest()->paginate(10)->withQueryString();
        $carriers = Carrier::where('is_active', true)->get();

        return view('shipments.index', compact('shipments', 'carriers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Shipment::class);

        $carriers = Carrier::where('is_active', true)->get();
        $locations = Location::orderBy('name')->get();
        $customers = User::where('role', 'customer')->orderBy('name')->get();

        return view('shipments.create', compact('carriers', 'locations', 'customers'));
    }

    /**
     * Store a newly created resource in storage using ShipmentService.
     */
    public function store(StoreShipmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Shipment::class);

        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        $shipment = $this->shipmentService->createShipment($validated, $items, $request->user());

        return redirect()->route('shipments.show', $shipment)->with('success', 'Pengiriman berhasil dibuat.');
    }

    /**
     * Display the specified resource with historical tracking timeline and latest coordinates.
     */
    public function show(Shipment $shipment): View
    {
        $this->authorize('view', $shipment);

        $shipment->load([
            'customer',
            'carrier',
            'origin',
            'destination',
            'items',
            'trackingEvents.location',
            'trackingEvents.creator',
            'exceptions',
        ]);

        $timeline = $this->trackingService->getCombinedTimeline($shipment);
        $latestEvent = $shipment->trackingEvents->last();
        $locations = Location::orderBy('name')->get();

        return view('shipments.show', compact('shipment', 'timeline', 'latestEvent', 'locations'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shipment $shipment): View
    {
        $this->authorize('update', $shipment);

        $shipment->load('items');
        $carriers = Carrier::where('is_active', true)->get();
        $locations = Location::orderBy('name')->get();
        $customers = User::where('role', 'customer')->orderBy('name')->get();

        return view('shipments.edit', compact('shipment', 'carriers', 'locations', 'customers'));
    }

    /**
     * Update the specified resource in storage using ShipmentService.
     */
    public function update(UpdateShipmentRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        $validated = $request->validated();
        $items = $validated['items'] ?? null;
        unset($validated['items']);

        try {
            $this->shipmentService->updateShipment($shipment, $validated, $items, $request->user());
        } catch (InvalidStatusTransitionException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('shipments.show', $shipment)->with('success', 'Data pengiriman dan item berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage using ShipmentService.
     */
    public function destroy(Request $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('delete', $shipment);

        $this->shipmentService->deleteShipment($shipment, $request->user());

        return redirect()->route('shipments.index')->with('success', 'Data pengiriman berhasil dihapus.');
    }
}
