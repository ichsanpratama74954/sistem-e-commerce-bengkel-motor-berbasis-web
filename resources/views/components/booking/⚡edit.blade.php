<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Booking;
use App\Livewire\Forms\BookingForm;
use Flux\Flux;

new class extends Component
{
    public BookingForm $form;

    public function mount()
    {
        $this->form = new BookingForm($this, 'form');
    }

    #[On('edit-booking')]
    public function editBooking($id)
    {
        $booking = Booking::find($id);
        
        if ($booking) {
            $this->form->setBooking($booking);
            Flux::modal('edit-booking')->show();
        }
    }

    public function updateBooking() {
        $this->form->update();
        Flux::modal('edit-booking')->close();
        session()->flash('success', 'Booking updated successfully');
        $this->redirectRoute('booking.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }

    #[On('confirm-delete')]
    public function confirmDelete($id)
    {
        $booking = Booking::find($id);
        
        if ($booking) {
            $this->form->setBooking($booking);
            Flux::modal('delete-booking')->show();
        }
    }

     public function deleteBooking() {
        $this->form->booking->delete();
        Flux::modal('delete-booking')->close();
        session()->flash('success', 'Booking deleted successfully');
        $this->redirectRoute('booking.index', navigate: true);
    }
};
?>

<div>
    {{-- ==================== MODAL EDIT ==================== --}}
    <flux:modal 
        name="edit-booking" 
        class="md:w-150" 
        x-on:close="$wire.resetForm()" 
    >
        <form class="space-y-8" wire:submit.prevent="updateBooking">
            {{-- header --}}
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Edit Booking
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    Edit your booking details below
                </flux:text>
            </div>

            {{-- form field --}}
            <div class="space-y-6">
                <flux:input
                    label="User ID"
                    type="number"
                    placeholder="Enter user ID"
                    wire:model="form.user_id"
                />

                <flux:input
                    label="Motorcycle ID"
                    type="number"
                    placeholder="Enter motorcycle ID"
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

            <div 
                wire:show="$dirty"
                class="text-red-500 dark:text-red-400 text-sm"
            >
                You have unsaved changes *
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


    {{-- ==================== MODAL DELETE ==================== --}}
    <flux:modal 
        name="delete-booking" 
        class="md:w-150" 
        x-on:close="$wire.resetForm()" 
    >
        <form class="space-y-8" wire:submit.prevent="deleteBooking">
            {{-- header --}}
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Delete Booking
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    This action cannot be undone. Are you sure you want to delete this booking?
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