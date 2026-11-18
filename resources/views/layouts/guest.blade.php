@props(['split' => false])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CargoTrace') }} - Logistics & Freight Tracking</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased bg-[#F8FAFC]">
        @if ($split)
            {{ $slot }}
        @else
            <div class="min-h-screen flex flex-col sm:justify-center items-center py-12 px-4 bg-[#F8FAFC]">
                <div class="mb-4">
                    <a href="/" class="flex flex-col items-center hover:opacity-90 transition">
                        <x-application-logo class="h-12 w-auto text-slate-900" />
                    </a>
                </div>

                <div class="w-full sm:max-w-md px-6 py-6 bg-white shadow-xl border border-slate-200/80 overflow-hidden rounded-2xl">
                    {{ $slot }}
                </div>
            </div>
        @endif
    </body>
</html>
