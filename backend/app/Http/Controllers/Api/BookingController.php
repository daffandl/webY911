<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Rules\PhoneNumberRule;
use App\Rules\ServiceTypeRule;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
    ) {}

    // ─────────────────────────────────────────────────────────────
    //  POST /api/bookings  — Create new booking (public or authenticated)
    // ─────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'phone'        => ['required', 'string', 'max:30', new PhoneNumberRule()],
            'email'        => ['required', 'email', 'max:255'],
            'car_model'    => ['required', 'string', 'max:255'],
            'vehicle_info' => ['nullable', 'string', 'max:500'],
            'service_type' => ['required', 'string', 'max:150', new ServiceTypeRule()],
            'date'         => ['required', 'date', 'after_or_equal:today'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $bookingData = [
                'name'          => $validated['name'],
                'phone'         => $this->normalizePhoneNumber($validated['phone']),
                'email'         => $validated['email'],
                'car_model'     => $validated['car_model'],
                'vehicle_info'  => $validated['vehicle_info'] ?? null,
                'service_type'  => $validated['service_type'],
                'preferred_date' => $validated['date'],
                'notes'         => $validated['notes'] ?? null,
            ];

            // Attach user_id if authenticated
            if ($request->user()) {
                $bookingData['user_id'] = $request->user()->id;
            }

            $booking = $this->bookingService->createBooking($bookingData);

            return response()->json([
                'success'      => true,
                'message'      => 'Booking berhasil dibuat! Kami akan segera menghubungi Anda.',
                'booking_code' => $booking->booking_code,
                'data'         => $this->formatBooking($booking),
            ], 201);

        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'error' => 'Gagal membuat booking: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Normalize phone number to standard format (62xxxxxxxxx)
     */
    private function normalizePhoneNumber(string $phone): string
    {
        // Remove spaces, dashes, parentheses
        $phone = str_replace([' ', '-', '(', ')'], '', $phone);

        // Convert leading 0 to 62
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        // Remove leading + if present
        elseif (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /api/bookings/{code}  — Track booking by code (public)
    // ─────────────────────────────────────────────────────────────

    public function track(string $code): JsonResponse
    {
        $booking = Booking::byCode(strtoupper($code))->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak ditemukan. Periksa kembali kode booking Anda.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatBooking($booking),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /api/bookings  — List all bookings (protected - admin)
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        // If authenticated customer, return their bookings
        if ($request->user() && !$request->user()->isAdmin()) {
            return $this->myBookings($request);
        }

        // Admin - return all bookings
        $bookings = Booking::with(['user', 'latestInvoice', 'review'])
            ->latest()
            ->paginate(15);

        return response()->json($bookings);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /api/bookings/my  — List authenticated user's bookings
    // ─────────────────────────────────────────────────────────────

    public function myBookings(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get bookings by user_id OR by email (for bookings created before registration)
        $query = Booking::with(['latestInvoice', 'review'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('email', $user->email);
            });

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $bookings->map(function ($booking) {
                return $this->formatBooking($booking);
            }),
            'meta'    => [
                'current_page' => $bookings->currentPage(),
                'last_page'    => $bookings->lastPage(),
                'per_page'     => $bookings->perPage(),
                'total'        => $bookings->total(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /api/bookings/my/{id}  — Get specific booking detail (protected)
    // ─────────────────────────────────────────────────────────────

    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $booking = Booking::with(['latestInvoice', 'review'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $this->formatBooking($booking),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /api/bookings/my/invoices  — List user's invoices
    // ─────────────────────────────────────────────────────────────

    public function myInvoices(Request $request): JsonResponse
    {
        $user = $request->user();

        $invoices = \App\Models\Invoice::whereHas('booking', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['booking'])
        ->latest()
        ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $invoices->map(function ($invoice) {
                return [
                    'id'             => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'booking_code'   => $invoice->booking->booking_code ?? null,
                    'status'         => $invoice->status,
                    'total'          => $invoice->total,
                    'paid_amount'    => $invoice->paid_amount,
                    'payment_url'    => $invoice->payment_url,
                    'payment_status' => $invoice->booking?->payment_status,
                    'issued_at'      => $invoice->issued_at?->toIso8601String(),
                    'due_at'         => $invoice->due_at?->toIso8601String(),
                    'created_at'     => $invoice->created_at->toIso8601String(),
                ];
            }),
            'meta'    => [
                'current_page' => $invoices->currentPage(),
                'last_page'    => $invoices->lastPage(),
                'per_page'     => $invoices->perPage(),
                'total'        => $invoices->total(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /api/bookings/my/reviews  — List user's reviews
    // ─────────────────────────────────────────────────────────────

    public function myReviews(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get reviews that either:
        // 1. Belong to a booking made by this user, OR
        // 2. Were submitted by this user (matching user_name) without a booking
        $reviews = \App\Models\Review::where(function ($query) use ($user) {
            $query->whereHas('booking', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->orWhere(function ($q) use ($user) {
                $q->whereNull('booking_id')
                  ->where('user_name', $user->name);
            });
        })
        ->with(['booking'])
        ->latest()
        ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $reviews->map(function ($review) {
                return [
                    'id'            => $review->id,
                    'booking_code'  => $review->booking->booking_code ?? null,
                    'car_model'     => $review->booking->car_model ?? null,
                    'service_type'  => $review->booking->service_type ?? null,
                    'rating'        => $review->rating,
                    'comment'       => $review->comment,
                    'status'        => $review->status,
                    'created_at'    => $review->created_at->toIso8601String(),
                ];
            }),
            'meta'    => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'per_page'     => $reviews->perPage(),
                'total'        => $reviews->total(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /api/bookings/statistics  — Stats (public)
    // ─────────────────────────────────────────────────────────────

    public function statistics(): JsonResponse
    {
        $stats = [
            'total_bookings'       => Booking::count(),
            'pending'              => Booking::where('status', 'pending')->count(),
            'confirmed'            => Booking::where('status', 'confirmed')->count(),
            'rejected'             => Booking::where('status', 'rejected')->count(),
            'in_progress'          => Booking::where('status', 'in_progress')->count(),
            'completed'            => Booking::where('status', 'completed')->count(),
            'cancelled'            => Booking::where('status', 'cancelled')->count(),
            'today_bookings'       => Booking::whereDate('created_at', today())->count(),
            'this_week_bookings'   => Booking::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month_bookings'  => Booking::whereMonth('created_at', now()->month)->count(),
        ];

        return response()->json($stats);
    }

    // ─────────────────────────────────────────────────────────────
    //  PATCH /api/bookings/{booking}/status  — Update status (protected)
    // ─────────────────────────────────────────────────────────────

    public function updateStatus(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'status'      => ['required', 'in:pending,confirmed,rejected,in_progress,issue,completed,cancelled'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['status'] === 'confirmed') {
            $this->bookingService->confirmBooking($booking, $validated['admin_notes'] ?? null);
        } elseif ($validated['status'] === 'rejected') {
            $this->bookingService->rejectBooking($booking, $validated['admin_notes'] ?? null);
        } elseif ($validated['status'] === 'in_progress') {
            $this->bookingService->markInProgress($booking, $validated['admin_notes'] ?? null);
        } elseif ($validated['status'] === 'issue') {
            $this->bookingService->markIssue($booking, $validated['admin_notes'] ?? null);
        } elseif ($validated['status'] === 'completed') {
            $this->bookingService->markCompleted($booking, $validated['admin_notes'] ?? null);
        } elseif ($validated['status'] === 'cancelled') {
            $this->bookingService->markCancelled($booking, $validated['admin_notes'] ?? null);
        } else {
            $booking->update($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status booking berhasil diperbarui.',
            'data'    => $this->formatBooking($booking->fresh()),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /api/bookings/{booking}/cancel  — Cancel (protected)
    // ─────────────────────────────────────────────────────────────

    public function cancel(Booking $booking): JsonResponse
    {
        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibatalkan.',
            'data'    => $this->formatBooking($booking->fresh()),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /api/bookings/{code}/reschedule  — Reschedule (public)
    // ─────────────────────────────────────────────────────────────

    public function reschedule(Request $request, string $code): JsonResponse
    {
        $booking = Booking::where('booking_code', strtoupper($code))->firstOrFail();

        // Validate booking can be rescheduled
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Booking hanya bisa direschedule jika status pending atau confirmed'
            ], 422);
        }

        $validated = $request->validate([
            'preferred_date' => 'required|date|after:today',
            'reason' => 'nullable|string|max:500'
        ]);

        // Check availability
        $slots = Booking::getAvailableSlots($validated['preferred_date']);
        if (!$slots['available']) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal yang dipilih penuh, silakan pilih tanggal lain'
            ], 422);
        }

        $oldDate = $booking->preferred_date;
        $booking->update([
            'preferred_date' => $validated['preferred_date'],
            'admin_notes' => "Rescheduled by customer from {$oldDate}. Reason: " . ($validated['reason'] ?? 'N/A')
        ]);

        // Notify admin
        $this->bookingService->rescheduleBooking($booking, $oldDate, $validated['preferred_date'], $validated['reason'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil direschedule'
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /api/bookings/{code}/cancel  — Cancel by customer (public)
    // ─────────────────────────────────────────────────────────────

    public function customerCancel(Request $request, string $code): JsonResponse
    {
        $booking = Booking::where('booking_code', strtoupper($code))->firstOrFail();

        // Validate booking can be cancelled
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Booking hanya bisa dibatalkan jika status pending atau confirmed'
            ], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'cancel_reason_category' => 'required|in:change_of_plans,found_other_service,price_issue,schedule_conflict,other'
        ]);

        $booking->update([
            'status' => 'cancelled',
            'admin_notes' => "Cancelled by customer. Category: {$validated['cancel_reason_category']}. Reason: {$validated['reason']}"
        ]);

        // Notify admin
        $this->bookingService->cancelBooking($booking, $validated['cancel_reason_category'], $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibatalkan'
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /api/bookings/availability  — Check availability (public)
    // ─────────────────────────────────────────────────────────────

    public function availability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date'
        ]);

        return response()->json(Booking::getAvailableSlots($validated['date']));
    }

    // ─────────────────────────────────────────────────────────────
    //  Private helpers
    // ─────────────────────────────────────────────────────────────

    private function formatBooking(Booking $booking): array
    {
        return [
            'id'             => $booking->id,
            'booking_code'   => $booking->booking_code,
            'name'           => $booking->name,
            'phone'          => $booking->phone,
            'email'          => $booking->email,
            'car_model'      => $booking->car_model,
            'vehicle_info'   => $booking->vehicle_info,
            'service_type'   => $booking->service_type,
            'preferred_date' => $booking->preferred_date?->format('Y-m-d'),
            'scheduled_at'   => $booking->scheduled_at?->toIso8601String(),
            'notes'          => $booking->notes,
            'admin_notes'    => $booking->admin_notes,
            'status'         => $booking->status,
            'status_label'   => $booking->status_label,
            'status_color'   => $booking->status_color,
            'payment_status' => $booking->payment_status,
            'payment_status_color' => $booking->payment_status_color,
            'created_at'     => $booking->created_at->toIso8601String(),
            'updated_at'     => $booking->updated_at->toIso8601String(),
            'has_review'     => $booking->hasReview(),
        ];
    }
}
