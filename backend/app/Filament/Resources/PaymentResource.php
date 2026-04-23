<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Services\FonnteService;
use App\Services\MidtransService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components as FormComponents;
use Filament\Schemas\Schema;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';

    protected static string | \UnitEnum | null $navigationGroup = 'Bookings';

    protected static ?string $navigationLabel = 'Payment';

    protected static ?string $modelLabel = 'Payment';

    protected static ?string $pluralModelLabel = 'Payments';

    protected static ?int $navigationSort = 3;

    // ─────────────────────────────────────────────────────────────
    //  Form
    // ─────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Informasi Payment')
                    ->description('Detail pembayaran dan invoice terkait')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        FormComponents\TextInput::make('payment_number')
                            ->label('Nomor Payment')
                            ->disabled()
                            ->placeholder('Auto-generated')
                            ->helperText('Nomor payment dibuat otomatis saat disimpan.')
                            ->columnSpanFull(),

                        FormComponents\Select::make('invoice_id')
                            ->label('Invoice Terkait')
                            ->relationship(
                                'invoice',
                                'invoice_number',
                                fn (Builder $query) => $query->orderByDesc('created_at')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record): string =>
                                "{$record->invoice_number} — {$record->booking->name} ({$record->booking->car_model})"
                            )
                            ->searchable(['invoice_number'])
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?int $state) {
                                if ($state) {
                                    $invoice = \App\Models\Invoice::with('booking')->find($state);
                                    if ($invoice) {
                                        $set('booking_id', $invoice->booking_id);
                                        $set('amount', $invoice->total);
                                        $set('gross_amount', $invoice->total);
                                    }
                                }
                            })
                            ->columnSpanFull(),

                        FormComponents\Select::make('booking_id')
                            ->label('Booking Terkait')
                            ->relationship(
                                'booking',
                                'booking_code',
                                fn (Builder $query) => $query->orderByDesc('created_at')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record): string =>
                                "{$record->booking_code} — {$record->name} ({$record->car_model})"
                            )
                            ->searchable(['booking_code', 'name', 'car_model'])
                            ->preload()
                            ->required()
                            ->disabled()
                            ->columnSpanFull(),

                        FormComponents\Select::make('status')
                            ->label('Status Payment')
                            ->required()
                            ->options([
                                'pending'  => '⏳ Menunggu',
                                'success'  => '✅ Berhasil',
                                'failed'   => '❌ Gagal',
                                'refunded' => '💰 Dikembalikan',
                            ])
                            ->default('pending')
                            ->native(false)
                            ->columnSpanFull(),

                        FormComponents\TextInput::make('amount')
                            ->label('Jumlah (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->default(0)
                            ->columnSpan(2),

                        FormComponents\TextInput::make('paid_amount')
                            ->label('Dibayar (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->columnSpan(2),

                        FormComponents\Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'bank_transfer' => 'Transfer Bank',
                                'credit_card'   => 'Kartu Kredit',
                                'gopay'         => 'GoPay',
                                'shopeepay'     => 'ShopeePay',
                                'qris'          => 'QRIS',
                                'indomaret'     => 'Indomaret',
                                'alfamart'      => 'Alfamart',
                            ])
                            ->native(false)
                            ->columnSpan(2),

                        FormComponents\TextInput::make('payment_type')
                            ->label('Tipe Payment')
                            ->maxLength(50)
                            ->placeholder('bank_transfer, credit_card, etc.')
                            ->columnSpan(2),

                        FormComponents\TextInput::make('bank')
                            ->label('Bank')
                            ->maxLength(50)
                            ->placeholder('BCA, MANDIRI, BNI, etc.')
                            ->columnSpan(2),

                        FormComponents\TextInput::make('va_number')
                            ->label('Nomor VA')
                            ->maxLength(50)
                            ->placeholder('Nomor Virtual Account')
                            ->columnSpan(2),

                        FormComponents\DateTimePicker::make('paid_at')
                            ->label('Dibayar Pada')
                            ->displayFormat('d M Y H:i')
                            ->columnSpan(2),

                        FormComponents\Textarea::make('status_message')
                            ->label('Pesan Status')
                            ->maxLength(1000)
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Table
    // ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_number')
                    ->label('No. Payment')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('booking.booking_code')
                    ->label('Kode Booking')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('booking.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('booking.car_model')
                    ->label('Mobil')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('booking.service_type')
                    ->label('Layanan')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'maintenance'  => 'Service Berkala',
                        'repair'       => 'Perbaikan',
                        'diagnostic'   => 'Diagnosa',
                        'oil-change'   => 'Ganti Oli',
                        'brakes'       => 'Sistem Rem',
                        'other'        => 'Lainnya',
                        default        => ucfirst($state ?? '-'),
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('booking.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),

                Tables\Columns\TextColumn::make('booking.phone')
                    ->label('No. HP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->weight('semibold')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'  => 'warning',
                        'success'  => 'success',
                        'failed'   => 'danger',
                        'refunded' => 'info',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'  => 'Menunggu',
                        'success'  => 'Berhasil',
                        'failed'   => 'Gagal',
                        'refunded' => 'Dikembalikan',
                        default    => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode')
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'bank_transfer' => 'Transfer Bank',
                        'credit_card'   => 'Kartu Kredit',
                        'gopay'         => 'GoPay',
                        'shopeepay'     => 'ShopeePay',
                        'qris'          => 'QRIS',
                        'indomaret'     => 'Indomaret',
                        'alfamart'      => 'Alfamart',
                        default         => ucfirst($state ?? '-'),
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('bank')
                    ->label('Bank')
                    ->formatStateUsing(fn ($state): string => strtoupper($state ?? '-'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('va_number')
                    ->label('No. VA')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('midtrans_status')
                    ->label('Midtrans Status')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Dibayar Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                        'pending'  => 'Menunggu',
                        'success'  => 'Berhasil',
                        'failed'   => 'Gagal',
                        'refunded' => 'Dikembalikan',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    // ── View action ──────────────────────────────────────
                    ViewAction::make(),

                    // ── Edit action ──────────────────────────────────────
                    EditAction::make(),

                    // ── Mark as Success ─────────────────────────────────
                    Action::make('mark_success')
                        ->label('Berhasil')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Payment $record) => $record->status === 'pending')
                        ->requiresConfirmation()
                        ->modalHeading('Tandai Payment Berhasil')
                        ->modalDescription('Pembayaran akan ditandai berhasil dan invoice akan lunas.')
                        ->action(function (Payment $record) {
                            $record->markAsSuccess($record->payment_response ?? []);

                            Notification::make()
                                ->title('✅ Payment berhasil!')
                                ->body('Invoice telah ditandai lunas.')
                                ->success()
                                ->send();
                        }),

                    // ── Check Status from Midtrans ──────────────────────
                    Action::make('check_status')
                        ->label('Cek Status Midtrans')
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->visible(fn (Payment $record) => $record->status === 'pending' && $record->transaction_id)
                        ->requiresConfirmation()
                        ->modalHeading('Cek Status dari Midtrans')
                        ->modalDescription('Status pembayaran akan dicek langsung dari Midtrans.')
                        ->action(function (Payment $record) {
                            try {
                                $orderId = $record->transaction_id;

                                if (!$orderId) {
                                    throw new \Exception('Transaction ID tidak ditemukan');
                                }

                                $midtrans = new \App\Services\MidtransService();
                                $status = $midtrans->getTransactionStatus($orderId);

                                if (isset($status['transaction_status'])) {
                                    $paymentStatus = match ($status['transaction_status']) {
                                        'settlement', 'capture' => 'success',
                                        'pending' => 'pending',
                                        'deny', 'expire', 'cancel' => 'failed',
                                        'refund' => 'refunded',
                                        default => 'pending',
                                    };

                                    // Extract payment details from Midtrans response
                                    $paymentType = $status['payment_type'] ?? null;
                                    $bank = $status['bank'] ?? null;
                                    $vaNumber = $status['va_numbers'][0] ?? $status['va_number'] ?? null;
                                    $grossAmount = $status['gross_amount'] ?? $record->amount;
                                    $fraudStatus = $status['fraud_status'] ?? null;

                                    // Map payment type
                                    $paymentMethod = match ($paymentType) {
                                        'bank_transfer' => 'bank_transfer',
                                        'credit_card'   => 'credit_card',
                                        'gopay'         => 'gopay',
                                        'shopeepay'     => 'shopeepay',
                                        'qris'          => 'qris',
                                        'cimb_clicks'   => 'cimb_clicks',
                                        'bca_klikpay'   => 'bca_klikpay',
                                        'bca_klikbca'   => 'bca_klikbca',
                                        'permata_va'    => 'permata_va',
                                        'echannel'      => 'echannel',
                                        'indomaret'     => 'indomaret',
                                        'alfamart'      => 'alfamart',
                                        default         => $paymentType ?? $record->payment_method,
                                    };

                                    $updateData = [
                                        'status' => $paymentStatus,
                                        'midtrans_status' => $status['transaction_status'],
                                        'payment_method' => $paymentMethod,
                                        'payment_type' => $paymentType ?? $record->payment_type,
                                        'bank' => $bank ?? $record->bank,
                                        'va_number' => $vaNumber ?? $record->va_number,
                                        'fraud_status' => $fraudStatus ?? $record->fraud_status,
                                        'status_message' => $status['status_message'] ?? $record->status_message,
                                        'payment_response' => $status,
                                    ];

                                    // If payment is successful, update paid_amount and paid_at
                                    if ($paymentStatus === 'success') {
                                        $updateData['paid_amount'] = $grossAmount;
                                        $updateData['gross_amount'] = $grossAmount;
                                        $updateData['paid_at'] = now();

                                        // Update invoice if payment is successful
                                        if ($record->invoice) {
                                            $record->invoice->markAsPaid($paymentMethod, $grossAmount);
                                        }

                                        // Update booking payment status
                                        if ($record->booking) {
                                            $record->booking->update(['payment_status' => 'paid']);
                                        }
                                    }

                                    $record->update($updateData);

                                    Notification::make()
                                        ->title('✅ Status updated!')
                                        ->body('Status dari Midtrans: ' . ucfirst($status['transaction_status']))
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('⚠️ Status tidak ditemukan')
                                        ->body('Transaksi tidak ditemukan di Midtrans.')
                                        ->warning()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('❌ Gagal cek status')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    // ── Mark as Failed ──────────────────────────────────
                    Action::make('mark_failed')
                        ->label('Gagal')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->visible(fn (Payment $record) => in_array($record->status, ['pending', 'success']))
                        ->requiresConfirmation()
                        ->modalHeading('Tandai Payment Gagal')
                        ->modalDescription(fn (Payment $record) => 'Payment ' . $record->payment_number . ' akan ditandai gagal.')
                        ->action(function (Payment $record) {
                            $record->markAsFailed('Ditandai gagal oleh admin.');

                            Notification::make()
                                ->title('❌ Payment ditandai gagal')
                                ->body('Customer akan diberitahu tentang kegagalan pembayaran.')
                                ->danger()
                                ->send();
                        }),

                    // ── Regenerate Payment Link ─────────────────────────
                    Action::make('regenerate_payment_link')
                        ->label('Regenerate Link')
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->visible(fn (Payment $record) => $record->status === 'pending')
                        ->requiresConfirmation()
                        ->modalHeading('Regenerate Link Pembayaran')
                        ->modalDescription('Link pembayaran Midtrans akan dibuat ulang.')
                        ->action(function (Payment $record) {
                            try {
                                if ($record->invoice) {
                                    $paymentUrl = $record->invoice->generatePaymentLink();

                                    $record->update([
                                        'payment_url' => $paymentUrl,
                                    ]);

                                    Notification::make()
                                        ->title('🔗 Link pembayaran di-regenerate!')
                                        ->body('Link pembayaran baru telah dibuat.')
                                        ->success()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('❌ Gagal regenerate link')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    // ── Send Payment Link ───────────────────────────────
                    Action::make('send_payment_link')
                        ->label('Kirim Link')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->visible(fn (Payment $record) => $record->status === 'pending' && $record->payment_url)
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Link Pembayaran ke Customer')
                        ->modalDescription('Link pembayaran akan dikirim via WhatsApp dan Email ke customer.')
                        ->action(function (Payment $record) {
                            try {
                                if ($record->invoice && $record->booking) {
                                    app(FonnteService::class)->notifyUserPaymentLink(
                                        $record->booking,
                                        $record->invoice,
                                        $record->payment_url
                                    );

                                    \Illuminate\Support\Facades\Mail::to($record->booking->email)
                                        ->send(new \App\Mail\UserInvoicePaymentLinkMail($record->invoice, $record->payment_url));

                                    Notification::make()
                                        ->title('📤 Link pembayaran dikirim!')
                                        ->body("WA & Email telah dikirim ke {$record->booking->name}.")
                                        ->success()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('❌ Gagal mengirim link')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    // ── View Payment URL ────────────────────────────────
                    Action::make('view_payment_url')
                        ->label('Buka Link')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->color('success')
                        ->visible(fn (Payment $record) => $record->payment_url)
                        ->url(fn (Payment $record) => $record->payment_url, shouldOpenInNewTab: true),

                    // ── Delete ──────────────────────────────────────────
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // ─────────────────────────────────────────────────────────────
    //  Pages
    // ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view'   => Pages\ViewPayment::route('/{record}'),
            'edit'   => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Navigation badge — pending payments
    // ─────────────────────────────────────────────────────────────

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    // ─────────────────────────────────────────────────────────────
    //  Global Search
    // ─────────────────────────────────────────────────────────────

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        /** @var Payment $record */
        return $record->payment_number;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        /** @var Payment $record */
        return [
            'Invoice' => $record->invoice?->invoice_number ?? '-',
            'Customer' => $record->booking?->name ?? '-',
            'Amount' => 'Rp ' . number_format($record->amount, 0, ',', '.'),
            'Status' => ucfirst($record->status),
        ];
    }

    public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): string
    {
        /** @var Payment $record */
        return Pages\ViewPayment::getUrl(['record' => $record]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'payment_number',
            'transaction_id',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(['invoice', 'booking']);
    }
}
