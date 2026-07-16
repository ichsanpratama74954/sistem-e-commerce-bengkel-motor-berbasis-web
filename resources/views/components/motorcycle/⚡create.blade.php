<?php

use Livewire\Component;
use App\Livewire\Forms\MotorcycleForm;
use App\Models\User;

new class extends Component
{
    public MotorcycleForm $form;
    public $users = [];

    public function mount()
    {
        // JIKA PELANGGAN: Langsung kunci pemilik motor ke akun dia sendiri
        if (auth()->user()->role === 'pelanggan') {
            $this->form->user_id = auth()->id();
        } else {
            // JIKA ADMIN: Ambil daftar user untuk ditampilkan di dropdown
            $this->users = User::select('id', 'name', 'email')->get();
        }
    } 

    public function save()
    {
        $this->form->store();
        Flux::modal('create-motorcycle')->close();
        
        session()->flash('success', 'Motorcycle created successfully');
        $this->redirectRoute('motorcycle.index', navigate: true);
    } 

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();

        // Kembalikan binding default setelah form di-reset
        if (auth()->user()->role === 'pelanggan') {
            $this->form->user_id = auth()->id();
        }
    }
};
?>

<div>
    <flux:modal name="create-motorcycle" class="md:w-150" x-on:close="$wire.resetForm()"> 
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
                
                {{-- REVISI: Tampilkan pilihan Owner HANYA jika yang login BUKAN pelanggan --}}
                @if(auth()->user()->role !== 'pelanggan')
                    <flux:select label="Owner / Client" wire:model="form.user_id">
                        <flux:select.option value="">Select Owner</flux:select.option>
                        @foreach($users as $user)
                            <flux:select.option value="{{ $user->id }}">
                                {{ $user->name }} ({{ $user->email }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                @endif

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
</div>