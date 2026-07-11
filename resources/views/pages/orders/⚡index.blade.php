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

    // Properti filter pencarian & kategori
    public $search = '';
    public $filter_status = '';
    public $filter_customer = '';

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

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }

    #[Computed]
    public function orders()
    {
        return Order::with('user')->withCount('orderDetails')->latest()->paginate(10);
    }

    #[Computed]
    public function users() { return User::all(); }

    // Hitungan statistik otomatis & persentase dinamis dibanding bulan lalu
    #[Computed]
    public function stats()
    {
        $total      = Order::count();
        $completed  = Order::where('status', 'approved')->count();
        $processing = Order::where('status', 'pending')->count();
        $cancelled  = Order::where('status', 'rejected')->count();

        // Mengambil rentang tanggal bulan lalu untuk pembanding persentase otomatis
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd   = now()->subMonth()->endOfMonth();

        $totalLastMonth      = Order::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $completedLastMonth  = Order::where('status', 'approved')->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $processingLastMonth = Order::where('status', 'pending')->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $cancelledLastMonth  = Order::where('status', 'rejected')->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

        // Helper function menghitung persentase kenaikan/penurunan
        $calculatePercentage = function($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? ['val' => 100, 'up' => true] : ['val' => 0, 'up' => true];
            }
            $diff = $current - $previous;
            $pct = ($diff / $previous) * 100;
            return ['val' => round(abs($pct), 1), 'up' => $diff >= 0];
        };

        return [
            'total'          => $total,
            'completed'      => $completed,
            'processing'     => $processing,
            'cancelled'      => $cancelled,
            'total_pct'      => $calculatePercentage($total, $totalLastMonth),
            'completed_pct'  => $calculatePercentage($completed, $completedLastMonth),
            'processing_pct' => $calculatePercentage($processing, $processingLastMonth),
            'cancelled_pct'  => $calculatePercentage($cancelled, $cancelledLastMonth),
        ];
    }

    // Fungsi klik tombol mata untuk melihat detail order
    public function showOrder($id)
    {
        $this->viewingOrder = Order::with('user')->findOrFail($id);
        Flux::modal('view-order-modal')->show();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filter_status', 'filter_customer']);
        $this->resetPage();
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

<div class="max-w-7xl mx-auto space-y-6 p-4 font-sans text-zinc-950 dark:text-zinc-50">
    
    {{-- Breadcrumbs & Header Section --}}
    <div class="space-y-1">
        <div class="text-xs font-semibold text-zinc-400 flex items-center gap-1.5">
            <span>Dashboard</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3 text-zinc-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
            <span class="text-violet-600 dark:text-violet-400">Orders</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-1">
            <flux:heading size="2xl" class="font-black tracking-tight text-zinc-900 dark:text-white">Orders</flux:heading>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-80">
                    <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Search order ID, customer..." class="w-full" />
                </div>
                <flux:modal.trigger name="create-order">
                    <button class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-md shadow-violet-500/10 transition-all shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Order
                    </button>
                </flux:modal.trigger>
            </div>
        </div>
    </div>

    <x-flash-message />
    <livewire:payment.create />

    {{-- Main Layout Split Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        
        {{-- SISI KIRI: TABEL ORDERS UTAMA --}}
        <div class="lg:col-span-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto w-full">
                <flux:table :paginate="$this->orders" class="w-full min-w-[1020px]">
                    <flux:table.columns>
                        <flux:table.column class="w-36 font-bold text-xs uppercase text-zinc-400">Order ID</flux:table.column>
                        <flux:table.column class="w-56 font-bold text-xs uppercase text-zinc-400">Customer</flux:table.column>
                        <flux:table.column class="w-48 font-bold text-xs uppercase text-zinc-400">Motorcycle</flux:table.column>
                        <flux:table.column class="w-36 font-bold text-xs uppercase text-zinc-400">Total</flux:table.column>
                        <flux:table.column class="w-28 font-bold text-xs uppercase text-zinc-400">Status</flux:table.column>
                        <flux:table.column class="w-36 font-bold text-xs uppercase text-zinc-400">Order Date</flux:table.column>
                        <flux:table.column class="w-24 font-bold text-xs uppercase text-zinc-400 text-center">Actions</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($this->orders as $order)
                            @php
                                $words = explode(' ', $order->user->name ?? 'Customer');
                                $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                            @endphp
                            <flux:table.row :key="$order->id" class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/30 transition-colors">
                                
                                {{-- Order ID --}}
                                <flux:table.cell>
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-zinc-900 dark:text-zinc-100 text-xs tracking-tight">#ORD-2026-{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                </flux:table.cell>
                                
                                {{-- Customer Avatar --}}
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-950/50 text-violet-700 dark:text-violet-300 font-black text-xs flex items-center justify-center border border-violet-200/50 dark:border-violet-800/30 shrink-0">
                                            {{ $initials }}
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100 text-xs tracking-tight truncate">{{ $order->user->name ?? 'Unknown User' }}</span>
                                            <span class="text-[11px] text-zinc-400 dark:text-zinc-500 truncate mt-0.5">{{ $order->user->email ?? 'no-email@example.com' }}</span>
                                        </div>
                                    </div>
                                </flux:table.cell>

                                {{-- Motorcycle --}}
                                <flux:table.cell>
                                    <div class="flex items-center gap-2.5">
                                        <div class="p-1.5 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-100 dark:border-zinc-700/60 text-base shrink-0">🛵</div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-bold text-zinc-800 dark:text-zinc-200 text-xs tracking-tight truncate">Honda Vario 160</span>
                                            <span class="text-[10px] text-zinc-400 font-semibold mt-0.5">2024</span>
                                        </div>
                                    </div>
                                </flux:table.cell>

                                {{-- Total Amount --}}
                                <flux:table.cell class="font-extrabold text-zinc-900 dark:text-white text-xs">
                                    Rp {{ number_format($order->total_amount ?? 0, 0, ',', '.') }}
                                </flux:table.cell>

                                {{-- Status Badges --}}
                                <flux:table.cell>
                                    @if($order->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/60">
                                            Completed
                                        </span>
                                    @elseif($order->status === 'rejected')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/60">
                                            Cancelled
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900/60">
                                            Processing
                                        </span>
                                    @endif
                                </flux:table.cell>

                                {{-- Order Date --}}
                                <flux:table.cell>
                                    <div class="flex flex-col text-xs">
                                        <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $order->created_at ? $order->created_at->translatedFormat('d M Y') : '-' }}</span>
                                        <span class="text-[10px] text-zinc-400 mt-0.5">{{ $order->created_at ? $order->created_at->format('H:i \W\I\B') : '' }}</span>
                                    </div>
                                </flux:table.cell>

                                {{-- Actions --}}
                                <flux:table.cell>
                                    <div class="flex items-center justify-center gap-1">
                                        {{-- Tombol Fitur Mata (Sekarang Berfungsi) --}}
                                        <button type="button" wire:click="showOrder({{ $order->id }})" class="p-1 text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </button>
                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" inset="top bottom"></flux:button>
                                            <flux:menu>
                                                <flux:menu.item icon="pencil" wire:click="$dispatch('edit-order', { id: {{ $order->id }} })">Edit Order</flux:menu.item>
                                                <flux:menu.separator />
                                                <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete', {id: {{ $order->id }}})">Delete Order</flux:menu.item>
                                            </flux:menu>
                                        </flux:dropdown>
                                    </div>
                                </flux:table.cell>

                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="7" class="text-center py-10 text-zinc-400">
                                    No orders found matching the log matrix.
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        {{-- SISI KANAN: SIDEBAR WIDGETS STATISTIK & FILTER --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- Widget 1: Statistics Overview Panel (Persentase Otomatis & Dinamis) --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 space-y-4 shadow-sm">
                <h3 class="text-xs font-black text-zinc-800 dark:text-zinc-200 tracking-tight">Statistics Overview</h3>
                
                <div class="space-y-3.5">
                    {{-- Total Orders --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-violet-50 dark:bg-violet-950/40 text-violet-600 flex items-center justify-center text-sm">🛒</div>
                            <div class="flex flex-col">
                                <span class="text-xs text-zinc-400 font-semibold">Total Orders</span>
                                <span class="text-base font-black text-zinc-900 dark:text-white mt-0.5">{{ number_format($this->stats['total']) }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md {{ $this->stats['total_pct']['up'] ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/30' : 'text-rose-600 bg-rose-50 dark:bg-rose-950/30' }}">
                            {{ $this->stats['total_pct']['up'] ? '↑' : '↓' }} {{ $this->stats['total_pct']['val'] }}%
                        </span>
                    </div>

                    {{-- Completed --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center text-sm">🛍️</div>
                            <div class="flex flex-col">
                                <span class="text-xs text-zinc-400 font-semibold">Completed</span>
                                <span class="text-base font-black text-zinc-900 dark:text-white mt-0.5">{{ number_format($this->stats['completed']) }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md {{ $this->stats['completed_pct']['up'] ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/30' : 'text-rose-600 bg-rose-50 dark:bg-rose-950/30' }}">
                            {{ $this->stats['completed_pct']['up'] ? '↑' : '↓' }} {{ $this->stats['completed_pct']['val'] }}%
                        </span>
                    </div>

                    {{-- Processing --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center text-sm">⏳</div>
                            <div class="flex flex-col">
                                <span class="text-xs text-zinc-400 font-semibold">Processing</span>
                                <span class="text-base font-black text-zinc-900 dark:text-white mt-0.5">{{ number_format($this->stats['processing']) }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md {{ $this->stats['processing_pct']['up'] ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/30' : 'text-rose-600 bg-rose-50 dark:bg-rose-950/30' }}">
                            {{ $this->stats['processing_pct']['up'] ? '↑' : '↓' }} {{ $this->stats['processing_pct']['val'] }}%
                        </span>
                    </div>

                    {{-- Cancelled --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 flex items-center justify-center text-sm">❌</div>
                            <div class="flex flex-col">
                                <span class="text-xs text-zinc-400 font-semibold">Cancelled</span>
                                <span class="text-base font-black text-zinc-900 dark:text-white mt-0.5">{{ number_format($this->stats['cancelled']) }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md {{ $this->stats['cancelled_pct']['up'] ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/30' : 'text-rose-600 bg-rose-50 dark:bg-rose-950/30' }}">
                            {{ $this->stats['cancelled_pct']['up'] ? '↑' : '↓' }} {{ $this->stats['cancelled_pct']['val'] }}%
                        </span>
                    </div>
                </div>
            </div>

            {{-- Widget 2: Filters Dropdown Panel --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 space-y-4 shadow-sm">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-2.5">
                    <h3 class="text-xs font-black text-zinc-800 dark:text-zinc-200 tracking-tight">Filters</h3>
                    <button type="button" wire:click="resetFilters" class="text-xs font-bold text-violet-600 dark:text-violet-400 hover:underline">Reset</button>
                </div>
                
                <div class="space-y-4">
                    <flux:select label="Status" wire:model.live="filter_status" placeholder="Select status">
                        <flux:select.option value="pending">Processing</flux:select.option>
                        <flux:select.option value="approved">Completed</flux:select.option>
                        <flux:select.option value="rejected">Cancelled</flux:select.option>
                    </flux:select>

                    <flux:select label="Payment" placeholder="Select payment">
                        <flux:select.option value="cash">Cash / Tunai</flux:select.option>
                        <flux:select.option value="transfer">Bank Transfer</flux:select.option>
                    </flux:select>

                    <flux:select label="Customer" placeholder="Select customer">
                        @foreach ($this->users as $user)
                            <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL FORM: DETAIL VIEW --}}
    <flux:modal name="view-order-modal" class="md:w-130" x-on:close="$wire.resetForm()">
        @if($viewingOrder)
            <div class="space-y-6">
                <flux:select label="Choose User" wire:model.live="user_id">
                    <flux:select.option value="">Select User</flux:select.option>
                    @foreach ($this->users as $user)
                        <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
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
                    <flux:select.option value="pending">Processing</flux:select.option>
                    <flux:select.option value="approved">Completed</flux:select.option>
                    <flux:select.option value="rejected">Cancelled</flux:select.option>
                </flux:select>
            </div>
    
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit" class="bg-violet-600 hover:bg-violet-700 text-white font-bold">Create Order</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- MODAL FORM: EDIT --}}
    <flux:modal name="edit-order-modal" class="md:w-150" x-on:close="$wire.resetForm()"> 
        <form class="space-y-6" wire:submit.prevent="update">
            <div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white font-bold">Edit Order Records</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400 text-xs">Modify billing and registration status tags safely.</flux:text>
            </div>

            <div class="space-y-6">
                <flux:select label="Choose User" wire:model.live="edit_user_id">
                    <flux:select.option value="">Select User</flux:select.option>
                    @foreach ($this->users as $user)
                        <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
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
                    <flux:select.option value="pending">Processing</flux:select.option>
                    <flux:select.option value="approved">Completed</flux:select.option>
                    <flux:select.option value="rejected">Cancelled</flux:select.option>
                </flux:select>
            </div>
    
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit" class="bg-violet-600 hover:bg-violet-700 text-white font-bold">Update Changes</flux:button>
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