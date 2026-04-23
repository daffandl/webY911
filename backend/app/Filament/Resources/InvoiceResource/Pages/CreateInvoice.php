<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Services\InvoiceService;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    /**
     * Pre-fill booking_id from query string when coming from BookingResource.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $bookingId = request()->query('booking_id');
        if ($bookingId) {
            $data['booking_id'] = (int) $bookingId;
        }
        return $data;
    }

    /**
     * Ensure invoice_number is not passed to the model (it's auto-generated).
     * Also merges jasa_items and sparepart_items into single items collection.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove invoice_number if present - it will be auto-generated
        unset($data['invoice_number']);

        // Set default issued_at if not set
        if (empty($data['issued_at'])) {
            $data['issued_at'] = now()->toDateString();
        }

        // Ensure tax_percent has a default value (not null)
        if (!isset($data['tax_percent']) || $data['tax_percent'] === null) {
            $data['tax_percent'] = 0;
        }

        // Ensure other numeric fields have default values
        if (!isset($data['tax_amount']) || $data['tax_amount'] === null) {
            $data['tax_amount'] = 0;
        }

        if (!isset($data['discount']) || $data['discount'] === null) {
            $data['discount'] = 0;
        }

        if (!isset($data['subtotal']) || $data['subtotal'] === null) {
            $data['subtotal'] = 0;
        }

        if (!isset($data['total']) || $data['total'] === null) {
            $data['total'] = 0;
        }

        // Merge jasa_items and sparepart_items into single items collection
        return $this->getResource()::mutateFormDataBeforeCreate($data);
    }

    /**
     * Validate items and total after invoice is created.
     */
    protected function afterCreate(): void
    {
        $invoice = $this->getRecord();

        // FIRST: Recalculate totals from items (this updates the total)
        $invoice->recalculate();
        $invoice->refresh(); // Reload fresh data from database

        // Reload invoice with items
        $invoice->load('items');

        // THEN: Validate items
        $itemCount = $invoice->items->count();
        if ($itemCount === 0) {
            // Delete the invoice
            $invoice->delete();
            throw new \RuntimeException('Invoice harus memiliki minimal 1 item. Silakan tambahkan item jasa atau sparepart.');
        }

        // THEN: Validate total > 0 (after recalculation)
        if ($invoice->total <= 0) {
            // Delete the invoice
            $invoice->delete();
            throw new \RuntimeException('Total invoice harus lebih dari 0. Pastikan items memiliki harga yang valid.');
        }

        // Invoice created successfully - admin can send it manually when ready
    }
}
