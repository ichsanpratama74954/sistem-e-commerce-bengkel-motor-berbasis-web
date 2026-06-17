<x-layouts::app :title="__('Dashboard Admin')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        
        <div class="p-6 bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-sm">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center gap-2">
                👑 Selamat Datang di Dashboard Admin Bengkel!
            </h1>
            <p class="text-gray-600 dark:text-neutral-400 mt-2 text-sm">
                Sistem Autentikasi Multi-Role Berhasil Diterapkan. Kamu masuk dengan hak akses penuh sebagai Administrator.
            </p>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
                <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">Total Pelanggan</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-neutral-100 mt-2">124 User</p>
            </div>
            <div class="p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
                <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">Mekanik Standby</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-neutral-100 mt-2">5 Orang</p>
            </div>
            <div class="p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
                <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">Antrean Hari Ini</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-neutral-100 mt-2">12 Motor</p>
            </div>
        </div>

        <div class="relative min-h-[300px] flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
            <p class="text-gray-500 dark:text-neutral-400 text-sm">Tempat komponen tabel atau grafik transaksi bengkel...</p>
        </div>

    </div>
</x-layouts::app>
