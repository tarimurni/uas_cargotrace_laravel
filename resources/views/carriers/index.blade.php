<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Carriers / Ekspedisi') }}
            </h2>
            @can('create', App\Models\Carrier::class)
                <a href="{{ route('carriers.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Carrier
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Filter & Search Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('carriers.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700">Pencarian</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari nama, kode carrier..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="service_type" class="block text-sm font-medium text-gray-700">Tipe Layanan</label>
                        <select name="service_type" id="service_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Semua Layanan</option>
                            <option value="sea" {{ request('service_type') === 'sea' ? 'selected' : '' }}>Sea Freight</option>
                            <option value="air" {{ request('service_type') === 'air' ? 'selected' : '' }}>Air Freight</option>
                            <option value="land" {{ request('service_type') === 'land' ? 'selected' : '' }}>Land Freight</option>
                            <option value="multimodal" {{ request('service_type') === 'multimodal' ? 'selected' : '' }}>Multimodal</option>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none sm:text-sm">
                            Filter
                        </button>
                        <a href="{{ route('carriers.index') }}" class="inline-flex justify-center items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none sm:text-sm">
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carrier</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layanan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shipments</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($carriers as $carrier)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-gray-900">{{ $carrier->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded font-mono text-xs font-bold bg-gray-100 text-gray-800">
                                            {{ $carrier->code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">
                                        {{ $carrier->service_type }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ $carrier->contact_email ?? '-' }}</div>
                                        <div class="text-xs text-gray-400">{{ $carrier->contact_phone ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <span class="font-semibold">{{ $carrier->shipments_count }}</span> pengiriman
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($carrier->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Non-Aktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <a href="{{ route('carriers.show', $carrier) }}" class="text-indigo-600 hover:text-indigo-900">Detail</a>
                                        @can('update', $carrier)
                                            <a href="{{ route('carriers.edit', $carrier) }}" class="text-amber-600 hover:text-amber-900">Edit</a>
                                        @endcan
                                        @can('delete', $carrier)
                                            <form action="{{ route('carriers.destroy', $carrier) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus carrier ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                        Tidak ada data carrier yang ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($carriers->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $carriers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
