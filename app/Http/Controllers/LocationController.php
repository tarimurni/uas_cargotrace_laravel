<?php

namespace App\Http\Controllers;

use App\Http\Requests\Location\StoreLocationRequest;
use App\Http\Requests\Location\UpdateLocationRequest;
use App\Models\AuditLog;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Location::class);

        $query = Location::query()
            ->withCount(['originShipments', 'destinationShipments']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $locations = $query->latest()->paginate(10)->withQueryString();

        return view('locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Location::class);

        return view('locations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLocationRequest $request): RedirectResponse
    {
        $location = Location::create($request->validated());

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'CREATE_LOCATION',
            'auditable_type' => Location::class,
            'auditable_id' => $location->id,
            'new_values' => $location->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('locations.index')->with('success', 'Lokasi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location): View
    {
        $this->authorize('view', $location);

        $location->load([
            'originShipments' => fn ($q) => $q->latest()->limit(5),
            'destinationShipments' => fn ($q) => $q->latest()->limit(5),
        ]);

        return view('locations.show', compact('location'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location): View
    {
        $this->authorize('update', $location);

        return view('locations.edit', compact('location'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $oldValues = $location->toArray();
        $location->update($request->validated());

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'UPDATE_LOCATION',
            'auditable_type' => Location::class,
            'auditable_id' => $location->id,
            'old_values' => $oldValues,
            'new_values' => $location->fresh()->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('locations.index')->with('success', 'Lokasi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Location $location): RedirectResponse
    {
        $this->authorize('delete', $location);

        if ($location->originShipments()->exists() || $location->destinationShipments()->exists()) {
            return back()->with('error', 'Lokasi tidak dapat dihapus karena sedang digunakan dalam data pengiriman.');
        }

        $oldValues = $location->toArray();
        $location->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'DELETE_LOCATION',
            'auditable_type' => Location::class,
            'auditable_id' => $location->id,
            'old_values' => $oldValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('locations.index')->with('success', 'Lokasi berhasil dihapus.');
    }
}
