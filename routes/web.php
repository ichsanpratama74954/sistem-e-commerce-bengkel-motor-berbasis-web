<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard') ->name('dashboard');
    Route::livewire('/categories', 'pages::category.index')->name('category.index');
    Route::livewire('/services', 'pages::service.index')->name('service.index');
    Route::livewire('/motorcycles', 'pages::motorcycle.index')->name('motorcycle.index');
    Route::livewire('/orders', 'pages::orders.index')->name('order.index');
    Route::livewire('/bookings', 'pages::booking.index')->name('booking.index');
});

require __DIR__.'/settings.php';