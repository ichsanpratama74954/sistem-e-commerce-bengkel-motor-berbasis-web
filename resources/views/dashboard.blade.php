@php
    // Menghitung jumlah data secara real-time langsung lewat database dengan aman
    $totalServices = class_exists('App\Models\Service') ? \App\Models\Service::count() : 0;
    $totalMotorcycles = class_exists('App\Models\Motorcycle') ? \App\Models\Motorcycle::count() : 0;
    $totalOrders = class_exists('App\Models\Order') ? \App\Models\Order::count() : 0;
@endphp

@component('layouts.app')
    <div class="space-y-6">
        <div>
            <flux:heading size="xl" level="1" class="font-black text-indigo-950 dark:text-indigo-400">Selamat Datang di Dashboard Bengkel 🛠️</flux:heading>
            <flux:subheading size="sm" class="text-zinc-500 dark:text-zinc-400">Pantau performa bisnis dan pesanan masuk hari ini.</flux:subheading>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="p-6 bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-800 flex items-center space-x-4">
                <div class="p-3 bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 rounded-lg flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-zinc-500">Pesanan Masuk</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $totalOrders }} Order</p>
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-800 flex items-center space-x-4">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 rounded-lg flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-zinc-500">Varian Layanan Servis</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $totalServices }} Layanan</p>
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-800 flex items-center space-x-4">
                <div class="p-3 bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 rounded-lg flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M21 16V10a2 2 0 00-2-2h-4.24l-1.42-1.42A1 1 0 0012.63 6H11" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-zinc-500">Motor Terdaftar</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $totalMotorcycles }} Unit</p>
                </div>
            </div>
        </div>

        <div class="p-6 bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-800">
            <flux:heading size="lg" class="mb-2">Aksi Cepat Manajemen Bengkel</flux:heading>
            <flux:subheading size="sm" class="mb-6">Klik tombol di bawah untuk langsung mengelola data operasional.</flux:subheading>
            
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('service.index') }}" wire:navigate class="p-4 rounded-lg border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition block text-center font-medium">
                    ➕ Tambah Layanan Baru
                </a>
                <a href="/motorcycles" wire:navigate class="p-4 rounded-lg border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition block text-center font-medium">
                    🏍️ Lihat Daftar Motor
                </a>
                <a href="/categories" wire:navigate class="p-4 rounded-lg border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition block text-center font-medium">
                    📦 Kelola Kategori Sparepart
                </a>
                <a href="#" class="p-4 rounded-lg border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition block text-center font-medium text-zinc-400 cursor-not-allowed">
                    📑 Laporan Penjualan (Soon)
                </a>
            </div>
        </div>
    </div>
@endcomponent
