<?php

namespace App\Filament\Resources\SparepartResource\Pages;

use App\Filament\Resources\SparepartResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSparepart extends CreateRecord
{
    protected static string $resource = SparepartResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'sparepart';

        // Ensure is_active is boolean (PostgreSQL compatibility)
        $data['is_active'] = filter_var($data['is_active'] ?? '1', FILTER_VALIDATE_BOOLEAN);

        return $data;
    }
}
