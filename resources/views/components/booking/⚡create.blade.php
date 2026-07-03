<?php

use Livewire\Component;
use App\Livewire\Forms\BookingForm;

new class extends Component
{
    public BookingForm $form;
    
    public function mount()
    {
        $this->form = new BookingForm($this, 'form');
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
                <flux:input
                    label="User ID"
                    type="number"
                    placeholder="Enter user ID (e.g., 1)"
                    wire:model="form.user_id"
                />

                <flux:input
                    label="Motorcycle ID"
                    type="number"
                    placeholder="Enter motorcycle ID (e.g., 3)"
                    wire:model="form.motorcycle_id"
                />

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