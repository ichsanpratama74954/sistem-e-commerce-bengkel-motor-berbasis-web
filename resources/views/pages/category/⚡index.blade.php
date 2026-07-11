<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Category;
use Illuminate\Support\Facades\Schema;

new class extends Component
{
    use WithPagination;

    // Properti untuk pencarian kategori
    public $search = '';

    // Reset halaman paginasi ketika mengetik pencarian
    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        return Category::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function stats()
    {
        return [
            // 1. Menghitung total seluruh kategori yang ada
            'total'    => Category::count(),
            
            // 2. Menghitung total data asli dari tabel Sparepart (aman jika model belum ada/berbeda)
            'sparepart'=> class_exists('App\Models\Sparepart') ? \App\Models\Sparepart::count() : 0, 
            
            // 3. Menghitung data riil dari menu Service kamu (yang isinya ada 2 data itu)
            'service'  => class_exists('App\Models\Service') ? \App\Models\Service::count() : 0,
            
            // 4. Menyesuaikan dengan tampilan row tabel yang otomatis fallback ke status 'aktif'
            'aktif'    => Category::count(),
            'nonaktif' => 0,
        ];
    }

    public function show($id){
        $this->dispatch('view-category', id: $id);
    }

    public function edit($id){
        $this->dispatch('edit-category', id: $id);
    }
};
?>

