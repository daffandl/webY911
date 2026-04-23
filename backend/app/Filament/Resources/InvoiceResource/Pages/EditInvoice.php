<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    /**
     * Mutate form data before saving to merge items.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->getResource()::mutateFormDataBeforeSave($data);
    }

    /**
     * Mutate form data before editing to split items.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->getResource()::mutateFormDataBeforeEdit($data);
    }

    /**
     * After saving, recalculate totals from items.
     */
    protected function afterSave(): void
    {
        $this->getRecord()->recalculate();
    }
}
