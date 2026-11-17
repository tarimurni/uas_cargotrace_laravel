<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Carrier: ' . $carrier->name) }}
            </h2>
            <a href="{{ route('carriers.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                &larr; Kembali ke daftar
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('carriers.update', $carrier) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Perusahaan Carrier *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $carrier->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700">Kode Unik Carrier *</label>
                            <input type="text" name="code" id="code" value="{{ old('code', $carrier->code) }}" required class="mt-1 block w-full uppercase font-mono rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('code') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="service_type" class="block text-sm font-medium text-gray-700">Tipe Layanan *</label>
                            <select name="service_type" id="service_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="multimodal" {{ old('service_type', $carrier->service_type) === 'multimodal' ? 'selected' : '' }}>Multimodal (Kombinasi)</option>
                                <option value="sea" {{ old('service_type', $carrier->service_type) === 'sea' ? 'selected' : '' }}>Sea Freight (Laut)</option>
                                <option value="air" {{ old('service_type', $carrier->service_type) === 'air' ? 'selected' : '' }}>Air Freight (Udara)</option>
                                <option value="land" {{ old('service_type', $carrier->service_type) === 'land' ? 'selected' : '' }}>Land Freight (Darat)</option>
                            </select>
                            @error('service_type') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700">Email Operasional / CS</label>
                            <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $carrier->contact_email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('contact_email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700">Nomor Telepon Kontak</label>
                            <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $carrier->contact_phone) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('contact_phone') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $carrier->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="is_active" class="ms-2 block text-sm text-gray-900">Carrier Aktif (dapat dipilih untuk pemesanan pengiriman)</label>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('carriers.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Perbarui Carrier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
