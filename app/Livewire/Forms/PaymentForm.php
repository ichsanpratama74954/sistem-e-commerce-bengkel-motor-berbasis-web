<?php

namespace App\Livewire\Forms;

use App\Models\Payment;
use Livewire\Form;

class PaymentForm extends Form
{
    public string $paymentable_type = '';
    public int $paymentable_id = 0;
    public string $amount = '';
    public string $payment_status = '';
    public string $payment_method = '';
    public ?Payment $payment = null;

    public function rules(): array
    {
        return [
            'paymentable_type' => ['required', 'string', 'in:App\Models\Booking,App\Models\Order'],
            'paymentable_id' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'string', 'in:Cash,Transfer,QRIS,DANA'],
        ];
    }

    public function setPayment(Payment $payment): void
    {
        $this->payment = $payment;
        $this->paymentable_type = $payment->paymentable_type;
        $this->paymentable_id = (int) $payment->paymentable_id;
        $this->amount = $payment->amount;
        $this->payment_status = $payment->payment_status;
        $this->payment_method = $payment->payment_method;
    }

    public function store()
    {
        $this->validate();

        Payment::create([
            'paymentable_type' => $this->paymentable_type,
            'paymentable_id' => $this->paymentable_id,
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
            'paymentable_type' => $this->paymentable_type,
            'paymentable_id' => $this->paymentable_id,
            'amount' => $this->amount,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
        ]);
    }
}