<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Booking;

new class extends Component
{
    use WithPagination;

    #[Computed]
    public function bookings()
    {
        // 1. Mulai query dasar dengan eager loading relasi termasuk 'mechanic'
        $query = Booking::with(['user', 'motorcycle', 'bookingDetails', 'mechanic'])->latest();
        
        // 2. Jika yang login adalah pelanggan, BATASI hanya data miliknya sendiri
        if (auth()->user()->role === 'pelanggan') {
            $query->where('user_id', auth()->id());
        } 
        // 3. Jika yang login adalah mekanik, BATASI hanya yang ditugaskan kepadanya atau yang belum ada mekanik
        elseif (in_array(auth()->user()->role, ['mekanik', 'mechanic'])) {
            $query->where(function($q) {
                $q->where('mechanic_id', auth()->id())
                  ->orWhereNull('mechanic_id');
            });
        }

        return $query->paginate(10);
    }

    public function edit($id){
        $this->dispatch('edit-booking', id: $id);
    }

    // 🌟 FUNGSI BARU: Mekanik mulai mengerjakan motor
    public function startWorking($id)
    {
        $booking = Booking::find($id);
        if ($booking && $booking->status === 'pending') {
            $booking->status = 'processing';
            $booking->mechanic_id = auth()->id(); // Otomatis mencatat siapa mekanik yang mengerjakan
            $booking->save();
            
            session()->flash('success', 'Motor berhasil diproses. Selamat bekerja!');
        }
    }

    // 🌟 FUNGSI BARU: Mekanik menyelesaikan servis motor
    public function completeWorking($id)
    {
        $booking = Booking::find($id);
        if ($booking && $booking->status === 'processing') {
            $booking->status = 'completed';
            $booking->save();
            
            session()->flash('success', 'Servis selesai! Motor siap diserahkan ke pelanggan.');
        }
    }
};
?>

