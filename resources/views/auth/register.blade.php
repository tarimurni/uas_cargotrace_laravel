<x-guest-layout>
    <!-- Logo & Header di dalam card -->
    <div class="text-center mb-6">
        <a href="/" class="inline-flex items-center justify-center mb-2 hover:opacity-90 transition">
            <x-application-logo class="h-10 w-auto text-slate-900" />
        </a>
        <h2 class="text-lg font-bold text-slate-900 tracking-tight">Daftar Akun CargoTrace</h2>
        <p class="text-xs text-slate-500 mt-1">Buat akun untuk memantau pengiriman barang Anda</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" class="text-xs font-semibold uppercase tracking-wider text-slate-700" />
            <x-text-input id="name" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm py-2.5" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nama Lengkap" />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" class="text-xs font-semibold uppercase tracking-wider text-slate-700" />
            <x-text-input id="email" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm py-2.5" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-xs font-semibold uppercase tracking-wider text-slate-700" />
            <x-text-input id="password" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm py-2.5"
                            type="password"
                            name="password"
                            required autocomplete="new-password"
                            placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-xs font-semibold uppercase tracking-wider text-slate-700" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm py-2.5"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password"
                            placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl font-bold text-sm text-white bg-[#0284C7] hover:bg-sky-700 active:bg-sky-800 shadow-md transition duration-150">
                <span>{{ __('Daftar Sekarang') }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>

        <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
            {{ __('Sudah punya akun?') }}
            <a href="{{ route('login') }}" class="font-bold text-[#0284C7] hover:text-sky-800 hover:underline ms-1 transition">
                {{ __('Masuk di sini') }} &rarr;
            </a>
        </div>
    </form>
</x-guest-layout>
