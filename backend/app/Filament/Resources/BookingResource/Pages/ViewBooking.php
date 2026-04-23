<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Filament\Actions;
use Filament\Forms\Components as FormComponents;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── Confirm action ──────────────────────────────────────────
            Actions\Action::make('confirm')
                ->label('Konfirmasi Booking')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'pending')
                ->form([
                    FormComponents\DateTimePicker::make('scheduled_at')
                        ->label('Jadwal Terkonfirmasi')
                        ->nullable()
                        ->helperText('Opsional — isi jika ingin menentukan jadwal pasti.'),
                    FormComponents\Textarea::make('admin_notes')
                        ->label('Catatan untuk Customer (opsional)')
                        ->maxLength(500),
                ])
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Booking')
                ->modalDescription('Booking akan dikonfirmasi dan notifikasi WA + Email akan dikirim ke customer.')
                ->action(function (array $data) {
                    /** @var Booking $booking */
                    $booking = $this->record;

                    if (! empty($data['scheduled_at'])) {
                        $booking->update(['scheduled_at' => $data['scheduled_at']]);
                    }

                    app(BookingService::class)->confirmBooking(
                        $booking,
                        $data['admin_notes'] ?? null,
                    );

                    Notification::make()
                        ->title('✅ Booking dikonfirmasi!')
                        ->body("Notifikasi WA & Email telah dikirim ke {$booking->name}.")
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'admin_notes', 'scheduled_at']);
                }),

            // ── Reject action ───────────────────────────────────────────
            Actions\Action::make('reject')
                ->label('Tolak Booking')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status === 'pending')
                ->form([
                    FormComponents\Textarea::make('admin_notes')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->maxLength(500)
                        ->placeholder('Contoh: Jadwal penuh, silakan pilih tanggal lain.'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Tolak Booking')
                ->modalDescription('Booking akan ditolak dan notifikasi WA + Email akan dikirim ke customer.')
                ->action(function (array $data) {
                    /** @var Booking $booking */
                    $booking = $this->record;

                    app(BookingService::class)->rejectBooking(
                        $booking,
                        $data['admin_notes'],
                    );

                    Notification::make()
                        ->title('❌ Booking ditolak')
                        ->body("Notifikasi WA & Email telah dikirim ke {$booking->name}.")
                        ->warning()
                        ->send();

                    $this->refreshFormData(['status', 'admin_notes']);
                }),

            // ── In Progress action ──────────────────────────────────────
            Actions\Action::make('in_progress')
                ->label('Mulai Kerjakan')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('primary')
                ->visible(fn () => $this->record->status === 'confirmed')
                ->form([
                    FormComponents\Textarea::make('admin_notes')
                        ->label('Catatan Teknisi (opsional)')
                        ->maxLength(500)
                        ->placeholder('Contoh: Mulai pengerjaan ganti oli dan filter.'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Mulai Pengerjaan')
                ->modalDescription('Status akan diubah ke Sedang Dikerjakan dan notifikasi WA + Email akan dikirim ke customer.')
                ->action(function (array $data) {
                    /** @var Booking $booking */
                    $booking = $this->record;

                    app(BookingService::class)->markInProgress(
                        $booking,
                        $data['admin_notes'] ?? null,
                    );

                    Notification::make()
                        ->title('🔧 Pengerjaan dimulai!')
                        ->body("Notifikasi WA & Email telah dikirim ke {$booking->name}.")
                        ->info()
                        ->send();

                    $this->refreshFormData(['status', 'admin_notes']);
                }),

            // ── Issue action ────────────────────────────────────────────
            Actions\Action::make('issue')
                ->label('Ada Masalah')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->visible(fn () => $this->record->status === 'in_progress')
                ->form([
                    FormComponents\Textarea::make('admin_notes')
                        ->label('Detail Masalah')
                        ->required()
                        ->maxLength(500)
                        ->placeholder('Contoh: Ditemukan kerusakan pada komponen suspensi, perlu penggantian part.'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Laporkan Masalah')
                ->modalDescription('Status akan diubah ke Ada Masalah dan notifikasi WA + Email akan dikirim ke customer.')
                ->action(function (array $data) {
                    /** @var Booking $booking */
                    $booking = $this->record;

                    app(BookingService::class)->markIssue(
                        $booking,
                        $data['admin_notes'],
                    );

                    Notification::make()
                        ->title('⚠️ Masalah dilaporkan')
                        ->body("Notifikasi WA & Email telah dikirim ke {$booking->name}.")
                        ->warning()
                        ->send();

                    $this->refreshFormData(['status', 'admin_notes']);
                }),

            // ── Completed action ────────────────────────────────────────
            Actions\Action::make('completed')
                ->label('Tandai Selesai')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['in_progress', 'issue']))
                ->form([
                    FormComponents\Textarea::make('admin_notes')
                        ->label('Catatan Penyelesaian (opsional)')
                        ->maxLength(500)
                        ->placeholder('Contoh: Semua pekerjaan selesai, kendaraan siap diambil.'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Tandai Selesai')
                ->modalDescription('Status akan diubah ke Selesai dan notifikasi WA + Email akan dikirim ke customer.')
                ->action(function (array $data) {
                    /** @var Booking $booking */
                    $booking = $this->record;

                    app(BookingService::class)->markCompleted(
                        $booking,
                        $data['admin_notes'] ?? null,
                    );

                    Notification::make()
                        ->title('✨ Layanan selesai!')
                        ->body("Notifikasi WA & Email telah dikirim ke {$booking->name}.")
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'admin_notes']);
                }),

            // ── Cancel action ───────────────────────────────────────────
            Actions\Action::make('cancel')
                ->label('Batalkan Booking')
                ->icon('heroicon-o-no-symbol')
                ->color('gray')
                ->visible(fn () => in_array($this->record->status, ['pending', 'confirmed']))
                ->form([
                    FormComponents\Textarea::make('admin_notes')
                        ->label('Alasan Pembatalan (opsional)')
                        ->maxLength(500)
                        ->placeholder('Contoh: Pelanggan meminta pembatalan.'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Batalkan Booking')
                ->modalDescription('Booking akan dibatalkan dan notifikasi WA + Email akan dikirim ke customer.')
                ->action(function (array $data) {
                    /** @var Booking $booking */
                    $booking = $this->record;

                    app(BookingService::class)->markCancelled(
                        $booking,
                        $data['admin_notes'] ?? null,
                    );

                    Notification::make()
                        ->title('🚫 Booking dibatalkan')
                        ->body("Notifikasi WA & Email telah dikirim ke {$booking->name}.")
                        ->danger()
                        ->send();

                    $this->refreshFormData(['status', 'admin_notes']);
                }),

            Actions\EditAction::make(),

            // ── Create / View Invoice ──────────────────────────────────
            Actions\Action::make('create_invoice')
                ->label('Buat Invoice')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn (): string =>
                    route('filament.admin.resources.invoices.create') . '?booking_id=' . $this->record->id
                ),
        ];
    }
}