<div class="max-w-7xl mx-auto space-y-4">
    <flux:heading size="xl" class="text-zinc-800 dark:text-white">Bookings</flux:heading>
    <flux:subheading size="lg" class="text-zinc-600 dark:text-zinc-400">Manage your bookings</flux:subheading>
    <flux:separator variant="subtle" />
    
    <!-- Tombol tambah booking hanya untuk pelanggan atau admin -->
    @if(auth()->user()->role !== 'mekanik' && auth()->user()->role !== 'mechan')
        <flux:modal.trigger name="create-booking">
            <flux:button variant="primary" icon="plus" color="primary">Add Booking</flux:button>
        </flux:modal.trigger>
    @endif

    <livewire:booking.create />
    <livewire:booking.edit />
    <livewire:payment.create />
    <x-flash-message />

    {{-- table --}}
    <div class="overflow-x-auto">
       <flux:table :paginate="$this->bookings">
            <flux:table.columns>
                <flux:table.column>No</flux:table.column>
                <flux:table.column>User</flux:table.column>
                <flux:table.column>Motorcycle</flux:table.column>
                <flux:table.column>Mekanik</flux:table.column> 
                <flux:table.column>Booking Date</flux:table.column>
                <flux:table.column>Services</flux:table.column>
                <flux:table.column>Total</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Created At</flux:table.column>
                <flux:table.column class="text-right">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->bookings as $index => $booking)
                    <flux:table.row :key="$booking->id">
                        
                        {{-- 1. No --}}
                        <flux:table.cell class="text-zinc-500">
                            {{ $this->bookings->firstItem() + $index }}
                        </flux:table.cell>

                        {{-- 2. User --}}
                        <flux:table.cell>
                            <div class="flex flex-col justify-center">
                                <span class="font-medium text-zinc-800 dark:text-white">
                                    {{ $booking->user->name ?? 'No Name' }}
                                </span>
                                <span class="text-zinc-500 dark:text-zinc-400 text-xs">
                                    ID: {{ $booking->user_id }}
                                </span>
                            </div>
                        </flux:table.cell>

                        {{-- 3. Motorcycle --}}
                        <flux:table.cell>
                            <div class="flex flex-col justify-center">
                                <span class="font-medium text-zinc-800 dark:text-white">
                                    {{ $booking->motorcycle->brand ?? 'No Brand' }} {{ $booking->motorcycle->model ?? '' }}
                                </span>
                                <span class="text-zinc-500 dark:text-zinc-400 text-xs font-mono">
                                    {{ $booking->motorcycle->plate_number ?? 'No Plate' }}
                                </span>
                            </div>
                        </flux:table.cell>

                        {{-- 4. Mekanik --}}
                        <flux:table.cell>
                            @if($booking->mechanic)
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2 w-2 rounded-full bg-indigo-500"></span>
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200 text-sm">
                                        {{ $booking->mechanic->name }}
                                    </span>
                                </div>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-600 italic text-xs">
                                    Belum Ditugaskan
                                </span>
                            @endif
                        </flux:table.cell>

                        {{-- 5. Booking Date --}}
                        <flux:table.cell class="whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}
                        </flux:table.cell>

                        {{-- 6. Services --}}
                        <flux:table.cell>
                            <span class="text-xs text-zinc-500">
                                {{ $booking->bookingDetails->count() }} item(s)
                            </span>
                        </flux:table.cell>

                        {{-- 7. Total --}}
                        <flux:table.cell class="font-semibold text-zinc-900 dark:text-white">
                            Rp {{ number_format($booking->bookingDetails->sum('subtotal'), 0, ',', '.') }}
                        </flux:table.cell>

                        {{-- 8. Status --}}
                        <flux:table.cell>
                            @php
                                $statusClasses = match($booking->status) {
                                    'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/30',
                                    'completed' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-900/30',
                                    'processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/20 dark:text-indigo-400 dark:border-indigo-900/30',
                                    'rejected' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-900/30',
                                    default => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/30', 
                                };

                                $statusLabels = [
                                    'pending' => 'Pending',
                                    'processing' => 'Mekanik Bekerja',
                                    'completed' => 'Servis Selesai',
                                    'approved' => 'Lunas',
                                    'rejected' => 'Ditolak',
                                ];
                                $labelText = $statusLabels[$booking->status] ?? ucfirst($booking->status);
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusClasses }}">
                                {{ $labelText }}
                            </span>
                        </flux:table.cell>

                        {{-- 9. Created At --}}
                        <flux:table.cell class="whitespace-nowrap">
                            {{ $booking->created_at->diffForHumans() }}
                        </flux:table.cell>

                        {{-- 10. Actions --}}
                        <flux:table.cell class="text-right">
                            @php
                                $userRole = auth()->user()->role;
                                $isAdminOrCashier = in_array($userRole, ['admin', 'kasir', 'cashier']);
                                $isMechanic = in_array($userRole, ['mekanik', 'mechanic']);
                            @endphp

                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>

                                <flux:menu>
                                    <!-- Edit Item -->
                                    @if($isAdminOrCashier)
                                        <flux:menu.item icon="pencil" wire:click="edit({{ $booking->id }})">Edit</flux:menu.item>
                                    @endif

                                    <!-- 💳 TOMBOL BAYAR (Admin/Kasir ATAU Pemilik Booking) -->
                                    @if($booking->status !== 'approved')
                                        @if($isAdminOrCashier || $booking->user_id === auth()->id())
                                            <flux:menu.item icon="credit-card" wire:click="$dispatch('pay-booking', { id: {{ $booking->id }} })">
                                                Bayar
                                            </flux:menu.item>
                                        @endif
                                    @endif

                                    <!-- 🔧 MENU KHUSUS MEKANIK -->
                                    @if($isMechanic || $userRole === 'admin')
                                        @if($booking->status === 'pending')
                                            <flux:menu.item icon="play" wire:click="startWorking({{ $booking->id }})">
                                                Mulai Kerja
                                            </flux:menu.item>
                                        @elseif($booking->status === 'processing')
                                            <flux:menu.item icon="check" wire:click="completeWorking({{ $booking->id }})">
                                                Selesai Servis
                                            </flux:menu.item>
                                        @endif
                                    @endif

                                    <!-- Tombol Delete -->
                                    @if($isAdminOrCashier)
                                        <flux:menu.separator />
                                        <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete', {id: {{ $booking->id }}})">
                                            Delete
                                        </flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>

                    </flux:table.row>
                @endforeach
            </flux:table.rows>
       </flux:table>
    </div>
</div>