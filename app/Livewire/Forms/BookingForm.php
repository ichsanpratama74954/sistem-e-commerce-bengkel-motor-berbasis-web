<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Booking;
use Illuminate\Validation\Rule;

class BookingForm extends Form
{
    public int $user_id = 0;
    public int $motorcycle_id = 0;
    public string $booking_date = '';
    public string $status = 'pending';
    public ?Booking $booking = null;

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
                Rule::in(['pending', 'approved', 'rejected']),
            ],
        ];
    }

    /**
     * Kustomisasi Pesan Error Agar Muncul Peringatan Bahasa Indonesia yang Jelas
     */
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
        $this->booking_date = $booking->booking_date;
        $this->status = $booking->status;
    }

    public function store()
    {
        $this->validate();
        Booking::create($this->only(['user_id', 'motorcycle_id', 'booking_date', 'status']));
        $this->reset();
    }

    public function update()
    {
        $this->validate();
        $this->booking->update($this->only(['user_id', 'motorcycle_id', 'booking_date', 'status']));
        $this->reset();
    }
}