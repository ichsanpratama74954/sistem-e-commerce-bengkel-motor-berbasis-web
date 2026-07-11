<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Payment;

new class extends Component {
    use WithPagination;

    // Properti Filter & Cari
    public $search = '';
    public $filterStatus = '';
    public $filterMethod = '';

    // Properti Sorting
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    public function sort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterMethod()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    #[Computed]
    public function stats()
    {
        return [
            'total_revenue' => Payment::where('payment_status', 'Success')->sum('amount'),
            'success_count' => Payment::where('payment_status', 'Success')->count(),
            'pending_count' => Payment::where('payment_status', 'Pending')->count(),
        ];
    }

    #[Computed]
    public function payments()
    {
        return Payment::query()
            ->when($this->search, function ($query) {
                $query->where('id', 'like', "%{$this->search}%")
                      ->orWhere('paymentable_id', 'like', "%{$this->search}%")
                      ->orWhere('paymentable_type', 'like', "%{$this->search}%")
                      ->orWhere('amount', 'like', "%{$this->search}%")
                      ->orWhere('payment_method', 'like', "%{$this->search}%"); 
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('payment_status', $this->filterStatus);
            })
            ->when($this->filterMethod, function ($query) {
                $query->where('payment_method', $this->filterMethod); 
            })
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(5);
    }

    public function edit($id){
        $this->dispatch('edit-payment', id: $id);
    }
};?>

