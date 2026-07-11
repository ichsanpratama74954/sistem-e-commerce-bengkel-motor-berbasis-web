<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Order;
use App\Models\Sparepart;
use App\Models\OrderDetail;
use Illuminate\Validation\Rule;

class OrdersForm extends Form
{
    public string $user_id = '';
    public string $total_amount = '';
    public string $status = 'pending';
    public ?Order $orders = null;

    public array $selected_spareparts = [];

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
            'selected_spareparts' => ['nullable', 'array'],
            'selected_spareparts.*.id' => ['required', 'integer', 'exists:spareparts,id'],
            'selected_spareparts.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [];
    }

    public function setOrders(Order $orders): void
    {
        $this->orders = $orders;
        $this->user_id = (string) $orders->user_id;
        $this->total_amount = (string) $orders->total_amount;
        $this->status = $orders->status;

        $this->selected_spareparts = $orders->orderDetails()
            ->get()
            ->map(fn ($d) => ['id' => $d->sparepart_id, 'quantity' => $d->quantity])
            ->toArray();
    }

    public function store()
    {
        $this->validate();
        $order = Order::create([
            'user_id' => $this->user_id,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
        ]);
        $this->syncDetails($order);
        $this->reset();
    }

    public function update()
    {
        $this->validate();
        $this->orders->update([
            'user_id' => $this->user_id,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
        ]);
        $this->orders->orderDetails()->delete();
        $this->syncDetails($this->orders);
        $this->reset();
    }

    protected function syncDetails(Order $order): void
    {
        foreach ($this->selected_spareparts as $item) {
            $sparepart = Sparepart::find($item['id']);
            $price = $sparepart?->price ?? 0;
            $order->orderDetails()->create([
                'sparepart_id' => $item['id'],
                'quantity' => $item['quantity'],
                'unit_price' => $price,
                'subtotal' => $price * $item['quantity'],
            ]);
        }
    }
}