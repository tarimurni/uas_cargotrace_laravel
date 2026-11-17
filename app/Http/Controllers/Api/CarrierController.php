<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCarrierRequest;
use App\Http\Resources\CarrierResource;
use App\Models\AuditLog;
use App\Models\Carrier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CarrierController extends Controller
{
    /**
     * Display a listing of carriers.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Carrier::class);

        $query = Carrier::query()->withCount('shipments');

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->input('service_type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $carriers = $query->latest()->paginate($perPage);

        return CarrierResource::collection($carriers);
    }

    /**
     * Store a newly created carrier.
     */
    public function store(StoreCarrierRequest $request): JsonResponse
    {
        $this->authorize('create', Carrier::class);

        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $carrier = Carrier::create($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'CREATE_CARRIER',
            'auditable_type' => Carrier::class,
            'auditable_id' => $carrier->id,
            'new_values' => $carrier->toArray(),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent(),
        ]);

        return (new CarrierResource($carrier))
            ->response()
            ->setStatusCode(201);
    }
}
