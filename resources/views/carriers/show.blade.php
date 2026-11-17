<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Carrier: ' . $carrier->name) }}
            </h2>
            <div class="space-x-2">
                @can('update', $carrier)
                    <a href="{{ route('carriers.edit', $carrier) }}" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700">
                        Edit Carrier
                    </a>
                @endcan
                <a href="{{ route('carriers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Carrier Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Nama Carrier</span>
                        <div class="text-lg font-bold text-gray-900 mt-1">{{ $carrier->name }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Kode Unik</span>
                        <div class="mt-1">
                            <span class="font-mono text-base font-bold bg-gray-100 text-gray-900 px-2.5 py-0.5 rounded">{{ $carrier->code }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Layanan</span>
                        <div class="text-base text-gray-900 capitalize mt-1">{{ $carrier->service_type }} Freight</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Status</span>
                        <div class="mt-1">
                            @if ($carrier->is_active)
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                            @else
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Non-Aktif</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Email Kontak</span>
                        <div class="text-sm text-gray-800 mt-1">{{ $carrier->contact_email ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Telepon Kontak</span>
                        <div class="text-sm text-gray-800 mt-1">{{ $carrier->contact_phone ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Dibuat Pada</span>
                        <div class="text-sm text-gray-800 mt-1">{{ $carrier->created_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent Shipments by Carrier -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Pengiriman Terbaru Melalui Carrier Ini</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No. Shipment</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rute</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jenis Kargo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($carrier->shipments as $shipment)
                                <tr>
                                    <td class="px-4 py-3 font-mono font-semibold text-indigo-600">
                                        <a href="{{ route('shipments.show', $shipment) }}">{{ $shipment->shipment_number }}</a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ $shipment->origin?->city }} &rarr; {{ $shipment->destination?->city }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $shipment->cargo_type }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase
                                            @if($shipment->current_status === 'delivered') bg-green-100 text-green-800
                                            @elseif($shipment->current_status === 'delayed') bg-red-100 text-red-800
                                            @elseif($shipment->current_status === 'in_transit') bg-blue-100 text-blue-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ str_replace('_', ' ', $shipment->current_status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <a href="{{ route('shipments.show', $shipment) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">Lihat</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada riwayat pengiriman untuk carrier ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
