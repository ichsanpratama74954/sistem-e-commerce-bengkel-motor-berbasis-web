<?php

use App\Livewire\Forms\PaymentForm;
use Livewire\Component;

new class extends Component {
    public PaymentForm $form;

    public function save()
    {
        $this->form->store();
        Flux::modal('create-payment')->close();
        session()->flash('success', 'Payment created successfully');
        $this->redirectRoute('payment.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }
};?>

<div>
    <flux:modal name="create-payment" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="save">
            <div class="space-y-2">
                <flux:heading size="lg">Create Payment</flux:heading>
                <flux:text>Input new payment transaction details for financial auditing.</flux:text>
            </div>

            <div class="space-y-6">
                {{-- Grid 1: Source Origin --}}
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="Booking ID (Optional)" placeholder="e.g. 1" wire:model="form.booking_id" />
                    <flux:input label="Order ID (Optional)" placeholder="e.g. 3" wire:model="form.order_id" />
                </div>

                {{-- Input 2: Nominal Uang --}}
                <flux:input label="Amount (Rp)" type="number" placeholder="Enter total amount" wire:model="form.amount" />
                
                {{-- Grid 3: Metode & Status --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- METODE PEMBAYARAN 💳 --}}
                    <flux:select label="Payment Method" wire:model="form.payment_method" placeholder="Select method...">
                        <flux:select.option value="Cash">💵 Cash / Tunai</flux:select.option>
                        <flux:select.option value="Transfer">🏦 Bank Transfer</flux:select.option>
                        <flux:select.option value="QRIS">📱 QRIS / E-Wallet</flux:select.option>
                        <flux:select.option value="DANA">DANA</flux:select.option>
                    </flux:select>

                    <flux:select label="Payment Status" wire:model="form.payment_status" placeholder="Select status...">
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
                <flux:button variant="primary" color="primary" type="submit">Create</flux:button>
            </div>
        </form>
    </flux:modal>
</div>