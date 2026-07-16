<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Sparepart;
use App\Models\BookingDetail;
use Illuminate\Validation\Rule;

class BookingForm extends Form
{
    public int $user_id = 0;
    public int $motorcycle_id = 0;
    public ?int $mechanic_id = null; // 🌟 Properti baru untuk menampung ID Mekanik
    public string $booking_date = '';
    public string $status = 'pending';
    public ?Booking $booking = null;

    public array $selected_services = [];
    public array $selected_spareparts = [];

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'motorcycle_id' => [
                'required',
                'integer',
                'exists:motorcycles,id',
            ],
            'mechanic_id' => [ // 🌟 Aturan validasi untuk mekanik pilihan Admin
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'booking_date' => [
                'required',
                'date',
                Rule::unique('bookings', 'booking_date')
                    ->where(function ($query) {
                        return $query->where('user_id', $this->user_id)
                                     ->where('motorcycle_id', $this->motorcycle_id);
                    })
                    ->ignore($this->booking?->id),
            ],
            'status' => [
                'required',
                'string',
                // 🌟 Mendaftarkan status alur kerja baru agar lolos validasi saat disimpan
                Rule::in(['pending', 'processing', 'completed', 'approved', 'rejected']),
            ],
            'selected_services' => ['nullable', 'array'],
            'selected_services.*.id' => ['required', 'integer', 'exists:services,id'],
            'selected_services.*.quantity' => ['required', 'integer', 'min:1'],
            'selected_spareparts' => ['nullable', 'array'],
            'selected_spareparts.*.id' => ['required', 'integer', 'exists:spareparts,id'],
            'selected_spareparts.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'booking_date.unique' => 'Peringatan: Anda sudah melakukan booking untuk motor ini pada tanggal tersebut!',
        ];
    }

    public function setBooking(Booking $booking): void
    {
        $this->booking = $booking;
        $this->user_id = (int) $booking->user_id;
        $this->motorcycle_id = (int) $booking->motorcycle_id;
        $this->mechanic_id = $booking->mechanic_id ? (int) $booking->mechanic_id : null; // 🌟 Ambil data mekanik aktif
        $this->booking_date = $booking->booking_date;
        $this->status = $booking->status;

        $this->selected_services = $booking->bookingDetails()
            ->whereNotNull('service_id')
            ->get()
            ->map(fn ($d) => ['id' => $d->service_id, 'quantity' => $d->quantity])
            ->toArray();

        $this->selected_spareparts = $booking->bookingDetails()
            ->whereNotNull('sparepart_id')
            ->get()
            ->map(fn ($d) => ['id' => $d->sparepart_id, 'quantity' => $d->quantity])
            ->toArray();
    }

    public function store()
    {
        $this->validate();
        // 🌟 Pastikan mechanic_id diikutkan saat menyimpan booking baru
        $booking = Booking::create($this->only(['user_id', 'motorcycle_id', 'mechanic_id', 'booking_date', 'status']));
        $this->syncDetails($booking);
        $this->reset();
    }

    public function update()
    {
        $this->validate();
        // 🌟 Pastikan mechanic_id ikut diperbarui ke database saat Admin melakukan edit
        $this->booking->update($this->only(['user_id', 'motorcycle_id', 'mechanic_id', 'booking_date', 'status']));
        $this->booking->bookingDetails()->delete();
        $this->syncDetails($this->booking);
        $this->reset();
    }

    protected function syncDetails(Booking $booking): void
    {
        foreach ($this->selected_services as $item) {
            $service = Service::find($item['id']);
            $price = $service?->service_price ?? 0;
            $booking->bookingDetails()->create([
                'service_id' => $item['id'],
                'quantity' => $item['quantity'],
                'unit_price' => $price,
                'subtotal' => $price * $item['quantity'],
            ]);
        }

        foreach ($this->selected_spareparts as $item) {
            $sparepart = Sparepart::find($item['id']);
            $price = $sparepart?->price ?? 0;
            $booking->bookingDetails()->create([
                'sparepart_id' => $item['id'],
                'quantity' => $item['quantity'],
                'unit_price' => $price,
                'subtotal' => $price * $item['quantity'],
            ]);
        }
    }
}