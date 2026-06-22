<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;
use App\Models\Category;
use Illuminate\Validation\Rule;

class CategoryForm extends Form
{
    public string $name = '';
    public string $description = '';
    public ?Category $category = null;

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('categories', 'name')->ignore($this->category?->id),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function store()
    {
        $this->validate();
        Category::create($this->only(['name', 'description']));
        $this->reset();
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
    }

    public function update()
    {
        $this->validate();
        $this->category->update($this->only(['name', 'description']));
    }
}
