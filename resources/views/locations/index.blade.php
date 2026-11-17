<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Lokasi & Hub Kargo') }}
            </h2>
            @can('create', App\Models\Location::class)
                <a href="{{ route('locations.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Lokasi
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Filter & Search Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('locations.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700">Pencarian</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari nama, kota, negara, atau kode..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Tipe Fasilitas</label>
                        <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Semua Tipe</option>
                            <option value="seaport" {{ request('type') === 'seaport' ? 'selected' : '' }}>Pelabuhan (Seaport)</option>
                            <option value="airport" {{ request('type') === 'airport' ? 'selected' : '' }}>Bandara (Airport)</option>
                            <option value="warehouse" {{ request('type') === 'warehouse' ? 'selected' : '' }}>Gudang (Warehouse)</option>
                            <option value="hub" {{ request('type') === 'hub' ? 'selected' : '' }}>Cargo Hub</option>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none sm:text-sm">
                            Filter
                        </button>
                        <a href="{{ route('locations.index') }}" class="inline-flex justify-center items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none sm:text-sm">
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lokasi</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kota & Negara</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Koordinat GPS</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktivitas</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($locations as $location)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-gray-900">{{ $location->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded font-mono text-xs font-bold bg-gray-100 text-gray-800">
                                            {{ $location->code ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($location->type === 'seaport') bg-blue-100 text-blue-800
                                            @elseif($location->type === 'airport') bg-sky-100 text-sky-800
                                            @elseif($location->type === 'warehouse') bg-amber-100 text-amber-800
                                            @else bg-purple-100 text-purple-800 @endif">
                                            {{ $location->type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $location->city }}, <span class="font-medium text-gray-900">{{ $location->country }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-500">
                                        {{ number_format($location->latitude, 5) }}, {{ number_format($location->longitude, 5) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                                        <div>Asal: <span class="font-semibold">{{ $location->origin_shipments_count }}</span></div>
                                        <div>Tujuan: <span class="font-semibold">{{ $location->destination_shipments_count }}</span></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <a href="{{ route('locations.show', $location) }}" class="text-indigo-600 hover:text-indigo-900">Detail</a>
                                        @can('update', $location)
                                            <a href="{{ route('locations.edit', $location) }}" class="text-amber-600 hover:text-amber-900">Edit</a>
                                        @endcan
                                        @can('delete', $location)
                                            <form action="{{ route('locations.destroy', $location) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?');">
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
                                        Tidak ada data lokasi yang ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($locations->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $locations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
