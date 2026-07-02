<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    public $created_at;
    public $total_amount;
    public $status = '';
    public $editingOrderId;

    public function sort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->where('user_id', Auth::id())
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }

    public function saveOrder()
    {
        $this->validate([
            'created_at' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,selesai,batal',
        ]);

        if ($this->editingOrderId) {
            $order = Order::findOrFail($this->editingOrderId);
            $order->update([
                'created_at' => $this->created_at,
                'total_amount' => $this->total_amount,
                'status' => $this->status,
            ]);
            session()->flash('success', 'Order updated successfully');
        } else {
            Order::create([
                'user_id' => Auth::id(),
                'created_at' => $this->created_at,
                'total_amount' => $this->total_amount,
                'status' => $this->status,
            ]);
            session()->flash('success', 'Order created successfully');
        }

        $this->reset(['created_at', 'total_amount', 'status', 'editingOrderId']);
    }

    public function edit($id)
    {
        $order = Order::findOrFail($id);
        $this->editingOrderId = $order->id;
        $this->created_at = $order->created_at->format('Y-m-d\TH:i');
        $this->total_amount = $order->total_amount;
        $this->status = $order->status;
    }

    public function cancelEdit()
    {
        $this->reset(['created_at', 'total_amount', 'status', 'editingOrderId']);
    }

    public function delete($id)
    {
        Order::findOrFail($id)->delete();
        session()->flash('success', 'Order deleted successfully');
    }
};?>

<div class="max-w-7xl mx-auto space-y-6">
    <x-flash-message />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <div class="lg:col-span-3 bg-white dark:bg-zinc-900 p-5 rounded-xl shadow-md border-t-4 border-t-emerald-600 border-x border-b border-zinc-200 dark:border-zinc-800">
            <div class="mb-5">
                <flux:heading size="lg" level="2" class="text-emerald-950 dark:text-emerald-400 font-black tracking-wide">
                    {{ $editingOrderId ? 'Edit Order' : 'Add Order' }}
                </flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400 mt-1 text-xs">
                    {{ $editingOrderId ? 'Ubah data riwayat booking secara langsung' : 'p booking' }}
                </flux:subheading>
            </div>

            <form wire:submit.prevent="saveOrder" class="space-y-4">
                <flux:input
                    type="datetime-local"
                    label="Tanggal Booking"
                    wire:model="created_at"
                />

                <flux:input
                    type="number"
                    label="Total Biaya (Rp)"
                    placeholder="Masukkan angka saja"
                    wire:model="total_amount"
                />

                <flux:select label="Status Orderan" wire:model="status">
                    <option value="">Pilih Status</option>
                    <option value="pending">Pending</option>
                    <option value="selesai">Selesai</option>
                    <option value="batal">Batal</option>
                </flux:select>

                <div class="space-y-2">
                    <flux:button variant="primary" color="emerald" type="submit" class="w-full font-bold tracking-wide shadow-sm py-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 mr-1 inline">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        {{ $editingOrderId ? 'Update Order' : 'Save Order' }}
                    </flux:button>

                    @if($editingOrderId)
                        <flux:button variant="ghost" wire:click="cancelEdit" class="w-full text-xs">
                            Cancel Edit
                        </flux:button>
                    @endif
                </div>
            </form>
        </div>

        <div class="lg:col-span-9 bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-md border-t-4 border-t-emerald-600 border-x border-b border-zinc-200 dark:border-zinc-800">
            
            <div class="mb-6">
                <flux:heading size="xl" level="1" class="text-emerald-950 dark:text-emerald-400 font-black tracking-wide">Riwayat Booking & Orders</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400 mt-1">Kelola seluruh daftar layanan dan urutkan tarif harga bengkel</flux:subheading>
            </div>

            <div class="overflow-x-auto mt-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <flux:table :paginate="$this->orders" class="w-full border-collapse">
                    
                    <flux:table.columns class="bg-emerald-600 dark:bg-emerald-900 border-b border-emerald-700">
                        <flux:table.column class="text-white font-bold text-sm bg-emerald-600 dark:bg-emerald-900 text-left w-16 ps-5 py-3.5">No</flux:table.column>
                        
                        <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')" class="text-white font-bold text-sm bg-emerald-600 dark:bg-emerald-900 hover:bg-emerald-700 cursor-pointer transition-colors py-3.5 min-w-[160px] pr-8">
                            Tanggal Booking
                        </flux:table.column>
                        
                        <flux:table.column sortable :sorted="$sortBy === 'total_amount'" :direction="$sortDirection" wire:click="sort('total_amount')" class="text-white font-bold text-sm bg-emerald-600 dark:bg-emerald-900 hover:bg-emerald-700 cursor-pointer transition-colors py-3.5">
                            Total Biaya
                        </flux:table.column>
                        
                        <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection" wire:click="sort('status')" class="text-white font-bold text-sm bg-emerald-600 dark:bg-emerald-900 hover:bg-emerald-700 cursor-pointer transition-colors py-3.5">
                            Status
                        </flux:table.column>
                        
                        <flux:table.column class="text-white font-bold text-sm bg-emerald-600 dark:bg-emerald-900 py-3.5">action</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->orders as $order)
                            <flux:table.row :key="$order->id" class="odd:bg-white even:bg-emerald-50/20 dark:odd:bg-zinc-900 dark:even:bg-zinc-800/40 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-colors duration-150">
                                
                                <flux:table.cell class="text-zinc-500 dark:text-zinc-400 font-bold text-left w-16 ps-5 py-4.5">
                                    {{ $this->orders->firstItem() + $loop->index }}
                                </flux:table.cell>
                                
                                <flux:table.cell class="font-bold text-emerald-950 dark:text-emerald-200 py-4.5 pr-8 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                        <span>{{ $order->created_at->format('d M Y H:i') }}</span>
                                    </div>
                                </flux:table.cell>
                                
                                <flux:table.cell class="font-black text-emerald-600 dark:text-emerald-400 text-sm tracking-wide py-4.5">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </flux:table.cell>
                                
                                <flux:table.cell class="py-4.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                        {{ $order->status == 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200 dark:border-amber-900' : '' }}
                                        {{ $order->status == 'selesai' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900' : '' }}
                                        {{ $order->status == 'batal' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200 dark:border-rose-900' : '' }}
                                    ">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </flux:table.cell>
                                
                                <flux:table.cell class="py-4.5">
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" class="hover:bg-emerald-100 dark:hover:bg-zinc-700 text-emerald-600 dark:text-emerald-400" />
                                        <flux:menu>
                                            <flux:menu.item icon="pencil" wire:click="edit({{ $order->id }})">Edit</flux:menu.item>
                                            <flux:menu.separator />
                                            <flux:menu.item variant="danger" icon="trash" wire:click="delete({{ $order->id }})">Delete</flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>
    </div>
</div>