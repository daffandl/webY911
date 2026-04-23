<?php

namespace App\Services;

use App\Mail\AdminInvoiceCreatedMail;
use App\Mail\AdminInvoicePaidMail;
use App\Mail\UserInvoiceMail;
use App\Mail\UserInvoicePaidMail;
use App\Mail\UserInvoicePaymentLinkMail;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\InvoiceCreatedNotification;
use App\Notifications\InvoicePaidNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceService
{
    public function __construct(
        private FonnteService $fonnte,
    ) {}

    // ─────────────────────────────────────────────────────────────
    //  Mark Invoice as Sent
    // ─────────────────────────────────────────────────────────────

    /**
     * Mark invoice as sent and notify the customer.
     * This sends invoice WITHOUT payment link.
     * Use sendPaymentLinkNotification() when payment link is generated.
     */
    public function markAsSent(Invoice $invoice): Invoice
    {
        $oldStatus = $invoice->status;

        $invoice->update(['status' => 'sent']);

        $invoice->refresh();

        // Send regular invoice notification (WITHOUT payment link)
        $this->safeWa(fn () => $this->fonnte->notifyUserInvoice($invoice));
        $this->safeMail(fn () => $this->sendUserInvoiceEmail($invoice));

        // Send Filament notification to admins
        $this->sendFilamentInvoiceSentNotification($invoice, $oldStatus);

        return $invoice;
    }

    // ─────────────────────────────────────────────────────────────
    //  Mark Invoice as Paid
    // ─────────────────────────────────────────────────────────────

    /**
     * Mark invoice as paid and notify customer and admin.
     */
    public function markAsPaid(Invoice $invoice): Invoice
    {
        $oldStatus = $invoice->status;

        $invoice->update(['status' => 'paid']);

        $invoice->refresh();

        // Notify customer via WA + Email
        $this->safeWa(fn () => $this->fonnte->notifyUserInvoicePaid($invoice));
        $this->safeMail(fn () => $this->sendUserInvoicePaidEmail($invoice));

        // Notify admin via Email + Filament
        $this->safeMail(fn () => $this->sendAdminInvoicePaidEmail($invoice));
        $this->sendFilamentInvoicePaidNotification($invoice, $oldStatus);

        return $invoice;
    }

    // ─────────────────────────────────────────────────────────────
    //  Mark Invoice as Cancelled
    // ─────────────────────────────────────────────────────────────

    /**
     * Mark invoice as cancelled and notify customer and admin.
     */
    public function markAsCancelled(Invoice $invoice): Invoice
    {
        $oldStatus = $invoice->status;

        $invoice->update(['status' => 'cancelled']);

        $invoice->refresh();

        // Notify customer via WA
        $this->safeWa(fn () => $this->fonnte->notifyUserInvoiceCancelled($invoice));

        // Send Filament notification to admins
        $this->sendFilamentInvoiceCancelledNotification($invoice, $oldStatus);

        return $invoice;
    }

    // ─────────────────────────────────────────────────────────────
    //  Create Invoice with Payment Link
    // ─────────────────────────────────────────────────────────────

    /**
     * Create invoice (without payment link).
     * Payment link can be generated later via generatePaymentLinkForInvoice().
     */
    public function createOrUpdateInvoice(array $data): Invoice
    {
        $bookingId = $data['booking_id'];

        return DB::transaction(function () use ($data, $bookingId) {
            // Check if invoice already exists for this booking
            $existingInvoice = Invoice::where('booking_id', $bookingId)
                ->whereIn('status', ['draft', 'sent'])
                ->latest()
                ->first();

            if ($existingInvoice) {
                // Update existing invoice
                $invoice = $this->updateInvoice($existingInvoice, $data);
            } else {
                // Create new invoice
                $invoice = $this->createInvoice($data);
            }

            return $invoice;
        });
    }

    /**
     * Generate payment link for an existing invoice.
     * Call this method when admin wants to add payment link.
     */
    public function generatePaymentLinkForInvoice(Invoice $invoice): string
    {
        if ($invoice->total <= 0) {
            throw new \RuntimeException('Total invoice harus lebih dari 0 untuk generate payment link.');
        }

        if ($invoice->isPaid()) {
            throw new \RuntimeException('Invoice sudah lunas, tidak perlu payment link.');
        }

        try {
            $paymentUrl = $invoice->getPaymentLink();

            // Create or update payment record
            $this->createOrUpdatePayment($invoice, $paymentUrl);

            // Update invoice status to sent if still draft
            if ($invoice->status === 'draft') {
                $invoice->update(['status' => 'sent']);
            }

            // DO NOT send notification here - admin will send manually using "Kirim Link Pembayaran" button

            return $paymentUrl;
        } catch (\Throwable $e) {
            Log::error('Failed to generate payment link: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send payment link notification to customer.
     */
    public function sendPaymentLinkNotification(Invoice $invoice, string $paymentUrl): void
    {
        // Send WA
        $this->safeWa(fn () => $this->fonnte->notifyUserPaymentLink($invoice->booking, $invoice, $paymentUrl));
        
        // Send Email
        $this->safeMail(fn () => $this->sendUserInvoicePaymentLinkEmail($invoice, $paymentUrl));
    }

    /**
     * Update existing invoice.
     */
    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice->update([
                'status'        => $data['status'] ?? $invoice->status,
                'issued_at'     => $data['issued_at'] ?? $invoice->issued_at,
                'due_at'        => $data['due_at'] ?? $invoice->due_at,
                'notes'         => $data['notes'] ?? $invoice->notes,
                'subtotal'      => $data['subtotal'] ?? $invoice->subtotal,
                'tax_percent'   => $data['tax_percent'] ?? $invoice->tax_percent,
                'tax_amount'    => $data['tax_amount'] ?? $invoice->tax_amount,
                'discount'      => $data['discount'] ?? $invoice->discount,
                'total'         => $data['total'] ?? $invoice->total,
            ]);

            $invoice->refresh();

            // Recalculate totals from items
            $invoice->recalculate();

            return $invoice;
        });
    }

    /**
     * Create invoice and notify admin.
     */
    public function createInvoice(array $data): Invoice
    {
        $invoice = Invoice::create([
            'booking_id'    => $data['booking_id'],
            'status'        => $data['status'] ?? 'draft',
            'issued_at'     => $data['issued_at'] ?? now(),
            'due_at'        => $data['due_at'] ?? null,
            'notes'         => $data['notes'] ?? null,
            'subtotal'      => $data['subtotal'] ?? 0,
            'tax_percent'   => $data['tax_percent'] ?? 0,
            'tax_amount'    => $data['tax_amount'] ?? 0,
            'discount'      => $data['discount'] ?? 0,
            'total'         => $data['total'] ?? 0,
        ]);

        return $invoice;
    }

    // ─────────────────────────────────────────────────────────────
    //  Payment helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Create or update Payment record when payment link is generated.
     */
    private function createOrUpdatePayment(Invoice $invoice, string $paymentUrl): void
    {
        try {
            // Check if payment already exists for this invoice
            $payment = Payment::where('invoice_id', $invoice->id)
                ->where('status', 'pending')
                ->first();

            if (!$payment) {
                // Create new payment record
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'booking_id' => $invoice->booking_id,
                    'transaction_id' => $invoice->transaction_id ?? $invoice->invoice_number . '-' . time(),
                    'payment_method' => null,
                    'payment_type' => null,
                    'bank' => null,
                    'va_number' => null,
                    'amount' => $invoice->total,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'midtrans_status' => null,
                    'gross_amount' => $invoice->total,
                    'currency' => 'IDR',
                    'payment_response' => ['payment_url' => $paymentUrl],
                    'fraud_status' => null,
                    'status_message' => null,
                    'paid_at' => null,
                    'payment_url' => $paymentUrl,
                ]);
            } else {
                // Update existing payment record with latest payment URL
                $payment->update([
                    'payment_url' => $paymentUrl,
                    'payment_response' => array_merge(
                        $payment->payment_response ?? [],
                        ['payment_url' => $paymentUrl]
                    ),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create/update Payment record: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  Filament Notification helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Send Filament notification when invoice is created.
     */
    private function sendFilamentInvoiceCreatedNotification(Invoice $invoice): void
    {
        $booking = $invoice->booking;

        // Send database notification to all admin users
        User::where('role', 'admin')->get()->each(function (User $admin) use ($invoice, $booking) {
            $admin->notify(new InvoiceCreatedNotification($invoice, $booking));
        });

        // Also send in-app notification (for currently logged-in admin)
        Notification::make()
            ->title('📄 Invoice Baru Dibuat')
            ->body("Invoice {$invoice->invoice_number} untuk {$booking->name} ({$booking->car_model})")
            ->info()
            ->icon('heroicon-o-document-text')
            ->iconColor('info')
            ->persistent()
            ->actions([
                Action::make('view')
                    ->button()
                    ->label('Lihat Invoice')
                    ->url(route('filament.admin.resources.invoices.view', ['record' => $invoice->id])),
            ])
            ->send();
    }

    /**
     * Send Filament notification when invoice is sent.
     */
    private function sendFilamentInvoiceSentNotification(Invoice $invoice, string $oldStatus): void
    {
        $booking = $invoice->booking;

        // Send database notification to all admin users
        User::where('role', 'admin')->get()->each(function (User $admin) use ($invoice, $booking, $oldStatus) {
            $admin->notify(new InvoiceCreatedNotification($invoice, $booking, $oldStatus, 'sent'));
        });

        // Also send in-app notification (for currently logged-in admin)
        Notification::make()
            ->title('📤 Invoice Dikirim ke Customer')
            ->body("Invoice {$invoice->invoice_number} telah dikirim ke {$booking->name}")
            ->success()
            ->icon('heroicon-o-paper-airplane')
            ->iconColor('success')
            ->persistent()
            ->actions([
                Action::make('view')
                    ->button()
                    ->label('Lihat Invoice')
                    ->url(route('filament.admin.resources.invoices.view', ['record' => $invoice->id])),
            ])
            ->send();
    }

    /**
     * Send Filament notification when invoice is paid.
     */
    private function sendFilamentInvoicePaidNotification(Invoice $invoice, string $oldStatus): void
    {
        $booking = $invoice->booking;

        // Send database notification to all admin users
        User::where('role', 'admin')->get()->each(function (User $admin) use ($invoice, $booking, $oldStatus) {
            $admin->notify(new InvoicePaidNotification($invoice, $booking, $oldStatus));
        });

        // Also send in-app notification (for currently logged-in admin)
        Notification::make()
            ->title('💰 Invoice Lunas!')
            ->body("Invoice {$invoice->invoice_number} dari {$booking->name} telah lunas")
            ->success()
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->persistent()
            ->actions([
                Action::make('view')
                    ->button()
                    ->label('Lihat Invoice')
                    ->url(route('filament.admin.resources.invoices.view', ['record' => $invoice->id])),
            ])
            ->send();
    }

    /**
     * Send Filament notification when invoice is cancelled.
     */
    private function sendFilamentInvoiceCancelledNotification(Invoice $invoice, string $oldStatus): void
    {
        $booking = $invoice->booking;

        // Send database notification to all admin users
        User::where('role', 'admin')->get()->each(function (User $admin) use ($invoice, $booking, $oldStatus) {
            $admin->notify(new InvoiceCreatedNotification($invoice, $booking, $oldStatus, 'cancelled'));
        });

        // Also send in-app notification (for currently logged-in admin)
        Notification::make()
            ->title('🚫 Invoice Dibatalkan')
            ->body("Invoice {$invoice->invoice_number} untuk {$booking->name} telah dibatalkan")
            ->danger()
            ->icon('heroicon-o-no-symbol')
            ->iconColor('danger')
            ->persistent()
            ->actions([
                Action::make('view')
                    ->button()
                    ->label('Lihat Invoice')
                    ->url(route('filament.admin.resources.invoices.view', ['record' => $invoice->id])),
            ])
            ->send();
    }

    // ─────────────────────────────────────────────────────────────
    //  Mail helpers
    // ─────────────────────────────────────────────────────────────

    private function sendUserInvoiceEmail(Invoice $invoice): void
    {
        if ($invoice->booking->email) {
            Mail::to($invoice->booking->email)->send(new UserInvoiceMail($invoice));
        }
    }

    private function sendUserInvoicePaymentLinkEmail(Invoice $invoice, string $paymentUrl): void
    {
        if ($invoice->booking->email) {
            Mail::to($invoice->booking->email)->send(new UserInvoicePaymentLinkMail($invoice, $paymentUrl));
        }
    }

    private function sendUserInvoicePaidEmail(Invoice $invoice): void
    {
        if ($invoice->booking->email) {
            Mail::to($invoice->booking->email)->send(new UserInvoicePaidMail($invoice));
        }
    }

    private function sendAdminInvoicePaidEmail(Invoice $invoice): void
    {
        $adminEmail = config('mail.admin_email', config('mail.from.address'));
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new AdminInvoicePaidMail($invoice));
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
            Log::error('InvoiceService WA error: ' . $e->getMessage());
        }
    }

    private function safeMail(callable $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            Log::error('InvoiceService Mail error: ' . $e->getMessage());
        }
    }
}
