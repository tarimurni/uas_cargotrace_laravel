<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="font-mono text-2xl font-bold text-gray-900">
                        {{ $shipment->shipment_number }}
                    </h2>
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full uppercase
                        @if($shipment->current_status === 'delivered') bg-green-100 text-green-800 border border-green-200
                        @elseif($shipment->current_status === 'delayed') bg-red-100 text-red-800 border border-red-200
                        @elseif($shipment->current_status === 'in_transit') bg-blue-100 text-blue-800 border border-blue-200
                        @elseif($shipment->current_status === 'customs_clearance') bg-purple-100 text-purple-800 border border-purple-200
                        @else bg-yellow-100 text-yellow-800 border border-yellow-200 @endif">
                        {{ str_replace('_', ' ', $shipment->current_status) }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Customer: <span class="font-semibold text-gray-700">{{ $shipment->customer?->name }}</span> |
                    Carrier: <span class="font-semibold text-gray-700">{{ $shipment->carrier?->name }} ({{ $shipment->carrier?->code }})</span>
                </p>
            </div>

            <div class="flex items-center space-x-2">
                @can('update', $shipment)
                    <a href="{{ route('shipments.edit', $shipment) }}" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit Pengiriman
                    </a>
                @endcan
                <a href="{{ route('shipments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Delay Alert Banner -->
            @if ($shipment->is_delayed)
                <div class="bg-red-50 border-l-4 border-red-500 p-5 rounded-lg shadow-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="ms-3">
                            <h3 class="text-sm font-bold text-red-800">
                                PERINGATAN KETERLAMBATAN (DELAY DETECTED)
                            </h3>
                            <div class="mt-1 text-sm text-red-700">
                                Pengiriman ini teridentifikasi mengalami delay sekitar <span class="font-bold underline">{{ $shipment->delay_hours }} Jam</span> dari jadwal semula.
                                @if($shipment->notes)
                                    <p class="mt-1 italic font-medium">"{{ $shipment->notes }}"</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Route & Schedule Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Origin Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-t-4 border-blue-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Titik Asal (Origin)</span>
                            <h3 class="text-xl font-bold text-gray-900 mt-1">{{ $shipment->origin?->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $shipment->origin?->city }}, {{ $shipment->origin?->country }}</p>
                        </div>
                        <span class="font-mono text-xs font-bold bg-blue-50 text-blue-700 px-2 py-1 rounded">
                            {{ $shipment->origin?->code ?? 'N/A' }}
                        </span>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Jadwal Keberangkatan:</span>
                            <span class="font-medium text-gray-900">{{ $shipment->expected_departure?->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Aktual Keberangkatan:</span>
                            <span class="font-medium text-gray-900">{{ $shipment->actual_departure ? $shipment->actual_departure->format('d M Y, H:i') : 'Belum Berangkat' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-t-4 border-emerald-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Titik Tujuan (Destination)</span>
                            <h3 class="text-xl font-bold text-gray-900 mt-1">{{ $shipment->destination?->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $shipment->destination?->city }}, {{ $shipment->destination?->country }}</p>
                        </div>
                        <span class="font-mono text-xs font-bold bg-emerald-50 text-emerald-700 px-2 py-1 rounded">
                            {{ $shipment->destination?->code ?? 'N/A' }}
                        </span>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Estimasi Tiba (ETA):</span>
                            <span class="font-bold text-indigo-700">{{ $shipment->estimated_arrival?->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Jadwal Asli (Expected):</span>
                            <span class="font-medium text-gray-900">{{ $shipment->expected_arrival?->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Aktual Kedatangan:</span>
                            <span class="font-medium text-gray-900">{{ $shipment->actual_arrival ? $shipment->actual_arrival->format('d M Y, H:i') : 'Belum Tiba' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cargo Specifications Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4 border-b pb-2">Spesifikasi Kargo</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <span class="text-xs text-gray-500">Kategori Kargo</span>
                        <div class="text-base font-semibold text-gray-900 mt-0.5">{{ $shipment->cargo_type }}</div>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <span class="text-xs text-gray-500">Total Berat Kotor</span>
                        <div class="text-base font-semibold text-gray-900 mt-0.5">{{ number_format($shipment->weight, 2) }} kg</div>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <span class="text-xs text-gray-500">Total Volume</span>
                        <div class="text-base font-semibold text-gray-900 mt-0.5">{{ number_format($shipment->volume, 2) }} m³ (CBM)</div>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <span class="text-xs text-gray-500">Layanan Carrier</span>
                        <div class="text-base font-semibold text-gray-900 capitalize mt-0.5">{{ $shipment->carrier?->service_type }} Freight</div>
                    </div>
                </div>
            </div>

            <!-- Shipment Items Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-gray-900">Daftar Barang (Manifest Items)</h3>
                    <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">
                        {{ $shipment->items->count() }} Komoditas Terdaftar
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode / SKU</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah (Qty)</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Berat (kg)</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Volume (m³)</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nilai Deklarasi ($)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($shipment->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $item->item_name }}</td>
                                    <td class="px-4 py-3 text-sm font-mono text-gray-500">{{ $item->item_code ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-800 font-medium">{{ number_format($item->quantity) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-800">{{ number_format($item->weight, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-800">{{ $item->volume ? number_format($item->volume, 2) : '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-medium text-emerald-700">
                                        {{ $item->declared_value ? '$' . number_format($item->declared_value, 2) : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada item barang yang dicatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50 font-semibold text-sm">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-gray-700">Total Keseluruhan</td>
                                <td class="px-4 py-3 text-right text-gray-900">{{ number_format($shipment->items->sum('quantity')) }}</td>
                                <td class="px-4 py-3 text-right text-gray-900">{{ number_format($shipment->items->sum('weight'), 2) }} kg</td>
                                <td class="px-4 py-3 text-right text-gray-900">{{ number_format($shipment->items->sum('volume'), 2) }} m³</td>
                                <td class="px-4 py-3 text-right text-emerald-800">
                                    ${{ number_format($shipment->items->sum('declared_value'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Tracking Timeline & Exceptions Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Tracking Timeline (2 cols) -->
                <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 border-b pb-2">Riwayat Perjalanan (Tracking Timeline)</h3>

                    <div class="relative border-l-2 border-indigo-200 ms-4 space-y-6">
                        @forelse ($shipment->trackingEvents as $event)
                            <div class="relative ps-6">
                                <span class="absolute -start-2 top-1.5 w-4 h-4 rounded-full bg-indigo-600 border-2 border-white ring-2 ring-indigo-200"></span>
                                <div class="flex justify-between items-start">
                                    <h4 class="text-sm font-bold text-gray-900">{{ $event->status }}</h4>
                                    <time class="text-xs text-gray-500 font-mono">{{ $event->event_time->format('d M Y, H:i') }}</time>
                                </div>
                                @if($event->location)
                                    <p class="text-xs font-medium text-indigo-700 mt-0.5">
                                        <svg class="w-3.5 h-3.5 inline me-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                        {{ $event->location->name }} ({{ $event->location->city }})
                                    </p>
                                @endif
                                @if($event->description)
                                    <p class="text-sm text-gray-600 mt-1">{{ $event->description }}</p>
                                @endif
                                @if($event->creator)
                                    <span class="text-xs text-gray-400">Dicatat oleh: {{ $event->creator->name }}</span>
                                @endif
                            </div>
                        @empty
                            <div class="ps-6 text-sm text-gray-500">
                                Belum ada tracking event yang dicatat.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Exceptions Column -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 border-b pb-2">Insiden / Kendala (Exceptions)</h3>

                    <div class="space-y-4">
                        @forelse ($shipment->exceptions as $exc)
                            <div class="p-4 rounded-lg border {{ $exc->is_resolved ? 'bg-gray-50 border-gray-200' : 'bg-red-50 border-red-200' }}">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-sm {{ $exc->is_resolved ? 'text-gray-700' : 'text-red-800' }}">
                                        {{ $exc->exception_type }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase
                                        @if($exc->severity === 'critical') bg-red-200 text-red-900
                                        @elseif($exc->severity === 'high') bg-orange-200 text-orange-900
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ $exc->severity }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 mt-1">{{ $exc->description }}</p>
                                <div class="mt-2 text-xs font-mono text-gray-400">
                                    {{ $exc->occurred_at->format('d M Y, H:i') }}
                                </div>
                                <div class="mt-2 pt-2 border-t border-gray-200 flex justify-between items-center text-xs">
                                    <span>Status Solusi:</span>
                                    @if($exc->is_resolved)
                                        <span class="text-emerald-700 font-semibold">Terselesaikan</span>
                                    @else
                                        <span class="text-red-700 font-semibold">Belum Selesai</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400">
                                <svg class="w-10 h-10 mx-auto text-emerald-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-sm font-medium text-gray-700">Tidak ada kendala</p>
                                <p class="text-xs text-gray-500">Pengiriman berjalan lancar tanpa exception.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
