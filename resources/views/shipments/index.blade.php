<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Pengiriman Kargo (Shipments)') }}
            </h2>
            @can('create', App\Models\Shipment::class)
                <a href="{{ route('shipments.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Pengiriman Baru
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Filter & Search Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
                <form method="GET" action="{{ route('shipments.index') }}" class="space-y-4">
                    <!-- Row 1: Search, Status, Carrier -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="block text-xs font-semibold text-gray-700 uppercase">Pencarian Cepat</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nomor shipment, jenis kargo, atau nama item..." class="pl-9 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                            </div>
                        </div>

                        <div>
                            <label for="current_status" class="block text-xs font-semibold text-gray-700 uppercase">Status Pengiriman</label>
                            <select name="current_status" id="current_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                                <option value="">Semua Status</option>
                                @foreach ($statuses as $val => $lbl)
                                    <option value="{{ $val }}" {{ strtoupper(request('current_status')) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="carrier_id" class="block text-xs font-semibold text-gray-700 uppercase">Carrier (Armada)</label>
                            <select name="carrier_id" id="carrier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                                <option value="">Semua Carrier</option>
                                @foreach ($carriers as $c)
                                    <option value="{{ $c->id }}" {{ request('carrier_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Row 2: Origin, Destination, Date Range, Delay Status, Sorting -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3 pt-3 border-t border-gray-100">
                        <div>
                            <label for="origin_id" class="block text-xs font-semibold text-gray-700 uppercase">Lokasi Asal</label>
                            <select name="origin_id" id="origin_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                                <option value="">Semua Asal</option>
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ request('origin_id') == $loc->id ? 'selected' : '' }}>{{ $loc->city }} - {{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="destination_id" class="block text-xs font-semibold text-gray-700 uppercase">Lokasi Tujuan</label>
                            <select name="destination_id" id="destination_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                                <option value="">Semua Tujuan</option>
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ request('destination_id') == $loc->id ? 'selected' : '' }}>{{ $loc->city }} - {{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_from" class="block text-xs font-semibold text-gray-700 uppercase">Berangkat Dari</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                        </div>

                        <div>
                            <label for="date_to" class="block text-xs font-semibold text-gray-700 uppercase">Sampai Tanggal</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                        </div>

                        <div>
                            <label for="is_delayed" class="block text-xs font-semibold text-gray-700 uppercase">Status Delay</label>
                            <select name="is_delayed" id="is_delayed" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                                <option value="">Semua</option>
                                <option value="1" {{ request('is_delayed') === '1' ? 'selected' : '' }}>Terlambat (Delayed)</option>
                                <option value="0" {{ request('is_delayed') === '0' ? 'selected' : '' }}>Tepat Waktu (On Schedule)</option>
                            </select>
                        </div>

                        <div>
                            <label for="sort_by" class="block text-xs font-semibold text-gray-700 uppercase">Urutkan</label>
                            <div class="flex space-x-1 mt-1">
                                <select name="sort_by" id="sort_by" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                                    <option value="created_at" {{ ($sortBy ?? '') === 'created_at' ? 'selected' : '' }}>Tgl Buat</option>
                                    <option value="shipment_number" {{ ($sortBy ?? '') === 'shipment_number' ? 'selected' : '' }}>No. Shipment</option>
                                    <option value="expected_departure" {{ ($sortBy ?? '') === 'expected_departure' ? 'selected' : '' }}>Tgl Berangkat</option>
                                    <option value="expected_arrival" {{ ($sortBy ?? '') === 'expected_arrival' ? 'selected' : '' }}>Tgl Tiba</option>
                                    <option value="weight" {{ ($sortBy ?? '') === 'weight' ? 'selected' : '' }}>Berat</option>
                                    <option value="delay_hours" {{ ($sortBy ?? '') === 'delay_hours' ? 'selected' : '' }}>Jam Delay</option>
                                </select>
                                <select name="sort_direction" class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                                    <option value="desc" {{ ($sortDirection ?? '') === 'desc' ? 'selected' : '' }}>&darr;</option>
                                    <option value="asc" {{ ($sortDirection ?? '') === 'asc' ? 'selected' : '' }}>&uarr;</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-xs text-gray-500">
                            Menampilkan <strong class="text-gray-900">{{ $shipments->total() }}</strong> pengiriman ditemukan
                        </span>

                        <div class="flex items-center space-x-2">
                            <a href="{{ route('shipments.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-md transition">
                                Reset Filter
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-md shadow-xs transition">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Shipment</th>
                                @if(!Auth::user()->isCustomer())
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                @endif
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carrier</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rute (Asal &rarr; Tujuan)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kargo & Berat</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estimasi Tiba</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($shipments as $shipment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-mono font-bold text-indigo-600">
                                            <a href="{{ route('shipments.show', $shipment) }}">{{ $shipment->shipment_number }}</a>
                                        </div>
                                        <div class="text-xs text-gray-400">{{ $shipment->items->count() }} item barang</div>
                                    </td>
                                    @if(!Auth::user()->isCustomer())
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                            {{ $shipment->customer?->name ?? 'N/A' }}
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $shipment->carrier?->name }}</div>
                                        <div class="text-xs text-gray-400 uppercase font-mono">{{ $shipment->carrier?->code }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <div class="font-medium text-gray-900">{{ $shipment->origin?->city }} &rarr; {{ $shipment->destination?->city }}</div>
                                        <div class="text-xs text-gray-400">{{ $shipment->origin?->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <div class="font-medium text-gray-800">{{ $shipment->cargo_type }}</div>
                                        <div class="text-xs text-gray-500">{{ number_format($shipment->weight, 2) }} kg | {{ number_format($shipment->volume, 2) }} m³</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <div>{{ $shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y, H:i') : '-' }}</div>
                                        @if($shipment->is_delayed)
                                            <span class="inline-flex items-center text-xs font-semibold text-red-600">
                                                <svg class="w-3.5 h-3.5 me-1 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                Delay +{{ $shipment->delay_hours }}j
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full uppercase
                                            @if($shipment->current_status === 'delivered') bg-green-100 text-green-800 border border-green-200
                                            @elseif($shipment->current_status === 'delayed') bg-red-100 text-red-800 border border-red-200
                                            @elseif($shipment->current_status === 'in_transit') bg-blue-100 text-blue-800 border border-blue-200
                                            @elseif($shipment->current_status === 'customs_clearance') bg-purple-100 text-purple-800 border border-purple-200
                                            @elseif($shipment->current_status === 'cancelled') bg-gray-100 text-gray-800 border border-gray-200
                                            @else bg-yellow-100 text-yellow-800 border border-yellow-200 @endif">
                                            {{ str_replace('_', ' ', $shipment->current_status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <a href="{{ route('shipments.show', $shipment) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">Detail</a>
                                        @can('update', $shipment)
                                            <a href="{{ route('shipments.edit', $shipment) }}" class="text-amber-600 hover:text-amber-900 font-semibold">Edit</a>
                                        @endcan
                                        @can('delete', $shipment)
                                            <form action="{{ route('shipments.destroy', $shipment) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pengiriman ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Hapus</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                        Tidak ada data pengiriman yang ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($shipments->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $shipments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
