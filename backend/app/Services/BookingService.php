<?php

namespace App\Services;

use App\Mail\AdminNewBookingMail;
use App\Mail\UserBookingCancelledMail;
use App\Mail\UserBookingCompletedMail;
use App\Mail\UserBookingConfirmedMail;
use App\Mail\UserBookingInProgressMail;
use App\Mail\UserBookingIssueMail;
use App\Mail\UserBookingRejectedMail;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingStatusChangedNotification;
use App\Notifications\NewBookingNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingService
{
    public function __construct(
        private FonnteService $fonnte,
    ) {}

    // ─────────────────────────────────────────────────────────────
    //  Create Booking
    // ─────────────────────────────────────────────────────────────

    /**
     * Create a new booking and send notifications to admin.
     *
     * @param  array{
     *   name: string,
     *   phone: string,
     *   email: string,
     *   car_model: string,
     *   vehicle_info?: string|null,
     *   service_type: string,
     *   preferred_date: string,
     *   notes?: string|null,
     * } $data
     */
    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $booking = Booking::create([
                'name'          => $data['name'],
                'phone'         => $data['phone'],
                'email'         => $data['email'],
                'car_model'     => $data['car_model'],
                'vehicle_info'  => $data['vehicle_info'] ?? null,
                'service_type'  => $data['service_type'],
                'preferred_date' => $data['preferred_date'],
                'notes'         => $data['notes'] ?? null,
                'status'        => 'pending',
            ]);

            // Send WA receipt to user
            $this->safeWa(fn () => $this->fonnte->sendBookingReceipt($booking));

            // Send WA + Email notification to admin
            $this->safeWa(fn () => $this->fonnte->notifyAdminNewBooking($booking));
            $this->safeMail(fn () => $this->sendAdminEmail($booking));

            // Send Filament database notification to all admin users
            $this->sendFilamentNewBookingNotification($booking);

            return $booking;
        });
    }

    // ─────────────────────────────────────────────────────────────
    //  Confirm Booking
    // ─────────────────────────────────────────────────────────────

    /**
     * Confirm a booking and notify the user.
     */
    public function confirmBooking(Booking $booking, ?string $adminNotes = null): Booking
    {
        $oldStatus = $booking->status;

        $booking->update([
            'status'      => 'confirmed',
            'admin_notes' => $adminNotes,
        ]);

        $booking->refresh();

        // Notify user via WA + Email ONLY (no admin notification for status change)
        $this->safeWa(fn () => $this->fonnte->notifyUserConfirmed($booking));
        $this->safeMail(fn () => $this->sendUserConfirmedEmail($booking));

        // Send Filament database notification to all admin users
        $this->sendFilamentStatusChangedNotification($booking, $oldStatus);

        return $booking;
    }

    // ─────────────────────────────────────────────────────────────
    //  Reject Booking
    // ─────────────────────────────────────────────────────────────

    /**
     * Reject a booking and notify the user.
     */
    public function rejectBooking(Booking $booking, ?string $adminNotes = null): Booking
    {
        $oldStatus = $booking->status;

        $booking->update([
            'status'      => 'rejected',
            'admin_notes' => $adminNotes,
        ]);

        $booking->refresh();

        // Notify user via WA + Email
        $this->safeWa(fn () => $this->fonnte->notifyUserRejected($booking));
        $this->safeMail(fn () => $this->sendUserRejectedEmail($booking));

        // Send Filament database notification to all admin users
        $this->sendFilamentStatusChangedNotification($booking, $oldStatus);

        return $booking;
    }

    // ─────────────────────────────────────────────────────────────
    //  Mark In Progress
    // ─────────────────────────────────────────────────────────────

    /**
     * Mark booking as in_progress and notify the user.
     */
    public function markInProgress(Booking $booking, ?string $adminNotes = null): Booking
    {
        $oldStatus = $booking->status;

        $booking->update([
            'status'      => 'in_progress',
            'admin_notes' => $adminNotes,
        ]);

        $booking->refresh();

        // Notify user via WA + Email
        $this->safeWa(fn () => $this->fonnte->notifyUserInProgress($booking));
        $this->safeMail(fn () => $this->sendUserInProgressEmail($booking));

        // Send Filament database notification to all admin users
        $this->sendFilamentStatusChangedNotification($booking, $oldStatus);

        return $booking;
    }

    // ─────────────────────────────────────────────────────────────
    //  Mark Issue
    // ─────────────────────────────────────────────────────────────

    /**
     * Mark booking as issue (ada masalah) and notify the user.
     */
    public function markIssue(Booking $booking, ?string $adminNotes = null): Booking
    {
        $oldStatus = $booking->status;

        $booking->update([
            'status'      => 'issue',
            'admin_notes' => $adminNotes,
        ]);

        $booking->refresh();

        // Notify user via WA + Email
        $this->safeWa(fn () => $this->fonnte->notifyUserIssue($booking));
        $this->safeMail(fn () => $this->sendUserIssueEmail($booking));

        // Send Filament database notification to all admin users
        $this->sendFilamentStatusChangedNotification($booking, $oldStatus);

        return $booking;
    }

    // ─────────────────────────────────────────────────────────────
    //  Mark Completed
    // ─────────────────────────────────────────────────────────────

    /**
     * Mark booking as completed and notify the user.
     */
    public function markCompleted(Booking $booking, ?string $adminNotes = null): Booking
    {
        $oldStatus = $booking->status;

        $booking->update([
            'status'      => 'completed',
            'admin_notes' => $adminNotes,
        ]);

        $booking->refresh();

        // Notify user via WA + Email
        $this->safeWa(fn () => $this->fonnte->notifyUserCompleted($booking));
        $this->safeMail(fn () => $this->sendUserCompletedEmail($booking));

        // Send review request via WA (after a short delay)
        $this->safeWa(fn () => $this->fonnte->requestReview($booking));

        // Send Filament database notification to all admin users
        $this->sendFilamentStatusChangedNotification($booking, $oldStatus);

        return $booking;
    }

    // ─────────────────────────────────────────────────────────────
    //  Mark Cancelled
    // ─────────────────────────────────────────────────────────────

    /**
     * Mark booking as cancelled and notify the user.
     */
    public function markCancelled(Booking $booking, ?string $adminNotes = null): Booking
    {
        $oldStatus = $booking->status;

        $booking->update([
            'status'      => 'cancelled',
            'admin_notes' => $adminNotes,
        ]);

        $booking->refresh();

        // Notify user via WA + Email
        $this->safeWa(fn () => $this->fonnte->notifyUserCancelled($booking));
        $this->safeMail(fn () => $this->sendUserCancelledEmail($booking));

        // Send Filament database notification to all admin users
        $this->sendFilamentStatusChangedNotification($booking, $oldStatus);

        return $booking;
    }

    // ─────────────────────────────────────────────────────────────
    //  Reschedule Booking (Customer)
    // ─────────────────────────────────────────────────────────────

    /**
     * Reschedule a booking (customer-initiated) and notify admin.
     */
    public function rescheduleBooking(Booking $booking, string $oldDate, string $newDate, ?string $reason = null): Booking
    {
        // Notify admin via WA
        $this->safeWa(fn () => $this->fonnte->notifyAdminBookingRescheduled($booking, $oldDate));

        return $booking;
    }

    // ─────────────────────────────────────────────────────────────
    //  Cancel Booking (Customer)
    // ─────────────────────────────────────────────────────────────

    /**
     * Cancel a booking (customer-initiated), track cancellation reason, and notify admin.
     */
    public function cancelBooking(Booking $booking, string $reasonCategory, string $reason): Booking
    {
        // Track cancellation for analytics
        DB::table('booking_cancellations')->insert([
            'booking_id' => $booking->id,
            'reason_category' => $reasonCategory,
            'reason_text' => $reason,
            'cancelled_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Notify admin via WA
        $this->safeWa(fn () => $this->fonnte->notifyAdminBookingCancelled($booking));

        return $booking;
    }

    // ─────────────────────────────────────────────────────────────
    //  Filament Notification helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Send Filament database notification to all admin users for new booking.
     */
    private function sendFilamentNewBookingNotification(Booking $booking): void
    {
        // Send database notification to all admin users
        User::where('role', 'admin')->get()->each(function (User $admin) use ($booking) {
            $admin->notify(new NewBookingNotification($booking));
        });

        // Also send in-app notification (for currently logged-in admin)
        Notification::make()
            ->title('📬 Booking Baru Masuk!')
            ->body("{$booking->name} telah melakukan booking untuk {$booking->car_model}")
            ->success()
            ->icon('heroicon-o-calendar-days')
            ->iconColor('success')
            ->persistent()
            ->actions([
                Action::make('view')
                    ->button()
                    ->label('Lihat Booking')
                    ->url(route('filament.admin.resources.bookings.view', ['record' => $booking->id])),
            ])
            ->send();
    }

    /**
     * Send Filament database notification to all admin users for status change.
     */
    private function sendFilamentStatusChangedNotification(
        Booking $booking,
        string $oldStatus,
    ): void {
        $newStatus = $booking->status;

        // Send database notification to all admin users
        User::where('role', 'admin')->get()->each(function (User $admin) use ($booking, $oldStatus, $newStatus) {
            $admin->notify(new BookingStatusChangedNotification($booking, $oldStatus, $newStatus));
        });

        // Get status labels and colors
        $statusInfo = $this->getStatusInfo($newStatus);

        // Also send in-app notification (for currently logged-in admin)
        Notification::make()
            ->title('🔄 Status Booking Berubah')
            ->body("Booking {$booking->booking_code} ({$booking->name}) sekarang: {$statusInfo['label']}")
            ->color($statusInfo['color'])
            ->icon($statusInfo['icon'])
            ->iconColor($statusInfo['color'])
            ->persistent()
            ->actions([
                Action::make('view')
                    ->button()
                    ->label('Lihat Booking')
                    ->url(route('filament.admin.resources.bookings.view', ['record' => $booking->id])),
            ])
            ->send();
    }

    /**
     * Get status label, color, and icon.
     */
    private function getStatusInfo(string $status): array
    {
        return match ($status) {
            'pending' => [
                'label' => '⏳ Pending',
                'color' => 'warning',
                'icon' => 'heroicon-o-clock',
            ],
            'confirmed' => [
                'label' => '✅ Confirmed',
                'color' => 'success',
                'icon' => 'heroicon-o-check-circle',
            ],
            'rejected' => [
                'label' => '❌ Rejected',
                'color' => 'danger',
                'icon' => 'heroicon-o-x-circle',
            ],
            'in_progress' => [
                'label' => '🔧 In Progress',
                'color' => 'primary',
                'icon' => 'heroicon-o-wrench-screwdriver',
            ],
            'issue' => [
                'label' => '⚠️ Issue',
                'color' => 'warning',
                'icon' => 'heroicon-o-exclamation-triangle',
            ],
            'completed' => [
                'label' => '✨ Completed',
                'color' => 'success',
                'icon' => 'heroicon-o-check-badge',
            ],
            'cancelled' => [
                'label' => '🚫 Cancelled',
                'color' => 'gray',
                'icon' => 'heroicon-o-no-symbol',
            ],
            default => [
                'label' => ucfirst($status),
                'color' => 'gray',
                'icon' => 'heroicon-o-question-mark-circle',
            ],
        };
    }

    // ─────────────────────────────────────────────────────────────
    //  Mail helpers
    // ─────────────────────────────────────────────────────────────

    private function sendAdminEmail(Booking $booking): void
    {
        $adminEmail = config('mail.admin_email', config('mail.from.address'));
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new AdminNewBookingMail($booking));
        }
    }

    private function sendUserConfirmedEmail(Booking $booking): void
    {
        if ($booking->email) {
            Mail::to($booking->email)->send(new UserBookingConfirmedMail($booking));
        }
    }

    private function sendUserRejectedEmail(Booking $booking): void
    {
        if ($booking->email) {
            Mail::to($booking->email)->send(new UserBookingRejectedMail($booking));
        }
    }

    private function sendUserInProgressEmail(Booking $booking): void
    {
        if ($booking->email) {
            Mail::to($booking->email)->send(new UserBookingInProgressMail($booking));
        }
    }

    private function sendUserIssueEmail(Booking $booking): void
    {
        if ($booking->email) {
            Mail::to($booking->email)->send(new UserBookingIssueMail($booking));
        }
    }

    private function sendUserCompletedEmail(Booking $booking): void
    {
        if ($booking->email) {
            Mail::to($booking->email)->send(new UserBookingCompletedMail($booking));
        }
    }

    private function sendUserCancelledEmail(Booking $booking): void
    {
        if ($booking->email) {
            Mail::to($booking->email)->send(new UserBookingCancelledMail($booking));
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  Safe wrappers — notifications must not break the main flow
    // ─────────────────────────────────────────────────────────────

    private function safeWa(callable $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            Log::error('BookingService WA error: ' . $e->getMessage());
        }
    }

    private function safeMail(callable $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            Log::error('BookingService Mail error: ' . $e->getMessage());
        }
    }
}
