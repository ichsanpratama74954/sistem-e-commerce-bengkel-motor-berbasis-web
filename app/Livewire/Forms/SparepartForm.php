<?php

namespace App\Livewire\Forms;

use App\Models\Sparepart;
use Livewire\Form;

class SparepartForm extends Form
{
    public ?Sparepart $sparepart = null;

    public string $category_id = '';
    public string $part_name = '';
    public string $price = '';
    public int $stock = 0;

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'part_name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }

    public function setSparepart(Sparepart $sparepart): void
    {
        $this->sparepart = $sparepart;
        $this->category_id = $sparepart->category_id;
        $this->part_name = $sparepart->part_name;
        $this->price = $sparepart->price;
        $this->stock = $sparepart->stock;
    }

    public function store()
    {
        $this->validate();
        Sparepart::create($this->all());
        $this->reset();
    }

    public function update()
    {
        $this->validate();
        $this->sparepart->update($this->all());
    }
}