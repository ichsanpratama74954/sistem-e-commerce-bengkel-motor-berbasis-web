<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php
            // Proteksi case-insensitive: Ubah semua ke huruf kecil agar kebal error salah ketik/kapital di DB
            $userRole = strtolower(trim(auth()->user()->role ?? ''));
        @endphp

        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <!-- BISA DIAKSES SEMUA ROLE -->
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    <flux:separator class="my-2" />

                    {{-- 1. MENU KHUSUS ADMIN (Manajemen Inti & Finansial) --}}
                    @if($userRole === 'admin')
                        <flux:sidebar.item icon="users" :href="route('user.index')" :current="request()->routeIs('user.index')" wire:navigate>
                            {{ __('Users') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="folder" :href="route('category.index')" :current="request()->routeIs('category.index')" wire:navigate>
                            {{ __('Category') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="cube" :href="route('sparepart.index')" :current="request()->routeIs('sparepart.index')" wire:navigate>
                            {{ __('Spareparts') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="credit-card" :href="route('payment.index')" :current="request()->routeIs('payment.index')" wire:navigate>
                            {{ __('Payments') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="document-chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.index')" wire:navigate>
                            {{ __('Reports') }}
                        </flux:sidebar.item>
                    @endif

                    {{-- 2. MENU ADMIN & MEKANIK (Proses Servis Motor) --}}
                    @if(in_array($userRole, ['admin', 'mekanik']))
                        <flux:sidebar.item icon="wrench" :href="route('service.index')" :current="request()->routeIs('service.index')" wire:navigate>
                            {{ __('Service') }}
                        </flux:sidebar.item>
                    @endif

                    {{-- 3. MENU ADMIN & PELANGGAN (Belanja & Data Kendaraan) --}}
                    @if(in_array($userRole, ['admin', 'pelanggan']))
                        <flux:sidebar.item icon="shopping-cart" :href="route('order.index')" :current="request()->routeIs('order.index')" wire:navigate>
                            {{ __('Orders') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="wrench" :href="route('motorcycle.index')" :current="request()->routeIs('motorcycle.index')" wire:navigate>
                            {{ __('Motorcycles') }}
                        </flux:sidebar.item>
                    @endif

                    {{-- 4. BISA DIAKSES SEMUA ROLE (Daftar Booking Servis) --}}
                    @if(in_array($userRole, ['admin', 'pelanggan', 'mekanik']))
                        <flux:sidebar.item icon="calendar" :href="route('booking.index')" :current="request()->routeIs('booking.index')" wire:navigate>
                            {{ __('Bookings') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        {{-- Chart.js --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        {{-- Tempat script dari halaman --}}
        @stack('scripts')

        @fluxScripts
    </body>
</html>