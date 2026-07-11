<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Category;

new class extends Component
{
    use WithPagination;

    #[Computed]
    public function categories()
    {
        return Category::latest()->paginate(10);
    }

    public function edit($id){
        $this->dispatch('edit-category', id: $id);
    }

};
?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="p-6 bg-white dark:bg-zinc-900 rounded-xl shadow-md border-t-4 border-t-emerald-600 border-x border-b border-zinc-200 dark:border-zinc-800">
        
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="xl" level="1" class="text-emerald-950 dark:text-emerald-400 font-black tracking-wide">Category Management</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400 mt-1">RAPIHIN KATEGORI KOMPONEN, SPAREPART, BIAR BENGKEL MAKIN SATSET EUY</flux:subheading>
            </div>
            
            <flux:modal.trigger name="create-category">
                <button class="flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white px-4 py-2.5 rounded-lg font-bold text-sm shadow-md transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Category
                </button>
            </flux:modal.trigger>
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
                    <flux:table.column class="text-white font-bold text-sm bg-emerald-600 dark:bg-emerald-900">action</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->categories as $category)
                        <flux:table.row :key="$category->id" class="odd:bg-white even:bg-emerald-50/20 dark:odd:bg-zinc-900 dark:even:bg-zinc-800/40 hover:bg-emerald-100 dark:hover:bg-emerald-950/40 transition-colors duration-150">

                            <flux:table.cell class="text-zinc-500 dark:text-zinc-400 font-medium">
                                {{ $loop->iteration + $this->categories->firstItem() - 1 }}
                            </flux:table.cell>    
                            
                            <flux:table.cell class="font-bold text-emerald-950 dark:text-emerald-200">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span>{{ $category->name }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ $category->slug }}</code>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if($category->description)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-cyan-100 text-cyan-800 dark:bg-cyan-950/60 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-900">
                                        {{ $category->description }}
                                    </span>
                                @else
                                    <span class="text-zinc-400 italic text-sm">-</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap text-slate-600 dark:text-zinc-400 text-xs font-semibold">
                                <span class="bg-slate-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-slate-200 dark:border-zinc-700">
                                    {{ $category->created_at->diffForHumans() }}
                                </span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" class="hover:bg-emerald-100 dark:hover:bg-zinc-700 text-emerald-600 dark:text-emerald-400" />

                                    <flux:menu>
                                        <flux:menu.item icon="pencil" wire:click="edit({{ $category->id }})">Edit</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete', {id: {{ $category->id }}})">Delete</flux:menu.item>
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