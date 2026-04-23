<?php

use App\Http\Controllers\InvoicePrintController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VerifyInvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/bookings/create', function () {
    return view('bookings.create');
})->name('bookings.create');

// Invoice print page (public — accessible by booking code owner)
Route::get('/invoice/{invoiceNumber}/print', [InvoicePrintController::class, 'print'])
    ->name('invoice.print');

// Payment pages
Route::get('/payment/{invoiceNumber}', [PaymentController::class, 'show'])
    ->name('payment.show');

Route::get('/payment/{invoiceNumber}/complete', [PaymentController::class, 'complete'])
    ->name('payment.complete');

// Midtrans redirect pages
Route::get('/payment/finish', [PaymentController::class, 'finish'])
    ->name('payment.finish');

Route::get('/payment/unfinish', [PaymentController::class, 'unfinish'])
    ->name('payment.unfinish');

Route::get('/payment/error', [PaymentController::class, 'error'])
    ->name('payment.error');

// Invoice verification page (public)
Route::get('/verify', [VerifyInvoiceController::class, 'show'])
    ->name('invoice.verify');
