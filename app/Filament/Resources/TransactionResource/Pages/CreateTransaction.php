<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function beforeCreate(): void
    {
        if (empty($this->data['items'])) {
            Notification::make()
                ->title('Keranjang kosong')
                ->body('Tambahkan barang sebelum menyimpan transaksi.')
                ->danger()
                ->send();

            $this->halt();
        }

        foreach ($this->data['items'] as $item) {
            $product = Product::find($item['product_id']);
            if ($product && $product->stock < $item['quantity']) {
                Notification::make()
                    ->title("Stok tidak cukup: {$product->name}")
                    ->body("Stok tersisa: {$product->stock}, kamu minta: {$item['quantity']}")
                    ->danger()
                    ->send();

                $this->halt();
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = $data['payment_method'] === 'debt' ? 'unpaid' : 'paid';

        return $data;
    }

    protected function afterCreate(): void
    {
        $total = 0;

        foreach ($this->record->items as $item) {
            $price = $item->product->selling_price;
            $subtotal = $price * $item->quantity;

            $item->update(['price' => $price, 'subtotal' => $subtotal]);
            $item->product->decrement('stock', $item->quantity);

            $total += $subtotal;
        }

        $this->record->update(['total_amount' => $total]);
    }
}
