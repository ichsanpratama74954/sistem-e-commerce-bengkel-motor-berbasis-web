<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\User;
use App\Models\Sparepart;

new class extends Component
{
    use WithPagination;

    public $user_id;
    public $total_amount = 0;
    public $status = 'pending';

    public $editingOrderId;
    public $edit_user_id;
    public $edit_total_amount = 0;
    public $edit_status;

    // Create sparepart items
    public $items = [];
    public $new_sparepart_id = '';
    public $new_sparepart_qty = 1;

    // Edit sparepart items
    public $edit_items = [];
    public $edit_new_sparepart_id = '';
    public $edit_new_sparepart_qty = 1;

    #[Computed]
    public function orders()
    {
        return Order::with('user')->withCount('orderDetails')->latest()->paginate(10);
    }

    #[Computed]
    public function users()
    {
        return User::all();
    }

    #[Computed]
    public function spareparts()
    {
        return Sparepart::select('id', 'part_name', 'price', 'stock')->get();
    }

    public function addSparepart()
    {
        $this->validate([
            'new_sparepart_id' => 'required|exists:spareparts,id',
            'new_sparepart_qty' => 'required|integer|min:1',
        ]);
        $this->items[] = [
            'id' => (int) $this->new_sparepart_id,
            'quantity' => (int) $this->new_sparepart_qty,
        ];
        $this->new_sparepart_id = '';
        $this->new_sparepart_qty = 1;
        $this->recalcTotal();
    }

    public function removeSparepart($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->recalcTotal();
    }

    public function recalcTotal()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $sp = Sparepart::find($item['id']);
            if ($sp) {
                $total += $sp->price * $item['quantity'];
            }
        }
        $this->total_amount = $total;
    }

    public function addEditSparepart()
    {
        $this->validate([
            'edit_new_sparepart_id' => 'required|exists:spareparts,id',
            'edit_new_sparepart_qty' => 'required|integer|min:1',
        ]);
        $this->edit_items[] = [
            'id' => (int) $this->edit_new_sparepart_id,
            'quantity' => (int) $this->edit_new_sparepart_qty,
        ];
        $this->edit_new_sparepart_id = '';
        $this->edit_new_sparepart_qty = 1;
        $this->recalcEditTotal();
    }

    public function removeEditSparepart($index)
    {
        unset($this->edit_items[$index]);
        $this->edit_items = array_values($this->edit_items);
        $this->recalcEditTotal();
    }

    public function recalcEditTotal()
    {
        $total = 0;
        foreach ($this->edit_items as $item) {
            $sp = Sparepart::find($item['id']);
            if ($sp) {
                $total += $sp->price * $item['quantity'];
            }
        }
        $this->edit_total_amount = $total;
    }

    public function save()
    {
        $this->validate([
            'user_id' => 'required',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $order = Order::create([
            'user_id' => $this->user_id,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
        ]);

        foreach ($this->items as $item) {
            $sp = Sparepart::find($item['id']);
            if ($sp) {
                $order->orderDetails()->create([
                    'sparepart_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $sp->price,
                    'subtotal' => $sp->price * $item['quantity'],
                ]);
            }
        }

        Flux::modal('create-order')->close();
        $this->reset(['user_id', 'total_amount', 'status', 'items']);

        session()->flash('success', 'Order created successfully');
        $this->redirectRoute('order.index', navigate: true);
    }

    #[On('edit-order')]
    public function loadOrder($id)
    {
        $order = Order::with('orderDetails')->findOrFail($id);
        $this->editingOrderId = $order->id;
        $this->edit_user_id = $order->user_id;
        $this->edit_total_amount = $order->total_amount;
        $this->edit_status = $order->status;

        $this->edit_items = $order->orderDetails
            ->map(fn ($d) => ['id' => $d->sparepart_id, 'quantity' => $d->quantity])
            ->toArray();

        Flux::modal('edit-order-modal')->show();
    }

    public function update()
    {
        $this->validate([
            'edit_user_id' => 'required',
            'edit_total_amount' => 'required|numeric|min:0',
            'edit_status' => 'required|in:pending,approved,rejected',
        ]);

        $order = Order::findOrFail($this->editingOrderId);
        $order->update([
            'user_id' => $this->edit_user_id,
            'total_amount' => $this->edit_total_amount,
            'status' => $this->edit_status,
        ]);

        $order->orderDetails()->delete();
        foreach ($this->edit_items as $item) {
            $sp = Sparepart::find($item['id']);
            if ($sp) {
                $order->orderDetails()->create([
                    'sparepart_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $sp->price,
                    'subtotal' => $sp->price * $item['quantity'],
                ]);
            }
        }

        Flux::modal('edit-order-modal')->close();
        $this->reset(['editingOrderId', 'edit_user_id', 'edit_total_amount', 'edit_status', 'edit_items']);

        session()->flash('success', 'Order updated successfully');
        $this->redirectRoute('order.index', navigate: true);
    }

    #[On('confirm-delete')]
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
        $this->reset([
            'user_id', 'total_amount', 'status',
            'editingOrderId', 'edit_user_id', 'edit_total_amount', 'edit_status',
            'items', 'edit_items',
            'new_sparepart_id', 'new_sparepart_qty',
            'edit_new_sparepart_id', 'edit_new_sparepart_qty',
        ]);
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
    <livewire:payment.create />

    <flux:modal name="create-order" class="md:w-150" x-on:close="$wire.resetForm()"> 
        <form class="space-y-8" wire:submit.prevent="save">
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Create Order</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">Add a new order to your system</flux:text>
            </div>

            <div class="space-y-6">
                <flux:select label="Choose User" wire:model.live="user_id">
                    <flux:select.option value="">Select User</flux:select.option>
                    @foreach ($this->users as $user)
                        <flux:select.option value="{{ $user->id }}">{{ $user->name }} (ID: {{ $user->id }})</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:separator variant="subtle" />

                <flux:heading size="sm">Spareparts</flux:heading>
                <div class="flex items-end gap-2">
                    <flux:select class="flex-1" wire:model="new_sparepart_id">
                        <flux:select.option value="">Select Sparepart</flux:select.option>
                        @foreach ($this->spareparts as $sp)
                            <flux:select.option value="{{ $sp->id }}">{{ $sp->part_name }} (Rp {{ number_format($sp->price, 0, ',', '.') }}, stok: {{ $sp->stock }})</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input type="number" class="w-20" min="1" value="1" wire:model="new_sparepart_qty" />
                    <flux:button type="button" variant="primary" size="sm" wire:click="addSparepart">Add</flux:button>
                </div>
                @if(count($items) > 0)
                    <div class="space-y-1">
                        @foreach($items as $i => $item)
                            @php $sp = $this->spareparts->firstWhere('id', $item['id']); @endphp
                            <div class="flex items-center justify-between bg-zinc-50 dark:bg-zinc-800 px-3 py-2 rounded-lg text-sm">
                                <span>{{ $sp?->part_name ?? 'Unknown' }} x{{ $item['quantity'] }} = Rp {{ number_format(($sp?->price ?? 0) * $item['quantity'], 0, ',', '.') }}</span>
                                <button type="button" wire:click="removeSparepart({{ $i }})" class="text-red-500 hover:text-red-700">&times;</button>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="text-right text-sm font-medium">Total: Rp {{ number_format($total_amount, 0, ',', '.') }}</div>

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
                <flux:select label="Choose User" wire:model.live="edit_user_id">
                    <flux:select.option value="">Select User</flux:select.option>
                    @foreach ($this->users as $user)
                        <flux:select.option value="{{ $user->id }}">{{ $user->name }} (ID: {{ $user->id }})</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:separator variant="subtle" />

                <flux:heading size="sm">Spareparts</flux:heading>
                <div class="flex items-end gap-2">
                    <flux:select class="flex-1" wire:model="edit_new_sparepart_id">
                        <flux:select.option value="">Select Sparepart</flux:select.option>
                        @foreach ($this->spareparts as $sp)
                            <flux:select.option value="{{ $sp->id }}">{{ $sp->part_name }} (Rp {{ number_format($sp->price, 0, ',', '.') }}, stok: {{ $sp->stock }})</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input type="number" class="w-20" min="1" value="1" wire:model="edit_new_sparepart_qty" />
                    <flux:button type="button" variant="primary" size="sm" wire:click="addEditSparepart">Add</flux:button>
                </div>
                @if(count($edit_items) > 0)
                    <div class="space-y-1">
                        @foreach($edit_items as $i => $item)
                            @php $sp = $this->spareparts->firstWhere('id', $item['id']); @endphp
                            <div class="flex items-center justify-between bg-zinc-50 dark:bg-zinc-800 px-3 py-2 rounded-lg text-sm">
                                <span>{{ $sp?->part_name ?? 'Unknown' }} x{{ $item['quantity'] }} = Rp {{ number_format(($sp?->price ?? 0) * $item['quantity'], 0, ',', '.') }}</span>
                                <button type="button" wire:click="removeEditSparepart({{ $i }})" class="text-red-500 hover:text-red-700">&times;</button>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="text-right text-sm font-medium">Total: Rp {{ number_format($edit_total_amount, 0, ',', '.') }}</div>
                
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
                <flux:table.column>User</flux:table.column>
                <flux:table.column>Items</flux:table.column>
                <flux:table.column>Total Amount</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Created At</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->orders as $order)
                    <flux:table.row :key="$order->id">
                        <flux:table.cell class="font-medium">{{ $loop->iteration }}</flux:table.cell>
                        <flux:table.cell class="font-medium">{{ $order->user->name ?? 'ID: ' . $order->user_id }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $order->order_details_count }} item(s)</flux:table.cell>
                        <flux:table.cell class="font-medium">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</flux:table.cell>
                        <flux:table.cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                {{ ucfirst($order->status) }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $order->created_at?->diffForHumans() ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="$dispatch('edit-order', { id: {{ $order->id }} })">Edit</flux:menu.item>
                                    <flux:menu.item icon="credit-card" wire:click="$dispatch('pay-order', { id: {{ $order->id }} })">Bayar</flux:menu.item>
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