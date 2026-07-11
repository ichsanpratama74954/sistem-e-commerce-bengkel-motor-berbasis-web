<?php

use Livewire\Component;
use App\Livewire\Forms\BookingForm;
use App\Models\User;
use App\Models\Motorcycle;
use App\Models\Service;
use App\Models\Sparepart;

new class extends Component {
    public BookingForm $form;
    public $users = [];
    public $motorcycles = [];
    public $services = [];
    public $spareparts = [];

    public $new_service_id = '';
    public $new_service_qty = 1;
    public $new_sparepart_id = '';
    public $new_sparepart_qty = 1;

    public function mount()
    {
        $this->form = new BookingForm($this, 'form');
        $this->users = User::select('id', 'name', 'email')->get();
        $this->motorcycles = Motorcycle::select('id', 'brand', 'model', 'plate_number')->get();
        $this->services = Service::select('id', 'service_name', 'service_price')->get();
        $this->spareparts = Sparepart::select('id', 'part_name', 'price', 'stock')->get();
    }

    public function addService()
    {
        $this->validate([
            'new_service_id' => 'required|exists:services,id',
            'new_service_qty' => 'required|integer|min:1',
        ]);
        $this->form->selected_services[] = [
            'id' => (int) $this->new_service_id,
            'quantity' => (int) $this->new_service_qty,
        ];
        $this->new_service_id = '';
        $this->new_service_qty = 1;
    }

    public function removeService($index)
    {
        unset($this->form->selected_services[$index]);
        $this->form->selected_services = array_values($this->form->selected_services);
    }

    public function addSparepart()
    {
        $this->validate([
            'new_sparepart_id' => 'required|exists:spareparts,id',
            'new_sparepart_qty' => 'required|integer|min:1',
        ]);
        $this->form->selected_spareparts[] = [
            'id' => (int) $this->new_sparepart_id,
            'quantity' => (int) $this->new_sparepart_qty,
        ];
        $this->new_sparepart_id = '';
        $this->new_sparepart_qty = 1;
    }

    public function removeSparepart($index)
    {
        unset($this->form->selected_spareparts[$index]);
        $this->form->selected_spareparts = array_values($this->form->selected_spareparts);
    }

    public function save()
    {
        $this->form->store();
        Flux::modal('create-booking')->close();
        session()->flash('success', 'Booking created successfully');
        $this->redirectRoute('booking.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }
};
?>

<div>
    <flux:modal name="create-booking" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="save">
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Create Booking
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    Add a new booking to your account
                </flux:text>
            </div>

            <div class="space-y-6">
                <flux:select label="User" wire:model="form.user_id">
                    <flux:select.option value="">Select User</flux:select.option>
                    @foreach($users as $user)
                        <flux:select.option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select label="Motorcycle" wire:model="form.motorcycle_id">
                    <flux:select.option value="">Select Motorcycle</flux:select.option>
                    @foreach($motorcycles as $motorcycle)
                        <flux:select.option value="{{ $motorcycle->id }}">{{ $motorcycle->brand }} {{ $motorcycle->model }}
                            - {{ $motorcycle->plate_number }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input label="Booking Date" type="date" wire:model="form.booking_date" />

                <flux:select label="Status" wire:model="form.status">
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="approved">Approved</flux:select.option>
                    <flux:select.option value="rejected">Rejected</flux:select.option>
                </flux:select>

                <flux:separator variant="subtle" />

                <flux:heading size="sm">Services</flux:heading>
                <div class="flex items-end gap-2">
                    <flux:select wire:model="new_service_id" placeholder="Select service...">
                        <flux:select.option value="">Select Service</flux:select.option>
                        @foreach($services as $service)
                            <flux:select.option value="{{ $service->id }}">{{ $service->service_name }} (Rp
                                {{ number_format($service->service_price, 0, ',', '.') }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input type="number" class="w-20" min="1" value="1" wire:model="new_service_qty" />
                    <flux:button type="button" variant="primary" size="sm" wire:click="addService">Add</flux:button>
                </div>
                @if(count($form->selected_services) > 0)
                    <div class="space-y-1">
                        @foreach($form->selected_services as $i => $item)
                            @php $s = $services->firstWhere('id', $item['id']); @endphp
                            <div
                                class="flex items-center justify-between bg-zinc-50 dark:bg-zinc-800 px-3 py-2 rounded-lg text-sm">
                                <span>{{ $s?->service_name ?? 'Unknown' }} x{{ $item['quantity'] }}</span>
                                <button type="button" wire:click="removeService({{ $i }})"
                                    class="text-red-500 hover:text-red-700">&times;</button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <flux:separator variant="subtle" />

                <flux:heading size="sm">Spareparts</flux:heading>
                <div class="flex items-end gap-2">
                    <flux:select wire:model="new_sparepart_id" placeholder="Select sparepart...">
                        <flux:select.option value="">Select Sparepart</flux:select.option>
                        @foreach($spareparts as $sp)
                            <flux:select.option value="{{ $sp->id }}">{{ $sp->part_name }} (Rp
                                {{ number_format($sp->price, 0, ',', '.') }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input type="number" class="w-20" min="1" value="1" wire:model="new_sparepart_qty" />
                    <flux:button type="button" variant="primary" size="sm" wire:click="addSparepart">Add</flux:button>
                </div>
                @if(count($form->selected_spareparts) > 0)
                    <div class="space-y-1">
                        @foreach($form->selected_spareparts as $i => $item)
                            @php $sp = $spareparts->firstWhere('id', $item['id']); @endphp
                            <div
                                class="flex items-center justify-between bg-zinc-50 dark:bg-zinc-800 px-3 py-2 rounded-lg text-sm">
                                <span>{{ $sp?->part_name ?? 'Unknown' }} x{{ $item['quantity'] }}</span>
                                <button type="button" wire:click="removeSparepart({{ $i }})"
                                    class="text-red-500 hover:text-red-700">&times;</button>
                            </div>
                        @endforeach
                    </div>
                @endif
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