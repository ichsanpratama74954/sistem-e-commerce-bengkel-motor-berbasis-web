<?php
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Sparepart;

new class extends Component {
    use WithPagination;

    // Menyimpan keyword pencarian di URL agar tidak hilang saat refresh
    #[Url(history: true)]
    public $search = '';

    // Pengaturan default sorting berdasarkan kolom part_name
    public $sortBy = 'part_name';
    public $sortDirection = 'asc';

    // Otomatis balik ke halaman 1 jika user mengetik pencarian baru
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // Logika pengurutan kolom tabel
    public function sort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    // Mengambil data sparepart dari database secara computed
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

    // Memicu event untuk membuka modal edit
    public function edit($id) {
        $this->dispatch('edit-sparepart', id: $id);
    }
};?>

<div class="max-w-7xl mx-auto space-y-4">
    <div>
        <flux:heading size="xl" class="text-zinc-800 dark:text-white">Spareparts</flux:heading>
        <flux:subheading size="lg" class="text-zinc-600 dark:text-zinc-400">Manage workshop spareparts and stock inventory</flux:subheading>
    </div>
    
    <flux:separator variant="subtle" />

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <flux:modal.trigger name="create-sparepart">
            <flux:button variant="primary" icon="plus" color="primary">Add Sparepart</flux:button>
        </flux:modal.trigger>

        <div class="w-full sm:w-80">
            <flux:input 
                wire:model.live.debounce.300ms="search" 
                icon="magnifying-glass" 
                placeholder="Search by part name..." 
                clearable
            />
        </div>
    </div>

    <livewire:sparepart.create />
    <livewire:sparepart.edit />
    
    <x-flash-message />

    <div class="overflow-x-auto">
        <flux:table :paginate="$this->spareparts">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'part_name'" :direction="$sortDirection" wire:click="sort('part_name')">Part Name</flux:table.column>
                <flux:table.column>Category</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'stock'" :direction="$sortDirection" wire:click="sort('stock')">Stock</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'price'" :direction="$sortDirection" wire:click="sort('price')">Price</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->spareparts as $item)
                    <flux:table.row :key="$item->id">
                        <flux:table.cell class="font-medium text-zinc-800 dark:text-white">
                            {{ $item->part_name }}
                        </flux:table.cell>
                        
                        <flux:table.cell>
                            <flux:badge size="sm" color="neutral" inset="top bottom">
                                {{ $item->category->name }}
                            </flux:badge>
                        </flux:table.cell>
                        
                        <flux:table.cell class="text-zinc-700 dark:text-zinc-300">
                            {{ $item->stock }} Pcs
                        </flux:table.cell>
                        
                        <flux:table.cell class="text-zinc-700 dark:text-zinc-300">
                            Rp {{ number_format($item->price, 0, ',', '.') }}
                        </flux:table.cell>
                        
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="edit({{ $item->id }})">Edit</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete-sparepart', {id: {{ $item->id }}})">Delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-12 text-zinc-400 dark:text-zinc-500">
                            No spareparts found matching "{{ $search }}"
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>