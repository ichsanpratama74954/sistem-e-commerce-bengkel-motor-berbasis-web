<?php

use Livewire\Component;
use App\Livewire\Forms\OrdersForm;

new class extends Component
{
    public OrdersForm $form;

    public function save()
    {
        $this->form->store();
        Flux::modal('create-order')->close();
        
        session()->flash('success', 'Order created successfully');

        $this->redirectRoute('order.index', navigate: true);
    } 

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }
};
?>

<div>
    <flux:modal name="create-order" class="md:w-150" x-on:close="$wire.resetForm()"> 
        <form class="space-y-8" wire:submit.prevent="save">
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Create Order
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    Add a new order to your system
                </flux:text>
            </div>

            <div class="space-y-6">
                <flux:input
                    label="User ID"
                    type="number"
                    placeholder="Enter user id"
                    wire:model="form.user_id"
                />

                <flux:input
                    label="Total Amount"
                    type="number"
                    step="0.01"
                    placeholder="Enter total amount"
                    wire:model="form.total_amount"
                />

                <flux:select label="Status" wire:model="form.status">
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="approved">Selesai</flux:select.option>
                    <flux:select.option value="rejected">Batal</flux:select.option>
                </flux:select>
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