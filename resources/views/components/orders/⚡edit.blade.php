<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Order;
use App\Livewire\Forms\OrdersForm;

new class extends Component
{
    public OrdersForm $form;

    #[On('edit-order')]
    public function loadOrder($id)
    {
        $order = Order::find($id);
        $this->form->setOrders($order);
        Flux::modal('edit-order')->show();
    }

    public function updateOrder()
    {
        $this->form->update();
        Flux::modal('edit-order')->close();
        session()->flash('success', 'Order updated successfully');
        return $this->redirectRoute('order.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }

    #[On('confirm-delete')]
    public function confirmDelete($id)
    {
        $order = Order::find($id);
        $this->form->setOrders($order);
        Flux::modal('delete-order')->show();
    }

    public function deleteOrder() {
        if ($this->form->order && $this->form->order->exists) {
            $this->form->order->delete();
        }
        Flux::modal('delete-order')->close();
        session()->flash('success', 'Order deleted successfully');
        $this->redirectRoute('order.index', navigate: true);
    }
};
?>

<div>
    <flux:modal 
        name="edit-order" 
        class="md:w-150" 
        x-on:close="$wire.resetForm()" 
    >
        <form class="space-y-8" wire:submit.prevent="updateOrder">
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Edit Order
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    Edit your order details below
                </flux:text>
            </div>

            <div class="space-y-6">
                <flux:input
                    label="User ID"
                    type="number"
                    placeholder="Enter user id"
                    wire:model="form.user_id"
                    wire:dirty.class="text-red-500"
                />

                <flux:input
                    label="Total Amount"
                    type="number"
                    step="0.01"
                    placeholder="Enter total amount"
                    wire:model="form.total_amount"
                    wire:dirty.class="text-red-500"
                />

                <flux:select label="Status" wire:model="form.status">
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="approved">Approved</flux:select.option>
                    <flux:select.option value="rejected">Rejected</flux:select.option>
                </flux:select>
            </div>

            <div wire:show="$dirty" class="text-red-500 dark:text-red-400">
                you have unsaved changes
            </div>
    
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="primary" type="submit">Update</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal 
        name="delete-order" 
        class="md:w-150" 
        x-on:close="$wire.resetForm()" 
    >
        <form class="space-y-8" wire:submit.prevent="deleteOrder">
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Delete Order
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    this action cannot be undone
                </flux:text>
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