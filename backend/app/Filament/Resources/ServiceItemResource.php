<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceItemResource\Pages;
use App\Models\ServiceItem;
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
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * @deprecated Use JasaResource and SparepartResource instead
 * This combined resource is kept for backward compatibility only.
 */
class ServiceItemResource extends Resource
{
    protected static ?string $model = ServiceItem::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static string | \UnitEnum | null $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Semua Item (Jasa & Sparepart)';

    protected static ?string $modelLabel = 'Item Katalog';

    protected static ?string $pluralModelLabel = 'Semua Item Katalog';

    protected static ?int $navigationSort = 12;

    protected static bool $shouldRegisterNavigation = false;

    // ─────────────────────────────────────────────────────────────
    //  Form
    // ─────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Informasi Item')
                    ->description('Detail jasa atau sparepart untuk katalog')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        FormComponents\TextInput::make('name')
                            ->label('Nama Jasa / Sparepart')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Ganti Oli Mesin, Filter Udara, dll.')
                            ->columnSpanFull(),

                        FormComponents\Select::make('type')
                            ->label('Tipe Item')
                            ->required()
                            ->options([
                                'jasa'      => '🔧 Jasa / Service',
                                'sparepart' => '⚙️ Sparepart / Parts',
                            ])
                            ->native(false)
                            ->searchable()
                            ->columnSpanFull(),

                        FormComponents\TextInput::make('unit')
                            ->label('Satuan')
                            ->maxLength(50)
                            ->placeholder('pcs, liter, jam, set, dll.')
                            ->helperText('Opsional — satuan item ini.')
                            ->columnSpan(2),

                        FormComponents\TextInput::make('price')
                            ->label('Harga Satuan (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->default(0)
                            ->columnSpan(2),

                        FormComponents\Textarea::make('description')
                            ->label('Deskripsi')
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Deskripsi singkat item ini (opsional).')
                            ->columnSpanFull(),

                        FormComponents\Toggle::make('is_active')
                            ->label('Aktif di Katalog')
                            ->default(true)
                            ->helperText('Item nonaktif tidak akan muncul saat membuat invoice.')
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Item')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->colors([
                        'primary' => 'jasa',
                        'warning' => 'sparepart',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'jasa'      => 'Jasa',
                        'sparepart' => 'Sparepart',
                        default     => ucfirst($state),
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'jasa'      => 'Jasa',
                        'sparepart' => 'Sparepart',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->actions([
                ActionGroup::make([
                    \Filament\Actions\ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('type')
            ->groups([
                Tables\Grouping\Group::make('type')
                    ->label('Tipe')
                    ->getTitleFromRecordUsing(fn (ServiceItem $record): string => match ($record->type) {
                        'jasa'      => 'Jasa',
                        'sparepart' => 'Sparepart',
                        default     => ucfirst($record->type),
                    }),
            ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Pages
    // ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServiceItems::route('/'),
            'create' => Pages\CreateServiceItem::route('/create'),
            'edit'   => Pages\EditServiceItem::route('/{record}/edit'),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Navigation badge — total active items
    // ─────────────────────────────────────────────────────────────

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereRaw('is_active IS TRUE')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'gray';
    }

    // ─────────────────────────────────────────────────────────────
    //  Global Search
    // ─────────────────────────────────────────────────────────────

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        /** @var ServiceItem $record */
        return $record->name;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        /** @var ServiceItem $record */
        return [
            'Tipe' => ucfirst($record->type),
            'Harga' => 'Rp ' . number_format($record->price, 0, ',', '.'),
            'Satuan' => $record->unit ?? '-',
            'Status' => $record->is_active ? 'Aktif' : 'Nonaktif',
        ];
    }

    public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): string
    {
        /** @var ServiceItem $record */
        return Pages\EditServiceItem::getUrl(['record' => $record]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'type',
            'unit',
        ];
    }
}
