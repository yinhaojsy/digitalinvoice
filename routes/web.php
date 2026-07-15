<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceFbrController;
use App\Http\Controllers\OrganizationSettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/settings/organization', [OrganizationSettingsController::class, 'edit'])->name('settings.organization');
    Route::put('/settings/organization', [OrganizationSettingsController::class, 'update'])->name('settings.organization.update');

    Route::patch('/theme', [ThemeController::class, 'update'])->name('theme.update');

    Route::post('/customers/live-status', [CustomerController::class, 'liveStatus'])->name('customers.live-status');
    Route::resource('customers', CustomerController::class)->except(['show']);

    Route::resource('invoices', InvoiceController::class);
    Route::post('/invoices/{invoice}/validate', [InvoiceFbrController::class, 'validateInvoice'])->name('invoices.validate');
    Route::post('/invoices/{invoice}/post', [InvoiceFbrController::class, 'postInvoice'])->name('invoices.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
