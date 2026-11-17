<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $shipmentQuery = Shipment::query();

        if ($user->isCustomer()) {
            $shipmentQuery->where('customer_id', $user->id);
        }

        $totalShipments = (clone $shipmentQuery)->count();
        $inTransitCount = (clone $shipmentQuery)->where('current_status', 'in_transit')->count();
        $delayedCount = (clone $shipmentQuery)->where('is_delayed', true)->count();
        $deliveredCount = (clone $shipmentQuery)->where('current_status', 'delivered')->count();

        $recentShipments = (clone $shipmentQuery)
            ->with(['carrier', 'origin', 'destination', 'customer'])
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
            'recentShipments',
            'notifications',
            'totalCarriers',
            'totalLocations'
        ));
    }
}
