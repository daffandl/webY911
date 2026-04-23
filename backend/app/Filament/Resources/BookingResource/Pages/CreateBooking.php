<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Ensure booking_code is not passed to the model (it's auto-generated).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove booking_code if present - it will be auto-generated
        unset($data['booking_code']);

        return $data;
    }

    /**
     * Show notification after creating a booking.
     */
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('✅ Booking berhasil dibuat!')
            ->body("{$this->record->booking_code} - {$this->record->name} ({$this->record->car_model})")
            ->success()
            ->icon('heroicon-o-calendar-days')
            ->iconColor('success')
            ->actions([
                Action::make('view')
                    ->button()
                    ->label('Lihat Booking')
                    ->url($this->getResource()::getUrl('view', ['record' => $this->record])),
            ]);
    }
}
