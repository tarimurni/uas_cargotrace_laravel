<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight">
                    {{ __('Dashboard Operasional CargoTrace') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Selamat datang kembali, <span class="font-bold text-gray-800">{{ Auth::user()->name }}</span>
                    (Hak Akses: <span class="uppercase font-semibold text-indigo-600">{{ Auth::user()->role }}</span>)
                </p>
            </div>

            <div class="flex items-center gap-2">
                @can('create', App\Models\Shipment::class)
                    <a href="{{ route('shipments.create') }}" class="inline-flex items-center px-4 py-2 bg-sky-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 active:bg-sky-800 shadow-sm transition">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Buat Shipment Baru
                    </a>
                @endcan
                <a href="{{ route('shipments.index') }}" class="inline-flex items-center px-3.5 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-50 shadow-xs transition">
                    Lihat Semua Kargo &rarr;
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- KPI Metric Cards (Direct MySQL Aggregations) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                <!-- Total Shipments -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-5 border-l-4 border-indigo-500 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {{ Auth::user()->isCustomer() ? 'Kargo Saya' : 'Total Kargo' }}
                    </p>
                    <div class="flex items-baseline justify-between mt-2">
                        <p class="text-2xl font-extrabold text-gray-900 font-mono">{{ $totalShipments }}</p>
                        <span class="text-xs text-indigo-600 font-semibold">Total</span>
                    </div>
                </div>

                <!-- In Transit -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-5 border-l-4 border-blue-500 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Dalam Perjalanan</p>
                    <div class="flex items-baseline justify-between mt-2">
                        <p class="text-2xl font-extrabold text-blue-600 font-mono">{{ $inTransitCount }}</p>
                        <span class="text-xs text-blue-500 font-semibold">In Transit</span>
                    </div>
                </div>

                <!-- Delivered -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-5 border-l-4 border-emerald-500 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Terkirim</p>
                    <div class="flex items-baseline justify-between mt-2">
                        <p class="text-2xl font-extrabold text-emerald-600 font-mono">{{ $deliveredCount }}</p>
                        <span class="text-xs text-emerald-500 font-semibold">Delivered</span>
                    </div>
                </div>

                <!-- Delayed -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-5 border-l-4 border-red-500 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Mengalami Delay</p>
                    <div class="flex items-baseline justify-between mt-2">
                        <p class="text-2xl font-extrabold text-red-600 font-mono">{{ $delayedCount }}</p>
                        <span class="text-xs text-red-500 font-semibold">{{ $delayRate }}%</span>
                    </div>
                </div>

                <!-- Open Exceptions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-5 border-l-4 border-rose-500 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Kendala Aktif</p>
                    <div class="flex items-baseline justify-between mt-2">
                        <p class="text-2xl font-extrabold text-rose-600 font-mono">{{ $openExceptionsCount }}</p>
                        <span class="text-xs text-rose-500 font-semibold">Open / Inv</span>
                    </div>
                </div>

                <!-- Exception Rate -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-5 border-l-4 border-amber-500 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tingkat Kendala</p>
                    <div class="flex items-baseline justify-between mt-2">
                        <p class="text-2xl font-extrabold text-amber-600 font-mono">{{ $exceptionRate }}%</p>
                        <span class="text-xs text-amber-500 font-semibold">{{ $totalExceptionsCount }} Total</span>
                    </div>
                </div>
            </div>

            <!-- Operational Quality KPIs (Slide 23 Brief Metrics) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- KPI 1: On-Time Delivery Rate (%) -->
                <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-xl p-5 text-white shadow-md relative overflow-hidden border border-emerald-500/20">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="inline-block px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-white/20 rounded text-emerald-100">Keandalan Logistik</span>
                            <p class="text-xs uppercase font-bold tracking-wider text-emerald-100 mt-2">On-Time Delivery Rate</p>
                            <p class="text-3xl font-extrabold font-mono mt-1">{{ $onTimeDeliveryRate }}%</p>
                            <p class="text-xs text-emerald-100/90 mt-1">Persentase pengiriman tiba sesuai target jadwal</p>
                        </div>
                        <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- KPI 2: Average Delay Duration (jam) -->
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-5 text-white shadow-md relative overflow-hidden border border-amber-500/20">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="inline-block px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-white/20 rounded text-amber-100">Efisiensi Rute</span>
                            <p class="text-xs uppercase font-bold tracking-wider text-amber-100 mt-2">Average Delay Duration</p>
                            <p class="text-3xl font-extrabold font-mono mt-1">{{ $avgDelayDuration }} <span class="text-lg font-normal">jam</span></p>
                            <p class="text-xs text-amber-100/90 mt-1">Rata-rata durasi deviasi keterlambatan kargo bermasalah</p>
                        </div>
                        <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- KPI 3: Average Exception Resolution Time (jam) -->
                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-xl p-5 text-white shadow-md relative overflow-hidden border border-indigo-500/20">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="inline-block px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-white/20 rounded text-indigo-100">Response & Handling</span>
                            <p class="text-xs uppercase font-bold tracking-wider text-indigo-100 mt-2">Average Exception Resolution Time</p>
                            <p class="text-3xl font-extrabold font-mono mt-1">{{ $avgExceptionResolutionTime }} <span class="text-lg font-normal">jam</span></p>
                            <p class="text-xs text-indigo-100/90 mt-1">Rata-rata kecepatan penyelesaian insiden operasional</p>
                        </div>
                        <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart.js Visualizations (Shipment by Status & by Carrier) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Chart 1: Shipment by Status (Doughnut Chart) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border border-gray-100">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 inline-block"></span>
                                Distribusi Status Kargo (Shipment by Status)
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Komposisi pengiriman kargo berdasarkan tahap operasional saat ini</p>
                        </div>
                    </div>

                    <div class="relative h-64 flex items-center justify-center">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- Chart 2: Shipment by Carrier (Bar Chart) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border border-gray-100">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span>
                                Volume Kargo per Carrier (Shipment by Carrier)
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Sebaran beban pengiriman yang dikelola masing-masing armada</p>
                        </div>
                    </div>

                    <div class="relative h-64 flex items-center justify-center">
                        <canvas id="carrierChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Global Shipment Map (Peta Distribusi Kargo Global) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-gray-100 mb-4 gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                            Global Shipment Map (Peta Distribusi Global)
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Visualisasi rute dan sebaran kargo aktif lintas benua secara real-time</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <div class="flex items-center gap-3 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100 text-[11px] font-medium text-gray-600">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span> In Transit</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-500"></span> Delayed</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Delivered</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-purple-500"></span> Booked / Other</span>
                        </div>

                        <button type="button" id="btnResetGlobalMap" class="inline-flex items-center px-2.5 py-1.5 bg-white border border-gray-300 rounded-md text-[11px] font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition">
                            <svg class="w-3.5 h-3.5 me-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 012 2v2.945M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Reset Tampilan Global
                        </button>

                        <button type="button" id="btnFitGlobalMap" class="inline-flex items-center px-2.5 py-1.5 bg-indigo-50 border border-indigo-200 rounded-md text-[11px] font-semibold text-indigo-700 hover:bg-indigo-100 shadow-sm transition">
                            <svg class="w-3.5 h-3.5 me-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4h-4"/></svg>
                            Fokus Semua Rute ({{ count($globalMapData) }})
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <div id="globalShipmentMap" class="w-full h-[460px] rounded-xl border border-gray-200 z-0"></div>
                </div>
            </div>

            <!-- Recent Shipments & Exceptions/Notifications Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Shipments Table (2 cols) -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border border-gray-100">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            Pengiriman Kargo Terbaru
                        </h3>
                        <a href="{{ route('shipments.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                            Lihat Semua &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">No. Shipment</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Carrier</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Rute</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Status</th>
                                    <th class="px-3 py-2 text-right font-semibold text-gray-600">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($recentShipments as $s)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2.5 font-mono font-bold text-indigo-600">
                                            <a href="{{ route('shipments.show', $s) }}">{{ $s->shipment_number }}</a>
                                        </td>
                                        <td class="px-3 py-2.5 text-gray-800 font-medium">{{ $s->carrier?->name }}</td>
                                        <td class="px-3 py-2.5 text-gray-600">{{ $s->origin?->city }} &rarr; {{ $s->destination?->city }}</td>
                                        <td class="px-3 py-2.5">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                                @if(in_array(strtoupper($s->current_status), ['DELIVERED'])) bg-green-100 text-green-800
                                                @elseif(in_array(strtoupper($s->current_status), ['IN_TRANSIT', 'PICKED_UP'])) bg-blue-100 text-blue-800
                                                @elseif(in_array(strtoupper($s->current_status), ['BOOKED'])) bg-purple-100 text-purple-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ str_replace('_', ' ', $s->current_status) }}
                                            </span>
                                            @if($s->is_delayed)
                                                <span class="ms-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 uppercase">DELAY</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-right">
                                            <a href="{{ route('shipments.show', $s) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-6 text-center text-gray-400">Belum ada pengiriman kargo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Notifications & Alerts (1 col) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border border-gray-100 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-3">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                Pemberitahuan Terbaru
                            </h3>
                            <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                                Semua &rarr;
                            </a>
                        </div>

                        <div class="space-y-2.5">
                            @forelse ($notifications as $notif)
                                <div class="p-3 rounded-lg border text-xs transition {{ $notif->is_read ? 'bg-gray-50 border-gray-100' : 'bg-indigo-50/60 border-indigo-200' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="font-bold text-gray-900">{{ $notif->title }}</div>
                                        <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ $notif->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-gray-600 mt-1 line-clamp-2 leading-relaxed">{{ $notif->message }}</p>
                                    @if ($notif->shipment)
                                        <div class="mt-1.5">
                                            <a href="{{ route('shipments.show', $notif->shipment) }}" class="text-indigo-600 hover:underline font-semibold text-[11px]">
                                                Buka Kargo #{{ $notif->shipment->shipment_number }} &rarr;
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="py-8 text-center text-gray-400 text-xs">
                                    Belum ada pemberitahuan baru.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-4 mt-4 border-t border-gray-100 text-center">
                        <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900">
                            Buka Pusat Notifikasi &rarr;
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            .leaflet-control-layers {
                border-radius: 0.6rem !important;
                border: 1px solid rgba(226, 232, 240, 0.9) !important;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
                font-family: inherit !important;
                font-size: 11px !important;
                font-weight: 600 !important;
                padding: 6px 12px !important;
                background: rgba(255, 255, 255, 0.95) !important;
                backdrop-filter: blur(4px);
            }
            .leaflet-popup-content-wrapper {
                border-radius: 0.75rem;
                padding: 0.25rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }
            .leaflet-popup-content {
                margin: 0.5rem;
                line-height: 1.4;
            }
            .custom-div-icon {
                background: transparent;
                border: none;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // 1. Status Doughnut Chart
                const statusCtx = document.getElementById('statusChart');
                if (statusCtx) {
                    new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: @json($statusChartLabels),
                            datasets: [{
                                data: @json($statusChartValues),
                                backgroundColor: [
                                    '#94a3b8', // Created (Slate)
                                    '#6366f1', // Booked (Indigo)
                                    '#0284c7', // Picked Up (Ocean Blue)
                                    '#0369a1', // In Transit (Deep Sky)
                                    '#10b981', // Delivered (Emerald)
                                ],
                                borderWidth: 2,
                                borderColor: '#ffffff',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 12,
                                        font: { size: 11 }
                                    }
                                }
                            }
                        }
                    });
                }

                // 2. Carrier Bar Chart
                const carrierCtx = document.getElementById('carrierChart');
                if (carrierCtx) {
                    new Chart(carrierCtx, {
                        type: 'bar',
                        data: {
                            labels: @json($carrierChartLabels),
                            datasets: [{
                                label: 'Jumlah Pengiriman',
                                data: @json($carrierChartValues),
                                backgroundColor: '#0284c7',
                                borderRadius: 6,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 }
                                }
                            },
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });
                }

                // 3. Global Shipment Map (Leaflet + OpenStreetMap + Esri Satellite)
                const globalMapElement = document.getElementById('globalShipmentMap');
                if (globalMapElement) {
                    const globalMap = L.map('globalShipmentMap', {
                        scrollWheelZoom: true,
                        zoomControl: true,
                        maxZoom: 19,
                        worldCopyJump: true
                    }).setView([15, 30], 2);

                    // Tile Layer 1: OpenStreetMap Standard (Layer utama gratis tanpa API key)
                    const osmLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'
                    });

                    // Tile Layer 2: Esri World Imagery (Realistic satellite view)
                    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 19,
                        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
                    });

                    // Default layer: OpenStreetMap
                    osmLayer.addTo(globalMap);

                    // Base layers switcher
                    const baseMaps = {
                        "OpenStreetMap (Peta Jalan)": osmLayer,
                        "Citra Satelit (Esri World Imagery)": satelliteLayer
                    };
                    L.control.layers(baseMaps, null, { position: 'topright' }).addTo(globalMap);

                    const globalRoutes = @json($globalMapData);
                    const globalBounds = L.latLngBounds();
                    let hasValidPoints = false;

                    function createMarkerIcon(bgColor, ringColor, label) {
                        return L.divIcon({
                            className: 'custom-div-icon',
                            html: `
                                <div class="relative flex items-center justify-center cursor-pointer group">
                                    <div class="w-6 h-6 rounded-full ${bgColor} ${ringColor} ring-2 flex items-center justify-center text-white text-[10px] font-bold shadow-md">
                                        ${label}
                                    </div>
                                </div>
                            `,
                            iconSize: [24, 24],
                            iconAnchor: [12, 12],
                            popupAnchor: [0, -14]
                        });
                    }

                    globalRoutes.forEach(function (s) {
                        if (!s.origin || !s.origin.lat || !s.destination || !s.destination.lat) return;

                        const origLatLng = [s.origin.lat, s.origin.lng];
                        const destLatLng = [s.destination.lat, s.destination.lng];

                        globalBounds.extend(origLatLng);
                        globalBounds.extend(destLatLng);
                        hasValidPoints = true;

                        let routeColor = '#0284c7'; // default In Transit ocean blue
                        let statusBadgeClass = 'bg-sky-100 text-sky-800 border border-sky-300';

                        if (s.is_delayed) {
                            routeColor = '#e11d48'; // red
                            statusBadgeClass = 'bg-rose-100 text-rose-800 border border-rose-300 font-bold';
                        } else if (s.status === 'DELIVERED') {
                            routeColor = '#059669'; // emerald
                            statusBadgeClass = 'bg-emerald-100 text-emerald-800 border border-emerald-300 font-semibold';
                        } else if (s.status === 'BOOKED' || s.status === 'CREATED' || s.status === 'PENDING') {
                            routeColor = '#4f46e5'; // indigo
                            statusBadgeClass = 'bg-indigo-100 text-indigo-800 border border-indigo-300 font-semibold';
                        }

                        // Origin marker
                        const originMarker = L.marker(origLatLng, {
                            icon: createMarkerIcon('bg-slate-800', 'ring-sky-400', 'O')
                        }).addTo(globalMap);

                        // Destination marker
                        const destMarker = L.marker(destLatLng, {
                            icon: createMarkerIcon(s.is_delayed ? 'bg-rose-600' : 'bg-emerald-600', 'ring-white', 'D')
                        }).addTo(globalMap);

                        // Route polyline connecting origin and destination
                        const polyline = L.polyline([origLatLng, destLatLng], {
                            color: routeColor,
                            weight: 3.5,
                            opacity: 0.9,
                            dashArray: s.status === 'DELIVERED' ? null : '6, 8',
                            lineCap: 'round',
                            lineJoin: 'round'
                        }).addTo(globalMap);

                        const popupHtml = `
                            <div class="p-2.5 min-w-[210px] text-xs">
                                <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-1.5 mb-2">
                                    <a href="${s.url}" class="font-mono font-bold text-sky-600 text-sm hover:underline">${s.shipment_number}</a>
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase ${statusBadgeClass}">${s.status_label}</span>
                                </div>
                                <div class="space-y-1 text-slate-600">
                                    <div class="font-bold text-slate-900 flex items-center gap-1.5 text-xs">
                                        <span>${s.origin.city}</span>
                                        <span class="text-sky-500">&rarr;</span>
                                        <span>${s.destination.city}</span>
                                    </div>
                                    <div class="text-[11px]"><span class="text-slate-400">Carrier:</span> <span class="font-medium text-slate-800">${s.carrier_name}</span></div>
                                    <div class="text-[11px]"><span class="text-slate-400">Muatan:</span> <span class="font-medium text-slate-800">${s.cargo_type}</span></div>
                                    ${s.is_delayed ? '<div class="text-rose-600 font-bold text-[11px] mt-1 bg-rose-50 p-1 rounded border border-rose-200">&#x26A0; Peringatan Keterlambatan (Delayed)</div>' : ''}
                                </div>
                                <div class="mt-2.5 pt-1.5 border-t border-slate-100 text-right">
                                    <a href="${s.url}" class="inline-flex items-center font-bold text-sky-600 hover:text-sky-800 text-[11px]">
                                        Buka Detail Kargo &rarr;
                                    </a>
                                </div>
                            </div>
                        `;

                        originMarker.bindPopup(popupHtml);
                        destMarker.bindPopup(popupHtml);
                        polyline.bindPopup(popupHtml);
                    });

                    const btnReset = document.getElementById('btnResetGlobalMap');
                    if (btnReset) {
                        btnReset.addEventListener('click', function () {
                            globalMap.setView([15, 30], 2);
                        });
                    }

                    const btnFit = document.getElementById('btnFitGlobalMap');
                    if (btnFit) {
                        btnFit.addEventListener('click', function () {
                            if (hasValidPoints) {
                                globalMap.fitBounds(globalBounds, { padding: [50, 50], maxZoom: 8 });
                            } else {
                                globalMap.setView([15, 30], 2);
                            }
                        });
                    }

                    // Invalidate size to ensure clean tile rendering after layout paint
                    setTimeout(function () {
                        globalMap.invalidateSize();
                    }, 200);

                    window.addEventListener('resize', function () {
                        globalMap.invalidateSize();
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
