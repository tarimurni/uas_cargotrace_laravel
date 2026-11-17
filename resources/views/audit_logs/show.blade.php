<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Audit Log #' . $auditLog->id) }}
            </h2>
            <a href="{{ route('audit-logs.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                &larr; Kembali ke daftar
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Summary Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Aksi</span>
                        <div class="font-mono font-bold text-gray-900 mt-1">{{ $auditLog->action }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Pengguna</span>
                        <div class="font-semibold text-gray-900 mt-1">{{ $auditLog->user?->name ?? 'System' }}</div>
                        <div class="text-xs text-gray-500">{{ $auditLog->user?->email }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Objek Terkait</span>
                        <div class="font-mono text-gray-900 mt-1">{{ $auditLog->auditable_type }} #{{ $auditLog->auditable_id }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Waktu Eksekusi</span>
                        <div class="font-mono text-gray-900 mt-1">{{ $auditLog->created_at->format('d M Y, H:i:s') }}</div>
                    </div>
                </div>
            </div>

            <!-- JSON Diff / Values -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Old Values -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-bold text-gray-700 uppercase mb-3 flex items-center">
                        <span class="w-3 h-3 bg-red-500 rounded-full inline-block me-2"></span>
                        Nilai Lama (Old Values)
                    </h3>
                    <pre class="bg-gray-900 text-red-300 p-4 rounded-md text-xs font-mono overflow-x-auto max-h-96">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: 'null' }}</pre>
                </div>

                <!-- New Values -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-bold text-gray-700 uppercase mb-3 flex items-center">
                        <span class="w-3 h-3 bg-emerald-500 rounded-full inline-block me-2"></span>
                        Nilai Baru (New Values)
                    </h3>
                    <pre class="bg-gray-900 text-emerald-300 p-4 rounded-md text-xs font-mono overflow-x-auto max-h-96">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: 'null' }}</pre>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
