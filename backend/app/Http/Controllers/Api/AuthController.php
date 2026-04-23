<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Review;
use App\Models\User;
use App\Services\SupabaseStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'profile_photo' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,jpg,webp'],
        ]);

        $profilePhotoUrl = null;
        if ($request->hasFile('profile_photo')) {
            $storageService = new SupabaseStorageService();
            $profilePhotoUrl = $storageService->upload($request->file('profile_photo'));
            
            if (!$profilePhotoUrl) {
                throw ValidationException::withMessages([
                    'profile_photo' => ['Gagal mengupload foto profil. Silakan coba lagi.'],
                ]);
            }
        }

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => 'user',
            'profile_photo' => $profilePhotoUrl,
        ]);

        $token = $user->createToken('customer-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil! Selamat datang di Young 911 Autowerks.',
            'data'    => [
                'user'  => $this->formatUser($user),
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($validated)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $user = Auth::user();

        if ($user->isAdmin()) {
            throw ValidationException::withMessages([
                'email' => ['Gunakan halaman login admin untuk masuk sebagai admin.'],
            ]);
        }

        $token = $user->createToken('customer-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil! Selamat datang kembali, ' . $user->name,
            'data'    => [
                'user'  => $this->formatUser($user),
                'token' => $token,
            ],
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Sampai jumpa!',
        ]);
    }

    /**
     * GET /api/auth/profile
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->formatUser($request->user()),
        ]);
    }

    /**
     * PUT /api/auth/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'profile_photo' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,jpg,webp'],
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $storageService = new SupabaseStorageService();
            
            // Delete old profile photo if exists
            if ($user->profile_photo) {
                $oldFilename = $storageService->extractFilename($user->profile_photo);
                if ($oldFilename) {
                    $storageService->delete($oldFilename);
                }
            }
            
            $profilePhotoUrl = $storageService->upload($request->file('profile_photo'));
            
            if (!$profilePhotoUrl) {
                throw ValidationException::withMessages([
                    'profile_photo' => ['Gagal mengupload foto profil. Silakan coba lagi.'],
                ]);
            }
            
            $validated['profile_photo'] = $profilePhotoUrl;
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data'    => $this->formatUser($user),
        ]);
    }

    /**
     * POST /api/auth/change-password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password'      => ['required'],
            'password'              => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini salah.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah.',
        ]);
    }

    /**
     * GET /api/auth/statistics — Customer dashboard statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        // Count bookings by user_id OR email (for bookings created before registration)
        $bookingsQuery = Booking::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('email', $user->email);
        });

        $stats = [
            'total_bookings'      => (clone $bookingsQuery)->count(),
            'pending'             => (clone $bookingsQuery)->where('status', 'pending')->count(),
            'confirmed'           => (clone $bookingsQuery)->where('status', 'confirmed')->count(),
            'in_progress'         => (clone $bookingsQuery)->where('status', 'in_progress')->count(),
            'completed'           => (clone $bookingsQuery)->where('status', 'completed')->count(),
            'cancelled'           => (clone $bookingsQuery)->where('status', 'cancelled')->count(),
            'pending_payment'     => (clone $bookingsQuery)->where('payment_status', 'pending')->count(),
            'paid'                => (clone $bookingsQuery)->where('payment_status', 'paid')->count(),
            'total_invoices'      => Invoice::whereIn('booking_id', (clone $bookingsQuery)->pluck('id'))->count(),
            'total_reviews'       => Review::whereIn('booking_id', (clone $bookingsQuery)->pluck('id'))->count(),
        ];

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'profile_photo' => $user->profile_photo,
            'role'       => $user->role,
            'created_at' => $user->created_at->toIso8601String(),
        ];
    }
}
