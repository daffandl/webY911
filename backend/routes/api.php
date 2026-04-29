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

// Create a new booking (public - for non-logged in users) - rate limited: 30 req/min
Route::throttle('30,1')->post('/bookings', [BookingController::class, 'store']);

// Track booking by code (public — user checks their own booking) - rate limited: 60 req/min
Route::throttle('60,1')->get('/bookings/track/{code}', [BookingController::class, 'track']);

// Get invoice by booking code (public — user checks their invoice) - rate limited: 60 req/min
Route::throttle('60,1')->get('/bookings/track/{code}/invoice', [InvoiceController::class, 'getByBookingCode']);

// Verify invoice authenticity (public) - rate limited: 60 req/min
Route::throttle('60,1')->get('/verify', [VerifyInvoiceController::class, 'verify']);

// Statistics (public dashboard widget) - rate limited: 120 req/min
Route::throttle('120,1')->get('/bookings/statistics', [BookingController::class, 'statistics']);

// Check booking availability (public) - rate limited: 120 req/min
Route::throttle('120,1')->get('/bookings/availability', [BookingController::class, 'availability']);

// Review routes - rate limited: 20 req/min for POST, 120 req/min for GET
Route::throttle('20,1')->post('/reviews', [ReviewController::class, 'store']); // Submit review (general)
Route::throttle('20,1')->post('/bookings/{code}/review', [ReviewController::class, 'store']); // Submit review for booking
Route::throttle('120,1')->get('/reviews', [ReviewController::class, 'index']);
Route::throttle('120,1')->get('/reviews/statistics', [ReviewController::class, 'statistics']);

// Customer reschedule & cancel (public) - rate limited: 30 req/min
Route::throttle('30,1')->post('/bookings/{code}/reschedule', [BookingController::class, 'reschedule']);
Route::throttle('30,1')->post('/bookings/{code}/cancel', [BookingController::class, 'customerCancel']);

// Payment routes - rate limited: 60 req/min for status, 30 req/min for generate
Route::throttle('60,1')->get('/payment/{invoiceNumber}/status', [PaymentController::class, 'status']);
Route::throttle('30,1')->post('/payment/{invoiceNumber}/generate', [PaymentController::class, 'generateLink']);

// Midtrans notification handler - NO RATE LIMIT (trusted webhook)
Route::post('/midtrans/notification', [PaymentController::class, 'notification']);

// Additional Midtrans notification handlers - NO RATE LIMIT (trusted webhook)
Route::post('/midtrans/recurring', [PaymentController::class, 'recurringNotification']);
Route::post('/midtrans/pay-account', [PaymentController::class, 'payAccountNotification']);

// ── Authentication routes (public) - rate limited: 5 req/min────────────────────────────────────────

Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
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
