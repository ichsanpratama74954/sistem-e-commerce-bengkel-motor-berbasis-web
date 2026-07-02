<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    
    // Perbaikan: Semua tanda '::' diganti menjadi '.' agar Livewire bisa membaca foldernya
    Route::livewire('/categories', 'pages.category.index')->name('category.index');
    Route::livewire('/services', 'pages.service.index')->name('service.index');
    Route::livewire('/motorcycles', 'pages.motorcycle.index')->name('motorcycle.index');
    Route::livewire('/orders', 'pages.order.index')->name('order.index');
});

require __DIR__.'/settings.php';