<div class="max-w-7xl mx-auto space-y-6 container pb-10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="h-6 w-1.5 bg-indigo-600 rounded-full hidden md:block"></span>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white font-extrabold tracking-tight">Financial Payments</flux:heading>
            </div>
            <flux:subheading size="lg" class="text-zinc-500 dark:text-zinc-400 mt-1 pl-0 md:pl-3.5">Monitor, verify, and trace all internal business revenues.</flux:subheading>
        </div>
        <div>
            <flux:modal.trigger name="create-payment">
                <flux:button variant="primary" icon="plus" class="shadow-md shadow-indigo-500/10 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold w-full md:w-auto border-none">Add New Payment</flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- BAGIAN CARDS STATISTIK --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="p-6 bg-gradient-to-br from-white to-emerald-50/40 dark:from-zinc-950 dark:to-emerald-950/10 border border-emerald-100 dark:border-emerald-900/30 rounded-xl shadow-sm shadow-emerald-500/5 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500"></div>
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-600/80 dark:text-emerald-400">Total Revenue</p>
                    <h3 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">Rp {{ number_format($this->stats['total_revenue'], 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-emerald-500 text-white dark:bg-emerald-500/20 dark:text-emerald-400 rounded-xl shadow-sm shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                    <flux:icon.banknotes class="w-6 h-6" />
                </div>
            </div>
        </div>

        <div class="p-6 bg-gradient-to-br from-white to-blue-50/40 dark:from-zinc-950 dark:to-blue-950/10 border border-blue-100 dark:border-blue-900/30 rounded-xl shadow-sm shadow-blue-500/5 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-500"></div>
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-bold uppercase tracking-wider text-blue-600/80 dark:text-blue-400">Successful Payments</p>
                    <h3 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">{{ $this->stats['success_count'] }} <span class="text-xs font-medium text-zinc-400 uppercase">Tx</span></h3>
                </div>
                <div class="p-3 bg-blue-500 text-white dark:bg-blue-500/20 dark:text-blue-400 rounded-xl shadow-sm shadow-blue-500/20 group-hover:scale-105 transition-transform">
                    <flux:icon.check-circle class="w-6 h-6" />
                </div>
            </div>
        </div>

        <div class="p-6 bg-gradient-to-br from-white to-amber-50/40 dark:from-zinc-950 dark:to-amber-950/10 border border-amber-100 dark:border-amber-900/30 rounded-xl shadow-sm shadow-amber-500/5 relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500"></div>
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-600/80 dark:text-amber-400">Pending Actions</p>
                    <h3 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">{{ $this->stats['pending_count'] }} <span class="text-xs font-medium text-zinc-400 uppercase">Tx</span></h3>
                </div>
                <div class="p-3 bg-amber-500 text-white dark:bg-amber-500/20 dark:text-amber-400 rounded-xl shadow-sm shadow-amber-500/20 group-hover:scale-105 transition-transform">
                    <flux:icon.clock class="w-6 h-6" />
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="flex flex-col sm:flex-row gap-3 items-center justify-between p-4 bg-gradient-to-r from-zinc-50 to-zinc-100/50 dark:from-zinc-900/40 dark:to-zinc-900/20 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-inner">
        <div class="w-full sm:max-w-xs">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Search ID, Amount, Method..." class="bg-white dark:bg-zinc-950 focus:border-indigo-500 focus:ring-indigo-500" />
        </div>
        <div class="w-full sm:w-auto flex gap-2 justify-end">
            {{-- METODE PEMBAYARAN BARU 💳 (Sudah Diperbaiki Tutup Tag-nya) --}}
            <flux:select wire:model.live="filterMethod" placeholder="Filter Method" class="w-full sm:w-44 bg-white dark:bg-zinc-950">
                <flux:select.option value="">All Methods</flux:select.option>
                <flux:select.option value="Cash">💵 Cash</flux:select.option>
                <flux:select.option value="Transfer">🏦 Transfer</flux:select.option>
                <flux:select.option value="QRIS">📱 QRIS</flux:select.option>
                <flux:select.option value="DANA">📱 DANA</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="filterStatus" placeholder="Filter Status" class="w-full sm:w-40 bg-white dark:bg-zinc-950">
                <flux:select.option value="">All Statuses</flux:select.option>
                <flux:select.option value="Success">Success</flux:select.option>
                <flux:select.option value="Pending">Pending</flux:select.option>
                <flux:select.option value="Failed">Failed</flux:select.option>
            </flux:select>
        </div>
    </div>

    {{-- MODAL CRUD & ALERTS --}}
    <livewire:payment.create />
    <livewire:payment.edit />
    <x-flash-message />

    {{-- STRUKTUR UTAMA TABEL --}}
    @if($this->payments->isEmpty())
        <div class="flex flex-col items-center justify-center p-16 text-center bg-white dark:bg-zinc-950 border border-dashed border-zinc-300 dark:border-zinc-800 rounded-2xl shadow-sm">
            <div class="p-5 bg-indigo-50 dark:bg-indigo-950/40 rounded-full text-indigo-600 dark:text-indigo-400 mb-4 ring-8 ring-indigo-50/50 dark:ring-indigo-950/20 animate-bounce-slow">
                <flux:icon.credit-card class="w-10 h-10" />
            </div>
            <h3 class="text-xl font-bold text-zinc-900 dark:text-white">No Transactions Found</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 max-w-sm mt-2">There are no records matching your criteria. Create a transaction to start reporting.</p>
            <div class="mt-6">
                <flux:modal.trigger name="create-payment">
                    <flux:button variant="outline" size="sm" icon="plus" class="border-indigo-200 dark:border-indigo-900 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/30">Add Your First Record</flux:button>
                </flux:modal.trigger>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
           <flux:table :paginate="$this->payments">
                <flux:table.columns class="bg-zinc-50/80 dark:bg-zinc-900/50 border-b border-zinc-200 dark:border-zinc-800">
                    <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">Transaction ID</flux:table.column>
                    <flux:table.column>Source Origin</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'amount'" :direction="$sortDirection" wire:click="sort('amount')">Amount</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'payment_method'" :direction="$sortDirection" wire:click="sort('payment_method')">Method</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'payment_status'" :direction="$sortDirection" wire:click="sort('payment_status')">Status</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">Timestamp</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->payments as $payment)
                        {{-- Diperbaiki penutupan tag baris tabel di bagian paling bawah loop --}}
                        <flux:table.row :key="$payment->id" class="hover:bg-indigo-50/20 dark:hover:bg-indigo-950/10 transition-colors duration-150">
                            <flux:table.cell class="font-mono text-xs font-bold text-indigo-600/80 dark:text-indigo-400">TX-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $isBooking = $payment->paymentable_type === 'App\Models\Booking';
                                    $label = $isBooking ? 'Booking' : 'Order';
                                    $color = $isBooking ? 'from-blue-400 to-blue-600 shadow-blue-500/50' : 'from-purple-400 to-purple-600 shadow-purple-500/50';
                                @endphp
                                @if($payment->paymentable_type && $payment->paymentable_id)
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-br {{ $color }} shadow-sm"></span>
                                        <span class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ $label }} #{{ $payment->paymentable_id }}</span>
                                    </div>
                                @else
                                    <span class="text-zinc-400 italic text-xs">Manual Injection</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="font-extrabold text-zinc-900 dark:text-zinc-100">
                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                            </flux:table.cell>

                            {{-- PAYMENT METHOD 💳 --}}
                            <flux:table.cell>
                                @if(($payment->payment_method ?? 'Cash') === 'Cash')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-zinc-100 text-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 border border-zinc-200/40 dark:border-zinc-800/40 shadow-sm">
                                        💵 Cash
                                    </span>
                                @elseif($payment->payment_method === 'Transfer')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400 border border-blue-200/40 dark:border-blue-900/40 shadow-sm">
                                        🏦 Transfer
                                    </span>
                                @elseif($payment->payment_method === 'QRIS')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/30 dark:text-purple-400 border border-purple-200/40 dark:border-purple-900/40 shadow-sm">
                                        📱 QRIS
                                    </span>
                                @elseif($payment->payment_method === 'DANA')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/30 dark:text-sky-400 border border-sky-200/40 dark:border-sky-900/40 shadow-sm">
                                        📱 DANA
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-zinc-100 text-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 border border-zinc-200/40 dark:border-zinc-800/40 shadow-sm">
                                        💵 Cash
                                    </span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                @if($payment->payment_status === 'Success')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/40 dark:border-emerald-900/40 shadow-sm shadow-emerald-500/5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-sm"></span> Success
                                    </span>
                                @elseif($payment->payment_status === 'Pending')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200/40 dark:border-amber-900/40 shadow-sm shadow-amber-500/5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 border border-rose-200/40 dark:border-rose-900/40 shadow-sm shadow-rose-500/5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Failed
                                    </span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="text-sm font-medium text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                {{ $payment->created_at->translatedFormat('d M Y, h:i A') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" class="hover:bg-zinc-100 dark:hover:bg-zinc-800"></flux:button>
                                    <flux:menu>
                                        <flux:menu.item icon="pencil" wire:click="edit({{ $payment->id }})">Modify Record</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete', {id: {{ $payment->id }}})">Purge Data</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row> {{-- 👈 DIBERSIHKAN: Sebelumnya tertulis </flux:row> --}}
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    @endif
</div>

<style>
    @keyframes bounceSlow {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }
    .animate-bounce-slow {
        animation: bounceSlow 3s ease-in-out infinite;
    }
</style>