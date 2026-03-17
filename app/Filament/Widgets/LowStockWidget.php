<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockWidget extends BaseWidget
{
    protected static ?string $heading = 'Stok Menipis';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('stock', '<=', 10)
                    ->orderBy('stock')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Produk')
                    ->description(fn($record) => ($record->category?->name ?? '') . ' · Rp ' . number_format($record->selling_price, 0, ',', '.')),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->extraHeaderAttributes(['class' => 'hidden sm:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden sm:table-cell']),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Sisa Stok')
                    ->badge()
                    ->alignCenter()
                    ->color(fn(int $state): string => $state <= 5 ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Harga Jual')
                    ->money('IDR')
                    ->sortable()
                    ->extraHeaderAttributes(['class' => 'hidden sm:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden sm:table-cell']),
            ])
            ->paginated(false);
    }
}
