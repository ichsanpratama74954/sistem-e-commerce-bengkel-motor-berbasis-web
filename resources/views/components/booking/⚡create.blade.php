<?php

use Livewire\Component;
use App\Livewire\Forms\BookingForm;
use App\Models\User;
use App\Models\Motorcycle;

new class extends Component
{
    public BookingForm $form;
    public $users = [];
    public $motorcycles = [];
    
    public function mount()
    {
        $this->form = new BookingForm($this, 'form');
        $this->users = User::select('id', 'name', 'email')->get();
        $this->motorcycles = Motorcycle::select('id', 'brand', 'model', 'plate_number')->get();
    }

    public function save()
    {
        $this->form->store();
        Flux::modal('create-booking')->close();
        session()->flash('success', 'Booking created successfully');
        $this->redirectRoute('booking.index',navigate: true);

    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }
};
?>

<div>
    <flux:modal name="create-booking" class="md:w-150"  x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="save">
            {{-- header --}}
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Create Booking
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    Add a new booking to your account
                </flux:text>
            </div>

            <div class="space-y-6">
                <flux:select label="User" wire:model="form.user_id">
                    <flux:select.option value="">Select User</flux:select.option>
                    @foreach($users as $user)
                        <flux:select.option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select label="Motorcycle" wire:model="form.motorcycle_id">
                    <flux:select.option value="">Select Motorcycle</flux:select.option>
                    @foreach($motorcycles as $motorcycle)
                        <flux:select.option value="{{ $motorcycle->id }}">{{ $motorcycle->brand }} {{ $motorcycle->model }} - {{ $motorcycle->plate_number }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    label="Booking Date"
                    type="date"
                    wire:model="form.booking_date"
                />

                <flux:select label="Status" wire:model="form.status">
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="approved">Approved</flux:select.option>
                    <flux:select.option value="rejected">Rejected</flux:select.option>
                </flux:select>
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