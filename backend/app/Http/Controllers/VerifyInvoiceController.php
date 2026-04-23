<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class VerifyInvoiceController extends Controller
{
    /**
     * Show the invoice verification page.
     * Route: GET /verify
     */
    public function show(): View
    {
        return view('invoices.verify');
    }

    /**
     * Verify an invoice by invoice number and hash.
     * Route: GET /api/verify
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'invoice' => 'required|string|max:50',
            'hash' => 'nullable|string|max:16',
        ]);

        $invoiceNumber = strtoupper($request->input('invoice'));
        $providedHash = $request->input('hash');

        // Find invoice
        $invoice = Invoice::where('invoice_number', $invoiceNumber)
            ->with(['items', 'booking'])
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice tidak ditemukan',
                'data' => null,
            ], 404);
        }

        // Generate expected hash
        $expectedHash = substr(
            hash('sha256', $invoice->invoice_number . '-' . $invoice->total . '-' . $invoice->created_at->timestamp),
            0,
            16
        );

        // Verify hash if provided
        $isValid = false;
        $hashMatch = false;

        if ($providedHash) {
            $hashMatch = $providedHash === $expectedHash;
            $isValid = $hashMatch;
        } else {
            // If no hash provided, just check if invoice exists
            $isValid = true;
        }

        // Calculate security score
        $securityScore = 0;
        $securityChecks = [];

        // Check 1: Invoice exists in database
        if ($invoice) {
            $securityScore += 25;
            $securityChecks[] = [
                'check' => 'Invoice terdaftar di sistem',
                'status' => 'pass',
            ];
        }

        // Check 2: Hash matches
        if ($hashMatch) {
            $securityScore += 25;
            $securityChecks[] = [
                'check' => 'Security hash valid',
                'status' => 'pass',
            ];
        } else {
            $securityChecks[] = [
                'check' => 'Security hash valid',
                'status' => $providedHash ? 'fail' : 'skip',
                'message' => $providedHash ? 'Hash tidak cocok' : 'Hash tidak disediakan',
            ];
        }

        // Check 3: Invoice status
        if ($invoice->status === 'paid') {
            $securityScore += 25;
            $securityChecks[] = [
                'check' => 'Status pembayaran lunas',
                'status' => 'pass',
            ];
        } else {
            $securityChecks[] = [
                'check' => 'Status pembayaran lunas',
                'status' => 'warning',
                'message' => 'Invoice belum lunas (' . $invoice->status_label . ')',
            ];
        }

        // Check 4: Invoice has items
        if ($invoice->items && $invoice->items->count() > 0) {
            $securityScore += 25;
            $securityChecks[] = [
                'check' => 'Invoice memiliki item valid',
                'status' => 'pass',
            ];
        } else {
            $securityChecks[] = [
                'check' => 'Invoice memiliki item valid',
                'status' => 'warning',
                'message' => 'Invoice tidak memiliki item',
            ];
        }

        // Determine overall status
        $status = 'invalid';
        $statusLabel = 'Tidak Valid';
        $statusColor = 'red';

        if ($isValid && $securityScore >= 75) {
            $status = 'authentic';
            $statusLabel = 'Asli & Terverifikasi';
            $statusColor = 'green';
        } elseif ($isValid && $securityScore >= 50) {
            $status = 'likely_authentic';
            $statusLabel = 'Kemungkinan Asli';
            $statusColor = 'yellow';
        } elseif ($invoice) {
            $status = 'exists_but_suspicious';
            $statusLabel = 'Terdeteksi Tapi Mencurigakan';
            $statusColor = 'orange';
        }

        return response()->json([
            'success' => true,
            'message' => $isValid ? 'Invoice terverifikasi' : 'Invoice tidak dapat diverifikasi',
            'data' => [
                'invoice' => [
                    'invoice_number' => $invoice->invoice_number,
                    'status' => $invoice->status,
                    'status_label' => $invoice->status_label,
                    'total' => $invoice->total,
                    'issued_at' => $invoice->issued_at?->format('d M Y'),
                    'customer_name' => $invoice->booking?->name,
                    'car_model' => $invoice->booking?->car_model,
                    'service_type' => $invoice->booking?->service_type,
                ],
                'verification' => [
                    'is_valid' => $isValid,
                    'hash_match' => $hashMatch,
                    'security_score' => $securityScore,
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'status_color' => $statusColor,
                    'checks' => $securityChecks,
                    'verified_at' => now()->format('d M Y H:i:s'),
                ],
            ],
        ]);
    }
}
