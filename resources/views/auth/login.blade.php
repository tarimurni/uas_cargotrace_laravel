<x-guest-layout :split="true">
    <div class="min-h-screen flex flex-col lg:flex-row bg-[#F8FAFC]">
        <!-- Sisi Kiri: Branding CargoTrace, Maritime Gradient & Ringkasan Platform -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-[#0B1528] via-[#0F172A] to-[#0369A1] text-white p-12 xl:p-16 flex-col justify-between relative overflow-hidden">
            <!-- Background Decorative Maritime Patterns -->
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800">
                    <defs>
                        <radialGradient id="grad-pulse" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#38BDF8" stop-opacity="0.6"/>
                            <stop offset="100%" stop-color="#0F172A" stop-opacity="0"/>
                        </radialGradient>
                    </defs>
                    <circle cx="400" cy="400" r="150" stroke="#38BDF8" stroke-width="1.5" fill="none" stroke-dasharray="4 6"/>
                    <circle cx="400" cy="400" r="280" stroke="#38BDF8" stroke-width="1.5" fill="none" stroke-dasharray="2 4"/>
                    <circle cx="400" cy="400" r="380" stroke="#38BDF8" stroke-width="1" fill="none"/>
                    <line x1="0" y1="400" x2="800" y2="400" stroke="#38BDF8" stroke-width="1"/>
                    <line x1="400" y1="0" x2="400" y2="800" stroke="#38BDF8" stroke-width="1"/>
                </svg>
            </div>

            <!-- Header Branding -->
            <div class="relative z-10">
                <a href="/" class="inline-flex items-center hover:opacity-90 transition">
                    <x-application-logo class="h-11 w-auto text-white" />
                </a>
            </div>

            <!-- Hero & Ringkasan Fitur Platform -->
            <div class="relative z-10 my-auto py-8">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-sky-500/20 text-sky-300 border border-sky-400/30 mb-5">
                    <span class="w-2 h-2 rounded-full bg-sky-400 animate-ping"></span>
                    Logistics & Maritime Intelligence v2.0
                </span>

                <h1 class="text-3xl xl:text-4xl font-extrabold tracking-tight leading-tight text-white mb-4">
                    Next-Generation Global Freight & Vessel Tracking
                </h1>

                <p class="text-slate-300 text-sm xl:text-base leading-relaxed mb-8 max-w-lg">
                    Platform terintegrasi untuk visibilitas rantai pasok maritim dan kargo udara secara real-time, dilengkapi automasi deteksi keterlambatan, resolusi kendala pelabuhan, dan visualisasi GIS satelit beresolusi tinggi.
                </p>

                <!-- 3 Key Feature Cards -->
                <div class="space-y-3.5 max-w-lg">
                    <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-slate-800/60 backdrop-blur-md border border-slate-700/60 shadow-sm">
                        <div class="p-2 rounded-lg bg-sky-500/20 text-sky-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Visualisasi Satelit & Leaflet GIS</h4>
                            <p class="text-xs text-slate-300 mt-0.5">Peta jalan CartoDB Voyager dan citra satelit nyata Esri World Imagery dengan koordinat waypoint presisi.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-slate-800/60 backdrop-blur-md border border-slate-700/60 shadow-sm">
                        <div class="p-2 rounded-lg bg-emerald-500/20 text-emerald-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Deterministic Delay & ETA Engine</h4>
                            <p class="text-xs text-slate-300 mt-0.5">Aturan kalkulasi deviasi jadwal otomatis berbasis konfigurasi threshold 12 jam dengan penentuan tingkat keparahan.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-slate-800/60 backdrop-blur-md border border-slate-700/60 shadow-sm">
                        <div class="p-2 rounded-lg bg-amber-500/20 text-amber-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Exception & Customs Incident Handling</h4>
                            <p class="text-xs text-slate-300 mt-0.5">Pencatatan kendala pelabuhan, cuaca, dan kepabeanan lengkap dengan lifecycle Open, Investigating, hingga Resolved.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Quote & Metrics -->
            <div class="relative z-10 pt-6 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <span>&copy; {{ date('Y') }} CargoTrace Global Logistics.</span>
                <span class="flex items-center gap-1.5 text-sky-400 font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Enterprise Security & RBAC Guarded
                </span>
            </div>
        </div>

        <!-- Sisi Kanan: Form Login Modern & Responsif -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-10 lg:p-12 xl:p-16">
            <div class="w-full max-w-md">
                <!-- Mobile Branding (Tampil di Layar Kecil) -->
                <div class="lg:hidden text-center mb-8">
                    <a href="/" class="inline-flex items-center justify-center">
                        <x-application-logo class="h-11 w-auto text-slate-900" />
                    </a>
                    <p class="text-xs text-slate-500 mt-2">Platform Pemantauan & Pelacakan Kargo Global</p>
                </div>

                <!-- Form Header -->
                <div class="mb-6">
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Selamat Datang Kembali</h2>
                    <p class="text-sm text-slate-500 mt-1">Masuk untuk mengakses dashboard operasional kargo dan sistem pelacakan Anda.</p>
                </div>

                <!-- Session Status Alert -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <div class="bg-white p-7 sm:p-8 rounded-2xl shadow-xl border border-slate-200/80">
                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            <x-input-label for="email" :value="__('Alamat Email')" class="font-semibold text-slate-700 text-xs uppercase tracking-wider" />
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                </div>
                                <x-text-input id="email" class="block w-full pl-10 pr-3 py-2.5 rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@perusahaan.com" />
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                        </div>

                        <!-- Password -->
                        <div>
                            <div class="flex items-center justify-between">
                                <x-input-label for="password" :value="__('Kata Sandi')" class="font-semibold text-slate-700 text-xs uppercase tracking-wider" />
                                @if (Route::has('password.request'))
                                    <a class="text-xs font-semibold text-sky-600 hover:text-sky-800 transition" href="{{ route('password.request') }}">
                                        {{ __('Lupa kata sandi?') }}
                                    </a>
                                @endif
                            </div>
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <x-text-input id="password" class="block w-full pl-10 pr-3 py-2.5 rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm"
                                                type="password"
                                                name="password"
                                                required autocomplete="current-password"
                                                placeholder="••••••••" />
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between pt-1">
                            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-sky-600 shadow-xs focus:ring-sky-500 h-4 w-4" name="remember">
                                <span class="ms-2 text-xs font-medium text-slate-600">{{ __('Ingat sesi saya') }}</span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-sky-600 via-sky-700 to-blue-800 hover:from-sky-700 hover:to-blue-900 shadow-md hover:shadow-lg transition duration-200">
                                <span>{{ __('Masuk ke Dashboard') }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </form>

                    <!-- Quick Demo Accounts One-Click Auto-Fill -->
                    <div class="mt-6 pt-5 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Akun Demo Pengujian (1-Klik Isi)</span>
                            <span class="text-[10px] text-slate-400">Password: <code class="font-mono text-slate-600 font-bold">password</code></span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" onclick="fillCredentials('admin@cargotrace.com')" class="px-2 py-1.5 text-center rounded-lg border border-purple-200 bg-purple-50/70 hover:bg-purple-100 text-purple-800 text-[11px] font-semibold transition">
                                👑 Admin
                            </button>
                            <button type="button" onclick="fillCredentials('operator@cargotrace.com')" class="px-2 py-1.5 text-center rounded-lg border border-sky-200 bg-sky-50/70 hover:bg-sky-100 text-sky-800 text-[11px] font-semibold transition">
                                🛠️ Operator
                            </button>
                            <button type="button" onclick="fillCredentials('customer@cargotrace.com')" class="px-2 py-1.5 text-center rounded-lg border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100 text-emerald-800 text-[11px] font-semibold transition">
                                📦 Customer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Register Footer -->
                <div class="mt-6 text-center text-xs text-slate-500">
                    Belum memiliki akun CargoTrace?
                    <a href="{{ route('register') }}" class="font-bold text-sky-600 hover:text-sky-800 hover:underline ms-1 transition">
                        Daftar Akun Customer &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fillCredentials(email) {
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('password');
            if (emailInput && passInput) {
                emailInput.value = email;
                passInput.value = 'password';
                emailInput.focus();
            }
        }
    </script>
</x-guest-layout>
