<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\ServiceItem;
use App\Services\FonnteService;
use App\Services\InvoiceService;
use App\Mail\UserInvoicePaymentLinkMail;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
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

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Bookings';

    protected static ?string $navigationLabel = 'Invoice';

    protected static ?string $modelLabel = 'Invoice';

    protected static ?string $pluralModelLabel = 'Invoice';

    protected static ?int $navigationSort = 2;

    // ─────────────────────────────────────────────────────────────
    //  Form
    // ─────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Informasi Invoice')
                    ->description('Detail invoice dan booking terkait')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        FormComponents\TextInput::make('invoice_number')
                            ->label('Nomor Invoice')
                            ->disabled()
                            ->placeholder('Auto-generated')
                            ->helperText('Nomor invoice dibuat otomatis saat disimpan.')
                            ->columnSpanFull(),

                        FormComponents\Select::make('booking_id')
                            ->label('Booking Terkait')
                            ->relationship(
                                'booking',
                                'booking_code',
                                fn (Builder $query) => $query->orderByDesc('created_at')
                            )
                            ->getOptionLabelFromRecordUsing(fn (Booking $record): string =>
                                "{$record->booking_code} — {$record->name} ({$record->car_model})"
                            )
                            ->searchable(['booking_code', 'name', 'car_model'])
                            ->preload()
                            ->required()
                            ->live()
                            ->columnSpanFull(),

                        FormComponents\Select::make('status')
                            ->label('Status Invoice')
                            ->required()
                            ->options([
                                'draft'     => '📝 Draft',
                                'sent'      => '📤 Terkirim',
                                'paid'      => '✅ Lunas',
                                'cancelled' => '🚫 Dibatalkan',
                            ])
                            ->default('draft')
                            ->native(false)
                            ->columnSpanFull(),

                        FormComponents\DatePicker::make('issued_at')
                            ->label('Tanggal Invoice')
                            ->default(now())
                            ->required()
                            ->displayFormat('d M Y')
                            ->columnSpan(2),

                        FormComponents\DatePicker::make('due_at')
                            ->label('Jatuh Tempo')
                            ->nullable()
                            ->displayFormat('d M Y')
                            ->columnSpan(2),

                        FormComponents\Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(1000)
                            ->rows(2)
                            ->placeholder('Catatan tambahan untuk invoice ini...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // ── Invoice Items: JASA ────────────────────────────────────────
                SchemaComponents\Section::make('Item Jasa')
                    ->description('🔧 Tambahkan jasa / layanan yang diberikan. Total akan dihitung otomatis.')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        FormComponents\Repeater::make('jasa_items')
                            ->relationship('items')
                            ->label('')
                            ->schema([
                                // Pilih dari katalog jasa (opsional)
                                FormComponents\Select::make('service_item_id')
                                    ->label('Pilih dari Katalog Jasa')
                                    ->options(
                                        ServiceItem::active()
                                            ->jasa()
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(fn (ServiceItem $item) => [
                                                $item->id => $item->name . ' — Rp ' . number_format($item->price, 0, ',', '.'),
                                            ])
                                    )
                                    ->searchable()
                                    ->nullable()
                                    ->placeholder('— Pilih dari katalog atau isi manual —')
                                    ->reactive()
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                        if ($state) {
                                            $item = ServiceItem::find($state);
                                            if ($item) {
                                                $set('name',       $item->name);
                                                $set('type',       'jasa');
                                                $set('unit_price', $item->price);
                                                $set('unit',       $item->unit);
                                                $set('description', $item->description);
                                                // Recalculate subtotal
                                                $qty = $get('qty') ?: 1;
                                                $set('subtotal', round($qty * $item->price, 2));
                                            }
                                        }
                                    })
                                    ->columnSpanFull(),

                                FormComponents\Hidden::make('type')
                                    ->default('jasa'),

                                FormComponents\TextInput::make('name')
                                    ->label('Nama Jasa')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Nama jasa')
                                    ->columnSpan(3),

                                FormComponents\TextInput::make('qty')
                                    ->label('Qty')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        $price = $get('unit_price') ?: 0;
                                        $set('subtotal', round(($state ?: 0) * $price, 2));
                                    }),

                                FormComponents\TextInput::make('unit')
                                    ->label('Satuan')
                                    ->maxLength(50)
                                    ->placeholder('jam, set, kali...'),

                                FormComponents\TextInput::make('unit_price')
                                    ->label('Harga Satuan (Rp)')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->minValue(0)
                                    ->default(0)
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        $qty = $get('qty') ?: 1;
                                        $set('subtotal', round($qty * ($state ?: 0), 2));
                                    }),

                                FormComponents\TextInput::make('subtotal')
                                    ->label('Subtotal (Rp)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0)
                                    ->reactive(),

                                FormComponents\Textarea::make('description')
                                    ->label('Keterangan')
                                    ->maxLength(255)
                                    ->rows(1)
                                    ->placeholder('Keterangan tambahan (opsional)')
                                    ->columnSpanFull(),
                            ])
                            ->columns(6)
                            ->addActionLabel('+ Tambah Jasa')
                            ->reorderable('sort_order')
                            ->cloneable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                ($state['name'] ?? 'Jasa') .
                                (isset($state['subtotal']) && $state['subtotal'] > 0
                                    ? ' — Rp ' . number_format($state['subtotal'], 0, ',', '.')
                                    : '')
                            )
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                // Trigger recalculation whenever items change
                                self::recalculateTotals($get, $set);
                            })
                            ->dehydrateStateUsing(function (Get $get, Set $set) {
                                // Ensure totals are calculated before saving
                                self::recalculateTotals($get, $set);
                            }),
                    ]),

                // ── Invoice Items: SPAREPART ────────────────────────────────────────
                SchemaComponents\Section::make('Item Sparepart')
                    ->description('⚙️ Tambahkan sparepart / parts yang digunakan. Total akan dihitung otomatis.')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        FormComponents\Repeater::make('sparepart_items')
                            ->relationship('items')
                            ->label('')
                            ->schema([
                                // Pilih dari katalog sparepart (opsional)
                                FormComponents\Select::make('service_item_id')
                                    ->label('Pilih dari Katalog Sparepart')
                                    ->options(
                                        ServiceItem::active()
                                            ->sparepart()
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(fn (ServiceItem $item) => [
                                                $item->id => $item->name . ' — Rp ' . number_format($item->price, 0, ',', '.'),
                                            ])
                                    )
                                    ->searchable()
                                    ->nullable()
                                    ->placeholder('— Pilih dari katalog atau isi manual —')
                                    ->reactive()
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                        if ($state) {
                                            $item = ServiceItem::find($state);
                                            if ($item) {
                                                $set('name',       $item->name);
                                                $set('type',       'sparepart');
                                                $set('unit_price', $item->price);
                                                $set('unit',       $item->unit);
                                                $set('description', $item->description);
                                                // Recalculate subtotal
                                                $qty = $get('qty') ?: 1;
                                                $set('subtotal', round($qty * $item->price, 2));
                                            }
                                        }
                                    })
                                    ->columnSpanFull(),

                                FormComponents\Hidden::make('type')
                                    ->default('sparepart'),

                                FormComponents\TextInput::make('name')
                                    ->label('Nama Sparepart')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Nama sparepart')
                                    ->columnSpan(3),

                                FormComponents\TextInput::make('qty')
                                    ->label('Qty')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        $price = $get('unit_price') ?: 0;
                                        $set('subtotal', round(($state ?: 0) * $price, 2));
                                    }),

                                FormComponents\TextInput::make('unit')
                                    ->label('Satuan')
                                    ->maxLength(50)
                                    ->placeholder('pcs, set, liter...'),

                                FormComponents\TextInput::make('unit_price')
                                    ->label('Harga Satuan (Rp)')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->minValue(0)
                                    ->default(0)
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        $qty = $get('qty') ?: 1;
                                        $set('subtotal', round($qty * ($state ?: 0), 2));
                                    }),

                                FormComponents\TextInput::make('subtotal')
                                    ->label('Subtotal (Rp)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0)
                                    ->reactive(),

                                FormComponents\Textarea::make('description')
                                    ->label('Keterangan')
                                    ->maxLength(255)
                                    ->rows(1)
                                    ->placeholder('Keterangan tambahan (opsional)')
                                    ->columnSpanFull(),
                            ])
                            ->columns(6)
                            ->addActionLabel('+ Tambah Sparepart')
                            ->reorderable('sort_order')
                            ->cloneable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                ($state['name'] ?? 'Sparepart') .
                                (isset($state['subtotal']) && $state['subtotal'] > 0
                                    ? ' — Rp ' . number_format($state['subtotal'], 0, ',', '.')
                                    : '')
                            )
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                // Trigger recalculation whenever items change
                                self::recalculateTotals($get, $set);
                            })
                            ->dehydrateStateUsing(function (Get $get, Set $set) {
                                // Ensure totals are calculated before saving
                                self::recalculateTotals($get, $set);
                            }),
                    ]),

                // ── Totals ───────────────────────────────────────────────
                SchemaComponents\Section::make('Ringkasan Biaya')
                    ->description('Total akan dihitung otomatis dari item yang ditambahkan')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        FormComponents\TextInput::make('subtotal')
                            ->label('Subtotal (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->default(0)
                            ->columnSpanFull(),

                        FormComponents\TextInput::make('tax_percent')
                            ->label('PPN (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::recalculateTotals($get, $set);
                            })
                            ->columnSpan(2),

                        FormComponents\TextInput::make('tax_amount')
                            ->label('Jumlah PPN (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->default(0)
                            ->columnSpan(2),

                        FormComponents\TextInput::make('discount')
                            ->label('Diskon (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->minValue(0)
                            ->step(1000)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::recalculateTotals($get, $set);
                            })
                            ->columnSpan(2),

                        FormComponents\TextInput::make('total')
                            ->label('TOTAL (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->default(0)
                            ->extraAttributes(['class' => 'font-bold text-lg'])
                            ->columnSpanFull(),
                    ])
                    ->columns(6)
                    ->live(),
            ]);
    }

    /**
     * Recalculate subtotal, tax, and total from repeater items.
     */
    protected static function recalculateTotals(Get $get, Set $set): void
    {
        $jasaItems = $get('jasa_items') ?? [];
        $sparepartItems = $get('sparepart_items') ?? [];

        // Calculate subtotal from all items (jasa + sparepart)
        $subtotal = 0.0;
        
        foreach ($jasaItems as $item) {
            if (is_array($item) && isset($item['subtotal'])) {
                $subtotal += (float) $item['subtotal'];
            }
        }
        
        foreach ($sparepartItems as $item) {
            if (is_array($item) && isset($item['subtotal'])) {
                $subtotal += (float) $item['subtotal'];
            }
        }

        $taxPct   = (float) ($get('tax_percent') ?? 0);
        $discount = (float) ($get('discount') ?? 0);
        $taxAmt   = round($subtotal * $taxPct / 100, 2);
        $total    = max(0, $subtotal + $taxAmt - $discount);

        $set('subtotal', round($subtotal, 2));
        $set('tax_amount', round($taxAmt, 2));
        $set('total', round($total, 2));
    }

    /**
     * Merge jasa_items and sparepart_items into single items collection for saving.
     */
    protected static function mergeItemsForSave(Get $get): array
    {
        $jasaItems = $get('jasa_items') ?? [];
        $sparepartItems = $get('sparepart_items') ?? [];
        
        $mergedItems = [];
        $sortOrder = 0;
        
        // Add jasa items first
        foreach ($jasaItems as $item) {
            if (is_array($item)) {
                $item['sort_order'] = $sortOrder++;
                $mergedItems[] = $item;
            }
        }
        
        // Add sparepart items
        foreach ($sparepartItems as $item) {
            if (is_array($item)) {
                $item['sort_order'] = $sortOrder++;
                $mergedItems[] = $item;
            }
        }
        
        return $mergedItems;
    }

    // ─────────────────────────────────────────────────────────────
    //  Table
    // ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
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

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'     => 'gray',
                        'sent'      => 'info',
                        'paid'      => 'success',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft'     => 'Draft',
                        'sent'      => 'Terkirim',
                        'paid'      => 'Lunas',
                        'cancelled' => 'Batal',
                        default     => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->weight('semibold')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Item')
                    ->counts('items')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('due_at')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'paid' => 'Lunas',
                        'pending' => 'Pending',
                        'failed' => 'Gagal',
                        default => 'Belum Bayar',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Dibayar Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'     => 'Draft',
                        'sent'      => 'Terkirim',
                        'paid'      => 'Lunas',
                        'cancelled' => 'Batal',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    // ── View action ──────────────────────────────────────
                    ViewAction::make(),

                    // ── Edit action ──────────────────────────────────────
                    EditAction::make(),

                    // ── Mark as Sent ────────────────────────────────────
                    Action::make('mark_sent')
                        ->label('Kirim')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->visible(fn (Invoice $record) => $record->status === 'draft')
                        ->requiresConfirmation()
                        ->modalHeading('Tandai Invoice Terkirim')
                        ->modalDescription('Invoice akan dikirim ke customer via WhatsApp dan Email.')
                        ->action(function (Invoice $record) {
                            app(InvoiceService::class)->markAsSent($record);

                            Notification::make()
                                ->title('📤 Invoice dikirim!')
                                ->body("WA & Email telah dikirim ke {$record->booking->name}.")
                                ->success()
                                ->send();
                        }),

                    // ── Mark as Paid ────────────────────────────────────
                    Action::make('mark_paid')
                        ->label('Lunas')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Invoice $record) => in_array($record->status, ['draft', 'sent']))
                        ->requiresConfirmation()
                        ->modalHeading('Tandai Invoice Lunas')
                        ->modalDescription('Pembayaran akan ditandai lunas dan notifikasi akan dikirim ke customer dan admin.')
                        ->action(function (Invoice $record) {
                            app(InvoiceService::class)->markAsPaid($record);

                            Notification::make()
                                ->title('💰 Invoice lunas!')
                                ->body("Notifikasi telah dikirim ke {$record->booking->name} dan admin.")
                                ->success()
                                ->send();
                        }),

                    // ── Mark as Cancelled ───────────────────────────────
                    Action::make('mark_cancelled')
                        ->label('Batalkan')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->visible(fn (Invoice $record) => in_array($record->status, ['draft', 'sent']))
                        ->requiresConfirmation()
                        ->modalHeading('Batalkan Invoice')
                        ->modalDescription('Invoice akan dibatalkan dan notifikasi akan dikirim ke customer.')
                        ->action(function (Invoice $record) {
                            app(InvoiceService::class)->markAsCancelled($record);

                            Notification::make()
                                ->title('🚫 Invoice dibatalkan')
                                ->body("Notifikasi telah dikirim ke {$record->booking->name}.")
                                ->danger()
                                ->send();
                        }),

                    // ── Generate Payment Link ───────────────────────────
                    Action::make('generate_payment_link')
                        ->label('Generate Payment Link')
                        ->icon('heroicon-o-link')
                        ->color('primary')
                        ->visible(fn (Invoice $record) => $record->isPending() && !$record->payment_url)
                        ->requiresConfirmation()
                        ->modalHeading('Generate Payment Link')
                        ->modalDescription('Link pembayaran Midtrans akan dibuat dan dikirim ke customer. PDF invoice akan ter-update dengan payment link.')
                        ->action(function (Invoice $record) {
                            try {
                                $paymentUrl = app(\App\Services\InvoiceService::class)->generatePaymentLinkForInvoice($record);

                                Notification::make()
                                    ->title('✅ Payment Link berhasil dibuat!')
                                    ->body("Link pembayaran telah dikirim ke {$record->booking->name}.")
                                    ->success()
                                    ->actions([
                                        Action::make('copy')
                                            ->label('Salin Link')
                                            ->url($paymentUrl, shouldOpenInNewTab: false)
                                            ->button(),
                                        Action::make('open')
                                            ->label('Buka Link')
                                            ->url($paymentUrl, shouldOpenInNewTab: true)
                                            ->button(),
                                    ])
                                    ->send();

                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('❌ Gagal membuat payment link')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    // ── View Payment Link ───────────────────────────────
                    Action::make('view_payment_link')
                        ->label('Bayar')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->visible(fn (Invoice $record) => $record->payment_url && $record->isPending())
                        ->url(fn (Invoice $record) => $record->payment_url, shouldOpenInNewTab: true),

                    // ── Send Invoice ────────────────────────────────────
                    Action::make('send_invoice')
                        ->label('Kirim Invoice')
                        ->icon('heroicon-o-document')
                        ->color('info')
                        ->visible(fn (Invoice $record) => in_array($record->status, ['draft', 'sent', 'paid']))
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Invoice ke Customer')
                        ->modalDescription('Invoice akan dikirim via WhatsApp dan Email ke customer.')
                        ->action(function (Invoice $record) {
                            $record->load(['booking', 'items']);

                            try {
                                app(FonnteService::class)->notifyUserInvoice($record);
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::error('Invoice WA error: ' . $e->getMessage());
                            }

                            try {
                                if ($record->booking->email) {
                                    \Illuminate\Support\Facades\Mail::to($record->booking->email)
                                        ->send(new \App\Mail\UserInvoiceMail($record));
                                }
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::error('Invoice Email error: ' . $e->getMessage());
                            }

                            if ($record->status === 'draft') {
                                $record->update(['status' => 'sent']);
                            }

                            Notification::make()
                                ->title('📤 Invoice dikirim!')
                                ->body("WA & Email telah dikirim ke {$record->booking->name}.")
                                ->success()
                                ->send();
                        }),

                    // ── Send Payment Link ───────────────────────────────
                    Action::make('send_payment_link')
                        ->label('Kirim Link Pembayaran')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('primary')
                        ->visible(fn (Invoice $record) => $record->isPending())
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Link Pembayaran ke Customer')
                        ->modalDescription('Link pembayaran Midtrans akan dikirim via WhatsApp dan Email ke customer.')
                        ->action(function (Invoice $record) {
                            try {
                                $paymentUrl = $record->getPaymentLink();

                                try {
                                    app(FonnteService::class)->notifyUserPaymentLink(
                                        $record->booking,
                                        $record,
                                        $paymentUrl
                                    );
                                } catch (\Throwable $e) {
                                    \Illuminate\Support\Facades\Log::error('Payment Link WA error: ' . $e->getMessage());
                                }

                                try {
                                    if ($record->booking->email) {
                                        \Illuminate\Support\Facades\Mail::to($record->booking->email)
                                            ->send(new \App\Mail\UserInvoicePaymentLinkMail($record, $paymentUrl));
                                    }
                                } catch (\Throwable $e) {
                                    \Illuminate\Support\Facades\Log::error('Payment Link Email error: ' . $e->getMessage());
                                }

                                if ($record->status === 'draft') {
                                    $record->update(['status' => 'sent']);
                                }

                                Notification::make()
                                    ->title('💳 Link pembayaran dikirim!')
                                    ->body("WA & Email telah dikirim ke {$record->booking->name}.")
                                    ->success()
                                    ->send();

                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('❌ Gagal mengirim link pembayaran')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

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
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view'   => Pages\ViewInvoice::route('/{record}'),
            'edit'   => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    /**
     * Mutate form data before creating invoice.
     * Merge jasa_items and sparepart_items into single items collection.
     */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // Merge items from both repeaters
        $mergedItems = [];
        $sortOrder = 0;
        
        // Add jasa items
        if (isset($data['jasa_items']) && is_array($data['jasa_items'])) {
            foreach ($data['jasa_items'] as $item) {
                $item['sort_order'] = $sortOrder++;
                $item['type'] = 'jasa';
                $mergedItems[] = $item;
            }
            unset($data['jasa_items']);
        }
        
        // Add sparepart items
        if (isset($data['sparepart_items']) && is_array($data['sparepart_items'])) {
            foreach ($data['sparepart_items'] as $item) {
                $item['sort_order'] = $sortOrder++;
                $item['type'] = 'sparepart';
                $mergedItems[] = $item;
            }
            unset($data['sparepart_items']);
        }
        
        $data['items'] = $mergedItems;
        
        return $data;
    }

    /**
     * Mutate form data before editing invoice.
     * Merge jasa_items and sparepart_items into single items collection.
     */
    public static function mutateFormDataBeforeSave(array $data): array
    {
        return self::mutateFormDataBeforeCreate($data);
    }

    /**
     * Mutate form data after retrieving from database for editing.
     * Split items into jasa_items and sparepart_items for separate repeaters.
     */
    public static function mutateFormDataBeforeEdit(array $data): array
    {
        $jasaItems = [];
        $sparepartItems = [];
        
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (($item['type'] ?? '') === 'sparepart') {
                    $sparepartItems[] = $item;
                } else {
                    $jasaItems[] = $item;
                }
            }
            
            $data['jasa_items'] = $jasaItems;
            $data['sparepart_items'] = $sparepartItems;
            unset($data['items']);
        }
        
        return $data;
    }

    // ─────────────────────────────────────────────────────────────
    //  Navigation badge — unpaid invoices
    // ─────────────────────────────────────────────────────────────

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereIn('status', ['draft', 'sent'])->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    // ─────────────────────────────────────────────────────────────
    //  Global Search
    // ─────────────────────────────────────────────────────────────

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        /** @var Invoice $record */
        return $record->invoice_number;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        /** @var Invoice $record */
        return [
            'Booking Code' => $record->booking?->booking_code ?? '-',
            'Customer' => $record->booking?->name ?? '-',
            'Status' => ucfirst($record->status),
            'Total' => 'Rp ' . number_format($record->total, 0, ',', '.'),
        ];
    }

    public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): string
    {
        /** @var Invoice $record */
        return Pages\ViewInvoice::getUrl(['record' => $record]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'invoice_number',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(['booking']);
    }
}
