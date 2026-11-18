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
                    <a href="{{ route('shipments.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 shadow-sm transition">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Buat Shipment Baru
                    </a>
                @endcan
                <a href="{{ route('shipments.index') }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 shadow-sm transition">
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

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                                    '#a855f7', // Booked (Purple)
                                    '#06b6d4', // Picked Up (Cyan)
                                    '#3b82f6', // In Transit (Blue)
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
                                backgroundColor: '#6366f1',
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
            });
        </script>
    @endpush
</x-app-layout>
