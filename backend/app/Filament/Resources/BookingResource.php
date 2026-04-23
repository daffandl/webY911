<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\BookingService;
use App\Services\FonnteService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components as FormComponents;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string | \UnitEnum | null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 1;

    // ─────────────────────────────────────────────────────────────
    //  Form
    // ─────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Informasi Customer')
                    ->description('Data customer yang melakukan booking')
                    ->icon('heroicon-o-user')
                    ->schema([
                        FormComponents\TextInput::make('booking_code')
                            ->label('Kode Booking')
                            ->disabled()
                            ->columnSpanFull()
                            ->helperText('Auto-generated saat booking dibuat'),
                        FormComponents\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        FormComponents\TextInput::make('phone')
                            ->label('No. WhatsApp')
                            ->required()
                            ->tel()
                            ->telRegex('/^[+]?[(]?[0-9]{1,4}[)]?[-\s.]?[(]?[0-9]{1,4}[)]?[-\s.]?[0-9]{1,9}$/')
                            ->columnSpan(2),
                        FormComponents\TextInput::make('email')
                            ->label('Email')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Detail Kendaraan & Layanan')
                    ->description('Informasi kendaraan dan jenis layanan yang diminta')
                    ->icon('heroicon-o-wrench')
                    ->schema([
                        FormComponents\TextInput::make('car_model')
                            ->label('Tipe Mobil')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Range Rover Sport 2020')
                            ->columnSpanFull(),
                        FormComponents\TextInput::make('vehicle_info')
                            ->label('Info Kendaraan (Opsional)')
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->placeholder('Tahun, nomor polisi, warna, dll.'),
                        FormComponents\Select::make('service_type')
                            ->label('Jenis Layanan')
                            ->required()
                            ->options([
                                'maintenance'  => 'Service Berkala / Maintenance',
                                'repair'       => 'Perbaikan / Repair',
                                'diagnostic'   => 'Diagnosa',
                                'oil-change'   => 'Ganti Oli',
                                'brakes'       => 'Sistem Rem',
                                'other'        => 'Lainnya',
                            ])
                            ->native(false)
                            ->searchable()
                            ->columnSpanFull(),
                        FormComponents\DatePicker::make('preferred_date')
                            ->label('Tanggal Pilihan')
                            ->required()
                            ->minDate(now())
                            ->displayFormat('d M Y')
                            ->columnSpan(2),
                        FormComponents\DateTimePicker::make('scheduled_at')
                            ->label('Jadwal Terkonfirmasi')
                            ->nullable()
                            ->displayFormat('d M Y H:i')
                            ->columnSpan(2),
                        FormComponents\Textarea::make('notes')
                            ->label('Catatan / Keluhan Customer')
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->rows(3)
                            ->placeholder('Deskripsikan keluhan atau permintaan customer...'),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Status & Catatan Admin')
                    ->description('Pengaturan status dan catatan internal')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        FormComponents\Select::make('status')
                            ->label('Status Booking')
                            ->required()
                            ->options([
                                'pending'     => '⏳ Pending - Menunggu Konfirmasi',
                                'confirmed'   => '✅ Confirmed - Dikonfirmasi',
                                'rejected'    => '❌ Rejected - Ditolak',
                                'in_progress' => '🔧 In Progress - Dikerjakan',
                                'issue'       => '⚠️ Issue - Ada Masalah',
                                'completed'   => '✨ Completed - Selesai',
                                'cancelled'   => '🚫 Cancelled - Dibatalkan',
                            ])
                            ->default('pending')
                            ->native(false)
                            ->columnSpanFull(),
                        FormComponents\Textarea::make('admin_notes')
                            ->label('Catatan Admin Internal')
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->rows(2)
                            ->placeholder('Catatan untuk keperluan internal (tidak terlihat oleh customer)'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Table
    // ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_code')
                    ->label('Kode Booking')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Customer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('WhatsApp')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('car_model')
                    ->label('Tipe Mobil')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Layanan')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('preferred_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'     => 'warning',
                        'confirmed'   => 'success',
                        'rejected'    => 'danger',
                        'in_progress' => 'primary',
                        'issue'       => 'warning',
                        'completed'   => 'success',
                        'cancelled'   => 'gray',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'     => 'Pending',
                        'confirmed'   => 'Dikonfirmasi',
                        'rejected'    => 'Ditolak',
                        'in_progress' => 'Dikerjakan',
                        'issue'       => 'Masalah',
                        'completed'   => 'Selesai',
                        'cancelled'   => 'Batal',
                        default       => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'     => 'Pending',
                        'confirmed'   => 'Dikonfirmasi',
                        'rejected'    => 'Ditolak',
                        'in_progress' => 'Dikerjakan',
                        'issue'       => 'Ada Masalah',
                        'completed'   => 'Selesai',
                        'cancelled'   => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('service_type')
                    ->label('Jenis Layanan')
                    ->options([
                        'maintenance'  => 'Maintenance',
                        'repair'       => 'Repair',
                        'diagnostic'   => 'Diagnostic',
                        'oil-change'   => 'Oil Change',
                        'brakes'       => 'Brakes',
                        'other'        => 'Other',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    // ── View action ──────────────────────────────────────
                    \Filament\Actions\ViewAction::make(),

                    // ── Edit action ──────────────────────────────────────
                    \Filament\Actions\EditAction::make(),

                    // ── Create Invoice action ───────────────────────────
                    Action::make('create_invoice')
                        ->label('Buat Invoice')
                        ->icon('heroicon-o-document-text')
                        ->color('primary')
                        ->url(fn (Booking $record): string =>
                            route('filament.admin.resources.invoices.create') . '?booking_id=' . $record->id
                        )
                        ->openUrlInNewTab(false)
                        ->visible(fn (Booking $record) => !$record->invoices()->exists()),

                    // ── Confirm action ──────────────────────────────────
                    Action::make('confirm')
                        ->label('Konfirmasi')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Booking $record) => $record->status === 'pending')
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
                        ->modalDescription(function (Booking $record) {
                            return "Status akan berubah dari '⏳ Pending' → '✅ Dikonfirmasi'. Notifikasi WA + Email akan dikirim ke {$record->name}.";
                        })
                        ->action(function (Booking $record, array $data) {
                            if (! empty($data['scheduled_at'])) {
                                $record->update(['scheduled_at' => $data['scheduled_at']]);
                            }

                            app(BookingService::class)->confirmBooking(
                                $record,
                                $data['admin_notes'] ?? null,
                            );

                            Notification::make()
                                ->title('✅ Booking dikonfirmasi!')
                                ->body("Notifikasi WA & Email telah dikirim ke {$record->name}.")
                                ->success()
                                ->send();
                        }),

                    // ── Reject action ───────────────────────────────────
                    Action::make('reject')
                        ->label('Tolak')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Booking $record) => $record->status === 'pending')
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
                        ->action(function (Booking $record, array $data) {
                            app(BookingService::class)->rejectBooking(
                                $record,
                                $data['admin_notes'],
                            );

                            Notification::make()
                                ->title('❌ Booking ditolak')
                                ->body("Notifikasi WA & Email telah dikirim ke {$record->name}.")
                                ->warning()
                                ->send();
                        }),

                    // ── In Progress action ──────────────────────────────
                    Action::make('in_progress')
                        ->label('Mulai Kerjakan')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->color('primary')
                        ->visible(fn (Booking $record) => $record->status === 'confirmed')
                        ->form([
                            FormComponents\Textarea::make('admin_notes')
                                ->label('Catatan Teknisi (opsional)')
                                ->maxLength(500)
                                ->placeholder('Contoh: Mulai pengerjaan ganti oli dan filter.'),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Mulai Pengerjaan')
                        ->modalDescription(function (Booking $record) {
                            return "Status akan berubah dari '✅ Dikonfirmasi' → '🔧 Dikerjakan'. Notifikasi WA + Email akan dikirim ke {$record->name}.";
                        })
                        ->action(function (Booking $record, array $data) {
                            app(BookingService::class)->markInProgress(
                                $record,
                                $data['admin_notes'] ?? null,
                            );

                            Notification::make()
                                ->title('🔧 Pengerjaan dimulai!')
                                ->body("Notifikasi WA & Email telah dikirim ke {$record->name}.")
                                ->info()
                                ->send();
                        }),

                    // ── Issue action ────────────────────────────────────
                    Action::make('issue')
                        ->label('Ada Masalah')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('warning')
                        ->visible(fn (Booking $record) => $record->status === 'in_progress')
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
                        ->action(function (Booking $record, array $data) {
                            app(BookingService::class)->markIssue(
                                $record,
                                $data['admin_notes'],
                            );

                            Notification::make()
                                ->title('⚠️ Masalah dilaporkan')
                                ->body("Notifikasi WA & Email telah dikirim ke {$record->name}.")
                                ->warning()
                                ->send();
                        }),

                    // ── Completed action ────────────────────────────────
                    Action::make('completed')
                        ->label('Selesai')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn (Booking $record) => in_array($record->status, ['in_progress', 'issue']))
                        ->form([
                            FormComponents\Textarea::make('admin_notes')
                                ->label('Catatan Penyelesaian (opsional)')
                                ->maxLength(500)
                                ->placeholder('Contoh: Semua pekerjaan selesai, kendaraan siap diambil.'),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Tandai Selesai')
                        ->modalDescription('Status akan diubah ke Selesai dan notifikasi WA + Email akan dikirim ke customer.')
                        ->action(function (Booking $record, array $data) {
                            app(BookingService::class)->markCompleted(
                                $record,
                                $data['admin_notes'] ?? null,
                            );

                            Notification::make()
                                ->title('✨ Layanan selesai!')
                                ->body("Notifikasi WA & Email telah dikirim ke {$record->name}.")
                                ->success()
                                ->send();
                        }),

                    // ── Cancel action ───────────────────────────────────
                    Action::make('cancel')
                        ->label('Batalkan')
                        ->icon('heroicon-o-no-symbol')
                        ->color('gray')
                        ->visible(fn (Booking $record) => in_array($record->status, ['pending', 'confirmed']))
                        ->form([
                            FormComponents\Textarea::make('admin_notes')
                                ->label('Alasan Pembatalan (opsional)')
                                ->maxLength(500)
                                ->placeholder('Contoh: Pelanggan meminta pembatalan.'),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Batalkan Booking')
                        ->modalDescription('Booking akan dibatalkan dan notifikasi WA + Email akan dikirim ke customer.')
                        ->action(function (Booking $record, array $data) {
                            app(BookingService::class)->markCancelled(
                                $record,
                                $data['admin_notes'] ?? null,
                            );

                            Notification::make()
                                ->title('🚫 Booking dibatalkan')
                                ->body("Notifikasi WA & Email telah dikirim ke {$record->name}.")
                                ->danger()
                                ->send();
                        }),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // ─────────────────────────────────────────────────────────────
    //  Relations & Pages
    // ─────────────────────────────────────────────────────────────

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'view'   => Pages\ViewBooking::route('/{record}'),
            'edit'   => Pages\EditBooking::route('/{record}/edit'),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Navigation badge — show pending count
    // ─────────────────────────────────────────────────────────────

    public static function getNavigationBadge(): ?string
    {
        $pending  = static::getModel()::where('status', 'pending')->count();
        $newToday = static::getModel()::whereDate('created_at', today())
            ->where('status', 'pending')
            ->count();

        if ($newToday > 0) {
            return "{$pending} (+{$newToday} baru)";
        }

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $newToday = static::getModel()::whereDate('created_at', today())
            ->where('status', 'pending')
            ->count();

        return $newToday > 0 ? 'danger' : 'warning';
    }

    // ─────────────────────────────────────────────────────────────
    //  Global Search
    // ─────────────────────────────────────────────────────────────

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        /** @var Booking $record */
        return [
            'Kode Booking' => $record->booking_code,
            'Mobil' => $record->car_model,
            'WhatsApp' => $record->phone,
        ];
    }

    public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): string
    {
        /** @var Booking $record */
        return Pages\ViewBooking::getUrl(['record' => $record]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'booking_code',
            'name',
            'phone',
            'email',
            'car_model',
        ];
    }
}
