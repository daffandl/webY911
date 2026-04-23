<?php

namespace App\Notifications;

use App\Models\Booking;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class BookingStatusChangedNotification extends BaseNotification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the status label and icon.
     */
    private function getStatusInfo(string $status): array
    {
        return match ($status) {
            'pending' => ['label' => 'Pending', 'icon' => 'heroicon-o-clock', 'color' => 'warning'],
            'confirmed' => ['label' => 'Dikonfirmasi', 'icon' => 'heroicon-o-check-circle', 'color' => 'success'],
            'rejected' => ['label' => 'Ditolak', 'icon' => 'heroicon-o-x-circle', 'color' => 'danger'],
            'in_progress' => ['label' => 'Dikerjakan', 'icon' => 'heroicon-o-wrench-screwdriver', 'color' => 'info'],
            'issue' => ['label' => 'Ada Masalah', 'icon' => 'heroicon-o-exclamation-triangle', 'color' => 'warning'],
            'completed' => ['label' => 'Selesai', 'icon' => 'heroicon-o-check-badge', 'color' => 'success'],
            'cancelled' => ['label' => 'Dibatalkan', 'icon' => 'heroicon-o-no-symbol', 'color' => 'gray'],
            default => ['label' => ucfirst($status), 'icon' => 'heroicon-o-question-mark-circle', 'color' => 'gray'],
        };
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $oldStatusInfo = $this->getStatusInfo($this->oldStatus);
        $newStatusInfo = $this->getStatusInfo($this->newStatus);

        return [
            'format' => 'filament',
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'customer_name' => $this->booking->name,
            'old_status' => $this->oldStatus,
            'old_status_label' => $oldStatusInfo['label'],
            'new_status' => $this->newStatus,
            'new_status_label' => $newStatusInfo['label'],
            'title' => 'Status Booking Berubah',
            'message' => "Booking {$this->booking->booking_code} ({$this->booking->name})",
            'icon' => 'heroicon-o-arrow-path',
            'color' => 'info',
            'type' => 'status_change',
            'old_status_icon' => $oldStatusInfo['icon'],
            'old_status_color' => $oldStatusInfo['color'],
            'new_status_icon' => $newStatusInfo['icon'],
            'new_status_color' => $newStatusInfo['color'],
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('🔄 Status Booking Berubah - ' . $this->booking->booking_code)
            ->greeting('Halo Admin,')
            ->line('Status booking telah berubah!')
            ->line('**Kode Booking:** ' . $this->booking->booking_code)
            ->line('**Nama Customer:** ' . $this->booking->name)
            ->line('**Mobil:** ' . $this->booking->car_model)
            ->line('**Status Sebelumnya:** ' . $this->getStatusLabel($this->oldStatus))
            ->line('**Status Baru:** ' . $this->getStatusLabel($this->newStatus))
            ->action('Lihat Booking', url('/admin/bookings/' . $this->booking->id))
            ->line('Terima kasih!');
    }
}
