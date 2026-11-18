<x-guest-layout>
    <!-- Logo & Header di dalam card -->
    <div class="text-center mb-6">
        <a href="/" class="inline-flex items-center justify-center mb-2 hover:opacity-90 transition">
            <x-application-logo class="h-10 w-auto text-slate-900" />
        </a>
        <h2 class="text-lg font-bold text-slate-900 tracking-tight">Perbarui Kata Sandi</h2>
        <p class="text-xs text-slate-500 mt-1">Buat kata sandi baru untuk akun Anda</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-xs font-semibold uppercase tracking-wider text-slate-700" />
            <x-text-input id="email" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm py-2.5" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Kata Sandi Baru')" class="text-xs font-semibold uppercase tracking-wider text-slate-700" />
            <x-text-input id="password" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm py-2.5" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi Baru')" class="text-xs font-semibold uppercase tracking-wider text-slate-700" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm py-2.5"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl font-bold text-sm text-white bg-[#0284C7] hover:bg-sky-700 active:bg-sky-800 shadow-md transition duration-150">
                <span>{{ __('Reset Password') }}</span>
            </button>
        </div>
    </form>
</x-guest-layout>
