<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Forms\PaymentForm;
use App\Models\Payment;

new class extends Component {
    public PaymentForm $form;

    #[On('edit-payment')]
    public function editPayment($id){
        $payment = Payment::find($id);
        $this->form->setPayment($payment);
        Flux::modal('edit-payment')->show();
    }

    public function updatePayment() {
        $this->form->update();
        Flux::modal('edit-payment')->close();
        session()->flash('success', 'Payment updated successfully');
        $this->redirectRoute('payment.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }

    #[On('confirm-delete')]
    public function confirmDelete($id)
    {
        $payment = Payment::find($id);
        $this->form->setPayment($payment);
        Flux::modal('delete-category')->show(); // Menggunakan nama modal delete terpadu
    }

    public function deletePayment() {
        $this->form->payment->delete();
        Flux::modal('delete-category')->close();
        session()->flash('success', 'Payment deleted successfully');
        $this->redirectRoute('payment.index', navigate: true);
    }
};?>

<div>
    <flux:modal name="edit-payment" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="updatePayment">
            <div class="space-y-2">
                <flux:heading size="lg">Edit Payment</flux:heading>
                <flux:text>Modify payment details below</flux:text>
            </div>

            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="Booking ID (Optional)" wire:model="form.booking_id" />
                    <flux:input label="Order ID (Optional)" wire:model="form.order_id" />
                </div>
                <flux:input label="Amount (Rp)" type="number" wire:model="form.amount" />
                
                {{-- GRID BARU: Membagi area status & metode menjadi 2 kolom --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- PEMBAYARAN 💳 --}}
                    <flux:select label="Payment Method" wire:model="form.payment_method" placeholder="Select method...">
                        <flux:select.option value="Cash">💵 Cash / Tunai</flux:select.option>
                        <flux:select.option value="Transfer">🏦 Bank Transfer</flux:select.option>
                        <flux:select.option value="QRIS">📱 QRIS / E-Wallet</flux:select.option>
                        <flux:select.option value="DANA">DANA</flux:select.option>
                    </flux:select>

                    <flux:select label="Payment Status" wire:model="form.payment_status">
                        <flux:select.option value="Pending">Pending</flux:select.option>
                        <flux:select.option value="Success">Success</flux:select.option>
                        <flux:select.option value="Failed">Failed</flux:select.option>
                    </flux:select>
                </div>
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
    <flux:modal name="delete-category" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="deletePayment">
            <div class="space-y-2">
                <flux:heading size="lg">Delete Payment</flux:heading>
                <flux:text>This action cannot be undone.</flux:text>
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