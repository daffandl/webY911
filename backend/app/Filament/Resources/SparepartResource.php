<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SparepartResource\Pages;
use App\Models\ServiceItem;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components as FormComponents;
use Filament\Schemas\Schema;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SparepartResource extends Resource
{
    protected static ?string $model = ServiceItem::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string | \UnitEnum | null $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Katalog Sparepart';

    protected static ?string $modelLabel = 'Sparepart';

    protected static ?string $pluralModelLabel = 'Sparepart';

    protected static ?int $navigationSort = 11;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'sparepart');
    }

    // ─────────────────────────────────────────────────────────────
    //  Form
    // ─────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Informasi Sparepart')
                    ->description('Detail sparepart / parts untuk katalog')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        FormComponents\TextInput::make('name')
                            ->label('Nama Sparepart')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Filter Udara, Brake Pad, Oli Mesin, dll.')
                            ->columnSpanFull(),

                        FormComponents\Hidden::make('type')
                            ->default('sparepart'),

                        FormComponents\TextInput::make('unit')
                            ->label('Satuan')
                            ->maxLength(50)
                            ->placeholder('pcs, set, liter, box, dll.')
                            ->helperText('Opsional — satuan sparepart ini.')
                            ->columnSpan(2),

                        FormComponents\TextInput::make('price')
                            ->label('Harga Sparepart (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->default(0)
                            ->columnSpan(2),

                        FormComponents\Textarea::make('description')
                            ->label('Deskripsi Sparepart')
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Deskripsi singkat sparepart ini (opsional).')
                            ->columnSpanFull(),

                        FormComponents\Select::make('is_active')
                            ->label('Status Aktif')
                            ->options([
                                '1' => 'Aktif',
                                '0' => 'Nonaktif',
                            ])
                            ->default('1')
                            ->helperText('Sparepart nonaktif tidak akan muncul saat membuat invoice.')
                            ->columnSpanFull()
                            ->native(false),
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
                    ->label('Nama Sparepart')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

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
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    // ─────────────────────────────────────────────────────────────
    //  Pages
    // ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSpareparts::route('/'),
            'create' => Pages\CreateSparepart::route('/create'),
            'edit'   => Pages\EditSparepart::route('/{record}/edit'),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Navigation badge — total active items
    // ─────────────────────────────────────────────────────────────

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('type', 'sparepart')->whereRaw('is_active IS TRUE')->count();
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
        /** @var ServiceItem $record */
        return $record->name;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        /** @var ServiceItem $record */
        return [
            'Tipe' => 'Sparepart',
            'Harga' => 'Rp ' . number_format($record->price, 0, ',', '.'),
            'Satuan' => $record->unit ?? '-',
            'Status' => $record->is_active ? 'Aktif' : 'Nonaktif',
        ];
    }

    public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): string
    {
        /** @var ServiceItem $record */
        return Pages\EditSparepart::getUrl(['record' => $record]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'unit'];
    }
}
