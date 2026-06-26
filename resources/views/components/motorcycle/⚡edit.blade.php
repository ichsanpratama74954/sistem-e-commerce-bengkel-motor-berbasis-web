<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Motorcycle;
use App\Livewire\Forms\MotorcycleForm;

new class extends Component
{
    public MotorcycleForm $form;

    #[On('edit-motorcycle')]
    public function loadMotorcycle($id)
    {
        $motorcycle = Motorcycle::find($id);
        $this->form->setMotorcycle($motorcycle);
        Flux::modal('edit-motorcycle')->show();
    }

    public function updateMotorcycle()
    {
        $this->form->update();
        Flux::modal('edit-motorcycle')->close();
        session()->flash('success', 'Motorcycle updated successfully');
        return $this->redirect('motorcycles', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }

    #[On('confirm-delete')]
    public function confirmDelete($id)
    {
        $motorcycle = Motorcycle::find($id);
        $this->form->setMotorcycle($motorcycle);
        Flux::modal('delete-motorcycle')->show();
    }

    public function deleteCategory() {
        $this->form->category->delete();
        Flux::modal('delete-category')->close();
        session()->flash('success', 'Category deleted successfully');
        $this->redirectRoute('category.index', navigate: true);
    }

    public function deleteMotorcycle() {
        $this->form->motorcycle->delete();
        Flux::modal('delete-motorcycle')->close();
        session()->flash('success', 'Motorcycle deleted successfully');
        $this->redirectRoute('motorcycle.index', navigate: true);
    }
};
?>

<div>
    {{-- edit modal --}}
    <flux:modal 
        name="edit-motorcycle" 
        class="md:w-150" 
        x-on:close="$wire.resetForm()" 
    >
        <form class="space-y-8" wire:submit.prevent="updateMotorcycle">
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Edit Motorcycle
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    Edit your motorcycle details below
                </flux:text>
            </div>

            <div class="space-y-6">
                <flux:input
                    label="Brand"
                    placeholder="Enter motorcycle brand"
                    wire:model="form.brand"
                    wire:dirty.class="text-red-500"
                />

                <flux:input
                    label="Model"
                    placeholder="Enter motorcycle model"
                    wire:model="form.model"
                    wire:dirty.class="text-red-500"
                />

                <flux:input
                    label="Plate Number"
                    placeholder="Enter plate number"
                    wire:model="form.plate_number"
                    wire:dirty.class="text-red-500"
                />
            </div>

            <div wire:show="$dirty" class="text-red-500 dark:text-red-400">
                you have unsaved changes
            </div>
    
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="primary" type="submit">Update</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- delete modal --}}

    <flux:modal 
        name="delete-motorcycle" 
        class="md:w-150" 
        x-on:close="$wire.resetForm()" 
    >
        <form class="space-y-8" wire:submit.prevent="deleteMotorcycle">
            {{-- header --}}
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Delete Motorcycle
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    this action cannot be undone
                </flux:text>
            </div>

            {{-- footer --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="danger" type="submit">Delete</flux:button>
            </div>
                

        </form>
    </flux:modal>
</div>