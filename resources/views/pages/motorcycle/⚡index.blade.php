<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Motorcycle;

new class extends Component
{
    use WithPagination;

    #[Computed]
    public function motorcycles()
    {
        return Motorcycle::with('user')->latest()->paginate(10);
    }
};
?>

<div class="max-w-7xl mx-auto space-y-4">
    <flux:heading size="xl" class="text-zinc-800 dark:text-white">Motorcycles</flux:heading>
    <flux:subheading size="lg" class="text-zinc-600 dark:text-zinc-400">Manage your motorcycles</flux:subheading>
    <flux:separator variant="subtle" />
    
    <flux:modal.trigger name="create-motorcycle">
        <flux:button variant="primary" icon="plus" color="primary">Add Motorcycle</flux:button>
    </flux:modal.trigger>

    <livewire:motorcycle.create />
    <livewire:motorcycle.edit :key="'edit-motorcycle-modal'" /> 
    <x-flash-message />

    {{-- table --}}
    <div class="overflow-x-auto">
       <flux:table :paginate="$this->motorcycles->hasPages()" :pagination="$this->motorcycles" class="w-full">
            <flux:table.columns>
                <flux:table.column>No</flux:table.column>
                <flux:table.column>Owner</flux:table.column>
                <flux:table.column>Brand</flux:table.column>
                <flux:table.column>Model</flux:table.column>
                <flux:table.column>Plate Number</flux:table.column>
                <flux:table.column>Created At</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->motorcycles as $motorcycle)
                    <flux:table.row :key="$motorcycle->id">
                        <flux:table.cell class="font-medium">
                            {{ $loop->iteration }}
                        </flux:table.cell>

                        <flux:table.cell class="font-medium">
                            {{ $motorcycle->user->name ?? '-' }}
                        </flux:table.cell>

                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                            {{ $motorcycle->brand ?? '-' }}
                        </flux:table.cell>

                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                            {{ $motorcycle->model ?? '-' }}
                        </flux:table.cell>

                        <flux:table.cell class="font-mono uppercase">
                            {{ $motorcycle->plate_number ?? '-' }}
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            {{ $motorcycle->created_at?->diffForHumans() ?? '-' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>

                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="$dispatch('edit-motorcycle', { id: {{ $motorcycle->id }} })">Edit</flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete', {id: {{ $motorcycle->id }}})">Delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>