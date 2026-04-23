<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class InvoiceCreatedNotification extends BaseNotification
{
    use Queueable;

    public function __construct(
        public Invoice $invoice,
        public Booking $booking,
        public ?string $oldStatus = null,
        public ?string $newStatus = null,
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
        $isStatusChange = $this->oldStatus && $this->newStatus;

        return [
            'format' => 'filament',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'customer_name' => $this->booking->name,
            'car_model' => $this->booking->car_model,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'is_status_change' => $isStatusChange,
            'title' => $isStatusChange ? 'Status Invoice Berubah' : 'Invoice Baru Dibuat',
            'message' => $isStatusChange
                ? "Invoice {$this->invoice->invoice_number} ({$this->booking->name}) berubah status"
                : "Invoice {$this->invoice->invoice_number} untuk {$this->booking->name}",
            'icon' => $isStatusChange ? 'heroicon-o-arrow-path' : 'heroicon-o-document-text',
            'color' => $isStatusChange ? 'info' : 'info',
            'type' => 'invoice',
        ];
    }

    /**
     * Get the Filament notification instance.
     */
    public function toFilamentNotification(): Notification
    {
        $isStatusChange = $this->oldStatus && $this->newStatus;

        return Notification::make()
            ->title($isStatusChange ? '🔄 Status Invoice Berubah' : '📄 Invoice Baru Dibuat')
            ->body($isStatusChange
                ? "Invoice {$this->invoice->invoice_number} ({$this->booking->name}) berubah status dari {$this->getStatusLabel($this->oldStatus)} menjadi {$this->getStatusLabel($this->newStatus)}"
                : "Invoice {$this->invoice->invoice_number} untuk {$this->booking->name} ({$this->booking->car_model}) telah dibuat"
            )
            ->info()
            ->icon('heroicon-o-document-text')
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
        $isStatusChange = $this->oldStatus && $this->newStatus;

        return (new MailMessage())
            ->subject($isStatusChange
                ? '🔄 Status Invoice Berubah - ' . $this->invoice->invoice_number
                : '📄 Invoice Baru - ' . $this->invoice->invoice_number
            )
            ->greeting('Halo Admin,')
            ->line($isStatusChange
                ? 'Status invoice telah berubah!'
                : 'Invoice baru telah dibuat!'
            )
            ->line('**No. Invoice:** ' . $this->invoice->invoice_number)
            ->line('**Customer:** ' . $this->booking->name)
            ->line('**Mobil:** ' . $this->booking->car_model)
            ->line('**Total:** Rp ' . number_format($this->invoice->total, 0, ',', '.'))
            ->when($isStatusChange, function ($mail) {
                return $mail
                    ->line('**Status Sebelumnya:** ' . $this->getStatusLabel($this->oldStatus))
                    ->line('**Status Baru:** ' . $this->getStatusLabel($this->newStatus));
            })
            ->when(!$isStatusChange, function ($mail) {
                return $mail->line('**Status:** ' . $this->getStatusLabel($this->invoice->status));
            })
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
