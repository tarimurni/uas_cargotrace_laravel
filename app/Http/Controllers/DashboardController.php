<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle incoming request for operational dashboard.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $shipmentQuery = Shipment::query();

        // Customer sees only their own shipments
        if ($user->isCustomer()) {
            $shipmentQuery->where('customer_id', $user->id);
        }

        // MySQL Direct Aggregations
        $totalShipments = (clone $shipmentQuery)->count();
        $inTransitCount = (clone $shipmentQuery)
            ->whereRaw('UPPER(current_status) IN (?, ?)', ['IN_TRANSIT', 'PICKED_UP'])
            ->count();
        $deliveredCount = (clone $shipmentQuery)
            ->whereRaw('UPPER(current_status) = ?', ['DELIVERED'])
            ->count();
        $delayedCount = (clone $shipmentQuery)->where('is_delayed', true)->count();

        // Exceptions counts directly from MySQL
        $exceptionQuery = ShipmentException::whereHas('shipment', function ($q) use ($user) {
            if ($user->isCustomer()) {
                $q->where('customer_id', $user->id);
            }
        });

        $totalExceptionsCount = (clone $exceptionQuery)->count();
        $openExceptionsCount = (clone $exceptionQuery)
            ->whereIn('status', [ShipmentException::STATUS_OPEN, ShipmentException::STATUS_INVESTIGATING])
            ->count();

        // Calculated Rates
        $delayRate = $totalShipments > 0 ? round(($delayedCount / $totalShipments) * 100, 1) : 0;
        $exceptionRate = $totalShipments > 0 ? round(($totalExceptionsCount / $totalShipments) * 100, 1) : 0;

        // Chart.js Data: Shipments by Status
        $canonicalStatuses = [
            'CREATED' => 'Created',
            'BOOKED' => 'Booked',
            'PICKED_UP' => 'Picked Up',
            'IN_TRANSIT' => 'In Transit',
            'DELIVERED' => 'Delivered',
        ];

        $statusCounts = (clone $shipmentQuery)
            ->selectRaw('UPPER(current_status) as status_name, count(*) as count')
            ->groupBy('status_name')
            ->pluck('count', 'status_name')
            ->toArray();

        $statusChartLabels = [];
        $statusChartValues = [];
        foreach ($canonicalStatuses as $code => $lbl) {
            $statusChartLabels[] = $lbl;
            $statusChartValues[] = $statusCounts[$code] ?? 0;
        }

        // Chart.js Data: Shipments by Carrier
        $carrierCounts = (clone $shipmentQuery)
            ->join('carriers', 'shipments.carrier_id', '=', 'carriers.id')
            ->selectRaw('carriers.name as carrier_name, count(shipments.id) as count')
            ->groupBy('carriers.name')
            ->orderByDesc('count')
            ->limit(6)
            ->pluck('count', 'carrier_name')
            ->toArray();

        $carrierChartLabels = array_keys($carrierCounts);
        $carrierChartValues = array_values($carrierCounts);

        // Recent Records
        $recentShipments = (clone $shipmentQuery)
            ->with(['carrier', 'origin', 'destination', 'customer'])
            ->latest()
            ->limit(5)
            ->get();

        $recentExceptions = (clone $exceptionQuery)
            ->with(['shipment.carrier'])
            ->latest()
            ->limit(5)
            ->get();

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $totalCarriers = $user->isAdmin() ? Carrier::count() : 0;
        $totalLocations = $user->isAdmin() ? Location::count() : 0;

        return view('dashboard', compact(
            'totalShipments',
            'inTransitCount',
            'delayedCount',
            'deliveredCount',
            'openExceptionsCount',
            'totalExceptionsCount',
            'delayRate',
            'exceptionRate',
            'statusChartLabels',
            'statusChartValues',
            'carrierChartLabels',
            'carrierChartValues',
            'recentShipments',
            'recentExceptions',
            'notifications',
            'totalCarriers',
            'totalLocations'
        ));
    }
}
