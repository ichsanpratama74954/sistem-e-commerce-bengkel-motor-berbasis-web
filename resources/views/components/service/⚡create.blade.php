<?php
use App\Livewire\Forms\ServiceForm;
use Livewire\Component;

new class extends Component {
    public ServiceForm $form;

    public function save()
    {
        $this->form->store();
        Flux::modal('create-service')->close();
        
        session()->flash('success', 'Service created successfully');
        $this->redirectRoute('service.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }
};?>

<div>
    <flux:modal name="create-service" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="save">
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Create Service</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">Add a new workshop service details below</flux:text>
            </div>

            <div class="space-y-6">
                <flux:input
                    label="Service Name"
                    placeholder="e.g., Service Motor Ringan"
                    wire:model="form.service_name"
                />
                
                <flux:input
                    type="number"
                    label="Service Price (Rp)"
                    placeholder="e.g., 50000"
                    wire:model="form.service_price"
                />

                <flux:textarea
                    label="Description"
                    placeholder="Enter service details/description"
                    wire:model="form.description"
                />
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="primary" type="submit">Create</flux:button>
            </div>
        </form>
    </flux:modal>
</div>