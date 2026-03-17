<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

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
            if (! $product) {
                Notification::make()
                    ->title('Produk tidak ditemukan')
                    ->body("Produk dengan ID {$item['product_id']} tidak ada.")
                    ->danger()
                    ->send();

                $this->halt();
            }
            if ($product->stock < $item['quantity']) {
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
        $data['status']     = $data['payment_method'] === 'debt' ? 'unpaid' : 'paid';
        $data['user_id']    = auth()->id();

        // FileUpload returns [] when empty — normalize to null
        if (empty($data['payment_proof']) || is_array($data['payment_proof'])) {
            $data['payment_proof'] = is_array($data['payment_proof'] ?? null) && !empty($data['payment_proof'])
                ? array_values($data['payment_proof'])[0]
                : null;
        }

        // Strip keys that don't belong in the transactions table
        unset($data['barcode_scan'], $data['items']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $stockError = null;

        try {
            DB::transaction(function () use (&$stockError) {
                $total = 0;

                foreach ($this->record->items as $item) {
                    $product = Product::lockForUpdate()->find($item->product_id);

                    // Re-check stock inside the lock to prevent race condition
                    if ($product->stock < $item->quantity) {
                        $stockError = "Stok tidak cukup: {$product->name}. Tersisa: {$product->stock}, diminta: {$item->quantity}";
                        throw new \RuntimeException($stockError);
                    }

                    $price = $product->selling_price;
                    $subtotal = $price * $item->quantity;

                    $item->update(['price' => $price, 'subtotal' => $subtotal]);
                    $product->decrement('stock', $item->quantity);

                    $total += $subtotal;
                }

                $this->record->update(['total_amount' => $total]);
            });
        } catch (\RuntimeException $e) {
            // DB transaction rolled back — clean up the orphaned transaction record
            $this->record->items()->delete();
            $this->record->delete();

            Notification::make()
                ->title('Transaksi gagal')
                ->body($stockError)
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
