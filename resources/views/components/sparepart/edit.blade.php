<?php
use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Forms\SparepartForm;
use App\Models\Sparepart;
use App\Models\Category;

new class extends Component {
    public SparepartForm $form;

    // Mendengarkan event 'edit-sparepart' yang dikirim dari tabel utama
    #[On('edit-sparepart')]
    public function editSparepart($id){
        $sparepart = Sparepart::find($id);
        $this->form->setSparepart($sparepart);
        
        // Membuka modal edit
        Flux::modal('edit-sparepart')->show();
    }

    public function updateSparepart() {
        $this->form->update();
        Flux::modal('edit-sparepart')->close();
        
        session()->flash('success', 'Sparepart updated successfully');
        $this->redirectRoute('sparepart.index', navigate: true);
    }

    // Mendengarkan event hapus dari tabel utama
    #[On('confirm-delete-sparepart')]
    public function confirmDelete($id) {
        $sparepart = Sparepart::find($id);
        $this->form->setSparepart($sparepart);
        
        // Membuka konfirmasi modal hapus
        Flux::modal('delete-sparepart')->show();
    }

    public function deleteSparepart() {
        $this->form->sparepart->delete();
        Flux::modal('delete-sparepart')->close();
        
        session()->flash('success', 'Sparepart deleted successfully');
        $this->redirectRoute('sparepart.index', navigate: true);
    }

    public function resetForm() {
        $this->resetValidation();
        $this->form->reset();
    }
};?>

<div>
    {{-- MODAL FORM EDIT --}}
    <flux:modal name="edit-sparepart" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-6" wire:submit.prevent="updateSparepart">
            <div>
                <flux:heading size="lg">Edit Sparepart</flux:heading>
                <flux:text>Update sparepart details below</flux:text>
            </div>

            <div class="space-y-4">
                <flux:select label="Category" wire:model="form.category_id">
                    @foreach(Category::all() as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input label="Part Name" wire:model="form.part_name" />
                
                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="number" label="Stock" wire:model="form.stock" />
                    <flux:input type="number" label="Price (Rp)" wire:model="form.price" />
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Update</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- MODAL KONFIRMASI HAPUS (DELETE) --}}
    <flux:modal name="delete-sparepart" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-6" wire:submit.prevent="deleteSparepart">
            <div>
                <flux:heading size="lg">Delete Sparepart</flux:heading>
                <flux:text>Are you sure you want to delete this sparepart? This action cannot be undone.</flux:text>
            </div>
            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="outline">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="danger" type="submit">Delete</flux:button>
            </div>
        </form>
    </flux:modal>
</div>