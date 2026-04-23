<?php

namespace App\Filament\Resources\JasaResource\Pages;

use App\Filament\Resources\JasaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJasa extends CreateRecord
{
    protected static string $resource = JasaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'jasa';

        // Ensure is_active is boolean (PostgreSQL compatibility)
        $data['is_active'] = filter_var($data['is_active'] ?? '1', FILTER_VALIDATE_BOOLEAN);

        return $data;
    }
}
