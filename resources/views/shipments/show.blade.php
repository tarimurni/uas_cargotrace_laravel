<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="font-mono text-2xl font-bold text-gray-900">
                        {{ $shipment->shipment_number }}
                    </h2>
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full uppercase
                        @if(in_array(strtoupper($shipment->current_status), ['DELIVERED'])) bg-green-100 text-green-800 border border-green-200
                        @elseif(in_array(strtoupper($shipment->current_status), ['DELAYED'])) bg-red-100 text-red-800 border border-red-200
                        @elseif(in_array(strtoupper($shipment->current_status), ['IN_TRANSIT', 'PICKED_UP'])) bg-blue-100 text-blue-800 border border-blue-200
                        @elseif(in_array(strtoupper($shipment->current_status), ['BOOKED'])) bg-purple-100 text-purple-800 border border-purple-200
                        @else bg-yellow-100 text-yellow-800 border border-yellow-200 @endif">
                        {{ str_replace('_', ' ', $shipment->current_status) }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Customer: <span class="font-semibold text-gray-700">{{ $shipment->customer?->name }}</span> |
                    Carrier: <span class="font-semibold text-gray-700">{{ $shipment->carrier?->name }} ({{ $shipment->carrier?->code }})</span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @can('update', $shipment)
                    <button type="button" onclick="document.getElementById('addCheckpointModal').classList.remove('hidden')" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                        + Update Checkpoint
                    </button>
                    <a href="{{ route('shipments.edit', $shipment) }}" class="inline-flex items-center px-3 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit
                    </a>
                @endcan
                <a href="{{ route('shipments.index') }}" class="inline-flex items-center px-3 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Latest Reported Location Banner (Lokasi Terkini dari Event Terbaru) -->
            @php
                $currentLoc = $latestEvent?->location ?? $shipment->origin;
                $currentLat = $latestEvent?->latitude ?? $currentLoc?->latitude;
                $currentLng = $latestEvent?->longitude ?? $currentLoc?->longitude;
            @endphp
            <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-xl p-6 shadow-md border border-slate-800">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <span class="flex h-3 w-3 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-bold text-emerald-400 tracking-wider uppercase">Lokasi Terkini (Current Location Tracking)</span>
                        </div>
                        <h3 class="text-xl font-extrabold text-white">
                            {{ $currentLoc ? $currentLoc->name . ' (' . $currentLoc->city . ', ' . $currentLoc->country . ')' : 'Lokasi Sedang Diperbarui' }}
                        </h3>
                        <p class="text-xs text-slate-300">
                            Status Terakhir: <span class="font-semibold text-white">{{ $latestEvent?->status ?? 'Created' }}</span>
                            @if($latestEvent)
                                &bull; <span class="text-slate-400">{{ $latestEvent->event_time->format('d M Y, H:i') }} ({{ $latestEvent->event_time->diffForHumans() }})</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        @if($currentLat && $currentLng)
                            <div class="bg-slate-800/80 px-3.5 py-2 rounded-lg border border-slate-700">
                                <span class="text-[10px] text-slate-400 uppercase tracking-widest block font-semibold">GPS Coordinates</span>
                                <span class="font-mono text-xs text-emerald-300 font-bold">
                                    {{ number_format($currentLat, 5) }}, {{ number_format($currentLng, 5) }}
                                </span>
                            </div>

                            <a href="https://www.google.com/maps?q={{ $currentLat }},{{ $currentLng }}" target="_blank" class="inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                Buka di Maps
                            </a>
                        @endif
                    </div>
                </div>
            </div>

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

            <!-- Historical Tracking Timeline (Kronologis & Pembeda Event Normal vs Exception) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6 border-b pb-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Kronologis Perjalanan (Historical Tracking Timeline)</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Urutan riwayat perpindahan kargo, checkpoint pelabuhan/bandara, dan catatan insiden.</p>
                    </div>

                    <div class="flex items-center space-x-3 text-xs">
                        <span class="inline-flex items-center">
                            <span class="w-3 h-3 bg-indigo-500 rounded-full me-1.5"></span>
                            Event Normal
                        </span>
                        <span class="inline-flex items-center">
                            <span class="w-3 h-3 bg-red-500 rounded-full me-1.5"></span>
                            Exception / Kendala
                        </span>
                    </div>
                </div>

                <div class="relative border-l-2 border-indigo-200 ms-4 space-y-6">
                    @forelse ($timeline as $index => $item)
                        @if ($item->is_exception)
                            <!-- EXCEPTION / INCIDENT TIMELINE ITEM -->
                            <div class="relative ps-8">
                                <span class="absolute -start-3 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 ring-4 ring-red-100 text-white">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </span>

                                <div class="p-4 rounded-xl border border-red-200 bg-red-50/70 shadow-sm space-y-2">
                                    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-red-600 text-white">
                                                EXCEPTION
                                            </span>
                                            <h4 class="text-sm font-bold text-red-900">{{ $item->status }}</h4>
                                        </div>
                                        <time class="text-xs font-mono font-medium text-red-700">
                                            {{ $item->timestamp->format('d M Y, H:i') }}
                                        </time>
                                    </div>

                                    @if($item->location)
                                        <div class="text-xs font-semibold text-red-800 flex items-center">
                                            <svg class="w-3.5 h-3.5 me-1 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                            {{ $item->location->name }} ({{ $item->location->city }})
                                        </div>
                                    @endif

                                    @if($item->description)
                                        <p class="text-xs text-red-800 leading-relaxed">{{ $item->description }}</p>
                                    @endif

                                    @if($item->creator)
                                        <div class="text-[11px] text-red-600/80">
                                            Dilaporkan oleh: <span class="font-medium">{{ $item->creator->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <!-- NORMAL CHECKPOINT TIMELINE ITEM -->
                            <div class="relative ps-8">
                                <span class="absolute -start-3 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 ring-4 ring-indigo-100 text-white">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>

                                <div class="p-4 rounded-xl border border-gray-200 bg-white hover:bg-gray-50/50 shadow-sm space-y-2">
                                    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-indigo-100 text-indigo-800">
                                                CHECKPOINT #{{ $index + 1 }}
                                            </span>
                                            <h4 class="text-sm font-bold text-gray-900">{{ $item->status }}</h4>
                                        </div>
                                        <time class="text-xs font-mono font-medium text-gray-500">
                                            {{ $item->timestamp->format('d M Y, H:i') }}
                                        </time>
                                    </div>

                                    @if($item->location)
                                        <div class="text-xs font-medium text-indigo-700 flex items-center">
                                            <svg class="w-3.5 h-3.5 me-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                            {{ $item->location->name }} ({{ $item->location->city }}, {{ $item->location->country }})
                                            @if($item->latitude && $item->longitude)
                                                <span class="ms-2 font-mono text-[11px] text-gray-400">[{{ number_format($item->latitude, 4) }}, {{ number_format($item->longitude, 4) }}]</span>
                                            @endif
                                        </div>
                                    @endif

                                    @if($item->description)
                                        <p class="text-xs text-gray-600 leading-relaxed">{{ $item->description }}</p>
                                    @endif

                                    @if($item->creator)
                                        <div class="text-[11px] text-gray-400">
                                            Dicatat oleh: <span class="font-medium text-gray-600">{{ $item->creator->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="ps-8 text-sm text-gray-500">
                            Belum ada riwayat tracking timeline untuk pengiriman ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- Add Checkpoint Modal (For Logistics Operator & Admin) -->
    @can('update', $shipment)
        <div id="addCheckpointModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 space-y-4">
                <div class="flex justify-between items-center border-b pb-3">
                    <h3 class="text-lg font-bold text-gray-900">Tambah Event Checkpoint Tracking</h3>
                    <button type="button" onclick="document.getElementById('addCheckpointModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('shipments.tracking-events.store', $shipment) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="status" class="block text-xs font-semibold text-gray-700 uppercase">Status Checkpoint *</label>
                        <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="BOOKED">BOOKED (Pesanan Dikonfirmasi)</option>
                            <option value="PICKED_UP">PICKED_UP (Kargo Dijemput / Dimuat)</option>
                            <option value="IN_TRANSIT">IN_TRANSIT (Dalam Perjalanan)</option>
                            <option value="DELIVERED">DELIVERED (Tiba di Tujuan & Selesai)</option>
                            <option value="Customs Inspection Checkpoint">Customs Inspection Checkpoint</option>
                            <option value="Port Terminal Departure">Port Terminal Departure</option>
                            <option value="Port Terminal Arrival">Port Terminal Arrival</option>
                        </select>
                    </div>

                    <div>
                        <label for="location_id" class="block text-xs font-semibold text-gray-700 uppercase">Lokasi Checkpoint</label>
                        <select name="location_id" id="location_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Pilih Lokasi Terdaftar</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->city }})</option>
                            @endforeach
                        </select>
                        <span class="text-[11px] text-gray-400">Koordinat GPS akan otomatis terisi dari master lokasi jika tidak diisi manual.</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="latitude" class="block text-xs font-semibold text-gray-700 uppercase">Latitude (Opsional)</label>
                            <input type="number" step="0.0000001" name="latitude" id="latitude" placeholder="-6.10174" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                        </div>
                        <div>
                            <label for="longitude" class="block text-xs font-semibold text-gray-700 uppercase">Longitude (Opsional)</label>
                            <input type="number" step="0.0000001" name="longitude" id="longitude" placeholder="106.88377" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="event_time" class="block text-xs font-semibold text-gray-700 uppercase">Waktu Checkpoint</label>
                        <input type="datetime-local" name="event_time" id="event_time" value="{{ date('Y-m-d\TH:i') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    </div>

                    <div>
                        <label for="description" class="block text-xs font-semibold text-gray-700 uppercase">Deskripsi / Catatan Perjalanan</label>
                        <textarea name="description" id="description" rows="2" placeholder="Informasi status kontainer, nomor seal, kondisi cuaca, dll..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"></textarea>
                    </div>

                    <div class="flex items-center">
                        <input type="hidden" name="update_shipment_status" value="0">
                        <input type="checkbox" name="update_shipment_status" id="update_shipment_status" value="1" checked class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="update_shipment_status" class="ms-2 block text-xs text-gray-700 font-medium">Perbarui status utama pengiriman sesuai checkpoint ini</label>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" onclick="document.getElementById('addCheckpointModal').classList.add('hidden')" class="px-4 py-2 border rounded-md text-xs font-semibold text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold uppercase hover:bg-indigo-700">
                            Simpan Checkpoint
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
</x-app-layout>
