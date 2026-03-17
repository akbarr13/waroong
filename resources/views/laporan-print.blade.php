<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; padding: 24px; }

        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: bold; }
        .header p  { font-size: 12px; color: #555; margin-top: 4px; }

        .meta { display: flex; justify-content: space-between; margin-bottom: 16px; font-size: 12px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead th { background: #f3f4f6; padding: 8px 10px; text-align: left; border: 1px solid #d1d5db; font-size: 11px; }
        tbody td { padding: 7px 10px; border: 1px solid #e5e7eb; font-size: 11px; }
        tbody tr:nth-child(even) { background: #f9fafb; }

        .total-row { font-weight: bold; background: #f3f4f6 !important; }
        .text-right { text-align: right; }
        .badge-lunas    { color: #15803d; font-weight: bold; }
        .badge-belum    { color: #b45309; font-weight: bold; }

        .summary { margin-top: 12px; border-top: 2px solid #000; padding-top: 10px; }
        .summary-row { display: flex; justify-content: space-between; padding: 3px 0; }
        .summary-row.grand { font-size: 14px; font-weight: bold; border-top: 1px solid #ccc; margin-top: 4px; padding-top: 6px; }

        .no-print { text-align: center; margin-top: 20px; }
        .no-print button { padding: 8px 24px; font-size: 14px; cursor: pointer; border: none; border-radius: 6px; margin: 0 4px; }
        .btn-print { background: #059669; color: white; }
        .btn-close  { background: #6b7280; color: white; }

        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="header">
    <h1>{{ strtoupper($user->store_name ?? 'WAROONG') }}</h1>
    @if($user->store_address)<p>{{ $user->store_address }}</p>@endif
    @if($user->store_phone)<p>{{ $user->store_phone }}</p>@endif
    <p style="margin-top:8px; font-size:14px; font-weight:bold;">LAPORAN TRANSAKSI</p>
</div>

<div class="meta">
    <span>Periode: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '-' }} s/d {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : '-' }}</span>
    <span>Dicetak: {{ now()->format('d/m/Y H:i') }}</span>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>No. Invoice</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>Metode</th>
            <th>Status</th>
            <th class="text-right">Total (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($transactions as $i => $t)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $t->invoice_number }}</td>
            <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $t->customer->name ?? 'Umum' }}</td>
            <td>{{ match($t->payment_method) { 'cash' => 'Tunai', 'qris' => 'QRIS', 'debt' => 'Kasbon', default => $t->payment_method } }}</td>
            <td class="{{ $t->status === 'paid' ? 'badge-lunas' : 'badge-belum' }}">
                {{ $t->status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
            </td>
            <td class="text-right">{{ number_format($t->total_amount, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center; color:#888;">Tidak ada transaksi</td></tr>
        @endforelse
    </tbody>
</table>

<div class="summary">
    <div class="summary-row"><span>Total Transaksi</span><span>{{ $transactions->count() }}</span></div>
    <div class="summary-row"><span>Transaksi Lunas</span><span>{{ $transactions->where('status', 'paid')->count() }}</span></div>
    <div class="summary-row"><span>Kasbon Belum Lunas</span><span>{{ $transactions->where('status', 'unpaid')->count() }}</span></div>
    <div class="summary-row grand"><span>Total Pendapatan (Lunas)</span><span>Rp {{ number_format($total, 0, ',', '.') }}</span></div>
</div>

<div class="no-print" style="margin-top:24px;">
    <button class="btn-print" onclick="window.print()">🖨️ Cetak</button>
    <button class="btn-close" onclick="window.close()">Tutup</button>
</div>

<script>window.onload = function() { window.print(); };</script>
</body>
</html>
