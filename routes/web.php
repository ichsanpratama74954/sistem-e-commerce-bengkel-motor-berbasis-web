<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('/categories', 'pages::category.index')->name('category.index');
    Route::livewire('/services', 'pages::service.index')->name('service.index');
    Route::livewire('/motorcycles', 'pages::motorcycle.index')->name('motorcycle.index');
    Route::livewire('/orders', 'pages::orders.index')->name('order.index');
    Route::livewire('/bookings', 'pages::booking.index')->name('booking.index');
    Route::livewire('/spareparts', 'pages::sparepart.index')->name('sparepart.index');
    Route::livewire('/payments', 'pages::payment.index')->name('payment.index');
    Route::livewire('/users', 'pages::users.index')->name('user.index');

    Route::get('/reports', [ReportController::class, 'index'])
    ->name('reports.index');
    Route::get('/reports/pdf', [ReportController::class, 'downloadPdf'])
    ->name('reports.pdf');
});

require __DIR__.'/settings.php';