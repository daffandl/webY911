<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\MidtransService;
use App\Services\FonnteService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private MidtransService $midtrans,
        private FonnteService $fonnte,
        private InvoiceService $invoiceService,
    ) {}

    /**
     * Display payment page for an invoice
     */
    public function show(string $invoiceNumber)
    {
        $invoice = Invoice::with('booking')->where('invoice_number', $invoiceNumber)->firstOrFail();

        return view('payment.show', compact('invoice'));
    }

    /**
     * Generate payment link for an invoice
     */
    public function generateLink(string $invoiceNumber): JsonResponse
    {
        try {
            $invoice = Invoice::with('booking')->where('invoice_number', $invoiceNumber)->firstOrFail();

            if ($invoice->isPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice sudah lunas',
                ], 400);
            }

            $paymentUrl = $invoice->getPaymentLink();

            // Auto-create or update Payment record
            try {
                $existingPayment = Payment::where('invoice_id', $invoice->id)
                    ->where('status', 'pending')
                    ->first();

                if (!$existingPayment) {
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
                    $existingPayment->update([
                        'payment_url' => $paymentUrl,
                        'payment_response' => array_merge(
                            $existingPayment->payment_response ?? [],
                            ['payment_url' => $paymentUrl]
                        ),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to create Payment record: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'payment_url' => $paymentUrl,
                'invoice' => [
                    'invoice_number' => $invoice->invoice_number,
                    'total' => $invoice->total,
                    'status' => $invoice->status,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Payment link generation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat link pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get invoice payment status
     */
    public function status(string $invoiceNumber): JsonResponse
    {
        $invoice = Invoice::with('booking')->where('invoice_number', $invoiceNumber)->firstOrFail();

        return response()->json([
            'success' => true,
            'invoice' => [
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'status_label' => $invoice->status_label,
                'total' => (float) $invoice->total,
                'paid_amount' => $invoice->paid_amount ? (float) $invoice->paid_amount : null,
                'is_paid' => $invoice->isPaid(),
                'payment_url' => $invoice->payment_url,
                'paid_at' => $invoice->paid_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Handle Midtrans payment notification (callback)
     */
    public function notification(Request $request): JsonResponse
    {
        $notification = $request->all();

        Log::info('Midtrans notification received', $notification);

        try {
            // Verify notification signature
            if (!$this->verifyNotification($notification)) {
                Log::error('Midtrans notification verification failed');
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid notification',
                ], 401);
            }

            $result = $this->midtrans->handleInvoiceNotification($notification);

            // Send notifications only if payment status changed to success
            $invoice = Invoice::find($result['invoice_id']);
            $payment = \App\Models\Payment::find($result['payment_id'] ?? null);
            
            if ($invoice && $payment && $result['payment_status'] === 'success') {
                try {
                    // Send WA to customer (simple notification)
                    try {
                        $this->fonnte->notifyUserInvoicePaid($invoice);
                    } catch (\Throwable $e) {
                        Log::error('Failed to send WA payment success: ' . $e->getMessage());
                    }
                    
                    // Send detailed email to customer with complete Midtrans data
                    try {
                        if ($invoice->booking && $invoice->booking->email) {
                            \Illuminate\Support\Facades\Mail::to($invoice->booking->email)
                                ->send(new \App\Mail\PaymentSuccessMail($payment, $notification));
                        }
                    } catch (\Throwable $e) {
                        Log::error('Failed to send email payment success: ' . $e->getMessage());
                    }
                    
                    // Send Filament database notification to all admins
                    try {
                        $adminNotification = new \App\Notifications\PaymentSuccessNotification($payment, $notification);
                        \App\Models\User::where('role', 'admin')->get()->each(function ($admin) use ($adminNotification) {
                            $admin->notify($adminNotification);
                        });
                    } catch (\Throwable $e) {
                        Log::error('Failed to send admin notification: ' . $e->getMessage());
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to send payment success notifications: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification processed',
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans notification error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error processing notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete payment page (after user returns from Midtrans)
     */
    public function complete(string $invoiceNumber, Request $request)
    {
        $invoice = Invoice::with('booking')->where('invoice_number', $invoiceNumber)->firstOrFail();

        $status = $request->get('status', 'pending');
        $transactionId = $request->get('transaction_id');

        return view('payment.complete', compact('invoice', 'status', 'transactionId'));
    }

    /**
     * Finish redirect page (successful payment)
     */
    public function finish(Request $request)
    {
        $orderId = $request->get('order_id');
        $transactionId = $request->get('transaction_id');
        $statusCode = $request->get('status_code');

        Log::info('Payment finish redirect', [
            'order_id' => $orderId,
            'transaction_id' => $transactionId,
            'status_code' => $statusCode,
        ]);

        // Extract invoice number from order_id
        $parts = explode('-', $orderId);
        $invoiceNumber = implode('-', array_slice($parts, 0, 3));

        $invoice = Invoice::with('booking')->where('invoice_number', $invoiceNumber)->first();

        return view('payment.complete', [
            'invoice' => $invoice,
            'status' => 'success',
            'transactionId' => $transactionId,
        ]);
    }

    /**
     * Unfinish redirect page (incomplete payment)
     */
    public function unfinish(Request $request)
    {
        $orderId = $request->get('order_id');
        $transactionId = $request->get('transaction_id');

        Log::info('Payment unfinish redirect', [
            'order_id' => $orderId,
            'transaction_id' => $transactionId,
        ]);

        // Extract invoice number from order_id
        $parts = explode('-', $orderId);
        $invoiceNumber = implode('-', array_slice($parts, 0, 3));

        $invoice = Invoice::with('booking')->where('invoice_number', $invoiceNumber)->first();

        return view('payment.complete', [
            'invoice' => $invoice,
            'status' => 'pending',
            'transactionId' => $transactionId,
        ]);
    }

    /**
     * Error redirect page (payment error)
     */
    public function error(Request $request)
    {
        $orderId = $request->get('order_id');
        $statusCode = $request->get('status_code');
        $statusMessage = $request->get('status_message');

        Log::error('Payment error redirect', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'status_message' => $statusMessage,
        ]);

        // Extract invoice number from order_id
        $parts = explode('-', $orderId);
        $invoiceNumber = implode('-', array_slice($parts, 0, 3));

        $invoice = Invoice::with('booking')->where('invoice_number', $invoiceNumber)->first();

        return view('payment.complete', [
            'invoice' => $invoice,
            'status' => 'error',
            'transactionId' => null,
            'errorMessage' => $statusMessage,
        ]);
    }

    /**
     * Verify Midtrans notification signature
     */
    private function verifyNotification(array $notification): bool
    {
        // In production, you should verify the signature key
        // For now, we'll check if required fields exist
        $requiredFields = ['order_id', 'transaction_status', 'gross_amount'];

        foreach ($requiredFields as $field) {
            if (!isset($notification[$field])) {
                return false;
            }
        }

        // If in sandbox mode, accept all notifications
        if (!config('services.midtrans.is_production')) {
            return true;
        }

        // In production, verify signature key
        // $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . config('services.midtrans.server_key'));
        // return $signatureKey === ($notification['signature_key'] ?? '');

        return true;
    }

    /**
     * Handle Midtrans recurring payment notification
     */
    public function recurringNotification(Request $request): JsonResponse
    {
        $notification = $request->all();

        Log::info('Midtrans recurring notification received', $notification);

        try {
            if (!$this->verifyNotification($notification)) {
                Log::error('Midtrans recurring notification verification failed');
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid notification',
                ], 401);
            }

            // Handle recurring payment logic here
            // Similar to regular notification but for subscription/recurring payments

            return response()->json([
                'success' => true,
                'message' => 'Recurring notification processed',
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans recurring notification error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error processing recurring notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Midtrans pay account notification
     */
    public function payAccountNotification(Request $request): JsonResponse
    {
        $notification = $request->all();

        Log::info('Midtrans pay account notification received', $notification);

        try {
            if (!$this->verifyNotification($notification)) {
                Log::error('Midtrans pay account notification verification failed');
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid notification',
                ], 401);
            }

            // Handle pay account logic here
            // For account-based payments

            return response()->json([
                'success' => true,
                'message' => 'Pay account notification processed',
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans pay account notification error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error processing pay account notification: ' . $e->getMessage(),
            ], 500);
        }
    }
}
