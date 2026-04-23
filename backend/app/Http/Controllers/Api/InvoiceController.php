<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  GET /api/bookings/track/{code}/invoice
    //  Public — user checks invoice by booking code
    // ─────────────────────────────────────────────────────────────

    public function getByBookingCode(string $code): JsonResponse
    {
        $booking = Booking::byCode(strtoupper($code))->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak ditemukan.',
            ], 404);
        }

        $invoice = Invoice::where('booking_id', $booking->id)
            ->with('items')
            ->whereIn('status', ['sent', 'paid', 'draft'])
            ->latest()
            ->first();

        if (! $invoice) {
            return response()->json([
                'success'  => false,
                'has_invoice' => false,
                'message'  => 'Invoice belum dibuat untuk booking ini.',
            ], 200);
        }

        return response()->json([
            'success'     => true,
            'has_invoice' => true,
            'data'        => $this->formatInvoice($invoice, $booking),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Private helpers
    // ─────────────────────────────────────────────────────────────

    private function formatInvoice(Invoice $invoice, Booking $booking): array
    {
        $printUrl = url("/invoice/{$invoice->invoice_number}/print");

        return [
            'invoice_number' => $invoice->invoice_number,
            'status'         => $invoice->status,
            'status_label'   => $invoice->status_label,
            'issued_at'      => $invoice->issued_at?->format('d M Y'),
            'due_at'         => $invoice->due_at?->format('d M Y'),
            'subtotal'       => (float) $invoice->subtotal,
            'tax_percent'    => (float) $invoice->tax_percent,
            'tax_amount'     => (float) $invoice->tax_amount,
            'discount'       => (float) $invoice->discount,
            'total'          => (float) $invoice->total,
            'notes'          => $invoice->notes,
            'print_url'      => $printUrl,
            'items'          => $invoice->items->map(fn ($item) => [
                'name'       => $item->name,
                'type'       => $item->type,
                'qty'        => (float) $item->qty,
                'unit'       => $item->unit,
                'unit_price' => (float) $item->unit_price,
                'subtotal'   => (float) $item->subtotal,
            ])->values()->toArray(),
            'booking' => [
                'booking_code' => $booking->booking_code,
                'name'         => $booking->name,
                'phone'        => $booking->phone,
                'car_model'    => $booking->car_model,
                'service_type' => $booking->service_type,
            ],
        ];
    }
}
