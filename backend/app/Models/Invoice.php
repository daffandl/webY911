<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'invoice_number',
        'status',
        'subtotal',
        'tax_percent',
        'tax_amount',
        'discount',
        'total',
        'notes',
        'issued_at',
        'due_at',
        'payment_token',
        'transaction_id',
        'payment_url',
        'payment_method',
        'paid_at',
        'paid_amount',
        'secure_hash',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount'  => 'decimal:2',
        'discount'    => 'decimal:2',
        'total'       => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'issued_at'   => 'date',
        'due_at'      => 'date',
        'paid_at'     => 'datetime',
    ];

    /**
     * Boot — auto-generate invoice_number on creation and recalculate on item changes.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
            if (empty($invoice->issued_at)) {
                $invoice->issued_at = now()->toDateString();
            }
            
            // Ensure default values for numeric fields to prevent null violations
            if ($invoice->tax_percent === null) {
                $invoice->tax_percent = 0;
            }
            if ($invoice->tax_amount === null) {
                $invoice->tax_amount = 0;
            }
            if ($invoice->discount === null) {
                $invoice->discount = 0;
            }
            if ($invoice->subtotal === null) {
                $invoice->subtotal = 0;
            }
            if ($invoice->total === null) {
                $invoice->total = 0;
            }
        });

        static::saved(function (Invoice $invoice) {
            if (empty($invoice->secure_hash)) {
                $invoice->generateSecureHash();
            }
        });

        // Auto-recalculate totals after saving
        static::saved(function (Invoice $invoice) {
            $invoice->recalculateFromItems();
        });
    }

    /**
     * Generate a unique invoice number: INV-YYYYMMDD-NNN
     */
    public static function generateInvoiceNumber(): string
    {
        $date   = now()->format('Ymd');
        $prefix = "INV-{$date}-";
        // Use now()->toDateString() for consistent date comparison
        $count  = static::whereDate('created_at', now()->toDateString())->count() + 1;

        return $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Recalculate subtotal, tax_amount, and total from items.
     * Called automatically after save via boot method.
     */
    public function recalculate(): void
    {
        $this->recalculateFromItems();
    }

    /**
     * Recalculate totals from items and update if changed.
     */
    private function recalculateFromItems(): void
    {
        $subtotal  = $this->items()->sum('subtotal');
        $taxAmount = round($subtotal * ($this->tax_percent / 100), 2);
        $total     = $subtotal + $taxAmount - $this->discount;

        // Only update if values actually changed to avoid infinite loop
        if ($this->subtotal != $subtotal ||
            $this->tax_amount != $taxAmount ||
            $this->total != $total) {

            // Use Laravel's built-in updateQuietly (Laravel 12+)
            $this->updateQuietly([
                'subtotal'   => $subtotal,
                'tax_amount' => $taxAmount,
                'total'      => max(0, $total),
            ], ['touch' => false]);
        }
    }

    // ─── Relationships ────────────────────────────────────────────

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    // ─── Accessors ────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Draft',
            'sent'      => 'Terkirim',
            'paid'      => 'Lunas',
            'cancelled' => 'Dibatalkan',
            default     => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'gray',
            'sent'      => 'info',
            'paid'      => 'success',
            'cancelled' => 'danger',
            default     => 'gray',
        };
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    public function getFormattedPaidAmountAttribute(): string
    {
        return $this->paid_amount 
            ? 'Rp ' . number_format($this->paid_amount, 0, ',', '.') 
            : '-';
    }

    // ─── Payment Methods ────────────────────────────────────────────

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if invoice is pending payment
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['draft', 'sent']);
    }

    /**
     * Generate payment link via Midtrans
     */
    public function generatePaymentLink(): string
    {
        if ($this->isPaid()) {
            throw new \RuntimeException('Invoice is already paid');
        }

        // Use existing transaction_id if available, otherwise create new one
        $orderId = $this->transaction_id ?? $this->invoice_number . '-' . time();
        
        $midtrans = new \App\Services\MidtransService();
        $paymentUrl = $midtrans->createInvoiceTransaction($this, $orderId);

        $this->update([
            'payment_token' => $midtrans->getSnapToken(),
            'transaction_id' => $orderId,
            'payment_url' => $paymentUrl,
        ]);

        // Auto-create Payment record
        $this->createOrUpdatePayment($paymentUrl, $orderId);

        return $paymentUrl;
    }

    /**
     * Get or refresh payment link
     */
    public function getPaymentLink(): ?string
    {
        if ($this->isPaid()) {
            return null;
        }

        if (!$this->payment_url) {
            return $this->generatePaymentLink();
        }

        // Payment record should exist if payment_url exists
        // But create one if it doesn't
        $orderId = $this->transaction_id ?? $this->invoice_number . '-' . time();
        $this->createOrUpdatePayment($this->payment_url, $orderId);

        return $this->payment_url;
    }

    /**
     * Create or update Payment record for this invoice
     */
    private function createOrUpdatePayment(string $paymentUrl, ?string $orderId = null): void
    {
        try {
            // Find existing payment for this invoice (any status, prefer pending)
            $payment = Payment::where('invoice_id', $this->id)
                ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                ->first();

            // Use the transaction_id from invoice (which is the Midtrans order_id)
            $transactionId = $orderId ?? $this->transaction_id ?? $this->invoice_number . '-' . time();

            if (!$payment) {
                // Create new payment record only if none exists
                Payment::create([
                    'invoice_id' => $this->id,
                    'booking_id' => $this->booking_id,
                    'transaction_id' => $transactionId,
                    'payment_method' => null,
                    'payment_type' => null,
                    'bank' => null,
                    'va_number' => null,
                    'amount' => $this->total,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'midtrans_status' => null,
                    'gross_amount' => $this->total,
                    'currency' => 'IDR',
                    'payment_response' => ['payment_url' => $paymentUrl, 'order_id' => $transactionId],
                    'fraud_status' => null,
                    'status_message' => null,
                    'paid_at' => null,
                    'payment_url' => $paymentUrl,
                ]);
            } else {
                // ALWAYS update existing payment with latest payment URL and order_id
                $payment->update([
                    'transaction_id' => $transactionId,
                    'payment_url' => $paymentUrl,
                    'payment_response' => array_merge(
                        $payment->payment_response ?? [],
                        ['payment_url' => $paymentUrl, 'order_id' => $transactionId]
                    ),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create/update Payment record: ' . $e->getMessage());
        }
    }

    /**
     * Build transaction ID for Midtrans
     */
    private function buildTransactionId(): string
    {
        return 'INV-' . $this->id . '-' . time();
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(?string $paymentMethod = null, ?float $paidAmount = null): void
    {
        $this->update([
            'status' => 'paid',
            'payment_method' => $paymentMethod,
            'paid_at' => now(),
            'paid_amount' => $paidAmount ?? $this->total,
        ]);

        // Update related booking payment status
        if ($this->booking) {
            $this->booking->update([
                'payment_status' => 'paid',
            ]);
        }
    }

    /**
     * Get payment URL for frontend
     */
    public function getFrontendPaymentUrl(): string
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        return "{$frontendUrl}/payment/{$this->invoice_number}";
    }

    // ─── Security Methods ────────────────────────────────────────────

    /**
     * Generate secure HMAC-SHA256 hash for invoice verification.
     * Updates the secure_hash field.
     */
    public function generateSecureHash(): void
    {
        $key = config('app.key');
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        $data = implode('|', [
            $this->invoice_number,
            $this->total,
            $this->issued_at?->timestamp ?? 0,
            $this->id,
        ]);

        $hash = hash_hmac('sha256', $data, $key);
        $this->updateQuietly(['secure_hash' => $hash], ['touch' => false]);
    }

    /**
     * Verify if the provided hash matches the invoice's secure hash.
     * Uses constant-time comparison to prevent timing attacks.
     */
    public static function verifyHash(string $invoiceNumber, string $providedHash): bool
    {
        $invoice = static::where('invoice_number', $invoiceNumber)->first();
        
        if (!$invoice || !$invoice->secure_hash) {
            return false;
        }

        return hash_equals($invoice->secure_hash, $providedHash);
    }

    /**
     * Get secure hash for API responses (short version for UI display).
     */
    public function getSecureHashDisplay(): string
    {
        return substr($this->secure_hash ?? '', 0, 16);
    }
}
