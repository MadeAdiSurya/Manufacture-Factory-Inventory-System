<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\FactoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;


// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Distributor Routes
Route::middleware(['auth:distributor'])->group(function () {
    Route::get('/distributor/dashboard', [DistributorController::class, 'dashboard'])->name('distributor.dashboard');
    Route::get('/distributor/create-request', [DistributorController::class, 'createRequest'])->name('distributor.create-request');
    Route::post('/distributor/store-request', [DistributorController::class, 'storeRequest'])->name('distributor.store-request');
    Route::delete('/distributor/delete-request/{id}', [DistributorController::class, 'deleteRequest'])->name('distributor.delete-request');
    Route::get('/payment/pay/{invoiceId}', [PaymentController::class, 'payWithPayPal'])->name('payment.pay');
    Route::get('/payment/status/{invoiceId}', [PaymentController::class, 'getPaymentStatus'])->name('payment.status');
});

// Supervisor Routes
Route::middleware(['auth:supervisor'])->group(function () {
    Route::get('/supervisor/dashboard', [SupervisorController::class, 'dashboard'])->name('supervisor.dashboard');
    Route::post('/supervisor/update-status', [SupervisorController::class, 'updateStatus'])->name('supervisor.update-status');
});

// Factory Routes
Route::group(['prefix' => 'factory', 'middleware' => ['auth:factory']], function () {
    Route::get('/dashboard', [FactoryController::class, 'dashboard'])->name('factory.dashboard');

    // Route to handle Maintenance action
    Route::post('/machine/{id}/maintenance', [FactoryController::class, 'setMachineMaintenance'])->name('factory.machine.maintenance');

    // Route to handle Workload Acceptance
    Route::post('/workload/{id}/accept', [FactoryController::class, 'acceptWorkload'])->name('factory.workload.accept');
    Route::post('/factory/machine/{id}/available', [FactoryController::class, 'setMachineAvailable'])->name('factory.machine.available');
});

Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'generatePDF'])->name('invoices.pdf');