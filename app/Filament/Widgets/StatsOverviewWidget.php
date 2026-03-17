<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columns = [
        'default' => 2,
        'md'      => 4,
    ];

    protected function getStats(): array
    {
        $pendapatan = Transaction::whereDate('created_at', today())
            ->where('status', 'paid')
            ->sum('total_amount');

        $keuntungan = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->whereDate('transactions.created_at', today())
            ->where('transactions.status', 'paid')
            ->selectRaw('SUM((transaction_items.price - products.purchase_price) * transaction_items.quantity) as profit')
            ->value('profit') ?? 0;

        $kasbon = Transaction::where('status', 'unpaid')->sum('total_amount');

        $jumlahTransaksi = Transaction::whereDate('created_at', today())->count();

        return [
            Stat::make('Omset Hari Ini', 'Rp ' . number_format($pendapatan, 0, ',', '.'))
                ->description('Total penjualan lunas hari ini')
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Estimasi Keuntungan', 'Rp ' . number_format($keuntungan, 0, ',', '.'))
                ->description('Keuntungan kotor hari ini')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('info'),

            Stat::make('Kasbon Aktif', 'Rp ' . number_format($kasbon, 0, ',', '.'))
                ->description('Total utang yang belum lunas')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Transaksi Hari Ini', $jumlahTransaksi)
                ->description('Total transaksi hari ini')
                ->icon('heroicon-o-shopping-cart')
                ->color('primary'),
        ];
    }
}
