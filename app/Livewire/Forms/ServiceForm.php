<?php

namespace App\Livewire\Forms;

//use Livewire\Attributes\Validate;
use Livewire\Form;
use App\Models\Service;
use Illuminate\Validation\Rule;

class ServiceForm extends Form
{
    public string $service_name = '';
    public string $description = '';
    public string|float $service_price = '';
    public ?Service $service = null;

    public function rules(): array
    {
        return [
            'service_name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('services', 'service_name')->ignore($this->service?->id),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'service_price' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }

    public function setService(Service $service): void
    {
        $this->service = $service;
        $this->service_name = $service->service_name;
        $this->description = $service->description ?? '';
        $this->service_price = $service->service_price;
    }

    public function store()
    {
        $this->validate();
        Service::create($this->only(['service_name', 'description', 'service_price']));
        $this->reset();
    }

    public function update()
    {
        $this->validate();
        $this->service->update($this->only(['service_name', 'description', 'service_price']));
    }
}
