<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Motorcycle;

new class extends Component
{
    use WithPagination;

    #[Computed]
    public function motorcycles()
    {
        $query = Motorcycle::with('user')->latest();

        // REVISI: Jika pelanggan, hanya tampilkan motor milik sendiri
        if (auth()->user()->role === 'pelanggan') {
            $query->where('user_id', auth()->id());
        }

        return $query->paginate(10);
    }

    #[Computed]
    public function totalMotorcycles()
    {
        $query = Motorcycle::query();

        // REVISI: Hitung total unit berdasarkan role yang login
        if (auth()->user()->role === 'pelanggan') {
            $query->where('user_id', auth()->id());
        }

        return $query->count();
    }
};
?>

<div class="max-w-7xl mx-auto space-y-4 container pb-12 px-4">
    {{-- HEADER UTAMA --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-5">
        <div>
            <flux:heading size="xl" class="text-zinc-900 dark:text-white font-black tracking-tight">Motorcycles</flux:heading>
            <flux:subheading size="sm" class="text-zinc-500 dark:text-zinc-400 mt-1">Manage corporate vehicles, client ownership profiles, and license plates.</flux:subheading>
        </div>
        
        <flux:modal.trigger name="create-motorcycle">
            <flux:button variant="primary" icon="plus" size="sm" class="w-full sm:w-auto font-bold shadow-xs bg-emerald-600 hover:bg-emerald-700 border-emerald-600 text-white">Add Motorcycle</flux:button>
        </flux:modal.trigger>
    </div>

    <livewire:motorcycle.create />
    <livewire:motorcycle.edit :key="'edit-motorcycle-modal'" /> 
    <x-flash-message />

    {{-- DESAIN BARU: HORIZONTAL STATS TOP BAR --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 flex items-center justify-between shadow-3xs">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">Active Fleet</p>
                <p class="text-2xl font-black text-zinc-900 dark:text-white mt-1">{{ $this->totalMotorcycles }} <span class="text-xs font-normal text-zinc-400">Units</span></p>
            </div>
            <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/40 rounded-lg text-emerald-600 dark:text-emerald-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.129-1.125v-3.07m-1.5 4.5V14.25m-16.5 0h16.5M3.75 14.25h16.5M4.5 10.5h15M5.25 6.75h13.5" />
                </svg>
            </div>
        </div>

        <div class="bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 flex items-center justify-between shadow-3xs">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">System Status</p>
                <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400 mt-2.5 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span> Synchronized
                </p>
            </div>
        </div>

        <div class="bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 flex items-center justify-between shadow-3xs">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">Quick Search</p>
                <p class="text-xs text-zinc-400 mt-2 leading-relaxed">Press <kbd class="px-1 py-0.5 bg-zinc-200 dark:bg-zinc-800 rounded text-[10px]">Ctrl</kbd> + <kbd class="px-1 py-0.5 bg-zinc-200 dark:bg-zinc-800 rounded text-[10px]">F</kbd> to locate plate logs instantly.</p>
            </div>
        </div>
    </div>

    {{-- DATA CONTAINER FULL-WIDTH --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto w-full">
            <flux:table :paginate="$this->motorcycles->hasPages()" :pagination="$this->motorcycles" class="w-full min-w-[850px]">
                <flux:table.columns>
                    <flux:table.column class="text-xs font-bold text-zinc-500 w-14">No</flux:table.column>
                    {{-- Sembunyikan kolom Owner Name secara visual jika yang login adalah pelanggan --}}
                    @if(auth()->user()->role !== 'pelanggan')
                        <flux:table.column class="text-xs font-bold text-zinc-500 w-64">Owner Name</flux:table.column>
                    @endif
                    <flux:table.column class="text-xs font-bold text-zinc-500 w-52">Vehicle Specs</flux:table.column>
                    <flux:table.column class="text-xs font-bold text-zinc-500 w-44">Plate Number</flux:table.column>
                    <flux:table.column class="text-xs font-bold text-zinc-500">Registered</flux:table.column>
                    <flux:table.column class="w-12"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->motorcycles as $motorcycle)
                        <flux:table.row :key="$motorcycle->id" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                            
                            {{-- No --}}
                            <flux:table.cell class="font-bold text-zinc-400 dark:text-zinc-600 text-xs">
                                {{ $loop->iteration }}
                            </flux:table.cell>

                            {{-- Owner Name (Hanya dirender jika admin) --}}
                            @if(auth()->user()->role !== 'pelanggan')
                                <flux:table.cell class="w-64 whitespace-normal break-words pr-6">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-zinc-950 dark:text-zinc-50 tracking-tight leading-tight">
                                            {{ $motorcycle->user->name ?? 'No Owner Assigned' }}
                                        </span>
                                        <span class="text-[10px] font-medium text-zinc-400 dark:text-zinc-500 mt-0.5">
                                            Client Account #{{ $motorcycle->user_id ?? '-' }}
                                        </span>
                                    </div>
                                </flux:table.cell>
                            @endif

                            {{-- Vehicle Specs --}}
                            <flux:table.cell class="w-52">
                                <div class="flex flex-col">
                                    <span class="font-extrabold text-zinc-900 dark:text-zinc-100 text-sm tracking-tight">
                                        {{ $motorcycle->brand ?? '-' }}
                                    </span>
                                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        Type: {{ $motorcycle->model ?? '-' }}
                                    </span>
                                </div>
                            </flux:table.cell>

                            {{-- Plate Number --}}
                            <flux:table.cell class="w-44 whitespace-nowrap">
                                <span class="inline-block bg-zinc-100 dark:bg-zinc-800/80 text-zinc-900 dark:text-zinc-100 border border-zinc-300 dark:border-zinc-700 px-3 py-1 rounded-md font-mono font-black tracking-widest text-xs uppercase shadow-2xs">
                                    {{ $motorcycle->plate_number ?? 'N/A' }}
                                </span>
                            </flux:table.cell>

                            {{-- Created At --}}
                            <flux:table.cell class="whitespace-nowrap text-zinc-500 dark:text-zinc-400 font-medium text-xs">
                                {{ $motorcycle->created_at?->diffForHumans() ?? '-' }}
                            </flux:table.cell>

                            {{-- Actions --}}
                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>
                                    <flux:menu>
                                        <flux:menu.item icon="pencil" wire:click="$dispatch('edit-motorcycle', { id: {{ $motorcycle->id }} })">Edit Log</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete', {id: {{ $motorcycle->id }}})">Delete Record</flux:menu.item>
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