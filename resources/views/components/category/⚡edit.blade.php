<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Category;
use App\Livewire\Forms\CategoryForm;

new class extends Component
{
    public CategoryForm $form;

    #[On('edit-category')]
    public function editCategory($id){
        $category = Category::find($id);
        $this->form->setCategory($category);
        Flux::modal('edit-category')->show();
    }

    public function updateCategory() {
        $this->form->update();
        Flux::modal('edit-category')->close();
        session()->flash('success', 'Category updated successfully');
        $this->redirectRoute('category.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }

    #[On('confirm-delete')]
    public function confirmDelete($id)
    {
        $category = Category::find($id);
        $this->form->setCategory($category);
        Flux::modal('delete-category')->show();
    }

    public function deleteCategory() {
        $this->form->category->delete();
        Flux::modal('delete-category')->close();
        session()->flash('success', 'Category deleted successfully');
        $this->redirectRoute('category.index', navigate: true);
    }
};
?>

<div>
    {{-- Modal Untuk Edit --}}

    <flux:modal 
        name="edit-category" 
        class="md:w-150" 
        x-on:close="$wire.resetForm()" 
    >
        <form class="space-y-8" wire:submit.prevent="updateCategory">
            {{-- header --}}
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Edit Category
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    Edit your category details below
                </flux:text>
            </div>

            {{-- form field --}}
            <div class="space-y-6">
                <flux:input
                    label="Name"
                    placeholder="Enter category name"
                    wire:model="form.name"
                    wire:dirty.class.text-red-500
                />

                <flux:textarea
                    label="Description"
                    placeholder="Enter category description"
                    wire:model="form.description"
                    wire:dirty.class.text-red-500
                />
            </div>

            <div 
                wire:show ="$dirty"
                class="text-red-500 dark:text-red-400"
            >
                you have unsaved changes
            </div>
    
            {{-- footer --}}
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
        name="delete-category" 
        class="md:w-150" 
        x-on:close="$wire.resetForm()" 
    >
        <form class="space-y-8" wire:submit.prevent="deleteCategory">
            {{-- header --}}
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Delete Category
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
