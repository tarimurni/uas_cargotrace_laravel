<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Pengiriman: ' . $shipment->shipment_number) }}
            </h2>
            <a href="{{ route('shipments.show', $shipment) }}" class="text-sm text-gray-600 hover:text-gray-900">
                &larr; Kembali ke detail
            </a>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        items: {{ json_encode(old('items', $shipment->items->map(function($i) {
            return [
                'id' => $i->id,
                'item_name' => $i->item_name,
                'item_code' => $i->item_code,
                'quantity' => $i->quantity,
                'weight' => (float)$i->weight,
                'volume' => (float)$i->volume,
                'declared_value' => (float)$i->declared_value,
            ];
        })->toArray())) }},
        addItem() {
            this.items.push({ id: null, item_name: '', item_code: '', quantity: 1, weight: '', volume: '', declared_value: '' });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            } else {
                alert('Minimal harus ada 1 item barang dalam pengiriman.');
            }
        }
    }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('shipments.update', $shipment) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Basic Shipment Info Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-3">Informasi Utama Pengiriman</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="shipment_number" class="block text-sm font-medium text-gray-700">Nomor Shipment (Tracking #) *</label>
                            <input type="text" name="shipment_number" id="shipment_number" value="{{ old('shipment_number', $shipment->shipment_number) }}" required class="mt-1 block w-full uppercase font-mono rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('shipment_number') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="customer_id" class="block text-sm font-medium text-gray-700">Customer (Pemilik Kargo) *</label>
                            <select name="customer_id" id="customer_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @foreach ($customers as $cust)
                                    <option value="{{ $cust->id }}" {{ old('customer_id', $shipment->customer_id) == $cust->id ? 'selected' : '' }}>
                                        {{ $cust->name }} ({{ $cust->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="carrier_id" class="block text-sm font-medium text-gray-700">Carrier / Ekspedisi *</label>
                            <select name="carrier_id" id="carrier_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @foreach ($carriers as $c)
                                    <option value="{{ $c->id }}" {{ old('carrier_id', $shipment->carrier_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->code }} - {{ $c->service_type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('carrier_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="origin_id" class="block text-sm font-medium text-gray-700">Lokasi Asal (Origin) *</label>
                            <select name="origin_id" id="origin_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('origin_id', $shipment->origin_id) == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})
                                    </option>
                                @endforeach
                            </select>
                            @error('origin_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="destination_id" class="block text-sm font-medium text-gray-700">Lokasi Tujuan (Destination) *</label>
                            <select name="destination_id" id="destination_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('destination_id', $shipment->destination_id) == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})
                                    </option>
                                @endforeach
                            </select>
                            @error('destination_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="cargo_type" class="block text-sm font-medium text-gray-700">Kategori / Jenis Kargo *</label>
                            <input type="text" name="cargo_type" id="cargo_type" value="{{ old('cargo_type', $shipment->cargo_type) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('cargo_type') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700">Total Berat (kg) *</label>
                            <input type="number" step="0.01" name="weight" id="weight" value="{{ old('weight', $shipment->weight) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('weight') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="volume" class="block text-sm font-medium text-gray-700">Total Volume (m³ / CBM) *</label>
                            <input type="number" step="0.01" name="volume" id="volume" value="{{ old('volume', $shipment->volume) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('volume') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="expected_departure" class="block text-sm font-medium text-gray-700">Jadwal Keberangkatan *</label>
                            <input type="datetime-local" name="expected_departure" id="expected_departure" value="{{ old('expected_departure', $shipment->expected_departure?->format('Y-m-d\TH:i')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('expected_departure') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="expected_arrival" class="block text-sm font-medium text-gray-700">Jadwal Tiba (Expected) *</label>
                            <input type="datetime-local" name="expected_arrival" id="expected_arrival" value="{{ old('expected_arrival', $shipment->expected_arrival?->format('Y-m-d\TH:i')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('expected_arrival') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="estimated_arrival" class="block text-sm font-medium text-gray-700">Estimasi Tiba Terkini (ETA) *</label>
                            <input type="datetime-local" name="estimated_arrival" id="estimated_arrival" value="{{ old('estimated_arrival', $shipment->estimated_arrival?->format('Y-m-d\TH:i')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('estimated_arrival') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="actual_departure" class="block text-sm font-medium text-gray-700">Aktual Keberangkatan</label>
                            <input type="datetime-local" name="actual_departure" id="actual_departure" value="{{ old('actual_departure', $shipment->actual_departure?->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('actual_departure') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="actual_arrival" class="block text-sm font-medium text-gray-700">Aktual Kedatangan</label>
                            <input type="datetime-local" name="actual_arrival" id="actual_arrival" value="{{ old('actual_arrival', $shipment->actual_arrival?->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('actual_arrival') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="current_status" class="block text-sm font-medium text-gray-700">Status Pengiriman *</label>
                            <select name="current_status" id="current_status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="pending" {{ old('current_status', $shipment->current_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_transit" {{ old('current_status', $shipment->current_status) === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                <option value="customs_clearance" {{ old('current_status', $shipment->current_status) === 'customs_clearance' ? 'selected' : '' }}>Customs Clearance</option>
                                <option value="delayed" {{ old('current_status', $shipment->current_status) === 'delayed' ? 'selected' : '' }}>Delayed</option>
                                <option value="delivered" {{ old('current_status', $shipment->current_status) === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ old('current_status', $shipment->current_status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('current_status') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center pt-6">
                            <input type="hidden" name="is_delayed" value="0">
                            <input type="checkbox" name="is_delayed" id="is_delayed" value="1" {{ old('is_delayed', $shipment->is_delayed) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <label for="is_delayed" class="ms-2 block text-sm font-medium text-red-700">Tandai Mengalami Delay (Keterlambatan)</label>
                        </div>

                        <div>
                            <label for="delay_hours" class="block text-sm font-medium text-gray-700">Durasi Delay (Jam)</label>
                            <input type="number" name="delay_hours" id="delay_hours" value="{{ old('delay_hours', $shipment->delay_hours) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('delay_hours') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Catatan Khusus Pengiriman</label>
                        <textarea name="notes" id="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes', $shipment->notes) }}</textarea>
                        @error('notes') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Shipment Items (Alpine.js Dynamic Form) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
                    <div class="flex justify-between items-center border-b pb-3">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Rincian Barang (Shipment Items)</h3>
                            <p class="text-xs text-gray-500">Perbarui item barang yang tercatat pada pengiriman ini.</p>
                        </div>
                        <button type="button" @click="addItem()" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 text-white rounded text-xs font-semibold hover:bg-emerald-700">
                            + Tambah Item Barang
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-4 border rounded-lg bg-gray-50 relative grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
                                <input type="hidden" :name="'items[' + index + '][id]'" x-model="item.id">

                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-700">Nama Barang *</label>
                                    <input type="text" :name="'items[' + index + '][item_name]'" x-model="item.item_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-xs">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Kode / SKU</label>
                                    <input type="text" :name="'items[' + index + '][item_code]'" x-model="item.item_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-xs">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Jumlah (Qty) *</label>
                                    <input type="number" min="1" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-xs">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Berat (kg) *</label>
                                    <input type="number" step="0.01" :name="'items[' + index + '][weight]'" x-model="item.weight" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-xs">
                                </div>

                                <div class="flex items-center space-x-2">
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-700">Nilai ($)</label>
                                        <input type="number" step="0.01" :name="'items[' + index + '][declared_value]'" x-model="item.declared_value" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-xs">
                                    </div>
                                    <button type="button" @click="removeItem(index)" class="mt-5 p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded" title="Hapus item">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('shipments.show', $shipment) }}" class="px-5 py-2.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Perbarui Pengiriman
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
