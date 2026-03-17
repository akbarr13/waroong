<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function exportCsv(Request $request)
    {
        $dateFrom = $request->query('from');
        $dateTo   = $request->query('to');

        $transactions = Transaction::with('items.product', 'customer')
            ->where('user_id', auth()->id())
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('created_at')
            ->get();

        $filename = 'laporan-transaksi-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel

            fputcsv($file, ['No. Invoice', 'Tanggal', 'Pelanggan', 'Metode', 'Status', 'Total (Rp)']);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->invoice_number,
                    $t->created_at->format('d/m/Y H:i'),
                    $t->customer->name ?? 'Umum',
                    match($t->payment_method) {
                        'cash' => 'Tunai', 'qris' => 'QRIS', 'debt' => 'Kasbon', default => $t->payment_method,
                    },
                    $t->status === 'paid' ? 'Lunas' : 'Belum Lunas',
                    $t->total_amount,
                ]);
            }

            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function cetakPdf(Request $request)
    {
        $dateFrom = $request->query('from');
        $dateTo   = $request->query('to');

        $transactions = Transaction::with('items.product', 'customer')
            ->where('user_id', auth()->id())
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('created_at')
            ->get();

        $total    = $transactions->where('status', 'paid')->sum('total_amount');
        $user     = auth()->user();

        return view('laporan-print', compact('transactions', 'total', 'dateFrom', 'dateTo', 'user'));
    }
}
