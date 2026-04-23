<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    /**
     * Pre-fill invoice_id and booking_id from query string.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $invoiceId = request()->query('invoice_id');
        if ($invoiceId) {
            $data['invoice_id'] = (int) $invoiceId;
        }

        $bookingId = request()->query('booking_id');
        if ($bookingId) {
            $data['booking_id'] = (int) $bookingId;
        }

        return $data;
    }

    /**
     * Ensure payment_number is not passed to the model (it's auto-generated).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['payment_number']);
        return $data;
    }
}
