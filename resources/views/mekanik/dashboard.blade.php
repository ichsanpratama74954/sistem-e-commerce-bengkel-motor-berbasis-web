<x-layouts::app :title="__('Dashboard Mekanik')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        
        <div class="p-6 bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-sm">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center gap-2">
                🔧 Selamat Datang di Dashboard Kerja Mekanik!
            </h1>
            <p class="text-gray-600 dark:text-neutral-400 mt-2 text-sm">
                Akses Berhasil. Di sini kamu bisa melihat daftar antrean servis dan status perbaikan motor pelanggan.
            </p>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
            <div class="p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
                <p class="text-sm font-medium text-amber-600 dark:text-amber-400 font-bold">🛠️ Tugas Menunggu</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-neutral-100 mt-2">3 Motor</p>
            </div>
            <div class="p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
                <p class="text-sm font-medium text-green-600 dark:text-green-400 font-bold">✅ Selesai Hari Ini</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-neutral-100 mt-2">5 Motor</p>
            </div>
        </div>

        <div class="relative min-h-[250px] flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-white/5 p-6">
            <p class="text-gray-500 dark:text-neutral-400 text-sm">List tabel antrean motor yang masuk ke bengkel akan muncul di sini...</p>
        </div>

    </div>
</x-layouts::app>