<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    private ?string $snapToken = null;

    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = (bool) config('services.midtrans.is_sanitized');
        Config::$is3ds = (bool) config('services.midtrans.is_3ds');

        // Note: Notification and redirect URLs must be configured in Midtrans Dashboard
        // Settings > Configuration > Notification & Redirect URLs
        // URLs will use the NGROK_URL from environment variable
    }

    /**
     * Get the last snap token
     */
    public function getSnapToken(): ?string
    {
        return $this->snapToken;
    }

    /**
     * Create a payment transaction for a booking
     *
     * @throws \RuntimeException If booking data is invalid or transaction fails
     */
    public function createTransaction(Booking $booking): string
    {
        // Validate required booking data
        if (empty($booking->service_type)) {
            throw new \RuntimeException('Booking service_type is required for payment');
        }

        if (empty($booking->name) || empty($booking->email) || empty($booking->phone)) {
            throw new \RuntimeException('Customer details (name, email, phone) are required for payment');
        }

        $serviceType = $booking->service_type;
        $amount = $this->getServiceAmount($serviceType);

        $params = [
            'transaction_details' => [
                'order_id' => 'Y911-' . $booking->id . '-' . time(),
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $booking->name,
                'email' => $booking->email,
                'phone' => $booking->phone,
            ],
            'item_details' => [
                [
                    'id' => $serviceType,
                    'price' => $amount,
                    'quantity' => 1,
                    'name' => 'Car Service - ' . $serviceType,
                ],
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $booking->update([
                'payment_token' => $snapToken,
                'transaction_id' => $params['transaction_details']['order_id'],
            ]);

            $this->snapToken = $snapToken;

            $environment = Config::$isProduction ? 'app' : 'app.sandbox';
            return "https://{$environment}.midtrans.com/snap/v2/vtweb/" . $snapToken;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to create payment transaction: ' . $e->getMessage());
        }
    }

    /**
     * Create a payment transaction for an invoice
     *
     * @throws \RuntimeException If invoice data is invalid or transaction fails
     */
    public function createInvoiceTransaction(Invoice $invoice, ?string $orderId = null): string
    {
        $booking = $invoice->booking;

        if (!$booking) {
            throw new \RuntimeException('Booking not found for invoice');
        }

        if (empty($booking->name) || empty($booking->email) || empty($booking->phone)) {
            throw new \RuntimeException('Customer details (name, email, phone) are required for payment');
        }

        $amount = (float) $invoice->total;

        if ($amount <= 0) {
            throw new \RuntimeException('Invoice total must be greater than 0');
        }

        // Use provided order_id or generate new one (should be consistent!)
        $orderId = $orderId ?? ($invoice->transaction_id ?? $invoice->invoice_number . '-' . time());

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $booking->name,
                'email' => $booking->email,
                'phone' => $booking->phone,
            ],
            'item_details' => $this->buildInvoiceItems($invoice),
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $invoice->update([
                'payment_token' => $snapToken,
                'transaction_id' => $orderId,
            ]);

            $this->snapToken = $snapToken;

            $environment = Config::$isProduction ? 'app' : 'app.sandbox';
            return "https://{$environment}.midtrans.com/snap/v2/vtweb/" . $snapToken;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to create payment transaction: ' . $e->getMessage());
        }
    }

    /**
     * Build item details from invoice items
     */
    private function buildInvoiceItems(Invoice $invoice): array
    {
        $items = [];
        
        foreach ($invoice->items as $item) {
            $items[] = [
                'id' => $item->service_item_id ?? 'item-' . $item->id,
                'price' => (float) $item->unit_price,
                'quantity' => (float) $item->qty,
                'name' => $item->name,
            ];
        }

        // Add tax as separate item if applicable
        if ($invoice->tax_amount > 0) {
            $items[] = [
                'id' => 'tax',
                'price' => (float) $invoice->tax_amount,
                'quantity' => 1,
                'name' => 'Pajak',
            ];
        }

        // Add discount as negative item if applicable
        if ($invoice->discount > 0) {
            $items[] = [
                'id' => 'discount',
                'price' => -(float) $invoice->discount,
                'quantity' => 1,
                'name' => 'Diskon',
            ];
        }

        return $items;
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus(string $orderId): array
    {
        try {
            $response = Transaction::status($orderId);
            
            // Convert stdClass to array
            if (is_object($response)) {
                $response = (array) $response;
            }
            
            return $response;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            
            // Check if it's a 404 error (transaction not found)
            if (strpos($message, '404') !== false || strpos($message, "doesn't exist") !== false) {
                \Illuminate\Support\Facades\Log::warning('Midtrans transaction not found: ' . $orderId);
                return [
                    'status_code' => '404',
                    'transaction_status' => 'not_found',
                    'status_message' => 'Transaction not found in Midtrans',
                    'order_id' => $orderId,
                ];
            }
            
            throw new \RuntimeException('Failed to get transaction status: ' . $message);
        }
    }

    /**
     * Get payment status from Midtrans
     * Alias for getTransactionStatus for backward compatibility
     */
    public function getPaymentStatus(string $transactionId): array
    {
        return $this->getTransactionStatus($transactionId);
    }

    /**
     * Handle Midtrans notification for bookings
     */
    public function handleNotification(array $notification): array
    {
        $orderId = $notification['order_id'];
        $transactionStatus = $notification['transaction_status'];
        $fraudStatus = $notification['fraud_status'] ?? null;

        $bookingId = explode('-', $orderId)[1];
        $booking = Booking::find($bookingId);

        if (!$booking) {
            throw new \RuntimeException('Booking not found');
        }

        $paymentStatus = 'pending';

        if ($transactionStatus == 'capture') {
            $paymentStatus = $fraudStatus == 'accept' ? 'paid' : 'pending';
        } elseif ($transactionStatus == 'settlement') {
            $paymentStatus = 'paid';
        } elseif ($transactionStatus == 'pending') {
            $paymentStatus = 'pending';
        } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            $paymentStatus = 'failed';
        }

        $booking->update([
            'payment_status' => $paymentStatus,
        ]);

        return [
            'booking_id' => $booking->id,
            'payment_status' => $paymentStatus,
            'transaction_status' => $transactionStatus,
        ];
    }

    /**
     * Handle Midtrans notification for invoices
     */
    public function handleInvoiceNotification(array $notification): array
    {
        $orderId = $notification['order_id'];
        $transactionStatus = $notification['transaction_status'];
        $fraudStatus = $notification['fraud_status'] ?? null;
        $grossAmount = $notification['gross_amount'] ?? 0;
        $paymentType = $notification['payment_type'] ?? null;
        $transactionId = $notification['transaction_id'] ?? null;
        $statusCode = $notification['status_code'] ?? null;
        $statusMessage = $notification['status_message'] ?? null;
        $signatureKey = $notification['signature_key'] ?? null;
        $bank = $notification['bank'] ?? null;
        $vaNumber = $notification['va_number'] ?? null;
        $currency = $notification['currency'] ?? 'IDR';
        $transactionTime = $notification['transaction_time'] ?? null;
        $settlementTime = $notification['settlement_time'] ?? null;
        $expiryTime = $notification['expiry_time'] ?? null;
        $merchantId = $notification['merchant_id'] ?? null;

        // Extract invoice ID from order_id (format: INV-XXXX-YYYY-timestamp)
        // The invoice_number is the first two parts: INV-XXXX-YYYY
        $parts = explode('-', $orderId);
        $invoiceNumber = implode('-', array_slice($parts, 0, 3));

        $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();

        if (!$invoice) {
            throw new \RuntimeException('Invoice not found for order_id: ' . $orderId);
        }

        $paymentStatus = 'pending';
        $midtransStatus = $transactionStatus;

        if ($transactionStatus == 'capture') {
            $paymentStatus = ($fraudStatus == 'accept' || $fraudStatus == null) ? 'success' : 'pending';
            $midtransStatus = 'capture';
        } elseif ($transactionStatus == 'settlement') {
            $paymentStatus = 'success';
            $midtransStatus = 'settle';
        } elseif ($transactionStatus == 'pending') {
            $paymentStatus = 'pending';
        } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            $paymentStatus = 'failed';
            $midtransStatus = $transactionStatus;
        } elseif ($transactionStatus == 'refund') {
            $paymentStatus = 'refunded';
            $midtransStatus = 'refund';
        }

        // Find payment record by invoice_id (most reliable)
        $payment = Payment::where('invoice_id', $invoice->id)
            ->orderByDesc('created_at')
            ->first();

        if (!$payment) {
            // Create new payment record with complete Midtrans data
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'booking_id' => $invoice->booking_id,
                'transaction_id' => $transactionId ?? $orderId,
                'payment_method' => $paymentType,
                'payment_type' => $paymentType,
                'bank' => $bank,
                'va_number' => $vaNumber,
                'amount' => $invoice->total,
                'paid_amount' => $paymentStatus === 'success' ? $grossAmount : 0,
                'status' => $paymentStatus,
                'midtrans_status' => $midtransStatus,
                'gross_amount' => $grossAmount,
                'currency' => $currency,
                'payment_response' => $notification,
                'fraud_status' => $fraudStatus,
                'status_message' => $statusMessage,
                'paid_at' => $paymentStatus === 'success' ? now() : null,
            ]);
        } else {
            // Update existing payment record with ALL Midtrans data
            $payment->update([
                'transaction_id' => $transactionId ?? $payment->transaction_id,
                'payment_method' => $paymentType ?? $payment->payment_method,
                'payment_type' => $paymentType ?? $payment->payment_type,
                'bank' => $bank ?? $payment->bank,
                'va_number' => $vaNumber ?? $payment->va_number,
                'status' => $paymentStatus,
                'midtrans_status' => $midtransStatus,
                'gross_amount' => $grossAmount,
                'currency' => $currency,
                'payment_response' => $notification,
                'fraud_status' => $fraudStatus,
                'status_message' => $statusMessage,
                'paid_amount' => $paymentStatus === 'success' ? $grossAmount : $payment->paid_amount,
                'paid_at' => $paymentStatus === 'success' ? now() : $payment->paid_at,
            ]);
        }

        if ($paymentStatus === 'success') {
            $invoice->markAsPaid($paymentType, (float) $grossAmount);
        } else {
            $invoice->update([
                'payment_method' => $paymentType,
            ]);
        }

        return [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'payment_id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'payment_status' => $paymentStatus,
            'transaction_status' => $transactionStatus,
            'payment_method' => $paymentType,
        ];
    }

    /**
     * Get service amount based on service type
     */
    private function getServiceAmount(string $serviceType): int
    {
        $prices = [
            'maintenance' => 500000,
            'repair' => 1000000,
            'diagnostic' => 300000,
            'oil-change' => 1200000,
            'brakes' => 700000,
            'other' => 500000,
        ];

        return $prices[$serviceType] ?? 500000;
    }
}
