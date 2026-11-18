<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CargoTrace') }} - Logistics & Freight Tracking</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased bg-gradient-to-br from-[#F1F5F9] via-slate-50 to-[#E2E8F0]">
        <div class="min-h-screen flex flex-col justify-center items-center py-10 px-4 sm:px-6">
            <div class="w-full sm:max-w-md p-8 sm:p-9 bg-white shadow-xl border border-slate-200/80 rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
