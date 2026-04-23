<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Services\SupabaseStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    protected SupabaseStorageService $storage;

    public function __construct(SupabaseStorageService $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Submit a review for a completed booking.
     */
    public function store(Request $request, ?string $code = null): JsonResponse
    {
        try {
            $booking = null;

            // If code is provided, validate booking
            if ($code) {
                $booking = Booking::where('booking_code', $code)->first();

                if (!$booking) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Booking tidak ditemukan.',
                    ], 404);
                }

                // Check if booking is completed
                if ($booking->status !== 'completed') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Review hanya dapat diberikan untuk booking yang telah selesai.',
                    ], 403);
                }

                // Check if review already exists
                if ($booking->hasReview()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah memberikan review untuk booking ini.',
                    ], 409);
                }
            }

            // Validate request
            $validated = $request->validate([
                'user_name' => 'required|string|max:255',
                'vehicle_info' => 'nullable|string|max:255',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|min:10|max:1000',
            ]);

            // Sanitize string inputs to ensure valid UTF-8
            $validated['user_name'] = mb_convert_encoding($validated['user_name'], 'UTF-8', 'UTF-8');
            $validated['comment'] = mb_convert_encoding($validated['comment'], 'UTF-8', 'UTF-8');
            if (isset($validated['vehicle_info'])) {
                $validated['vehicle_info'] = mb_convert_encoding($validated['vehicle_info'], 'UTF-8', 'UTF-8');
            }

            // Automatically use authenticated user's profile photo and name if available
            $profilePhotoPath = null;
            $userName = $validated['user_name'];
            
            if ($request->user()) {
                // Use authenticated user's profile photo
                if ($request->user()->profile_photo) {
                    $profilePhotoPath = $request->user()->profile_photo;
                }
                // Use authenticated user's name for consistency
                $userName = $request->user()->name;
            }

            // Create review - langsung approved tanpa verifikasi admin
            $review = Review::create([
                'booking_id' => $code ? $booking->id : null,
                'user_name' => $userName,
                'profile_photo' => $profilePhotoPath,
                'vehicle_info' => $validated['vehicle_info'] ?? ($booking->vehicle_info ?? $booking->car_model ?? null),
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'status' => 'approved', // Langsung approved, tanpa verifikasi admin
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review berhasil dikirim. Terima kasih atas ulasan Anda!',
                'data' => [
                    'review' => $review->toArray(),
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Review submission error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim review: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all approved reviews.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 20);
        
        $reviews = Review::with('booking')
            ->approved()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * Get reviews with statistics.
     */
    public function statistics(): JsonResponse
    {
        $approvedReviews = Review::approved();

        $total = $approvedReviews->count();
        $averageRating = $approvedReviews->avg('rating') ?? 0;

        // Get rating distribution efficiently
        $ratingDistribution = $approvedReviews
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $ratingDistribution[$i] ?? 0;
        }

        $recentReviews = Review::with('booking')
            ->approved()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'average_rating' => round($averageRating, 1),
                'rating_distribution' => $distribution,
                'recent_reviews' => $recentReviews,
            ],
        ]);
    }
}
