<?php

namespace App\Http\Controllers;

use App\Http\Requests\Carrier\StoreCarrierRequest;
use App\Http\Requests\Carrier\UpdateCarrierRequest;
use App\Models\AuditLog;
use App\Models\Carrier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarrierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Carrier::class);

        $query = Carrier::query()->withCount('shipments');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('service_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->input('service_type'));
        }

        $carriers = $query->latest()->paginate(10)->withQueryString();

        return view('carriers.index', compact('carriers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Carrier::class);

        return view('carriers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCarrierRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $carrier = Carrier::create($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'CREATE_CARRIER',
            'auditable_type' => Carrier::class,
            'auditable_id' => $carrier->id,
            'new_values' => $carrier->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('carriers.index')->with('success', 'Carrier berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Carrier $carrier): View
    {
        $this->authorize('view', $carrier);

        $carrier->load(['shipments' => function ($q) {
            $q->latest()->limit(10);
        }]);

        return view('carriers.show', compact('carrier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Carrier $carrier): View
    {
        $this->authorize('update', $carrier);

        return view('carriers.edit', compact('carrier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCarrierRequest $request, Carrier $carrier): RedirectResponse
    {
        $oldValues = $carrier->toArray();
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $carrier->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'UPDATE_CARRIER',
            'auditable_type' => Carrier::class,
            'auditable_id' => $carrier->id,
            'old_values' => $oldValues,
            'new_values' => $carrier->fresh()->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('carriers.index')->with('success', 'Carrier berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Carrier $carrier): RedirectResponse
    {
        $this->authorize('delete', $carrier);

        if ($carrier->shipments()->exists()) {
            return back()->with('error', 'Carrier tidak dapat dihapus karena masih memiliki riwayat pengiriman.');
        }

        $oldValues = $carrier->toArray();
        $carrier->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'DELETE_CARRIER',
            'auditable_type' => Carrier::class,
            'auditable_id' => $carrier->id,
            'old_values' => $oldValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('carriers.index')->with('success', 'Carrier berhasil dihapus.');
    }
}
