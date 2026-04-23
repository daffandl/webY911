<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    /**
     * Capture the current status before the record is saved,
     * so we can detect status changes in afterSave().
     */
    protected ?string $statusBeforeSave = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Store the original status before saving.
     */
    protected function beforeSave(): void
    {
        $this->statusBeforeSave = $this->record->getOriginal('status');
    }

    /**
     * After saving, check if status changed and trigger the appropriate notification.
     * This ensures notifications fire even when admin edits via the form directly.
     */
    protected function afterSave(): void
    {
        $newStatus = $this->record->status;

        // No status change — show simple saved notification
        if ($this->statusBeforeSave === $newStatus) {
            Notification::make()
                ->title('✅ Booking berhasil diperbarui')
                ->body("Data {$this->record->name} telah disimpan.")
                ->success()
                ->icon('heroicon-o-check-circle')
                ->send();
            return;
        }

        $booking = $this->record;
        $oldStatus = $this->statusBeforeSave;

        // Use BookingService to handle all notifications (WA, Email, Filament)
        $bookingService = app(\App\Services\BookingService::class);

        $notificationTitle = null;
        $notificationColor = 'success';

        switch ($newStatus) {
            case 'confirmed':
                $bookingService->confirmBooking($booking, $booking->admin_notes);
                $notificationTitle = '✅ Booking dikonfirmasi';
                break;

            case 'rejected':
                $bookingService->rejectBooking($booking, $booking->admin_notes);
                $notificationTitle = '❌ Booking ditolak';
                $notificationColor = 'warning';
                break;

            case 'in_progress':
                $bookingService->markInProgress($booking, $booking->admin_notes);
                $notificationTitle = '🔧 Pengerjaan dimulai';
                $notificationColor = 'info';
                break;

            case 'issue':
                $bookingService->markIssue($booking, $booking->admin_notes);
                $notificationTitle = '⚠️ Masalah dilaporkan';
                $notificationColor = 'warning';
                break;

            case 'completed':
                $bookingService->markCompleted($booking, $booking->admin_notes);
                $notificationTitle = '✨ Layanan selesai';
                break;

            case 'cancelled':
                $bookingService->markCancelled($booking, $booking->admin_notes);
                $notificationTitle = '🚫 Booking dibatalkan';
                $notificationColor = 'danger';
                break;
        }

        if ($notificationTitle) {
            Notification::make()
                ->title($notificationTitle)
                ->body("Notifikasi WA, Email & Database telah dikirim ke {$booking->name}.")
                ->color($notificationColor)
                ->icon('heroicon-o-bell-alert')
                ->persistent()
                ->send();
        }
    }
}
