<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Order;
use Illuminate\Validation\Rule;

class OrdersForm extends Form
{
    public string $user_id = '';
    public string $total_amount = '';
    public string $status = 'pending';
    public ?Order $orders = null;

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id', 
            ],
            'total_amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['pending', 'approved', 'rejected']),
            ],
        ];
    }

    public function messages(): array
    {
        return [];
    }

    public function setOrders(Orders $orders): void
    {
        $this->orders = $orders;
        $this->user_id = (string) $orders->user_id;
        $this->total_amount = (string) $orders->total_amount;
        $this->status = $orders->status;
    }

    public function store()
    {
        $this->validate();
        Order::create($this->only(['user_id', 'total_amount', 'status']));
        $this->reset();
    }

    public function update()
    {
        $this->validate();
        $this->orders->update($this->only(['user_id', 'total_amount', 'status']));
        $this->reset();
    }
}