<div class="max-w-7xl mx-auto space-y-6 p-4 font-sans text-zinc-900 dark:text-zinc-100">
    
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 bg-rose-500/10 text-rose-500 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h1.592c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127c.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.43l-1.003.767c-.3.23-.452.617-.431.996a4.5 4.5 0 0 1 0 .444c-.021.379.13.765.43 1.996l1.004.767a1.125 1.125 0 0 1 .26 1.43l-1.297 2.247a1.125 1.125 0 0 1-1.37.491l-1.216-.456c-.356-.133-.751-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-1.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.43l1.004-.767c.304-.23.455-.618.434-.996a4.5 4.5 0 0 1 0-.444c.022-.378-.129-.765-.434-1.996l-1.004-.767a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124c.072-.044.146-.087.22-.128c.332-.183.582-.495.644-.869l.214-1.281Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </span>
                <flux:heading size="2xl" level="1" class="font-black tracking-tight text-zinc-900 dark:text-white">
                    Category <span class="text-rose-600">Management</span>
                </flux:heading>
            </div>
            <flux:subheading class="text-zinc-500 dark:text-zinc-400 mt-1 text-sm font-medium">
                Rapihin kategori komponen, sparepart, biar bengkel makin satset euy!
            </flux:subheading>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
            {{-- Input Pencarian Dinamis --}}
            <div class="w-full sm:w-64">
                <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Cari kategori..." class="w-full" />
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <button class="flex items-center gap-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Export
                </button>
            
                <flux:modal.trigger name="create-category">
                    <button class="flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded-xl font-bold text-sm shadow-lg shadow-rose-500/20 transition-all transform hover:scale-[1.01] active:scale-[0.99]">
                        <svg xmlns="http://www.w3.org/2000/xl" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Kategori
                    </button>
                </flux:modal.trigger>
            </div>
        </div>
    </div>

    <livewire:category.create />
    <livewire:category.edit />
    <x-flash-message />

    {{-- Table Category --}}
    <div class="overflow-x-auto mt-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <flux:table :paginate="$this->categories" class="w-full border-collapse">
            
            <flux:table.columns class="bg-emerald-600 dark:bg-emerald-900 border-b border-emerald-700">
                <flux:table.column class="text-white font-bold text-sm bg-emerald-600 dark:bg-emerald-900">No</flux:table.column>
                <flux:table.column class="text-white font-bold text-sm bg-emerald-600 dark:bg-emerald-900">Name</flux:table.column>
                <flux:table.column class="text-white font-bold text-sm bg-emerald-600 dark:bg-emerald-900">Slug</flux:table.column>
                <flux:table.column class="text-white font-bold text-sm bg-emerald-600 dark:bg-emerald-900">Description</flux:table.column>
                <flux:table.column class="text-white font-bold text-sm bg-emerald-600 dark:bg-emerald-900">Created At</flux:table.column>
                <flux:table.column class="text-white font-bold text-sm bg-emerald-600 dark:bg-emerald-900 text-center">Action</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->categories as $category)
                    <flux:table.row :key="$category->id" class="border-b border-zinc-100 dark:border-zinc-800/60 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">

                        {{-- 1. No --}}
                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400 font-medium pl-4 py-4">
                            {{ $loop->iteration + $this->categories->firstItem() - 1 }}
                        </flux:table.cell>    
                        
                        {{-- 2. Nama Kategori --}}
                        <flux:table.cell class="font-bold text-zinc-900 dark:text-zinc-100">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0 border border-zinc-200 dark:border-zinc-700">
                                    {{-- Emoji Dinamis berdasarkan Tipe --}}
                                    <span class="text-xs">{{ ($category->type ?? '') === 'service' ? '🛠️' : '📦' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    {{-- Indikator warna dinamis --}}
                                    <span class="w-2 h-2 rounded-full {{ ($category->type ?? '') === 'service' ? 'bg-blue-500' : 'bg-amber-500' }} shrink-0"></span>
                                    <span class="tracking-tight">{{ $category->name }}</span>
                                </div>
                            </div>
                        </flux:table.cell>

                        {{-- 3. Slug --}}
                        <flux:table.cell>
                            <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded text-rose-600 dark:text-rose-400">{{ $category->slug }}</code>
                        </flux:table.cell>

                        {{-- 4. Deskripsi --}}
                        <flux:table.cell class="text-zinc-600 dark:text-zinc-400 text-sm max-w-xs truncate">
                            {{ $category->description ?? 'Tidak ada deskripsi.' }}
                        </flux:table.cell>

                        {{-- 5. Dibuat Pada --}}
                        <flux:table.cell class="whitespace-nowrap text-zinc-500 dark:text-zinc-400 text-xs font-medium">
                            <div>{{ $category->created_at ? $category->created_at->translatedFormat('d M Y H:i') : '-' }}</div>
                            @if($category->created_at)
                                <div class="text-[10px] text-zinc-400 mt-0.5 font-normal">{{ $category->created_at->diffForHumans() }}</div>
                            @endif
                        </flux:table.cell>

                        {{-- 6. Action Buttons --}}
                        <flux:table.cell class="py-4">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- View Button --}}
                                <button type="button" wire:click="show({{ $category->id }})" class="p-1.5 text-zinc-500 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>

                                {{-- Edit Button --}}
                                <button type="button" wire:click="edit({{ $category->id }})" class="p-1.5 text-zinc-500 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                    </svg>
                                </button>

                                {{-- Delete Button --}}
                                <button type="button" wire:click="$dispatch('confirm-delete', {id: {{ $category->id }}})" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-8 text-zinc-400">
                            Tidak ada data kategori yang ditemukan.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Bottom Quote Banner Section --}}
    <div class="bg-zinc-50 dark:bg-zinc-900/50 p-5 rounded-2xl border border-zinc-100 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <span class="text-3xl text-rose-500 font-serif select-none leading-none">“</span>
            <div>
                <p class="text-zinc-700 dark:text-zinc-300 font-bold italic text-sm tracking-wide">
                    Bengkel rapi, kerja satset, pelanggan puas!
                </p>
                <p class="text-zinc-400 text-xs mt-0.5">Kelola kategori dengan rapi, bisnis makin joss.</p>
            </div>
        </div>
        <div class="hidden sm:block opacity-20 dark:opacity-40 pr-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-zinc-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124l-.847-13.56A1.125 1.125 0 0 0 19.38 3H16.5M16.5 18.75V15.75M3.75 10.5h11.625M3.75 10.5V7.875A1.125 1.125 0 0 1 4.875 6.75H12M3.75 10.5h1.5m14.25 3.75h-3.375M16.5 14.25h3.375" />
            </svg>
        </div>
    </div>

</div>