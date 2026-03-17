<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Barang Terlaris';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model $record): string
    {
        return (string) $record->product_id;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TransactionItem::query()
                    ->selectRaw('product_id, SUM(quantity) as total_terjual, SUM(subtotal) as total_pendapatan')
                    ->groupBy('product_id')
                    ->orderByDesc('total_terjual')
                    ->limit(5)
                    ->with('product.category')
            )
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->description(fn($record) => $record->product?->category?->name ?? '-'),
                Tables\Columns\TextColumn::make('product.category.name')
                    ->label('Kategori')
                    ->getStateUsing(fn($record) => $record->product?->category?->name ?? '-')
                    ->extraHeaderAttributes(['class' => 'hidden sm:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden sm:table-cell']),
                Tables\Columns\TextColumn::make('total_terjual')
                    ->label('Terjual')
                    ->suffix(' pcs')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_pendapatan')
                    ->label('Pendapatan')
                    ->money('IDR')
                    ->sortable()
                    ->extraHeaderAttributes(['class' => 'hidden sm:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden sm:table-cell']),
            ])
            ->paginated(false);
    }
}
