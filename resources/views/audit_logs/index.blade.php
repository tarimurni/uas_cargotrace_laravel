<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit Logs & Rekam Aktivitas Sistem (Admin Only)') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Filter Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="action" class="block text-sm font-medium text-gray-700">Filter Aksi</label>
                        <select name="action" id="action" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Semua Aksi</option>
                            <option value="CREATE_SHIPMENT" {{ request('action') === 'CREATE_SHIPMENT' ? 'selected' : '' }}>CREATE_SHIPMENT</option>
                            <option value="UPDATE_SHIPMENT" {{ request('action') === 'UPDATE_SHIPMENT' ? 'selected' : '' }}>UPDATE_SHIPMENT</option>
                            <option value="DELETE_SHIPMENT" {{ request('action') === 'DELETE_SHIPMENT' ? 'selected' : '' }}>DELETE_SHIPMENT</option>
                            <option value="CREATE_CARRIER" {{ request('action') === 'CREATE_CARRIER' ? 'selected' : '' }}>CREATE_CARRIER</option>
                            <option value="UPDATE_CARRIER" {{ request('action') === 'UPDATE_CARRIER' ? 'selected' : '' }}>UPDATE_CARRIER</option>
                            <option value="DELETE_CARRIER" {{ request('action') === 'DELETE_CARRIER' ? 'selected' : '' }}>DELETE_CARRIER</option>
                            <option value="CREATE_LOCATION" {{ request('action') === 'CREATE_LOCATION' ? 'selected' : '' }}>CREATE_LOCATION</option>
                            <option value="UPDATE_LOCATION" {{ request('action') === 'UPDATE_LOCATION' ? 'selected' : '' }}>UPDATE_LOCATION</option>
                        </select>
                    </div>

                    <div class="flex items-end space-x-2">
                        <button type="submit" class="inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 sm:text-sm">
                            Filter
                        </button>
                        <a href="{{ route('audit-logs.index') }}" class="inline-flex justify-center items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 sm:text-sm">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengguna (User)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Objek / Model</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse ($logs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-600">
                                        {{ $log->created_at->format('d M Y, H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $log->user?->name ?? 'System' }}</div>
                                        <div class="text-xs text-gray-400">{{ $log->user?->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded text-xs font-bold font-mono uppercase
                                            @if(str_contains($log->action, 'CREATE')) bg-green-100 text-green-800
                                            @elseif(str_contains($log->action, 'UPDATE')) bg-amber-100 text-amber-800
                                            @elseif(str_contains($log->action, 'DELETE')) bg-red-100 text-red-800
                                            @else bg-blue-100 text-blue-800 @endif">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-mono text-xs">
                                        {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                                        {{ $log->ip_address ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('audit-logs.show', $log) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                            Lihat Diff
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada rekam audit log.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
