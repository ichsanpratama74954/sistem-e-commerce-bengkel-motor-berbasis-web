<div class="p-6 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 m-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Manajemen Kategori</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Kelola data kategori produk dan jasa bengkel di sini.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-5 rounded-xl border border-zinc-200 dark:border-zinc-700 h-fit">
            <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-4">
                {{ $isEdit ? 'Ubah Kategori' : 'Tambah Kategori Baru' }}
            </h3>

            @if (session()->has('message'))
                <div class="mb-4 p-3 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 text-sm rounded-lg border border-emerald-200 dark:border-emerald-800">
                    {{ session('message') }}
                </div>
            @endif

            <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nama Kategori</label>
                    <input type="text" id="name" wire:model="name" placeholder="Contoh: Oli Mesin, Ban Luar"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('name') 
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p> 
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Deskripsi / Keterangan</label>
                    <textarea id="description" wire:model="description" rows="4" placeholder="Tulis catatan kategori di sini..."
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"></textarea>
                    @error('description') 
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p> 
                    @enderror
                </div>
                
                <div class="flex flex-col gap-2 pt-2">
                    <button type="submit" 
                        class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-semibold text-white rounded-lg transition-colors cursor-pointer {{ $isEdit ? 'bg-amber-500 hover:bg-amber-600' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Kategori' }}
                    </button>
                    @if($isEdit)
                        <button type="button" wire:click="resetInput"
                            class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-semibold text-zinc-700 bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600 rounded-lg cursor-pointer">
                            Batal
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-700 text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">
                            <th class="px-4 py-3 text-center" width="8%">No</th>
                            <th class="px-4 py-3" width="30%">Nama Kategori</th>
                            <th class="px-4 py-3" width="25%">Slug (URL)</th>
                            <th class="px-4 py-3">Deskripsi</th>
                            <th class="px-4 py-3 text-center" width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-sm text-zinc-900 dark:text-zinc-100">
                        @forelse($categories as $index => $category)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="px-4 py-3.5 text-center font-medium text-zinc-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-3.5 font-bold">{{ $category->name }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-mono">
                                        {{ $category->slug }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-zinc-500 dark:text-zinc-400 text-xs truncate max-w-[150px]">
                                    {{ $category->description ?? '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-center space-x-1">
                                    <button wire:click="edit({{ $category->id }})" 
                                        class="px-2.5 py-1 text-xs font-medium bg-sky-500 text-white hover:bg-sky-600 rounded-md transition-colors cursor-pointer">
                                        Edit
                                    </button>
                                    <button wire:click="delete({{ $category->id }})" 
                                        onclick="confirm('Hapus kategori ini?') || event.stopImmediatePropagation()" 
                                        class="px-2.5 py-1 text-xs font-medium bg-red-500 text-white hover:bg-red-600 rounded-md transition-colors cursor-pointer">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-zinc-400 dark:text-zinc-500">
                                    <span class="block text-2xl mb-1">📂</span>
                                    Belum ada data kategori di database.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>