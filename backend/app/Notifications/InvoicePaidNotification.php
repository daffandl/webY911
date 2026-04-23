<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class InvoicePaidNotification extends BaseNotification
{
    use Queueable;

    public function __construct(
        public Invoice $invoice,
        public Booking $booking,
        public string $oldStatus,
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
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'format' => 'filament',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'customer_name' => $this->booking->name,
            'car_model' => $this->booking->car_model,
            'old_status' => $this->oldStatus,
            'new_status' => 'paid',
            'title' => 'Invoice Lunas',
            'message' => "Invoice {$this->invoice->invoice_number} dari {$this->booking->name} telah lunas",
            'icon' => 'heroicon-o-check-circle',
            'color' => 'success',
            'type' => 'payment',
        ];
    }

    /**
     * Get the Filament notification instance.
     */
    public function toFilamentNotification(): Notification
    {
        return Notification::make()
            ->title('💰 Invoice Lunas')
            ->body("Invoice {$this->invoice->invoice_number} dari {$this->booking->name} ({$this->booking->car_model}) telah lunas")
            ->success()
            ->icon('heroicon-o-check-circle')
            ->persistent()
            ->actions([
                Action::make('view')
                    ->button()
                    ->label('Lihat Invoice')
                    ->url(route('filament.admin.resources.invoices.view', ['record' => $this->invoice->id]))
                    ->markAsRead(),
            ]);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('💰 Invoice Lunas - ' . $this->invoice->invoice_number)
            ->greeting('Halo Admin,')
            ->line('Pembayaran invoice telah diterima!')
            ->line('**No. Invoice:** ' . $this->invoice->invoice_number)
            ->line('**Customer:** ' . $this->booking->name)
            ->line('**Mobil:** ' . $this->booking->car_model)
            ->line('**Total Pembayaran:** Rp ' . number_format($this->invoice->total, 0, ',', '.'))
            ->line('**Status Sebelumnya:** ' . $this->getStatusLabel($this->oldStatus))
            ->line('**Status Baru:** ✅ Lunas')
            ->action('Lihat Invoice', url('/admin/invoices/' . $this->invoice->id))
            ->line('Terima kasih!');
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'draft' => '📝 Draft',
            'sent' => '📤 Terkirim',
            'paid' => '✅ Lunas',
            'cancelled' => '🚫 Dibatalkan',
            default => ucfirst($status),
        };
    }
}
