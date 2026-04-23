<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    private string $apiKey;
    private string $adminTarget;
    private string $baseUrl = 'https://api.fonnte.com/send';

    public function __construct()
    {
        $this->apiKey      = config('services.fonnte.api_key', '');
        $this->adminTarget = config('services.fonnte.target', '');
    }

    // ─────────────────────────────────────────────────────────────
    //  Public helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Notify ADMIN about a new booking.
     */
    public function notifyAdminNewBooking(Booking $booking): bool
    {
        $message = $this->buildAdminNewBookingMessage($booking);
        return $this->send($this->adminTarget, $message);
    }

    /**
     * Notify USER that their booking was confirmed.
     */
    public function notifyUserConfirmed(Booking $booking): bool
    {
        $message = $this->buildUserConfirmedMessage($booking);
        return $this->send($booking->phone, $message);
    }

    /**
     * Notify USER that their booking was rejected.
     */
    public function notifyUserRejected(Booking $booking): bool
    {
        $message = $this->buildUserRejectedMessage($booking);
        return $this->send($booking->phone, $message);
    }

    /**
     * Notify USER that their booking is now in progress (sedang dikerjakan).
     */
    public function notifyUserInProgress(Booking $booking): bool
    {
        $message = $this->buildUserInProgressMessage($booking);
        return $this->send($booking->phone, $message);
    }

    /**
     * Notify USER that there is an issue with their booking (ada masalah).
     */
    public function notifyUserIssue(Booking $booking): bool
    {
        $message = $this->buildUserIssueMessage($booking);
        return $this->send($booking->phone, $message);
    }

    /**
     * Notify USER that their booking service is completed (selesai).
     */
    public function notifyUserCompleted(Booking $booking): bool
    {
        $message = $this->buildUserCompletedMessage($booking);
        return $this->send($booking->phone, $message);
    }

    /**
     * Notify USER to leave a review after booking is completed.
     */
    public function requestReview(Booking $booking): bool
    {
        $message = $this->buildReviewRequestMessage($booking);
        return $this->send($booking->phone, $message);
    }

    /**
     * Notify USER that their booking has been cancelled (dibatalkan).
     */
    public function notifyUserCancelled(Booking $booking): bool
    {
        $message = $this->buildUserCancelledMessage($booking);
        return $this->send($booking->phone, $message);
    }

    /**
     * Notify USER that their invoice is ready.
     */
    public function notifyUserInvoice(\App\Models\Invoice $invoice): bool
    {
        $message = $this->buildUserInvoiceMessage($invoice);
        return $this->send($invoice->booking->phone, $message);
    }

    /**
     * Notify USER that their invoice has been paid.
     */
    public function notifyUserInvoicePaid(\App\Models\Invoice $invoice): bool
    {
        $message = $this->buildUserInvoicePaidMessage($invoice);
        return $this->send($invoice->booking->phone, $message);
    }

    /**
     * Notify USER that their invoice has been cancelled.
     */
    public function notifyUserInvoiceCancelled(\App\Models\Invoice $invoice): bool
    {
        $message = $this->buildUserInvoiceCancelledMessage($invoice);
        return $this->send($invoice->booking->phone, $message);
    }

    /**
     * Notify USER about payment link for invoice.
     */
    public function notifyUserPaymentLink(\App\Models\Booking $booking, \App\Models\Invoice $invoice, string $paymentUrl): bool
    {
        $message = $this->buildUserPaymentLinkMessage($booking, $invoice, $paymentUrl);
        return $this->send($booking->phone, $message);
    }

    /**
     * Notify ADMIN that customer rescheduled booking.
     */
    public function notifyAdminBookingRescheduled(Booking $booking, string $oldDate): bool
    {
        $message = $this->buildAdminBookingRescheduledMessage($booking, $oldDate);
        return $this->send($this->adminTarget, $message);
    }

    /**
     * Notify ADMIN that customer cancelled booking.
     */
    public function notifyAdminBookingCancelled(Booking $booking): bool
    {
        $message = $this->buildAdminBookingCancelledMessage($booking);
        return $this->send($this->adminTarget, $message);
    }

    /**
     * Send booking confirmation receipt to USER (right after booking created).
     */
    public function sendBookingReceipt(Booking $booking): bool
    {
        $message = $this->buildBookingReceiptMessage($booking);
        return $this->send($booking->phone, $message);
    }

    /**
     * Generic send — kept for backward compatibility.
     */
    public function sendMessage(string $target, string $message): bool
    {
        return $this->send($target, $message);
    }

    // ─────────────────────────────────────────────────────────────
    //  Legacy aliases (used by old BookingController)
    // ─────────────────────────────────────────────────────────────

    public function sendBookingConfirmation(Booking $booking): bool
    {
        return $this->sendBookingReceipt($booking);
    }

    public function sendAdminNotification(Booking $booking): bool
    {
        return $this->notifyAdminNewBooking($booking);
    }

    public function sendStatusUpdate(Booking $booking): bool
    {
        return match ($booking->status) {
            'confirmed'   => $this->notifyUserConfirmed($booking),
            'rejected'    => $this->notifyUserRejected($booking),
            'in_progress' => $this->notifyUserInProgress($booking),
            'issue'       => $this->notifyUserIssue($booking),
            'completed'   => $this->notifyUserCompleted($booking),
            'cancelled'   => $this->notifyUserCancelled($booking),
            default       => $this->send($booking->phone, $this->buildGenericStatusMessage($booking)),
        };
    }

    public function sendCancellationNotification(Booking $booking): bool
    {
        return $this->notifyUserRejected($booking);
    }

    // ─────────────────────────────────────────────────────────────
    //  Core HTTP sender
    // ─────────────────────────────────────────────────────────────

    private function send(string $target, string $message): bool
    {
        // Normalize phone number to international format (628xxx)
        $target = $this->normalizePhone($target);

        if (empty($this->apiKey) || $this->apiKey === 'your-fonnte-api-key') {
            Log::info("[Fonnte MOCK] To: {$target}\n{$message}");
            return true;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->post($this->baseUrl, [
                'target'  => $target,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("Fonnte: message sent to {$target}");
                return true;
            }

            Log::error("Fonnte: failed to {$target} — " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('Fonnte API error: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  Phone number normalizer
    // ─────────────────────────────────────────────────────────────

    /**
     * Normalize phone number to international format for Fonnte API.
     * Examples:
     *   08123456789  → 628123456789
     *   +628123456789 → 628123456789
     *   628123456789  → 628123456789 (unchanged)
     */
    private function normalizePhone(string $phone): string
    {
        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        // If starts with 0, replace with 62 (Indonesia)
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        // If starts with 62, it's already correct
        // If starts with something else, assume it's already international
        return $digits;
    }

    // ─────────────────────────────────────────────────────────────
    //  Message builders
    // ─────────────────────────────────────────────────────────────

    private function buildAdminNewBookingMessage(Booking $booking): string
    {
        $date  = $booking->preferred_date
            ? $booking->preferred_date->format('d M Y')
            : '-';
        $notes = $booking->notes ?? '-';

        return <<<MSG
🔔 *BOOKING BARU — Young 911 Autowerks*

📋 *Kode Booking:* `{$booking->booking_code}`
👤 *Nama:* {$booking->name}
📱 *WhatsApp:* {$booking->phone}
📧 *Email:* {$booking->email}
🚗 *Tipe Mobil:* {$booking->car_model}
🔧 *Layanan:* {$booking->service_type}
📅 *Tanggal:* {$date}
📝 *Catatan:* {$notes}

Silakan buka Filament Admin untuk konfirmasi atau tolak booking ini.
MSG;
    }

    private function buildBookingReceiptMessage(Booking $booking): string
    {
        $date = $booking->preferred_date
            ? $booking->preferred_date->format('d M Y')
            : '-';

        $trackUrl = config('app.frontend_url', 'http://localhost:3000')
            . '/tracking?code=' . $booking->booking_code;

        return <<<MSG
✅ *Booking Diterima — Young 911 Autowerks*

Halo *{$booking->name}*, booking Anda telah kami terima!

📋 *Kode Booking:* `{$booking->booking_code}`
🚗 *Tipe Mobil:* {$booking->car_model}
🔧 *Layanan:* {$booking->service_type}
📅 *Tanggal:* {$date}
⏳ *Status:* Menunggu Konfirmasi

🔗 *Cek Status Booking:*
{$trackUrl}

Tim kami akan segera menghubungi Anda untuk konfirmasi.
Terima kasih telah mempercayai Young 911 Autowerks! 🙏
MSG;
    }

    private function buildUserConfirmedMessage(Booking $booking): string
    {
        $date = $booking->scheduled_at
            ? $booking->scheduled_at->format('d M Y, H:i')
            : ($booking->preferred_date ? $booking->preferred_date->format('d M Y') : '-');

        $trackUrl = config('app.frontend_url', 'http://localhost:3000')
            . '/tracking?code=' . $booking->booking_code;

        return <<<MSG
🎉 *Booking DIKONFIRMASI — Young 911 Autowerks*

Halo *{$booking->name}*, booking Anda telah dikonfirmasi!

📋 *Kode Booking:* `{$booking->booking_code}`
🚗 *Tipe Mobil:* {$booking->car_model}
🔧 *Layanan:* {$booking->service_type}
📅 *Jadwal:* {$date}
✅ *Status:* Dikonfirmasi

📍 *Lokasi:* Jl. Raya Utama No. 911, Jakarta
📞 *Kontak:* +62 812 3456 7890

🔗 *Cek Status:* {$trackUrl}

Sampai jumpa! 🚗✨
MSG;
    }

    private function buildUserRejectedMessage(Booking $booking): string
    {
        $reason = $booking->admin_notes ?? 'Tidak ada keterangan tambahan.';

        $trackUrl = config('app.frontend_url', 'http://localhost:3000')
            . '/tracking?code=' . $booking->booking_code;

        return <<<MSG
❌ *Booking DITOLAK — Young 911 Autowerks*

Halo *{$booking->name}*, mohon maaf booking Anda tidak dapat kami proses.

📋 *Kode Booking:* `{$booking->booking_code}`
🚗 *Tipe Mobil:* {$booking->car_model}
🔧 *Layanan:* {$booking->service_type}
❌ *Status:* Ditolak
📝 *Alasan:* {$reason}

Silakan hubungi kami untuk informasi lebih lanjut atau buat booking baru.
📞 *Kontak:* +62 812 3456 7890

🔗 *Cek Status:* {$trackUrl}
MSG;
    }

    private function buildUserInProgressMessage(Booking $booking): string
    {
        $trackUrl = config('app.frontend_url', 'http://localhost:3000')
            . '/tracking?code=' . $booking->booking_code;

        $notes = $booking->admin_notes ?? '-';

        return <<<MSG
🔧 *Kendaraan Sedang Dikerjakan — Young 911 Autowerks*

Halo *{$booking->name}*, kendaraan Anda sedang dalam proses pengerjaan!

📋 *Kode Booking:* `{$booking->booking_code}`
🚗 *Tipe Mobil:* {$booking->car_model}
🔧 *Layanan:* {$booking->service_type}
⏳ *Status:* Sedang Dikerjakan
📝 *Catatan Teknisi:* {$notes}

Tim teknisi kami sedang menangani kendaraan Anda. Kami akan segera memberikan update.

🔗 *Pantau Status:* {$trackUrl}

Terima kasih atas kesabaran Anda! 🚗✨
MSG;
    }

    private function buildUserIssueMessage(Booking $booking): string
    {
        $trackUrl = config('app.frontend_url', 'http://localhost:3000')
            . '/tracking?code=' . $booking->booking_code;

        $issue = $booking->admin_notes ?? 'Tim kami akan segera menghubungi Anda untuk informasi lebih lanjut.';

        return <<<MSG
⚠️ *Ada Masalah pada Kendaraan Anda — Young 911 Autowerks*

Halo *{$booking->name}*, tim teknisi kami menemukan kendala pada kendaraan Anda.

📋 *Kode Booking:* `{$booking->booking_code}`
🚗 *Tipe Mobil:* {$booking->car_model}
🔧 *Layanan:* {$booking->service_type}
⚠️ *Status:* Ada Masalah
📝 *Detail Masalah:* {$issue}

Mohon segera hubungi kami untuk mendiskusikan langkah selanjutnya.
📞 *Kontak:* +62 812 3456 7890

🔗 *Cek Status:* {$trackUrl}
MSG;
    }

    private function buildUserCompletedMessage(Booking $booking): string
    {
        $trackUrl = config('app.frontend_url', 'http://localhost:3000')
            . '/tracking?code=' . $booking->booking_code;

        $notes = $booking->admin_notes ?? '-';

        return <<<MSG
✨ *Layanan SELESAI — Young 911 Autowerks*

Halo *{$booking->name}*, kendaraan Anda telah selesai dikerjakan!

📋 *Kode Booking:* `{$booking->booking_code}`
🚗 *Tipe Mobil:* {$booking->car_model}
🔧 *Layanan:* {$booking->service_type}
✅ *Status:* Selesai
📝 *Catatan:* {$notes}

Kendaraan Anda sudah siap untuk diambil. Silakan datang ke bengkel kami.
📍 *Lokasi:* Jl. Raya Utama No. 911, Jakarta
📞 *Kontak:* +62 812 3456 7890

🔗 *Detail:* {$trackUrl}

Terima kasih telah mempercayai Young 911 Autowerks! 🙏
MSG;
    }

    private function buildReviewRequestMessage(Booking $booking): string
    {
        $reviewUrl = config('app.frontend_url', 'http://localhost:3000')
            . '/tracking?code=' . $booking->booking_code;

        return <<<MSG
⭐ *Bagaimana Pengalaman Anda? — Young 911 Autowerks*

Halo *{$booking->name}*, terima kasih telah menggunakan layanan kami!

📋 *Kode Booking:* `{$booking->booking_code}`
🚗 *Tipe Mobil:* {$booking->car_model}

Kami sangat menghargai pendapat Anda. Mohon luangkan waktu untuk memberikan review tentang layanan kami.

🔗 *BERI REVIEW:*
{$reviewUrl}

Review Anda sangat berarti untuk peningkatan kualitas layanan kami. Terima kasih! 🙏
MSG;
    }

    private function buildUserCancelledMessage(Booking $booking): string
    {
        $trackUrl = config('app.frontend_url', 'http://localhost:3000')
            . '/tracking?code=' . $booking->booking_code;

        $reason = $booking->admin_notes ?? 'Tidak ada keterangan tambahan.';

        return <<<MSG
🚫 *Booking DIBATALKAN — Young 911 Autowerks*

Halo *{$booking->name}*, booking Anda telah dibatalkan.

📋 *Kode Booking:* `{$booking->booking_code}`
🚗 *Tipe Mobil:* {$booking->car_model}
🔧 *Layanan:* {$booking->service_type}
🚫 *Status:* Dibatalkan
📝 *Keterangan:* {$reason}

Jika ada pertanyaan, silakan hubungi kami.
📞 *Kontak:* +62 812 3456 7890

🔗 *Cek Status:* {$trackUrl}
MSG;
    }

    private function buildGenericStatusMessage(Booking $booking): string
    {
        $statusLabel = $booking->status_label ?? ucfirst($booking->status);

        return <<<MSG
📋 *Update Status Booking — Young 911 Autowerks*

Halo *{$booking->name}*,

Kode Booking: `{$booking->booking_code}`
Status terbaru: *{$statusLabel}*

Terima kasih telah mempercayai Young 911 Autowerks!
MSG;
    }

    private function buildUserInvoiceMessage(\App\Models\Invoice $invoice): string
    {
        $booking  = $invoice->booking;
        $printUrl = url('/invoice/' . $invoice->invoice_number . '/print');
        $total    = 'Rp ' . number_format($invoice->total, 0, ',', '.');
        $itemCount = $invoice->items->count();

        return <<<MSG
🧧 *Invoice Siap — Young 911 Autowerks*

Halo *{$booking->name}*, invoice untuk layanan kendaraan Anda telah diterbitkan!

📋 *No. Invoice:* `{$invoice->invoice_number}`
🚗 *Kendaraan:* {$booking->car_model}
🔧 *Layanan:* {$booking->service_type}
📝 *Kode Booking:* `{$booking->booking_code}`
📊 *Jumlah Item:* {$itemCount} item
💰 *Total Tagihan:* *{$total}*
📊 *Status:* {$invoice->status_label}

🔗 *Lihat & Unduh Invoice:*
{$printUrl}

Silakan klik link di atas untuk melihat detail dan mencetak invoice Anda.
Terima kasih telah mempercayai Young 911 Autowerks! 🙏
MSG;
    }

    private function buildUserInvoicePaidMessage(\App\Models\Invoice $invoice): string
    {
        $booking  = $invoice->booking;
        $total    = 'Rp ' . number_format($invoice->total, 0, ',', '.');

        return <<<MSG
✅ *Pembayaran Diterima — Young 911 Autowerks*

Halo *{$booking->name}*, pembayaran invoice Anda telah kami terima!

📋 *No. Invoice:* `{$invoice->invoice_number}`
🚗 *Kendaraan:* {$booking->car_model}
💰 *Total Pembayaran:* *{$total}*
✅ *Status:* LUNAS

Terima kasih atas pembayaran Anda. Kendaraan sudah siap untuk diambil atau akan segera diselesaikan.
📞 *Kontak:* +62 812 3456 7890

Terima kasih telah mempercayai Young 911 Autowerks! 🙏
MSG;
    }

    private function buildUserInvoiceCancelledMessage(\App\Models\Invoice $invoice): string
    {
        $booking  = $invoice->booking;

        return <<<MSG
🚫 *Invoice Dibatalkan — Young 911 Autowerks*

Halo *{$booking->name}*, invoice Anda telah dibatalkan.

📋 *No. Invoice:* `{$invoice->invoice_number}`
🚗 *Kendaraan:* {$booking->car_model}
🔧 *Layanan:* {$booking->service_type}
🚫 *Status:* Dibatalkan

Jika ada pertanyaan, silakan hubungi kami.
📞 *Kontak:* +62 812 3456 7890
MSG;
    }

    private function buildUserPaymentLinkMessage(\App\Models\Booking $booking, \App\Models\Invoice $invoice, string $paymentUrl): string
    {
        $total = 'Rp ' . number_format($invoice->total, 0, ',', '.');
        $dueDate = $invoice->due_at ? $invoice->due_at->format('d M Y') : '-';

        return <<<MSG
💳 *Link Pembayaran — Young 911 Autowerks*

Halo *{$booking->name}*, berikut adalah link pembayaran untuk layanan kendaraan Anda!

📋 *No. Invoice:* `{$invoice->invoice_number}`
🚗 *Kendaraan:* {$booking->car_model}
🔧 *Layanan:* {$booking->service_type}
💰 *Total Tagihan:* *{$total}*
📅 *Jatuh Tempo:* {$dueDate}

💳 *LINK PEMBAYARAN:*
{$paymentUrl}

Klik link di atas untuk melakukan pembayaran melalui Midtrans (Transfer Bank, Kartu Kredit, GoPay, ShopeePay, QRIS, dll).

📞 *Kontak:* +62 812 3456 7890

Terima kasih telah mempercayai Young 911 Autowerks! 🙏
MSG;
    }

    private function buildAdminBookingRescheduledMessage(Booking $booking, string $oldDate): string
    {
        $oldDateFormatted = \Carbon\Carbon::parse($oldDate)->format('d M Y');
        $newDateFormatted = $booking->preferred_date->format('d M Y');
        $reason = $booking->admin_notes ? (explode('Reason: ', $booking->admin_notes)[1] ?? 'N/A') : 'N/A';

        return <<<MSG
📅 *BOOKING DIRESCHEDULE — Young 911 Autowerks*

Halo Admin, customer telah mereschedule booking:

📋 *Kode Booking:* `{$booking->booking_code}`
👤 *Nama:* {$booking->name}
📱 *WhatsApp:* {$booking->phone}
🚗 *Tipe Mobil:* {$booking->car_model}
🔧 *Layanan:* {$booking->service_type}

📆 *Tanggal Lama:* {$oldDateFormatted}
📆 *Tanggal Baru:* {$newDateFormatted}

📝 *Alasan:* {$reason}

Silakan buka Filament Admin untuk melihat detail booking.
MSG;
    }

    private function buildAdminBookingCancelledMessage(Booking $booking): string
    {
        $category = $booking->admin_notes ? (explode('Category: ', $booking->admin_notes)[1] ?? 'N/A') : 'N/A';
        $reason = $booking->admin_notes ? (explode('Reason: ', $booking->admin_notes)[1] ?? 'N/A') : 'N/A';

        // Clean up reason (remove everything after "Reason:")
        if (str_contains($reason, '.')) {
            $reason = explode('.', $reason)[0];
        }

        return <<<MSG
🚫 *BOOKING DIBATALKAN CUSTOMER — Young 911 Autowerks*

Halo Admin, customer telah membatalkan booking:

📋 *Kode Booking:* `{$booking->booking_code}`
👤 *Nama:* {$booking->name}
📱 *WhatsApp:* {$booking->phone}
🚗 *Tipe Mobil:* {$booking->car_model}
🔧 *Layanan:* {$booking->service_type}

📊 *Kategori Pembatalan:* {$category}
📝 *Alasan:* {$reason}

Silakan buka Filament Admin untuk melihat detail booking.
MSG;
    }
}
