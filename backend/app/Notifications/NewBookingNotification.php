<?php

namespace App\Notifications;

use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class NewBookingNotification extends BaseNotification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'format' => 'filament',
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'customer_name' => $this->booking->name,
            'car_model' => $this->booking->car_model,
            'service_type' => $this->booking->service_type,
            'preferred_date' => $this->booking->preferred_date?->format('d M Y'),
            'status' => $this->booking->status,
            'title' => 'Booking Baru Masuk',
            'message' => "{$this->booking->name} telah melakukan booking untuk {$this->booking->car_model}",
            'icon' => 'heroicon-o-calendar-days',
            'color' => 'success',
            'type' => 'new_booking',
        ];
    }

    /**
     * Get the Filament notification instance.
     */
    public function toFilamentNotification(): Notification
    {
        return Notification::make()
            ->title('📬 Booking Baru Masuk!')
            ->body("{$this->booking->name} telah melakukan booking untuk {$this->booking->car_model}")
            ->success()
            ->icon('heroicon-o-calendar-days')
            ->persistent()
            ->actions([
                Action::make('view')
                    ->button()
                    ->label('Lihat Booking')
                    ->url(route('filament.admin.resources.bookings.view', ['record' => $this->booking->id]))
                    ->markAsRead(),
            ]);
    }
}
