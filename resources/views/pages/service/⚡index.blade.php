<?php
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Service;

new class extends Component {
    use WithPagination;

    public $sortBy = 'service_name';
    public $sortDirection = 'asc';

    public $service_name;
    public $service_price;
    public $description;

    public function sort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function services()
    {
        return Service::query()
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }

    public function saveService()
    {
        $this->validate([
            'service_name' => 'required|string|max:255',
            'service_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        Service::create([
            'service_name' => $this->service_name,
            'service_price' => $this->service_price,
            'description' => $this->description,
        ]);

        $this->reset(['service_name', 'service_price', 'description']);

        session()->flash('success', 'Service created successfully');
        $this->redirectRoute('service.index', navigate: true);
    }

    public function edit($id){
        $this->dispatch('edit-service', id: $id);
    }
};?>

<div class="max-w-7xl mx-auto space-y-6">
    <x-flash-message />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <div class="lg:col-span-3 bg-white dark:bg-zinc-900 p-5 rounded-xl shadow-md border-t-4 border-t-indigo-600 border-x border-b border-zinc-200 dark:border-zinc-800">
            <div class="mb-5">
                <flux:heading size="lg" level="2" class="text-indigo-950 dark:text-indigo-400 font-black tracking-wide">Add Service</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400 mt-1 text-xs">Input langsung daftar layanan baru ke database</flux:subheading>
            </div>

            <form wire:submit.prevent="saveService" class="space-y-4">
                <flux:input
                    label="Service Name"
                    placeholder="Contoh: Service Karburator"
                    wire:model="service_name"
                />
                
                <flux:input
                    type="number"
                    label="Service Price (Rp)"
                    placeholder="Masukkan angka saja"
                    wire:model="service_price"
                />

                <flux:textarea
                    label="Description"
                    placeholder="Ketik deskripsi atau syarat VIP di sini..."
                    wire:model="description"
                    rows="3"
                />

                <flux:button variant="primary" color="indigo" type="submit" class="w-full font-bold tracking-wide shadow-sm py-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 mr-1 inline">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Save Service
                </flux:button>
            </form>
        </div>

        <div class="lg:col-span-9 bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-md border-t-4 border-t-indigo-600 border-x border-b border-zinc-200 dark:border-zinc-800">
            
            <div class="mb-6">
                <flux:heading size="xl" level="1" class="text-indigo-950 dark:text-indigo-400 font-black tracking-wide">Service Management</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400 mt-1">Kelola seluruh daftar layanan dan urutkan tarif harga bengkel</flux:subheading>
            </div>

            <livewire:service.edit />

            {{-- Table Service --}}
            <div class="overflow-x-auto mt-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <flux:table :paginate="$this->services" class="w-full border-collapse">
                    
                    <flux:table.columns class="bg-indigo-600 dark:bg-indigo-900 border-b border-indigo-700">
                        <flux:table.column class="text-white font-bold text-sm bg-indigo-600 dark:bg-indigo-900 text-left w-16 ps-5 py-3.5">No</flux:table.column>
                        
                        <flux:table.column sortable :sorted="$sortBy === 'service_name'" :direction="$sortDirection" wire:click="sort('service_name')" class="text-white font-bold text-sm bg-indigo-600 dark:bg-indigo-900 hover:bg-indigo-700 cursor-pointer transition-colors py-3.5 min-w-[160px] pr-8">
                            Service Name
                        </flux:table.column>
                        
                        <flux:table.column class="text-white font-bold text-sm bg-indigo-600 dark:bg-indigo-900 py-3.5 min-w-[140px]">Description</flux:table.column>
                        
                        <flux:table.column sortable :sorted="$sortBy === 'service_price'" :direction="$sortDirection" wire:click="sort('service_price')" class="text-white font-bold text-sm bg-indigo-600 dark:bg-indigo-900 hover:bg-indigo-700 cursor-pointer transition-colors py-3.5">
                            Price
                        </flux:table.column>
                        
                        <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')" class="text-white font-bold text-sm bg-indigo-600 dark:bg-indigo-900 hover:bg-indigo-700 cursor-pointer transition-colors py-3.5">
                            Created At
                        </flux:table.column>
                        
                        <flux:table.column class="text-white font-bold text-sm bg-indigo-600 dark:bg-indigo-900 py-3.5">action</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->services as $service)
                            <flux:table.row :key="$service->id" class="odd:bg-white even:bg-indigo-50/20 dark:odd:bg-zinc-900 dark:even:bg-zinc-800/40 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors duration-150">
                                
                                <flux:table.cell class="text-zinc-500 dark:text-zinc-400 font-bold text-left w-16 ps-5 py-4.5">
                                    {{ $this->services->firstItem() + $loop->index }}
                                </flux:table.cell>
                                
                                <flux:table.cell class="font-bold text-indigo-950 dark:text-indigo-200 py-4.5 pr-8 whitespace-nowrap">
                                    {{ $service->service_name }}
                                </flux:table.cell>
                                
                                <flux:table.cell class="py-4.5">
                                    @if($service->description)
                                        @if(str_contains(strtolower($service->description), 'vip'))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200 dark:border-amber-900 uppercase tracking-wider">
                                                {{ $service->description }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-400 border border-purple-200 dark:border-purple-900">
                                                {{ $service->description }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-zinc-400 italic text-sm">-</span>
                                    @endif
                                </flux:table.cell>
                                
                                <flux:table.cell class="font-black text-emerald-600 dark:text-emerald-400 text-sm tracking-wide py-4.5">
                                    Rp {{ number_format($service->service_price, 0, ',', '.') }}
                                </flux:table.cell>
                                
                                <flux:table.cell class="whitespace-nowrap text-slate-600 dark:text-zinc-400 text-xs font-semibold py-4.5">
                                    <span class="bg-slate-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-slate-200 dark:border-zinc-700">
                                        {{ $service->created_at->diffForHumans() }}
                                    </span>
                                </flux:table.cell>
                                
                                <flux:table.cell class="py-4.5">
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" class="hover:bg-indigo-100 dark:hover:bg-zinc-700 text-indigo-600 dark:text-indigo-400" />
                                        <flux:menu>
                                            <flux:menu.item icon="pencil" wire:click="edit({{ $service->id }})">Edit</flux:menu.item>
                                            <flux:menu.separator />
                                            <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete', {id: {{ $service->id }}})">Delete</flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>
    </div>
</div>