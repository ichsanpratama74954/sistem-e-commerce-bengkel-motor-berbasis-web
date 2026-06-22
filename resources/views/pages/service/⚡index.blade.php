<?php
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Service;

new class extends Component {
    use WithPagination;

    public $sortBy = 'service_name';
    public $sortDirection = 'asc';

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

    public function edit($id){
        $this->dispatch('edit-service', id: $id);
    }
};?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="p-6 bg-white dark:bg-zinc-900 rounded-xl shadow-md border-t-4 border-t-indigo-600 border-x border-b border-zinc-200 dark:border-zinc-800">
        
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="xl" level="1" class="text-indigo-950 dark:text-indigo-400 font-black tracking-wide">Service Management</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400 mt-1">Kelola seluruh daftar layanan dan tarif harga bengkel</flux:subheading>
            </div>
            
            <flux:modal.trigger name="create-service">
                <button class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-lg font-bold text-sm shadow-md transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Service
                </button>
            </flux:modal.trigger>
        </div>

        <livewire:service.create />
        <livewire:service.edit />
        <x-flash-message />

        {{-- Table Service --}}
        <div class="overflow-x-auto mt-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <flux:table :paginate="$this->services" class="w-full border-collapse">
                
                <flux:table.columns class="bg-indigo-600 dark:bg-indigo-900 border-b border-indigo-700">
                    <flux:table.column class="text-white font-bold text-sm bg-indigo-600 dark:bg-indigo-900">No</flux:table.column>
                    
                    <flux:table.column sortable :sorted="$sortBy === 'service_name'" :direction="$sortDirection" wire:click="sort('service_name')" class="text-white font-bold text-sm bg-indigo-600 dark:bg-indigo-900 hover:bg-indigo-700 cursor-pointer transition-colors">
                        Service Name
                    </flux:table.column>
                    
                    <flux:table.column class="text-white font-bold text-sm bg-indigo-600 dark:bg-indigo-900">Description</flux:table.column>
                    
                    <flux:table.column sortable :sorted="$sortBy === 'service_price'" :direction="$sortDirection" wire:click="sort('service_price')" class="text-white font-bold text-sm bg-indigo-600 dark:bg-indigo-900 hover:bg-indigo-700 cursor-pointer transition-colors">
                        Price
                    </flux:table.column>
                    
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')" class="text-white font-bold text-sm bg-indigo-600 dark:bg-indigo-900 hover:bg-indigo-700 cursor-pointer transition-colors">
                        Created At
                    </flux:table.column>
                    
                    <flux:table.column class="text-white font-bold text-sm bg-indigo-600 dark:bg-indigo-900">action</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->services as $service)
                        <flux:table.row :key="$service->id" class="odd:bg-white even:bg-indigo-50/20 dark:odd:bg-zinc-900 dark:even:bg-zinc-800/40 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors duration-150">
                            
                            <flux:table.cell class="text-zinc-500 dark:text-zinc-400 font-medium">
                                {{ $this->services->firstItem() + $loop->index }}
                            </flux:table.cell>
                            
                            <flux:table.cell class="font-bold text-indigo-950 dark:text-indigo-200">
                                {{ $service->service_name }}
                            </flux:table.cell>
                            
                            <flux:table.cell>
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
                            
                            <flux:table.cell class="font-black text-emerald-600 dark:text-emerald-400 text-sm tracking-wide">
                                Rp {{ number_format($service->service_price, 0, ',', '.') }}
                            </flux:table.cell>
                            
                            <flux:table.cell class="whitespace-nowrap text-slate-600 dark:text-zinc-400 text-xs font-semibold">
                                <span class="bg-slate-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-slate-200 dark:border-zinc-700">
                                    {{ $service->created_at->diffForHumans() }}
                                </span>
                            </flux:table.cell>
                            
                            <flux:table.cell>
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