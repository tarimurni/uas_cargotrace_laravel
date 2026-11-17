<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tambah Lokasi / Hub Kargo Baru') }}
            </h2>
            <a href="{{ route('locations.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                &larr; Kembali ke daftar
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('locations.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Lokasi / Pelabuhan / Terminal *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Contoh: Pelabuhan Tanjung Priok, Bandara Soekarno-Hatta" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700">Kode Lokasi (UN/LOCODE / IATA)</label>
                            <input type="text" name="code" id="code" value="{{ old('code') }}" placeholder="Contoh: IDTPP, IDCGK, SGSIN" class="mt-1 block w-full uppercase font-mono rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('code') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Tipe Fasilitas *</label>
                            <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="seaport" {{ old('type') === 'seaport' ? 'selected' : '' }}>Pelabuhan Laut (Seaport)</option>
                                <option value="airport" {{ old('type') === 'airport' ? 'selected' : '' }}>Bandara Udara (Airport)</option>
                                <option value="warehouse" {{ old('type') === 'warehouse' ? 'selected' : '' }}>Gudang Logistik (Warehouse)</option>
                                <option value="hub" {{ old('type') === 'hub' ? 'selected' : '' }}>Hub Distribusi Kargo</option>
                            </select>
                            @error('type') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">Kota / Wilayah *</label>
                            <input type="text" name="city" id="city" value="{{ old('city') }}" required placeholder="Contoh: Jakarta Utara, Surabaya" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('city') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700">Negara *</label>
                            <input type="text" name="country" id="country" value="{{ old('country', 'Indonesia') }}" required placeholder="Contoh: Indonesia, Singapore" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('country') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude (Garis Lintang) *</label>
                            <input type="number" step="0.0000001" name="latitude" id="latitude" value="{{ old('latitude') }}" required placeholder="Contoh: -6.1017400" class="mt-1 block w-full font-mono rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <span class="text-xs text-gray-400">Rentang: -90 s/d 90</span>
                            @error('latitude') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude (Garis Bujur) *</label>
                            <input type="number" step="0.0000001" name="longitude" id="longitude" value="{{ old('longitude') }}" required placeholder="Contoh: 106.8837700" class="mt-1 block w-full font-mono rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <span class="text-xs text-gray-400">Rentang: -180 s/d 180</span>
                            @error('longitude') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('locations.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Simpan Lokasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
