<?php

use Livewire\Component;
use App\Livewire\Forms\MotorcycleForm;

new class extends Component
{
    //instance class motorcycleform
    public MotorcycleForm $form;

    public function save()
    {
        $this->form->store();
        Flux::modal('create-motorcycle')->close();
        
        //session
        session()->flash('success', 'Motorcycle created successfully');

        $this->redirectRoute('motorcycle.index',navigate: true);

    } 

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }
};
?>

<div>
    <flux:modal name="create-motorcycle" class="md:w-150"  x-on:close="$wire.resetForm()"> 
        <form class="space-y-8" wire:submit.prevent="save">
            {{-- header --}}
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Create Motorcycle
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    Add a new motorcycle to your account
                </flux:text>
            </div>

            {{-- form field --}}
            <div class="space-y-6">
                <flux:input
                    label="Brand"
                    placeholder="Enter motorcycle brand"
                    wire:model="form.brand"
                />

                <flux:input
                    label="Model"
                    placeholder="Enter motorcycle model"
                    wire:model="form.model"
                />

                <flux:input
                    label="Plate Number"
                    placeholder="Enter plate number"
                    wire:model="form.plate_number"
                />
            </div>
    
            {{-- footer --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="primary" type="submit">Create</flux:button>
            </div>
        </form>
    </flux:modal>