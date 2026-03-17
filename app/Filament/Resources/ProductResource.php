<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = "heroicon-o-archive-box";

    // UX Tweaks: Mengelompokkan menu dan memberi nama yang lebih jelas
    protected static ?string $navigationGroup = "Master Data";
    protected static ?string $navigationLabel = "Produk / Barang";
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Informasi Produk")
                ->schema([
                    Forms\Components\TextInput::make("name")
                        ->label("Nama Barang")
                        ->required()
                        ->maxLength(255)
                        ->columnSpan("full"),
                    Forms\Components\Select::make("category_id")
                        ->relationship("category", "name")
                        ->label("Kategori")
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make("name")
                                ->label("Nama Kategori")
                                ->required()
                                ->maxLength(255),
                        ])
                        ->required(),
                    Forms\Components\TextInput::make("sku")
                        ->label("SKU / Barcode")
                        ->placeholder("Scan barcode atau ketik manual...")
                        ->prefix("🔍")
                        ->helperText("Fokus ke field ini lalu scan, atau tap tombol kamera.")
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('buka_kamera_sku')
                                ->icon('heroicon-o-camera')
                                ->label('Kamera')
                                ->extraAttributes(['onclick' => "openCameraScanner('sku'); return false;"])
                        )
                        ->maxLength(255),
                ])
                ->columns(['default' => 1, 'sm' => 2]),

            Forms\Components\Section::make("Harga & Stok")
                ->schema([
                    Forms\Components\TextInput::make("purchase_price")
                        ->label("Harga Modal")
                        ->required()
                        ->numeric()
                        ->prefix("Rp"),
                    Forms\Components\TextInput::make("selling_price")
                        ->label("Harga Jual")
                        ->required()
                        ->numeric()
                        ->prefix("Rp"),
                    Forms\Components\TextInput::make("stock")
                        ->label("Stok Awal")
                        ->required()
                        ->numeric()
                        ->default(0),
                ])
                ->columns(['default' => 1, 'sm' => 3]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")
                    ->label("Nama Barang")
                    ->searchable()
                    ->description(fn(Product $record): string => ($record->category?->name ?? '') . ' · Rp ' . number_format($record->selling_price, 0, ',', '.')),
                Tables\Columns\TextColumn::make("category.name")
                    ->label("Kategori")
                    ->sortable()
                    ->extraHeaderAttributes(['class' => 'hidden sm:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden sm:table-cell']),
                Tables\Columns\TextColumn::make("sku")
                    ->label("SKU")
                    ->searchable()
                    ->extraHeaderAttributes(['class' => 'hidden md:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden md:table-cell']),
                Tables\Columns\TextColumn::make("purchase_price")
                    ->label("Harga Modal")
                    ->money('IDR')
                    ->sortable()
                    ->extraHeaderAttributes(['class' => 'hidden md:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden md:table-cell']),
                Tables\Columns\TextColumn::make("selling_price")
                    ->label("Harga Jual")
                    ->money('IDR')
                    ->sortable()
                    ->extraHeaderAttributes(['class' => 'hidden sm:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden sm:table-cell']),
                Tables\Columns\TextColumn::make("stock")
                    ->label("Stok")
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn(int $state): string => match(true) {
                        $state <= 5 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make("created_at")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make("updated_at")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
                //
            ];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListProducts::route("/"),
            "create" => Pages\CreateProduct::route("/create"),
        ];
    }
}
