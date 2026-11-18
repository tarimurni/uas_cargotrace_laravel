<x-guest-layout>
    <!-- Logo & Header di dalam card -->
    <div class="text-center mb-6">
        <a href="/" class="inline-flex items-center justify-center mb-2 hover:opacity-90 transition">
            <x-application-logo class="h-10 w-auto text-slate-900" />
        </a>
        <h2 class="text-lg font-bold text-slate-900 tracking-tight">Atur Ulang Kata Sandi</h2>
        <p class="text-xs text-slate-500 mt-1">Masukkan email Anda untuk menerima link reset kata sandi.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-xs font-semibold uppercase tracking-wider text-slate-700" />
            <x-text-input id="email" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm py-2.5" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl font-bold text-sm text-white bg-[#0284C7] hover:bg-sky-700 active:bg-sky-800 shadow-md transition duration-150">
                <span>{{ __('Kirim Link Reset Kata Sandi') }}</span>
            </button>
        </div>

        <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
            <a href="{{ route('login') }}" class="font-bold text-[#0284C7] hover:text-sky-800 hover:underline transition">
                &larr; {{ __('Kembali ke halaman Masuk') }}
            </a>
        </div>
    </form>
</x-guest-layout>
