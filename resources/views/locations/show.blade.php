<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Lokasi: ' . $location->name) }}
            </h2>
            <div class="space-x-2">
                @can('update', $location)
                    <a href="{{ route('locations.edit', $location) }}" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700">
                        Edit Lokasi
                    </a>
                @endcan
                <a href="{{ route('locations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Location Details Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Nama Fasilitas</span>
                        <div class="text-lg font-bold text-gray-900 mt-1">{{ $location->name }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Kode Lokasi</span>
                        <div class="mt-1">
                            <span class="font-mono text-base font-bold bg-gray-100 text-gray-900 px-2.5 py-0.5 rounded">{{ $location->code ?? '-' }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Tipe Fasilitas</span>
                        <div class="mt-1">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase
                                @if($location->type === 'seaport') bg-blue-100 text-blue-800
                                @elseif($location->type === 'airport') bg-sky-100 text-sky-800
                                @elseif($location->type === 'warehouse') bg-amber-100 text-amber-800
                                @else bg-purple-100 text-purple-800 @endif">
                                {{ $location->type }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Kota & Negara</span>
                        <div class="text-base text-gray-900 mt-1">{{ $location->city }}, {{ $location->country }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Latitude</span>
                        <div class="font-mono text-sm text-gray-800 mt-1">{{ number_format($location->latitude, 7) }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Longitude</span>
                        <div class="font-mono text-sm text-gray-800 mt-1">{{ number_format($location->longitude, 7) }}</div>
                    </div>
                    <div class="md:col-span-2">
                        <span class="text-xs text-gray-500 uppercase font-semibold">Lihat Peta Eksternal</span>
                        <div class="mt-1 space-x-3">
                            <a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}" target="_blank" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                                Buka di Google Maps &rarr;
                            </a>
                            <a href="https://www.openstreetmap.org/?mlat={{ $location->latitude }}&mlon={{ $location->longitude }}#map=14/{{ $location->latitude }}/{{ $location->longitude }}" target="_blank" class="inline-flex items-center text-sm text-emerald-600 hover:text-emerald-900 font-medium">
                                Buka di OpenStreetMap &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipments through this location -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Origin Shipments -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-3">Pengiriman Dari Lokasi Ini (Origin)</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Shipment #</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tujuan</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($location->originShipments as $s)
                                    <tr>
                                        <td class="px-3 py-2 font-mono text-indigo-600">
                                            <a href="{{ route('shipments.show', $s) }}">{{ $s->shipment_number }}</a>
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">{{ $s->destination?->name }}</td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase
                                                @if($s->current_status === 'delivered') bg-green-100 text-green-800
                                                @elseif($s->current_status === 'delayed') bg-red-100 text-red-800
                                                @else bg-blue-100 text-blue-800 @endif">
                                                {{ str_replace('_', ' ', $s->current_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-4 text-center text-gray-500 text-xs">Belum ada pengiriman dari sini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Destination Shipments -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-3">Pengiriman Menuju Lokasi Ini (Destination)</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Shipment #</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Asal</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($location->destinationShipments as $s)
                                    <tr>
                                        <td class="px-3 py-2 font-mono text-indigo-600">
                                            <a href="{{ route('shipments.show', $s) }}">{{ $s->shipment_number }}</a>
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">{{ $s->origin?->name }}</td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase
                                                @if($s->current_status === 'delivered') bg-green-100 text-green-800
                                                @elseif($s->current_status === 'delayed') bg-red-100 text-red-800
                                                @else bg-blue-100 text-blue-800 @endif">
                                                {{ str_replace('_', ' ', $s->current_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-4 text-center text-gray-500 text-xs">Belum ada pengiriman menuju lokasi ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
