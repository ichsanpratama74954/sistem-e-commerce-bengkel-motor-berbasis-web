<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryManager extends Component
{
    public $name;
    public $description;
    public $categoryId;
    public $isEdit = false;

    // Validasi form realtime / saat submit
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name,' . $this->categoryId,
            'description' => 'nullable|string',
        ];
    }

    // Fungsi Tambah Data (Create)
    public function store()
    {
        $this->validate();

        Category::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
        ]);

        $this->resetInput();
        session()->flash('message', 'Kategori baru berhasil ditambahkan!');
    }

    // Fungsi Ambil Data Lama untuk Edit (Read khusus)
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $this->categoryId = $id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->isEdit = true;
    }

    // Fungsi Simpan Perubahan (Update)
    public function update()
    {
        $this->validate();

        $category = Category::findOrFail($this->categoryId);
        $category->update([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
        ]);

        $this->resetInput();
        $this->isEdit = false;
        session()->flash('message', 'Kategori berhasil diperbarui!');
    }

    // Fungsi Hapus Data (Delete)
    public function delete($id)
    {
        Category::destroy($id);
        session()->flash('message', 'Kategori berhasil dihapus!');
    }

    // Reset Form Input
    public function resetInput()
    {
        $this->name = '';
        $this->description = '';
        $this->categoryId = null;
        $this->isEdit = false;
    }

    // Merender data dan mengarahkannya ke file Tampilan (Blade HTML)
    public function render()
    {
        return view('admin.category-manager', [
            'categories' => Category::latest()->get()
        ]);
    }
}