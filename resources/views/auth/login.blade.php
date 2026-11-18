<x-guest-layout>
    <!-- Logo & Header di dalam card -->
    <div class="text-center mb-6">
        <a href="/" class="inline-flex items-center justify-center mb-2 hover:opacity-90 transition">
            <x-application-logo class="h-10 w-auto text-slate-900" />
        </a>
        <p class="text-xs text-slate-500 font-medium">Masuk ke akun Anda</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-xs font-semibold uppercase tracking-wider text-slate-700" />
            <x-text-input id="email" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm py-2.5" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" class="text-xs font-semibold uppercase tracking-wider text-slate-700" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-[#0284C7] hover:text-sky-800 transition" href="{{ route('password.request') }}">
                        {{ __('Lupa kata sandi?') }}
                    </a>
                @endif
            </div>

            <x-text-input id="password" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm py-2.5"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />

            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-[#0284C7] shadow-xs focus:ring-sky-500 h-4 w-4" name="remember">
                <span class="ms-2 text-xs font-medium text-slate-600">{{ __('Ingat saya') }}</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl font-bold text-sm text-white bg-[#0284C7] hover:bg-sky-700 active:bg-sky-800 shadow-md transition duration-150">
                <span>{{ __('Masuk') }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>

        <!-- Register Link -->
        <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
            {{ __('Belum punya akun?') }}
            <a href="{{ route('register') }}" class="font-bold text-[#0284C7] hover:text-sky-800 hover:underline ms-1 transition">
                {{ __('Daftar di sini') }} &rarr;
            </a>
        </div>
    </form>
</x-guest-layout>
