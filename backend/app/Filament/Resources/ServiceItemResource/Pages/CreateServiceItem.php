<?php

namespace App\Filament\Resources\ServiceItemResource\Pages;

use App\Filament\Resources\ServiceItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceItem extends CreateRecord
{
    protected static string $resource = ServiceItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Ensure all form data is properly passed to the model.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure is_active has a default value
        if (! isset($data['is_active'])) {
            $data['is_active'] = true;
        }
        
        return $data;
    }
}
