<?php

namespace App\Notifications;

use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class PaymentSuccessNotification extends BaseNotification
{
    use Queueable;

    public function __construct(
        public Payment $payment,
        public array $midtransData,
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
     * Get the payment method label.
     */
    private function getPaymentMethodLabel(?string $method): string
    {
        return match ($method) {
            'bank_transfer' => 'Transfer Bank',
            'credit_card'   => 'Kartu Kredit',
            'gopay'         => 'GoPay',
            'shopeepay'     => 'ShopeePay',
            'qris'          => 'QRIS',
            'indomaret'     => 'Indomaret',
            'alfamart'      => 'Alfamart',
            'echannel'      => 'Mandiri e-Channel',
            'cimb_clicks'   => 'CIMB Clicks',
            'bca_klikpay'   => 'BCA KlikPay',
            'bca_klikbca'   => 'BCA KlikBCA',
            'permata_va'    => 'Permata VA',
            default         => ucfirst($method ?? 'Unknown'),
        };
    }

    /**
     * Get the bank label.
     */
    private function getBankLabel(?string $bank): string
    {
        return strtoupper($bank ?? '-');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $payment = $this->payment;
        $invoice = $payment->invoice;
        $booking = $payment->booking;

        return [
            'format' => 'filament',
            'payment_id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'invoice_number' => $invoice->invoice_number,
            'booking_code' => $booking->booking_code,
            'customer_name' => $booking->name,
            'car_model' => $booking->car_model,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'payment_type' => $this->getPaymentMethodLabel($payment->payment_method),
            'bank' => $payment->bank,
            'va_number' => $payment->va_number,
            'transaction_id' => $payment->transaction_id,
            'paid_at' => $payment->paid_at?->format('d M Y H:i'),
            'title' => 'Pembayaran Berhasil',
            'message' => "Pembayaran {$booking->name} sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " telah diterima",
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
        $payment = $this->payment;
        $invoice = $payment->invoice;
        $booking = $payment->booking;

        $methodLabel = $this->getPaymentMethodLabel($payment->payment_method);
        $bankLabel = $this->getBankLabel($payment->bank);

        $body = "💰 *Pembayaran Berhasil!*\n\n";
        $body .= "📋 *No. Invoice:* `{$invoice->invoice_number}`\n";
        $body .= "👤 *Customer:* {$booking->name}\n";
        $body .= "🚗 *Mobil:* {$booking->car_model}\n";
        $body .= "💳 *Metode:* {$methodLabel}";
        
        if ($payment->bank) {
            $body .= " ({$bankLabel})";
        }
        
        if ($payment->va_number) {
            $body .= "\n🔢 *No. VA:* `{$payment->va_number}`";
        }
        
        $body .= "\n💵 *Jumlah:* Rp " . number_format($payment->amount, 0, ',', '.');
        $body .= "\n⏰ *Waktu:* " . $payment->paid_at?->format('d M Y H:i');
        $body .= "\n✅ *Status:* LUNAS";

        return Notification::make()
            ->title('💰 Pembayaran Berhasil!')
            ->body($body)
            ->success()
            ->icon('heroicon-o-check-circle')
            ->persistent()
            ->actions([
                Action::make('view')
                    ->button()
                    ->label('Lihat Payment')
                    ->url(route('filament.admin.resources.payments.view', ['record' => $payment->id]))
                    ->markAsRead(),
                Action::make('view_invoice')
                    ->label('Lihat Invoice')
                    ->url(route('filament.admin.resources.invoices.view', ['record' => $invoice->id]))
                    ->markAsRead(),
            ]);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $payment = $this->payment;
        $invoice = $payment->invoice;
        $booking = $payment->booking;
        $midtrans = $this->midtransData;

        return (new MailMessage())
            ->subject('💰 Pembayaran Berhasil - Invoice ' . $invoice->invoice_number)
            ->greeting('Halo Admin,')
            ->line('Pembayaran dari customer telah berhasil diterima!')
            ->line('')
            ->line('📋 **Detail Pembayaran:**')
            ->line('No. Invoice: ' . $invoice->invoice_number)
            ->line('No. Payment: ' . $payment->payment_number)
            ->line('Kode Booking: ' . $booking->booking_code)
            ->line('')
            ->line('👤 **Detail Customer:**')
            ->line('Nama: ' . $booking->name)
            ->line('Email: ' . $booking->email)
            ->line('WhatsApp: ' . $booking->phone)
            ->line('Mobil: ' . $booking->car_model)
            ->line('Layanan: ' . $booking->service_type)
            ->line('')
            ->line('💳 **Detail Pembayaran:**')
            ->line('Metode: ' . $this->getPaymentMethodLabel($payment->payment_method))
            ->line('Bank: ' . $this->getBankLabel($payment->bank))
            ->line($payment->va_number ? 'No. VA: ' . $payment->va_number : '')
            ->line('Jumlah: Rp ' . number_format($payment->amount, 0, ',', '.'))
            ->line('Status: ✅ LUNAS')
            ->line('Waktu: ' . $payment->paid_at?->format('d M Y H:i'))
            ->line('')
            ->line('🔗 **Detail Midtrans:**')
            ->line('Transaction ID: ' . ($midtrans['transaction_id'] ?? '-'))
            ->line('Order ID: ' . ($midtrans['order_id'] ?? '-'))
            ->line('Transaction Status: ' . ($midtrans['transaction_status'] ?? '-'))
            ->line('Fraud Status: ' . ($midtrans['fraud_status'] ?? '-'))
            ->line('Transaction Time: ' . ($midtrans['transaction_time'] ?? '-'))
            ->line('Settlement Time: ' . ($midtrans['settlement_time'] ?? '-'))
            ->line('')
            ->action('Lihat Payment', route('filament.admin.resources.payments.view', ['record' => $payment->id]))
            ->line('Terima kasih!');
    }
}
