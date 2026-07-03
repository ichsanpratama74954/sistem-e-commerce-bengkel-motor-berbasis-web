<?php
use App\Livewire\Forms\SparepartForm;
use App\Models\Category;
use Livewire\Component;

new class extends Component {
    // Menghubungkan ke Form Object yang sudah dibuat sebelumnya
    public SparepartForm $form;

    public function save()
    {
        $this->form->store();
        
        // Menutup modal Flux setelah berhasil menyimpan
        Flux::modal('create-sparepart')->close();
        
        session()->flash('success', 'Sparepart created successfully');
        
        // Refresh halaman menggunakan wire:navigate agar data terbaru langsung muncul
        $this->redirectRoute('sparepart.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }
};?>

<div>
    <flux:modal name="create-sparepart" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-6" wire:submit.prevent="save">
            <div>
                <flux:heading size="lg">Create Sparepart</flux:heading>
                <flux:text>Add a new sparepart item to inventory</flux:text>
            </div>

            <div class="space-y-4">
                <flux:select label="Category" placeholder="Choose category..." wire:model="form.category_id">
                    @foreach(Category::all() as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input label="Part Name" placeholder="e.g. Oli Mesin Yamalube" wire:model="form.part_name" />
                
                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="number" label="Stock" placeholder="0" wire:model="form.stock" />
                    <flux:input type="number" label="Price (Rp)" placeholder="0" wire:model="form.price" />
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Create</flux:button>
            </div>
        </form>
    </flux:modal>
</div>