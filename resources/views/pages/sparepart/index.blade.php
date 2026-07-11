<?php
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Sparepart;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    public $sortBy = 'part_name';
    public $sortDirection = 'asc';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function spareparts()
    {
        return Sparepart::with('category')
            ->when($this->search, function ($query) {
                $query->where('part_name', 'like', '%' . $this->search . '%');
            })
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }

    #[Computed]
    public function totalItems()
    {
        return Sparepart::count();
    }

    #[Computed]
    public function lowStockCount()
    {
        return Sparepart::where('stock', '<=', 5)->count();
    }

    #[Computed]
    public function totalValue()
    {
        return Sparepart::sum(DB::raw('stock * price'));
    }

    public function edit($id) {
        $this->dispatch('edit-sparepart', id: $id);
    }
};?>

<div class="max-w-7xl mx-auto space-y-6 container pb-12">
    {{-- 1. JUDUL: Garis vertikal dibuat lebih tebal --}}
    <div class="border-l-4 border-indigo-600 pl-4 py-0.5">
        <flux:heading size="xl" class="text-zinc-900 dark:text-white font-bold tracking-tight">Spareparts Catalog</flux:heading>
        <flux:subheading size="sm" class="text-zinc-500 dark:text-zinc-400 mt-1">Central registry for workshop assets, parts levels, and warehouse valuations.</flux:subheading>
    </div>

    {{-- 2. METRIK ROW: Angka & Label dibuat jauh lebih tebal dan berwarna pekat --}}
    <div class="flex flex-wrap items-center gap-y-4 gap-x-8 sm:gap-x-12 py-4 px-1 border-t border-b border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400">
        {{-- Total Items (Biru Tegas) --}}
        <div class="space-y-0.5">
            <p class="text-[11px] text-blue-700 dark:text-blue-400 font-extrabold uppercase tracking-wider">Total Items</p>
            <p class="text-3xl font-black text-blue-700 dark:text-blue-400 tracking-tight">
                {{ $this->totalItems }} <span class="text-xs font-bold text-zinc-500">SKUs</span>
            </p>
        </div>
        
        <div class="hidden sm:block h-8 border-r border-zinc-200 dark:border-zinc-800"></div>
        
        {{-- Stock Status (Amber/Oranye Tegas) --}}
        <div class="space-y-0.5">
            <p class="text-[11px] text-amber-700 dark:text-amber-500 font-extrabold uppercase tracking-wider">Stock Status</p>
            <div class="flex items-center gap-2">
                <p class="text-3xl font-black text-amber-700 dark:text-amber-500 tracking-tight">
                    {{ $this->lowStockCount }}
                </p>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-400 border border-amber-300">
                    <span class="size-1.5 rounded-full bg-amber-600 animate-pulse"></span> Alert Active
                </span>
            </div>
        </div>
        
        <div class="hidden sm:block h-8 border-r border-zinc-200 dark:border-zinc-800"></div>
        
        {{-- Total Inventory Value (Hijau Emerald Kontras) --}}
        <div class="space-y-0.5">
            <p class="text-[11px] text-emerald-700 dark:text-emerald-400 font-extrabold uppercase tracking-wider">Total Inventory Value</p>
            <p class="text-3xl font-black text-emerald-700 dark:text-emerald-400 tracking-tight">
                Rp {{ number_format($this->totalValue, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- FILTER & BUTTON ACTION --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
        <div class="w-full sm:w-80">
            <flux:input 
                wire:model.live.debounce.300ms="search" 
                icon="magnifying-glass" 
                placeholder="Filter by part name..." 
                clearable
                size="sm"
                class="w-full"
            />
        </div>
        
        {{-- BUTTON: Warna Indigo Solid Standar Aplikasi Premium --}}
        <flux:modal.trigger name="create-sparepart">
            <flux:button variant="primary" icon="plus" size="sm" class="w-full sm:w-auto font-bold shadow bg-indigo-600 hover:bg-indigo-700 border-indigo-600 text-white dark:bg-indigo-600 dark:hover:bg-indigo-500">Add Sparepart</flux:button>
        </flux:modal.trigger>
    </div>

    <livewire:sparepart.create />
    <livewire:sparepart.edit />
    
    <x-flash-message />

    {{-- DATA TABLE SECTION --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm">
        <flux:table :paginate="$this->spareparts">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'part_name'" :direction="$sortDirection" wire:click="sort('part_name')" class="text-xs font-bold text-zinc-500">Part Name</flux:table.column>
                <flux:table.column class="text-xs font-bold text-zinc-500">Category</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'stock'" :direction="$sortDirection" wire:click="sort('stock')" class="text-xs font-bold text-zinc-500">Stock Level</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'price'" :direction="$sortDirection" wire:click="sort('price')" class="text-xs font-bold text-zinc-500">Price</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->spareparts as $item)
                    {{-- Efek hover baris tabel dibuat lebih tegas bayangannya --}}
                    <flux:table.row :key="$item->id" class="hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 transition-colors">
                        
                        {{-- Part Name --}}
                        <flux:table.cell class="font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $item->part_name }}
                        </flux:table.cell>
                        
                        {{-- KATEGORI BADGE: Sekarang menggunakan warna Indigo SOLID (Sangat Terlihat Jelas) --}}
                        <flux:table.cell>
                            <span class="inline-flex text-xs px-2.5 py-0.5 rounded-md font-bold bg-indigo-600 text-white shadow-sm">
                                {{ $item->category->name ?? 'Uncategorized' }}
                            </span>
                        </flux:table.cell>
                        
                        {{-- STOCK LEVEL BADGES: Warna latar belakang dinaikkan ke level kepekatan 100 dengan border tebal --}}
                        <flux:table.cell>
                            @if($item->stock == 0)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold bg-red-100 text-red-800 border border-red-300">
                                    <span class="size-1.5 rounded-full bg-red-600"></span> Out of stock
                                </span>
                            @elseif($item->stock <= 5)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                    <span class="size-1.5 rounded-full bg-amber-500"></span> Low stock ({{ $item->stock }})
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    <span class="size-1.5 rounded-full bg-emerald-500"></span> {{ $item->stock }} Pcs
                                </span>
                            @endif
                        </flux:table.cell>
                        
                        {{-- Price --}}
                        <flux:table.cell class="font-extrabold text-zinc-900 dark:text-zinc-100">
                            Rp {{ number_format($item->price, 0, ',', '.') }}
                        </flux:table.cell>
                        
                        {{-- Actions --}}
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="edit({{ $item->id }})">Edit Details</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete-sparepart', {id: {{ $item->id }}})">Delete Item</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-16 text-zinc-400 dark:text-zinc-500">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <flux:icon icon="cube" class="size-8 text-zinc-300 dark:text-zinc-700" />
                                <div class="space-y-0.5">
                                    <p class="font-medium text-zinc-700 dark:text-zinc-300 text-sm">No items found</p>
                                    <p class="text-xs text-zinc-400">No parts matched "{{ $search }}".</p>
                                </div>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>