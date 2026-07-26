@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? "$title · " : '' }}{{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-base-100 antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            <div class="flex flex-col justify-center px-6 py-12 sm:px-10 lg:px-20">
                <div class="mx-auto w-full max-w-sm">
                    <a href="{{ url('/') }}" class="mb-10 flex items-center gap-2.5">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-primary text-primary-content">
                            <x-ui.icon name="grid" class="size-5" />
                        </span>
                        <span class="text-lg font-bold text-base-content">{{ config('app.name') }}</span>
                    </a>

                    {{ $slot }}
                </div>
            </div>

            <div class="relative hidden overflow-hidden bg-primary lg:flex lg:flex-col lg:justify-center lg:px-16">
                <div class="pointer-events-none absolute -top-24 -right-24 size-96 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-32 -left-10 size-80 rounded-full bg-white/10"></div>

                <div class="relative z-10 max-w-md">
                    <h2 class="text-3xl leading-tight font-bold text-primary-content">
                        Manage your dairy operations from one place.
                    </h2>
                    <p class="mt-4 text-primary-content/80">
                        Track suppliers, staff, and daily activity with a single, simple dashboard.
                    </p>
                </div>

                <div class="relative z-10 mt-10 grid max-w-md grid-cols-2 gap-4">
                    <div class="rounded-2xl bg-white/10 p-5 backdrop-blur-sm">
                        <p class="text-xs font-medium text-primary-content/70">Active Suppliers</p>
                        <p class="mt-2 text-2xl font-bold text-primary-content">128</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-5 backdrop-blur-sm">
                        <p class="text-xs font-medium text-primary-content/70">Daily Collection</p>
                        <p class="mt-2 text-2xl font-bold text-primary-content">4,320 L</p>
                    </div>
                    <div class="col-span-2 rounded-2xl bg-white/10 p-5 backdrop-blur-sm">
                        <p class="text-xs font-medium text-primary-content/70">Pending Orders</p>
                        <p class="mt-2 text-2xl font-bold text-primary-content">27</p>
                    </div>
                </div>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
