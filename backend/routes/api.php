<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VerifyInvoiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ── Public routes ──────────────────────────────────────────────────────────

// Create a new booking (public - for non-logged in users)
Route::post('/bookings', [BookingController::class, 'store']);

// Track booking by code (public — user checks their own booking)
Route::get('/bookings/track/{code}', [BookingController::class, 'track']);

// Get invoice by booking code (public — user checks their invoice)
Route::get('/bookings/track/{code}/invoice', [InvoiceController::class, 'getByBookingCode']);

// Verify invoice authenticity (public)
Route::get('/verify', [VerifyInvoiceController::class, 'verify']);

// Statistics (public dashboard widget)
Route::get('/bookings/statistics', [BookingController::class, 'statistics']);

// Check booking availability (public)
Route::get('/bookings/availability', [BookingController::class, 'availability']);

// Review routes
Route::post('/reviews', [ReviewController::class, 'store']); // Submit review (general)
Route::post('/bookings/{code}/review', [ReviewController::class, 'store']); // Submit review for booking
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/statistics', [ReviewController::class, 'statistics']);

// Customer reschedule & cancel (public)
Route::post('/bookings/{code}/reschedule', [BookingController::class, 'reschedule']);
Route::post('/bookings/{code}/cancel', [BookingController::class, 'customerCancel']);

// Payment routes
Route::get('/payment/{invoiceNumber}/status', [PaymentController::class, 'status']);
Route::post('/payment/{invoiceNumber}/generate', [PaymentController::class, 'generateLink']);

// Midtrans notification handler
Route::post('/midtrans/notification', [PaymentController::class, 'notification']);

// Additional Midtrans notification handlers
Route::post('/midtrans/recurring', [PaymentController::class, 'recurringNotification']);
Route::post('/midtrans/pay-account', [PaymentController::class, 'payAccountNotification']);

// ── Authentication routes (public) ────────────────────────────────────────

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// ── Protected routes (authenticated customers & admins) ───────────────────

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
    Route::get('/auth/statistics', [AuthController::class, 'statistics']);

    // Customer bookings
    Route::post('/bookings', [BookingController::class, 'store']); // Authenticated booking with user_id
    Route::get('/bookings/my', [BookingController::class, 'myBookings']);
    Route::get('/bookings/my/{id}', [BookingController::class, 'show']);
    Route::get('/bookings/my/invoices', [BookingController::class, 'myInvoices']);
    Route::get('/bookings/my/reviews', [BookingController::class, 'myReviews']);

    // Admin only routes
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
});
