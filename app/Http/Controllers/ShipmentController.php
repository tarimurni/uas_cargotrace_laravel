<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shipment\StoreShipmentRequest;
use App\Http\Requests\Shipment\UpdateShipmentRequest;
use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ShipmentController extends Controller
{
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
     * Store a newly created resource in storage.
     */
    public function store(StoreShipmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Shipment::class);

        $validated = $request->validated();

        $shipment = DB::transaction(function () use ($validated, $request) {
            $shipmentData = [
                'shipment_number' => $validated['shipment_number'],
                'customer_id' => $validated['customer_id'],
                'carrier_id' => $validated['carrier_id'],
                'origin_id' => $validated['origin_id'],
                'destination_id' => $validated['destination_id'],
                'cargo_type' => $validated['cargo_type'],
                'weight' => $validated['weight'],
                'volume' => $validated['volume'],
                'expected_departure' => $validated['expected_departure'],
                'expected_arrival' => $validated['expected_arrival'],
                'estimated_arrival' => $validated['estimated_arrival'],
                'current_status' => $validated['current_status'],
                'is_delayed' => $request->boolean('is_delayed', false),
                'delay_hours' => $validated['delay_hours'] ?? 0,
                'notes' => $validated['notes'] ?? null,
            ];

            $newShipment = Shipment::create($shipmentData);

            // Create items
            foreach ($validated['items'] as $item) {
                $newShipment->items()->create([
                    'item_name' => $item['item_name'],
                    'item_code' => $item['item_code'] ?? null,
                    'quantity' => $item['quantity'],
                    'weight' => $item['weight'],
                    'volume' => $item['volume'] ?? null,
                    'declared_value' => $item['declared_value'] ?? null,
                ]);
            }

            // Create initial tracking event
            $newShipment->trackingEvents()->create([
                'location_id' => $newShipment->origin_id,
                'status' => 'Shipment Created',
                'event_time' => now(),
                'description' => 'Shipment booking created by ' . $request->user()->name,
                'created_by' => $request->user()->id,
            ]);

            // Audit log
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'CREATE_SHIPMENT',
                'auditable_type' => Shipment::class,
                'auditable_id' => $newShipment->id,
                'new_values' => $newShipment->load('items')->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $newShipment;
        });

        return redirect()->route('shipments.show', $shipment)->with('success', 'Pengiriman berhasil dibuat.');
    }

    /**
     * Display the specified resource.
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

        return view('shipments.show', compact('shipment'));
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
     * Update the specified resource in storage.
     */
    public function update(UpdateShipmentRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        $validated = $request->validated();
        $oldValues = $shipment->load('items')->toArray();

        DB::transaction(function () use ($validated, $request, $shipment, $oldValues) {
            $statusChanged = $shipment->current_status !== $validated['current_status'];
            $oldStatus = $shipment->current_status;

            $shipmentData = [
                'shipment_number' => $validated['shipment_number'],
                'customer_id' => $validated['customer_id'],
                'carrier_id' => $validated['carrier_id'],
                'origin_id' => $validated['origin_id'],
                'destination_id' => $validated['destination_id'],
                'cargo_type' => $validated['cargo_type'],
                'weight' => $validated['weight'],
                'volume' => $validated['volume'],
                'expected_departure' => $validated['expected_departure'],
                'expected_arrival' => $validated['expected_arrival'],
                'estimated_arrival' => $validated['estimated_arrival'],
                'actual_departure' => $validated['actual_departure'] ?? $shipment->actual_departure,
                'actual_arrival' => $validated['actual_arrival'] ?? $shipment->actual_arrival,
                'current_status' => $validated['current_status'],
                'is_delayed' => $request->boolean('is_delayed', false),
                'delay_hours' => $validated['delay_hours'] ?? 0,
                'notes' => $validated['notes'] ?? null,
            ];

            $shipment->update($shipmentData);

            // Sync items if items are provided
            if (isset($validated['items']) && is_array($validated['items'])) {
                // Delete removed items
                $submittedIds = collect($validated['items'])->pluck('id')->filter()->toArray();
                $shipment->items()->whereNotIn('id', $submittedIds)->delete();

                foreach ($validated['items'] as $item) {
                    if (! empty($item['id'])) {
                        ShipmentItem::where('id', $item['id'])
                            ->where('shipment_id', $shipment->id)
                            ->update([
                                'item_name' => $item['item_name'],
                                'item_code' => $item['item_code'] ?? null,
                                'quantity' => $item['quantity'],
                                'weight' => $item['weight'],
                                'volume' => $item['volume'] ?? null,
                                'declared_value' => $item['declared_value'] ?? null,
                            ]);
                    } else {
                        $shipment->items()->create([
                            'item_name' => $item['item_name'],
                            'item_code' => $item['item_code'] ?? null,
                            'quantity' => $item['quantity'],
                            'weight' => $item['weight'],
                            'volume' => $item['volume'] ?? null,
                            'declared_value' => $item['declared_value'] ?? null,
                        ]);
                    }
                }
            }

            // If status changed, record automatic tracking event
            if ($statusChanged) {
                $shipment->trackingEvents()->create([
                    'location_id' => $shipment->destination_id,
                    'status' => 'Status Updated to ' . ucfirst(str_replace('_', ' ', $shipment->current_status)),
                    'event_time' => now(),
                    'description' => "Status changed from {$oldStatus} to {$shipment->current_status} by " . $request->user()->name,
                    'created_by' => $request->user()->id,
                ]);
            }

            // Audit log
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'UPDATE_SHIPMENT',
                'auditable_type' => Shipment::class,
                'auditable_id' => $shipment->id,
                'old_values' => $oldValues,
                'new_values' => $shipment->fresh()->load('items')->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()->route('shipments.show', $shipment)->with('success', 'Data pengiriman dan item berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('delete', $shipment);

        $oldValues = $shipment->load('items')->toArray();
        $shipment->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'DELETE_SHIPMENT',
            'auditable_type' => Shipment::class,
            'auditable_id' => $shipment->id,
            'old_values' => $oldValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('shipments.index')->with('success', 'Data pengiriman berhasil dihapus.');
    }
}
