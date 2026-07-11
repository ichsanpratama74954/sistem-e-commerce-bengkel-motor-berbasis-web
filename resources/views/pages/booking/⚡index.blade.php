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
        return Booking::with(['user', 'motorcycle', 'bookingDetails'])->latest()->paginate(10);
    }

    public function edit($id){
        $this->dispatch('edit-booking', id: $id);
    }
};
?>

<div class="max-w-7xl mx-auto space-y-4">
    <flux:heading size="xl" class="text-zinc-800 dark:text-white">Bookings</flux:heading>
    <flux:subheading size="lg" class="text-zinc-600 dark:text-zinc-400">Manage your bookings</flux:subheading>
    <flux:separator variant="subtle" />
    
    <flux:modal.trigger name="create-booking">
        <flux:button variant="primary" icon="plus" color="primary">Add Booking</flux:button>
    </flux:modal.trigger>

    <livewire:booking.create />
    <livewire:booking.edit />
    <x-flash-message />

    {{-- table --}}
    <div class="overflow-x-auto">
       <flux:table :paginate="$this->bookings">
            <flux:table.columns>
                <flux:table.column>No</flux:table.column>
                <flux:table.column>User</flux:table.column>
                <flux:table.column>Motorcycle</flux:table.column>
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
                        
                        <flux:table.cell class="text-zinc-500">
                            {{ $this->bookings->firstItem() + $index }}
                        </flux:table.cell>

                        <flux:table.cell class="flex items-center gap-3">
                            <div class="flex flex-col">
                                <span class="font-medium text-zinc-800 dark:text-white">{{ $booking->user->name ?? 'No Name' }}</span>
                                <span class="text-zinc-500 dark:text-zinc-400 text-xs">ID: {{ $booking->user_id }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="flex items-center gap-3">
                            <div class="flex flex-col">
                                <span class="font-medium text-zinc-800 dark:text-white">{{ $booking->motorcycle->brand ?? 'No Brand' }} {{ $booking->motorcycle->model ?? '' }}</span>
                                <span class="text-zinc-500 dark:text-zinc-400 text-xs">{{ $booking->motorcycle->plate_number ?? 'No Plate' }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-xs text-zinc-500">{{ $booking->bookingDetails->count() }} item(s)</span>
                        </flux:table.cell>

                        <flux:table.cell class="font-medium">
                            Rp {{ number_format($booking->bookingDetails->sum('subtotal'), 0, ',', '.') }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            {{ $booking->created_at->diffForHumans() }}
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>

                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="edit({{ $booking->id }})">Edit</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete', {id: {{ $booking->id }}})">Delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>

                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>