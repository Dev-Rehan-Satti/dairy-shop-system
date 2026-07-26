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
    <body class="bg-base-200 antialiased" x-data="{ sidebarOpen: false, collapsed: false }">
        <div class="flex min-h-screen">
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                @click="sidebarOpen = false"
                class="fixed inset-0 z-40 bg-black/40 lg:hidden"
                style="display: none"
            ></div>

            <aside
                :class="{ 'translate-x-0': sidebarOpen, 'collapsed lg:w-20': collapsed, 'lg:w-64': !collapsed }"
                class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-base-200 bg-base-100 transition-all duration-200 lg:static lg:translate-x-0"
            >
                <div class="flex h-16 shrink-0 items-center justify-between px-4">
                    <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2.5">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary text-primary-content">
                            <x-ui.icon name="grid" class="size-5" />
                        </span>
                        <span class="truncate font-bold text-base-content [.collapsed_&]:hidden">{{ config('app.name') }}</span>
                    </a>
                    <button @click="collapsed = !collapsed" class="btn btn-circle btn-ghost btn-sm hidden lg:flex">
                        <x-ui.icon name="chevron-right" class="size-4 transition-transform" x-bind:class="{ 'rotate-180': !collapsed }" />
                    </button>
                </div>

                <nav class="flex-1 space-y-6 overflow-x-hidden overflow-y-auto px-3 py-4">
                    <x-ui.nav-section title="Main">
                        <x-ui.nav-link :href="route('dashboard')" icon="home">Home</x-ui.nav-link>
                        <x-ui.nav-link :href="route('dashboard')" icon="grid">Dashboard</x-ui.nav-link>
                    </x-ui.nav-section>

                    @canany(['roles.view', 'users.view'])
                        <x-ui.nav-section title="User Management">
                            @can('roles.view')
                                <x-ui.nav-link :href="route('roles.index')" icon="shield">Roles</x-ui.nav-link>
                            @endcan
                            @can('users.view')
                                <x-ui.nav-link :href="route('users.index')" icon="users">Users</x-ui.nav-link>
                            @endcan
                        </x-ui.nav-section>
                    @endcanany

                    <x-ui.nav-section title="Profiles">
                        <x-ui.nav-link :href="route('employees.index')" icon="user">Employees</x-ui.nav-link>
                        <x-ui.nav-link :href="route('suppliers.index')" icon="truck">Suppliers</x-ui.nav-link>
                        @can('customers.view')
                            <x-ui.nav-link :href="route('customers.index')" icon="map-pin">Customers</x-ui.nav-link>
                        @endcan
                    </x-ui.nav-section>
                </nav>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between gap-4 border-b border-base-200 bg-base-100 px-4 sm:px-6">
                    <button @click="sidebarOpen = true" class="btn btn-ghost btn-sm lg:hidden">
                        <x-ui.icon name="menu" class="size-5" />
                    </button>

                    <div class="flex-1"></div>

                    <div class="flex items-center gap-1 sm:gap-2">
                        <button class="btn btn-ghost btn-circle btn-sm text-base-content/60" title="Filters">
                            <x-ui.icon name="filter" class="size-5" />
                        </button>
                        <button class="btn btn-ghost btn-circle btn-sm text-base-content/60" title="Settings">
                            <x-ui.icon name="cog" class="size-5" />
                        </button>
                        <button class="btn btn-ghost btn-circle btn-sm relative text-base-content/60" title="Notifications">
                            <x-ui.icon name="bell" class="size-5" />
                            <span class="absolute top-1.5 right-1.5 size-2 rounded-full bg-error"></span>
                        </button>

                        <div class="dropdown dropdown-end ml-1">
                            <button tabindex="0" class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-base-200">
                                <x-ui.avatar :name="auth()->user()?->name ?? '?'" size="size-8" />
                                <span class="hidden text-sm font-medium text-base-content sm:block">{{ auth()->user()?->name }}</span>
                                <x-ui.icon name="chevron-down" class="hidden size-4 text-base-content/40 sm:block" />
                            </button>
                            <ul tabindex="0" class="dropdown-content menu z-40 mt-2 w-48 rounded-box bg-base-100 p-2 shadow-lg border border-base-200">
                                <li class="px-3 py-2">
                                    <p class="truncate text-sm font-medium text-base-content">{{ auth()->user()?->name }}</p>
                                    <p class="truncate text-xs text-base-content/50">{{ auth()->user()?->email }}</p>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-2 text-error">
                                            <x-ui.icon name="logout" class="size-4" />
                                            Log out
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </header>

                <main class="flex-1 p-4 sm:p-6">
                    {{ $slot }}
                </main>

                <footer class="border-t border-base-200 px-6 py-4 text-center text-sm text-base-content/50">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </footer>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
