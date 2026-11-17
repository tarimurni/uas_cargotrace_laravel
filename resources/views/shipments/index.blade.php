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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('shipments.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700">Pencarian</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari nomor shipment, jenis kargo, nama item..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="current_status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="current_status" id="current_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('current_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_transit" {{ request('current_status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                            <option value="customs_clearance" {{ request('current_status') === 'customs_clearance' ? 'selected' : '' }}>Customs Clearance</option>
                            <option value="delayed" {{ request('current_status') === 'delayed' ? 'selected' : '' }}>Delayed</option>
                            <option value="delivered" {{ request('current_status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ request('current_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label for="carrier_id" class="block text-sm font-medium text-gray-700">Carrier</label>
                        <select name="carrier_id" id="carrier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Semua Carrier</option>
                            @foreach ($carriers as $c)
                                <option value="{{ $c->id }}" {{ request('carrier_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end space-x-2">
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none sm:text-sm">
                            Filter
                        </button>
                        <a href="{{ route('shipments.index') }}" class="inline-flex justify-center items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none sm:text-sm">
                            Reset
                        </a>
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
