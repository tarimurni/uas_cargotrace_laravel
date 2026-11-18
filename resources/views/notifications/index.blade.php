<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Pusat Pemberitahuan (Notifications)
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Riwayat update status kargo, notifikasi keterlambatan, dan peringatan kendala exception.
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-md border border-indigo-200 transition">
                            <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Tandai Semua Dibaca
                        </button>
                    </form>
                @endif

                <a href="{{ route('notifications.index', ['unread_only' => request('unread_only') ? 0 : 1]) }}"
                   class="inline-flex items-center px-3 py-1.5 {{ request('unread_only') ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }} text-xs font-semibold rounded-md transition">
                    {{ request('unread_only') ? 'Tampilkan Semua' : 'Hanya Belum Dibaca (' . $unreadCount . ')' }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="divide-y divide-gray-100">
                    @forelse ($notifications as $notification)
                        <div class="p-5 flex items-start gap-4 transition hover:bg-gray-50 {{ !$notification->is_read ? 'bg-indigo-50/40' : '' }}">
                            <div class="flex-shrink-0 mt-0.5">
                                @if ($notification->type === 'delay_alert')
                                    <div class="w-9 h-9 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                @elseif ($notification->type === 'exception_alert')
                                    <div class="w-9 h-9 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    </div>
                                @else
                                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                                        @if(!$notification->is_read)
                                            <span class="w-2 h-2 rounded-full bg-indigo-600 inline-block"></span>
                                        @endif
                                        {{ $notification->title }}
                                    </h4>
                                    <span class="text-xs text-gray-400 whitespace-nowrap">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                    {{ $notification->message }}
                                </p>

                                <div class="mt-3 flex items-center gap-4 text-xs">
                                    @if ($notification->shipment)
                                        <a href="{{ route('shipments.show', $notification->shipment) }}" class="font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                            Lihat Detail Kargo &rarr;
                                        </a>
                                    @endif

                                    @if (!$notification->is_read)
                                        <form method="POST" action="{{ route('notifications.read', $notification) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-gray-500 hover:text-gray-800 font-medium">
                                                Tandai Dibaca
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 text-[11px]">Dibaca pada {{ $notification->read_at?->format('d M, H:i') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">Belum Ada Notifikasi</h3>
                            <p class="mt-1 text-xs text-gray-500">
                                @if (request('unread_only'))
                                    Semua notifikasi telah Anda baca.
                                @else
                                    Pemberitahuan terkait kargo Anda akan muncul di sini.
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>

                @if ($notifications->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
