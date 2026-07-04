<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\User;

new class extends Component
{
    use WithPagination;

    public $user_id;
    public $total_amount;
    public $status = 'pending';

    public $editingOrderId;
    public $edit_user_id;
    public $edit_total_amount;
    public $edit_status;

    protected $listeners = [
        'edit-order' => 'loadOrder',
        'confirm-delete' => 'deleteOrder'
    ];

    #[Computed]
    public function orders()
    {
        return Order::with('user')->latest()->paginate(10);
    }

    #[Computed]
    public function users()
    {
        return User::all();
    }

    public function save()
    {
        $this->validate([
            'user_id' => 'required',
            'total_amount' => 'required|numeric',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        Order::create([
            'user_id' => $this->user_id,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
        ]);

        Flux::modal('create-order')->close();
        
        $this->reset(['user_id', 'total_amount', 'status']);

        session()->flash('success', 'Order created successfully');
        $this->redirectRoute('order.index', navigate: true);
    }

    public function loadOrder($id)
    {
        $order = Order::findOrFail($id);
        $this->editingOrderId = $order->id;
        $this->edit_user_id = $order->user_id;
        $this->edit_total_amount = $order->total_amount;
        $this->edit_status = $order->status;

        Flux::modal('edit-order-modal')->show();
    }

    public function update()
    {
        $this->validate([
            'edit_user_id' => 'required',
            'edit_total_amount' => 'required|numeric',
            'edit_status' => 'required|in:pending,approved,rejected',
        ]);

        $order = Order::findOrFail($this->editingOrderId);
        $order->update([
            'user_id' => $this->edit_user_id,
            'total_amount' => $this->edit_total_amount,
            'status' => $this->edit_status,
        ]);

        Flux::modal('edit-order-modal')->close();

        session()->flash('success', 'Order updated successfully');
        $this->redirectRoute('order.index', navigate: true);
    }

    public function deleteOrder($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        session()->flash('success', 'Order deleted successfully');
        $this->redirectRoute('order.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->reset(['user_id', 'total_amount', 'status', 'editingOrderId', 'edit_user_id', 'edit_total_amount', 'edit_status']);
    }
};
?>

<div class="max-w-7xl mx-auto space-y-4">
    <flux:heading size="xl" class="text-zinc-800 dark:text-white">Orders</flux:heading>
    <flux:subheading size="lg" class="text-zinc-600 dark:text-zinc-400">Manage your orders</flux:subheading>
    <flux:separator variant="subtle" />
    
    <flux:modal.trigger name="create-order">
        <flux:button variant="primary" icon="plus" color="primary">Add Order</flux:button>
    </flux:modal.trigger>

    <x-flash-message />

    <flux:modal name="create-order" class="md:w-150" x-on:close="$wire.resetForm()"> 
        <form class="space-y-8" wire:submit.prevent="save">
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Create Order</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">Add a new order to your system</flux:text>
            </div>

            <div class="space-y-6">
                <flux:select label="Choose User" wire:model.live="user_id" placeholder="Select a user...">
                    @foreach ($this->users as $user)
                        <flux:select.option value="{{ $user->id }}">{{ $user->name }} (ID: {{ $user->id }})</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input label="Total Amount" type="number" step="0.01" placeholder="Enter total amount" wire:model="total_amount" />
                
                <flux:select label="Status" wire:model="status">
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

    <flux:modal name="edit-order-modal" class="md:w-150" x-on:close="$wire.resetForm()"> 
        <form class="space-y-8" wire:submit.prevent="update">
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Edit Order</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">Update order details</flux:text>
            </div>

            <div class="space-y-6">
                <flux:select label="Choose User" wire:model.live="edit_user_id" placeholder="Select a user...">
                    @foreach ($this->users as $user)
                        <flux:select.option value="{{ $user->id }}">{{ $user->name }} (ID: {{ $user->id }})</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input label="Total Amount" type="number" step="0.01" placeholder="Enter total amount" wire:model="edit_total_amount" />
                
                <flux:select label="Status" wire:model="edit_status">
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="approved">Selesai</flux:select.option>
                    <flux:select.option value="rejected">Batal</flux:select.option>
                </flux:select>
            </div>
    
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="primary" type="submit">Update</flux:button>
            </div>
        </form>
    </flux:modal>

    <div class="overflow-x-auto">
       <flux:table :paginate="$this->orders->hasPages()" :pagination="$this->orders" class="w-full">
            <flux:table.columns>
                <flux:table.column>No</flux:table.column>
                <flux:table.column>User ID</flux:table.column>
                <flux:table.column>Total Amount</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Created At</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->orders as $order)
                    <flux:table.row :key="$order->id">
                        <flux:table.cell class="font-medium">{{ $loop->iteration }}</flux:table.cell>
                        <flux:table.cell class="font-medium">{{ $order->user_id ?? '-' }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $order->total_amount ?? '-' }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $order->status ?? '-' }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $order->created_at?->diffForHumans() ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="$dispatch('edit-order', { id: {{ $order->id }} })">Edit</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete', {id: {{ $order->id }}})">Delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>