<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\CategoryManager;
use App\Livewire\Admin\ServiceManager;

// 1. Halaman Utama / Landing Page (Bisa diakses siapa saja tanpa login)
Route::view('/', 'welcome')->name('home');

// 2. Kelompok Halaman yang WAJIB LOGIN & Verifikasi Email
Route::middleware(['auth', 'verified'])->group(function () {

    // 🚦 PENGATUR LALU LINTAS DASHBOARD 
    Route::get('dashboard', function () {
        $role = auth()->user()->role;

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($role === 'mekanik') {
            return redirect()->route('mekanik.dashboard');
        }

        // Jika dia 'pelanggan', tampilkan halaman dashboard bawaan
        return view('dashboard'); 
    })->name('dashboard');


    // 👑 KELOMPOK ROUTE: KHUSUS ADMIN
    Route::middleware(['role:admin'])->group(function () {
        
        // Halaman Dashboard Admin
        Route::get('/admin/dashboard', function () {
            return view('admin.dashboard'); 
        })->name('admin.dashboard');

    // 📂 Halaman Kelola Kategori Produk & Jasa
        Route::get('/admin/categories', CategoryManager::class)->name('admin.categories');

    // 🔧 ROUTE SERVICE
    Route::get('/admin/services', ServiceManager::class)->name('admin.services');
        
    });


    // 🔧 KELOMPOK ROUTE: KHUSUS MEKANIK
    Route::middleware(['role:mekanik'])->group(function () {
        Route::get('/mekanik/dashboard', function () {
            return view('mekanik.dashboard'); 
        })->name('mekanik.dashboard');
    });

});

require __DIR__.'/settings.php';