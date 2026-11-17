<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Dashboard CargoTrace') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Selamat datang kembali, <span class="font-bold text-gray-700">{{ Auth::user()->name }}</span>
                    (Role: <span class="uppercase font-semibold text-indigo-600">{{ Auth::user()->role }}</span>)
                </p>
            </div>

            @can('create', App\Models\Shipment::class)
                <a href="{{ route('shipments.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Shipment Baru
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- KPI Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Shipments -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">
                                {{ Auth::user()->isCustomer() ? 'Pengiriman Saya' : 'Total Pengiriman' }}
                            </p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalShipments }}</p>
                        </div>
                        <div class="p-3 bg-indigo-50 rounded-full text-indigo-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- In Transit -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Sedang Berjalan (In Transit)</p>
                            <p class="text-2xl font-bold text-blue-700 mt-1">{{ $inTransitCount }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-full text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Delayed -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Mengalami Delay</p>
                            <p class="text-2xl font-bold text-red-600 mt-1">{{ $delayedCount }}</p>
                        </div>
                        <div class="p-3 bg-red-50 rounded-full text-red-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Delivered -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-emerald-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Berhasil Terkirim</p>
                            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $deliveredCount }}</p>
                        </div>
                        <div class="p-3 bg-emerald-50 rounded-full text-emerald-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications & Quick Links Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Notifications (1 col) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-3 border-b pb-2 flex items-center">
                        <svg class="w-5 h-5 text-indigo-600 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        Pemberitahuan Terbaru
                    </h3>

                    <div class="space-y-3">
                        @forelse ($notifications as $notif)
                            <div class="p-3 rounded-lg border text-xs {{ $notif->is_read ? 'bg-gray-50 border-gray-100' : 'bg-indigo-50/50 border-indigo-200' }}">
                                <div class="font-bold text-gray-900">{{ $notif->title }}</div>
                                <p class="text-gray-600 mt-1">{{ $notif->message }}</p>
                                <span class="text-gray-400 mt-1 block font-mono">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 py-4 text-center">Belum ada pemberitahuan.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Shipments Table (2 cols) -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-3 border-b pb-2">
                        <h3 class="text-base font-bold text-gray-900">Aktivitas Pengiriman Terbaru</h3>
                        <a href="{{ route('shipments.index') }}" class="text-xs text-indigo-600 hover:text-indigo-900 font-semibold">
                            Lihat Semua &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Shipment</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rute</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($recentShipments as $s)
                                    <tr>
                                        <td class="px-4 py-3 font-mono font-semibold text-indigo-600">
                                            <a href="{{ route('shipments.show', $s) }}">{{ $s->shipment_number }}</a>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 text-xs">
                                            {{ $s->origin?->city }} &rarr; {{ $s->destination?->city }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase
                                                @if($s->current_status === 'delivered') bg-green-100 text-green-800
                                                @elseif($s->current_status === 'delayed') bg-red-100 text-red-800
                                                @elseif($s->current_status === 'in_transit') bg-blue-100 text-blue-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ str_replace('_', ' ', $s->current_status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-xs">
                                            <a href="{{ route('shipments.show', $s) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">Detail</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-500 text-xs">Belum ada pengiriman.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Admin Overview Section -->
            @if(Auth::user()->isAdmin())
                <div class="bg-gradient-to-r from-purple-800 to-indigo-900 rounded-lg p-6 text-white shadow-lg">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div>
                            <h3 class="text-lg font-bold">Admin Central Management</h3>
                            <p class="text-sm text-purple-200 mt-1">
                                Kelola master data ekspedisi, pelabuhan/bandara, dan pantau rekam jejak sistem secara terpadu.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('carriers.index') }}" class="px-4 py-2 bg-white text-purple-900 rounded-md font-semibold text-xs uppercase tracking-wider hover:bg-purple-50">
                                Ekspedisi ({{ $totalCarriers }})
                            </a>
                            <a href="{{ route('locations.index') }}" class="px-4 py-2 bg-white text-purple-900 rounded-md font-semibold text-xs uppercase tracking-wider hover:bg-purple-50">
                                Lokasi ({{ $totalLocations }})
                            </a>
                            <a href="{{ route('audit-logs.index') }}" class="px-4 py-2 bg-purple-700 text-white rounded-md font-semibold text-xs uppercase tracking-wider hover:bg-purple-600 border border-purple-400">
                                Audit Logs
                            </a>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
