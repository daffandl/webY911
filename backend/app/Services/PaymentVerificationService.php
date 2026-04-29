<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentVerificationService
{
    /**
     * Verify HMAC signature for Midtrans payments.
     *
     * @param string $signature_key
     * @param array $payload
     * @param string $signature
     * @return bool
     */
    public function verifyHmacSignature(string $signature_key, array $payload, string $signature): bool
    {
        // Create the string to be hashed
        $hash_string = json_encode($payload);

        // Generate HMAC signature from payload
        $generated_signature = hash_hmac('sha512', $hash_string, $signature_key);

        // Compare the generated signature with the provided signature
        return hash_equals($generated_signature, $signature);
    }

    /**
     * Validate payment amount matches invoice total (prevent amount tampering)
     */
    public static function validatePaymentAmount(string $invoiceNumber, float $paidAmount): bool
    {
        $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();

        if (!$invoice) {
            self::logSecurityEvent('payment_validation_failed', 'Invoice not found', ['invoice' => $invoiceNumber]);
            return false;
        }

        // Allow 1% variance for currency conversions
        $tolerance = $invoice->total * 0.01;
        $isValid = abs($invoice->total - $paidAmount) <= $tolerance;

        if (!$isValid) {
            self::logSecurityEvent('payment_amount_mismatch', 'Amount tampering detected', [
                'invoice' => $invoiceNumber,
                'expected' => $invoice->total,
                'received' => $paidAmount,
                'difference' => abs($invoice->total - $paidAmount),
            ]);
        }

        return $isValid;
    }

    /**
     * Check for duplicate payment processing (prevent replay attacks)
     */
    public static function isDuplicatePayment(string $transactionId): bool
    {
        $payment = Payment::where('transaction_id', $transactionId)
            ->where('status', 'paid')
            ->exists();

        if ($payment) {
            self::logSecurityEvent('duplicate_payment_detected', 'Replay attack prevented', ['transaction_id' => $transactionId]);
        }

        return $payment;
    }

    /**
     * Validate transaction ID format and ownership
     */
    public static function validateTransaction(string $transactionId, ?string $invoiceNumber = null): bool
    {
        // Check if transaction ID matches expected format
        if (!preg_match('/^[A-Za-z0-9\-_]{10,}$/', $transactionId)) {
            self::logSecurityEvent('invalid_transaction_format', 'Transaction ID format invalid', ['transaction_id' => $transactionId]);
            return false;
        }

        // Check if transaction is associated with correct invoice (if provided)
        if ($invoiceNumber) {
            $invoice = Invoice::where('invoice_number', $invoiceNumber)
                ->where('transaction_id', $transactionId)
                ->exists();

            if (!$invoice) {
                self::logSecurityEvent('transaction_mismatch', 'Transaction does not match invoice', [
                    'transaction_id' => $transactionId,
                    'invoice' => $invoiceNumber,
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Log security events for auditing
     * Redacts sensitive payment data
     */
    private static function logSecurityEvent(string $event, string $message, array $context = []): void
    {
        // Redact sensitive data
        $context = self::redactSensitiveData($context);

        Log::warning("Payment Security Event: {$event}", [
            'event' => $event,
            'message' => $message,
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Redact sensitive payment information from logs
     * Hides credit card numbers, VA numbers, and other sensitive data
     */
    public static function redactSensitiveData(array $data): array
    {
        $sensitiveFields = [
            'credit_card', 
            'card_number', 
            'va_number', 
            'bank_account', 
            'signature', 
            'server_key', 
            'client_key', 
            'token',
            'cvv',
            'cvc',
            'pin',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                // Redact but show last 4 digits for card/VA numbers
                if (in_array($field, ['credit_card', 'card_number', 'va_number'])) {
                    $original = (string)$data[$field];
                    $lastFour = strlen($original) > 4 ? substr($original, -4) : $original;
                    $data[$field] = '***' . $lastFour;
                } else {
                    $data[$field] = '***REDACTED***';
                }
            }
        }

        // Redact nested sensitive fields
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::redactSensitiveData($value);
            } elseif (is_string($value)) {
                $keyLower = strtolower($key);
                if (stripos($keyLower, 'credit_card') !== false || 
                    stripos($keyLower, 'card_number') !== false || 
                    stripos($keyLower, 'va_number') !== false) {
                    $lastFour = strlen($value) > 4 ? substr($value, -4) : $value;
                    $data[$key] = '***' . $lastFour;
                } elseif (stripos($keyLower, 'cvv') !== false || 
                         stripos($keyLower, 'cvc') !== false || 
                         stripos($keyLower, 'pin') !== false) {
                    $data[$key] = '***REDACTED***';
                }
            }
        }

        return $data;
    }

    /**
     * Get audit trail for a payment
     */
    public static function getPaymentAuditTrail(string $transactionId): array
    {
        return Payment::where('transaction_id', $transactionId)
            ->with(['invoice', 'invoice.booking'])
            ->get()
            ->map(fn($payment) => [
                'id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'fraud_status' => $payment->fraud_status,
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at,
            ])
            ->toArray();
    }
}