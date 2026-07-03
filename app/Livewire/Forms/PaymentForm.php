<?php

namespace App\Livewire\Forms;

use App\Models\Payment;
use Livewire\Form;

class PaymentForm extends Form
{
    public ?string $booking_id = null;
    public ?string $order_id = null;
    public string $amount = '';
    public string $payment_status = '';
    public string $payment_method = ''; 
    public ?Payment $payment = null;

    public function rules(): array
    {
        return [
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'string', 'in:Cash,Transfer,QRIS'],
            'payment_method' => ['required', 'string', 'in:Cash,Transfer,QRIS,DANA'],
        ];
    }

    public function setPayment(Payment $payment): void
    {
        $this->payment = $payment;
        $this->booking_id = $payment->booking_id;
        $this->order_id = $payment->order_id;
        $this->amount = $payment->amount;
        $this->payment_status = $payment->payment_status;
        $this->payment_method = $payment->payment_method; 
    }

    public function store()
    {
        $this->validate();
        
        Payment::create([
            'booking_id' => $this->booking_id ?: null,
            'order_id' => $this->order_id ?: null,
            'amount' => $this->amount,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
        ]);

        $this->reset();
    }

    public function update()
    {
        $this->validate();
        
        $this->payment->update([
            'booking_id' => $this->booking_id ?: null,
            'order_id' => $this->order_id ?: null,
            'amount' => $this->amount,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method, 
        ]);
    }
}