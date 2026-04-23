<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Support\Colors\Color;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Review')
                    ->schema([
                        TextEntry::make('user_name')
                            ->label('Nama Pengguna'),

                        TextEntry::make('vehicle_info')
                            ->label('Kendaraan'),

                        TextEntry::make('rating')
                            ->label('Rating')
                            ->badge()
                            ->color(fn (int $state): array => match ($state) {
                                5 => Color::Green,
                                4 => Color::Blue,
                                3 => Color::Amber,
                                default => Color::Red,
                            })
                            ->formatStateUsing(fn (int $state): string => str_repeat('⭐', $state)),

                        TextEntry::make('comment')
                            ->label('Komentar')
                            ->columnSpanFull(),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): array => match ($state) {
                                'approved' => Color::Green,
                                'pending' => Color::Amber,
                                'rejected' => Color::Red,
                                default => Color::Gray,
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'approved' => 'Disetujui',
                                'pending' => 'Menunggu',
                                'rejected' => 'Ditolak',
                                default => ucfirst($state),
                            }),

                        TextEntry::make('booking.booking_code')
                            ->label('Kode Booking'),

                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d F Y, H:i'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime('d F Y, H:i'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit')
                ->icon('heroicon-m-pencil-square'),
                
            Action::make('approve')
                ->label('Setujui')
                ->icon('heroicon-m-check')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (Review $record) {
                    $record->update(['status' => 'approved']);
                    $this->refresh();
                })
                ->visible(fn (Review $record) => $record->status === 'pending'),
                
            Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-m-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Tolak Review')
                ->modalDescription('Apakah Anda yakin ingin menolak review ini?')
                ->action(function (Review $record) {
                    $record->update(['status' => 'rejected']);
                    $this->refresh();
                })
                ->visible(fn (Review $record) => $record->status === 'pending'),
        ];
    }
}
