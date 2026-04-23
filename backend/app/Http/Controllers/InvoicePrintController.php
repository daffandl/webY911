<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Response;

class InvoicePrintController extends Controller
{
    /**
     * Render a printable HTML invoice page.
     * Route: GET /invoice/{invoiceNumber}/print
     */
    public function print(string $invoiceNumber): Response
    {
        $invoice = Invoice::where('invoice_number', strtoupper($invoiceNumber))
            ->with(['items', 'booking'])
            ->firstOrFail();

        // Generate unique security hash for verification
        $securityHash = hash('sha256', $invoice->invoice_number . '-' . $invoice->total . '-' . $invoice->created_at->timestamp);
        $shortHash = substr($securityHash, 0, 16);

        return response()->view('invoices.print', compact('invoice', 'securityHash', 'shortHash'))
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
