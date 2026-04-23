<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'booking_id',
        'payment_number',
        'transaction_id',
        'payment_method',
        'payment_type',
        'bank',
        'va_number',
        'amount',
        'paid_amount',
        'status',
        'midtrans_status',
        'gross_amount',
        'currency',
        'payment_response',
        'fraud_status',
        'status_message',
        'paid_at',
        'payment_url',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'gross_amount'=> 'decimal:2',
        'paid_at'     => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'payment_response' => 'array',
    ];

    /**
     * Boot — auto-generate payment_number on creation.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Payment $payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = static::generatePaymentNumber();
            }
            // Don't auto-generate transaction_id here
            // It should come from Midtrans order_id or Invoice transaction_id
        });

        // Don't auto-update transaction_id after creation
        // It should be set explicitly from Midtrans data
    }

    /**
     * Generate a unique payment number: PAY-YYYYMMDD-NNN
     */
    public static function generatePaymentNumber(): string
    {
        $date   = now()->format('Ymd');
        $prefix = "PAY-{$date}-";
        $count  = static::whereDate('created_at', now()->toDateString())->count() + 1;

        return $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Build transaction ID for Midtrans
     */
    public function buildTransactionId(): string
    {
        // Use payment_number for consistency and traceability
        return $this->payment_number . '-' . time();
    }

    // ─── Relationships ────────────────────────────────────────────

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // ─── Accessors ────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'Menunggu',
            'success'  => 'Berhasil',
            'failed'   => 'Gagal',
            'refunded' => 'Dikembalikan',
            default    => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'warning',
            'success'  => 'success',
            'failed'   => 'danger',
            'refunded' => 'info',
            default    => 'gray',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getFormattedPaidAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->paid_amount, 0, ',', '.');
    }

    public function getFormattedGrossAmountAttribute(): string
    {
        return $this->gross_amount 
            ? 'Rp ' . number_format($this->gross_amount, 0, ',', '.')
            : '-';
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    // ─── Payment Methods ────────────────────────────────────────────

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
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
            default         => ucfirst($this->payment_method ?? '-'),
        };
    }

    /**
     * Check if payment is successful
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark payment as successful
     */
    public function markAsSuccess(array $responseData = []): void
    {
        // Extract payment data from Midtrans response
        $paymentType    = $responseData['payment_type'] ?? null;
        $bank           = $responseData['bank'] ?? null;
        $vaNumber       = $responseData['va_numbers'][0] ?? $responseData['va_number'] ?? null;
        $grossAmount    = $responseData['gross_amount'] ?? $this->amount;
        $transactionStatus = $responseData['transaction_status'] ?? null;
        $fraudStatus    = $responseData['fraud_status'] ?? null;
        
        // Map payment type to payment method
        $paymentMethod = match ($paymentType) {
            'bank_transfer' => 'bank_transfer',
            'credit_card'   => 'credit_card',
            'gopay'         => 'gopay',
            'shopeepay'     => 'shopeepay',
            'qris'          => 'qris',
            'cimb_clicks'   => 'cimb_clicks',
            'bca_klikpay'   => 'bca_klikpay',
            'bca_klikbca'   => 'bca_klikbca',
            'permata_va'    => 'permata_va',
            'echannel'      => 'echannel',
            'indomaret'     => 'indomaret',
            'alfamart'      => 'alfamart',
            default         => $paymentType ?? $this->payment_method,
        };

        $this->update([
            'status'         => 'success',
            'midtrans_status'=> $transactionStatus ?? 'settle',
            'payment_method' => $paymentMethod,
            'payment_type'   => $paymentType ?? $this->payment_type,
            'bank'           => $bank ?? $this->bank,
            'va_number'      => $vaNumber ?? $this->va_number,
            'paid_amount'    => $grossAmount ?? $this->paid_amount,
            'gross_amount'   => $grossAmount ?? $this->gross_amount,
            'paid_at'        => now(),
            'payment_response' => $responseData,
            'fraud_status'   => $fraudStatus ?? $this->fraud_status,
            'status_message' => $responseData['status_message'] ?? $this->status_message,
        ]);

        // Update invoice status
        if ($this->invoice) {
            $this->invoice->markAsPaid($paymentMethod, $grossAmount);
        }

        // Update booking payment status
        if ($this->booking) {
            $this->booking->update([
                'payment_status' => 'paid',
            ]);
        }
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(string $message = ''): void
    {
        $this->update([
            'status'         => 'failed',
            'status_message' => $message,
        ]);
    }
}
