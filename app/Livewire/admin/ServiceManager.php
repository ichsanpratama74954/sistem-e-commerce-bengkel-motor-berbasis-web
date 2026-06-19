<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Service;

class ServiceManager extends Component
{
    public $service_name;
    public $service_price;
    public $description;
    public $serviceId;
    public $isEdit = false;

    protected function rules()
    {
        return [
            'service_name' => 'required|string|max:255|unique:services,service_name,' . $this->serviceId,
            'service_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ];
    }

    protected $messages = [
        'service_name.required' => 'Nama service wajib diisi.',
        'service_name.unique' => 'Nama service ini sudah ada.',
        'service_price.required' => 'Harga service wajib diisi.',
        'service_price.numeric' => 'Harga harus berupa angka.',
    ];

    public function store()
    {
        $this->validate();

        Service::create([
            'service_name' => $this->service_name,
            'service_price' => $this->service_price,
            'description' => $this->description,
        ]);

        $this->resetInput();
        session()->flash('message', 'Jasa service baru berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $service = Service::findOrFail($id);
        $this->serviceId = $id;
        $this->service_name = $service->service_name;
        $this->service_price = $service->service_price;
        $this->description = $service->description;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate();

        $service = Service::findOrFail($this->serviceId);
        $service->update([
            'service_name' => $this->service_name,
            'service_price' => $this->service_price,
            'description' => $this->description,
        ]);

        $this->resetInput();
        $this->isEdit = false;
        session()->flash('message', 'Data service berhasil diperbarui!');
    }

    public function delete($id)
    {
        Service::destroy($id);
        session()->flash('message', 'Jasa service berhasil dihapus!');
    }

    public function resetInput()
    {
        $this->service_name = '';
        $this->service_price = '';
        $this->description = '';
        $this->serviceId = null;
        $this->isEdit = false;
    }

    public function render()
    {
        return view('admin.service-manager', [
            'services' => Service::latest()->get()
        ])->layout('layouts.app');
    }
}