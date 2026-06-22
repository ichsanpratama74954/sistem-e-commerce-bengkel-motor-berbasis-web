<?php
use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Forms\ServiceForm;
use App\Models\Service;

new class extends Component {
    public ServiceForm $form;

    #[On('edit-service')]
    public function editService($id){
        $service = Service::find($id);
        $this->form->setService($service);
        Flux::modal('edit-service')->show();
    }

    public function updateService() {
        $this->form->update();
        Flux::modal('edit-service')->close();
        
        session()->flash('success', 'Service updated successfully');
        $this->redirectRoute('service.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }

    #[On('confirm-delete')]
    public function confirmDelete($id)
    {
        $service = Service::find($id);
        $this->form->setService($service);
        Flux::modal('delete-service')->show();
    }

    public function deleteService() {
        $this->form->service->delete();
        Flux::modal('delete-service')->close();
        
        session()->flash('success', 'Service deleted successfully');
        $this->redirectRoute('service.index', navigate: true);
    }
};?>

<div>
    {{-- Edit Modal --}}
    <flux:modal name="edit-service" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="updateService">
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Edit Service</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">Edit your service details below</flux:text>
            </div>

            <div class="space-y-6">
                <flux:input
                    label="Service Name"
                    wire:model="form.service_name"
                    wire:dirty.class.text-red-500
                />
                
                <flux:input
                    type="number"
                    label="Service Price (Rp)"
                    wire:model="form.service_price"
                    wire:dirty.class.text-red-500
                />

                <flux:textarea
                    label="Description"
                    wire:model="form.description"
                    wire:dirty.class.text-red-500
                />
            </div>

            <div wire:show="$dirty" class="text-red-500 dark:text-red-400">
                You have unsaved changes
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="primary" type="submit">Update</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Modal --}}
    <flux:modal name="delete-service" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="deleteService">
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Delete Service</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">Are you sure? This action cannot be undone.</flux:text>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="danger" type="submit">Delete</flux:button>
            </div>
        </form>
    </flux:modal>
</div>