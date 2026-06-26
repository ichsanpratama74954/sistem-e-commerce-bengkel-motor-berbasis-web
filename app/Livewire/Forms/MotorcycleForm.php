<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;
use App\Models\Motorcycle;
use Illuminate\Validation\Rule;

class MotorcycleForm extends Form
{
    public string $brand = '';
    public string $model = '';
    public string $plate_number = '';
    public ?Motorcycle $motorcycle = null;

    public function rules(): array
    {
        return [
            'brand' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'model' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'plate_number' => [
                'required',
                'string',
                'min:7',
                'max:10',
                Rule::unique('motorcycles', 'plate_number')->ignore($this->motorcycle?->id),
            ],
        ];
    }
    
    public function store()
    {
        $this->validate();
        Motorcycle::create($this->only(['brand', 'model', 'plate_number']) + ['user_id' => auth()->id()]);
        $this->reset();
    }

    public function setMotorcycle(Motorcycle $motorcycle): void
    {
        $this->motorcycle = $motorcycle;
        $this->brand = $motorcycle->brand;
        $this->model = $motorcycle->model;
        $this->plate_number = $motorcycle->plate_number;
    }

    public function update()
    {
        $this->validate();
        $this->motorcycle->update($this->only(['brand', 'model', 'plate_number']));
        $this->reset();
    }
}
