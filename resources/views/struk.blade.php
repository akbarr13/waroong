<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $transaction->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            width: 58mm;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 58mm;
            margin: 0;
            padding: 6px 8px;
            color: #000;
        }

        .center { text-align: center; }
        .right  { text-align: right; }
        .bold   { font-weight: bold; }

        .store-name {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 3px;
            margin-bottom: 2px;
        }

        .store-sub {
            font-size: 9px;
            text-align: center;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .divider-solid {
            border-top: 2px solid #000;
            margin: 8px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead th {
            text-align: left;
            font-size: 10px;
            padding: 3px 0;
            border-bottom: 1px dashed #000;
            border-top: 1px dashed #000;
        }

        table thead th:last-child { text-align: right; }

        table tbody td {
            font-size: 10px;
            padding: 5px 0;
            vertical-align: top;
            border-bottom: 1px dotted #ccc;
        }

        table tbody td:last-child { text-align: right; }

        .item-name { font-weight: bold; font-size: 11px; }
        .item-detail { color: #555; font-size: 9px; margin-top: 2px; }

        .total-section {
            margin-top: 6px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            font-size: 11px;
        }

        .grand-total {
            font-size: 14px;
            font-weight: bold;
            padding: 5px 0;
        }

        .payment-badge {
            display: inline-block;
            padding: 3px 10px;
            border: 1px solid #000;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            margin-top: 6px;
            letter-spacing: 1px;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 12px;
            line-height: 1.8;
            color: #333;
        }

        @page {
            size: 58mm auto;
            margin: 0;
        }

        @media print {
            html, body {
                width: 58mm;
                margin: 0;
                padding: 4px 6px;
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    {{-- Header Toko --}}
    <div class="store-name">{{ strtoupper($transaction->user->store_name ?? 'WAROONG') }}</div>
    <div class="store-sub">Kasir &amp; Manajemen Warung</div>
    <div class="divider-solid"></div>

    {{-- Info Transaksi --}}
    <div class="info-row">
        <span>No. Invoice</span>
        <span class="bold">{{ $transaction->invoice_number }}</span>
    </div>
    <div class="info-row">
        <span>Tanggal</span>
        <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <div class="info-row">
        <span>Kasir</span>
        <span>{{ $transaction->user->name ?? '-' }}</span>
    </div>
    @if($transaction->customer)
    <div class="info-row">
        <span>Pelanggan</span>
        <span>{{ $transaction->customer->name }}</span>
    </div>
    @endif
    <div class="divider"></div>

    {{-- Daftar Barang --}}
    <table>
        <thead>
            <tr>
                <th>Barang</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $item)
            <tr>
                <td>
                    <div class="item-name">{{ $item->product->name ?? '-' }}</div>
                    <div class="item-detail">{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                </td>
                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    {{-- Total --}}
    <div class="total-section">
        <div class="total-row grand-total">
            <span>TOTAL</span>
            <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="divider-solid"></div>

    {{-- Metode Pembayaran & Status --}}
    <div class="center" style="margin-top: 4px;">
        <span class="payment-badge">
            {{ match($transaction->payment_method) {
                'cash' => '💵 TUNAI',
                'qris' => '📱 QRIS',
                'debt' => '📋 KASBON',
                default => strtoupper($transaction->payment_method),
            } }}
        </span>

        @if($transaction->status === 'unpaid')
        <div style="margin-top: 4px; font-size: 11px; font-weight: bold;">
            ⚠ BELUM LUNAS
        </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="divider"></div>
    <div class="footer">
        Terima kasih sudah berbelanja!<br>
        Barang yang sudah dibeli tidak dapat dikembalikan.
    </div>

    {{-- Tombol Print (tidak ikut tercetak) --}}
    <div class="no-print" style="text-align:center; margin-top: 16px;">
        <button onclick="window.print()"
            style="padding: 8px 24px; font-size: 14px; cursor: pointer; background: #10b981; color: white; border: none; border-radius: 6px;">
            🖨️ Cetak Struk
        </button>
        <button onclick="window.close()"
            style="padding: 8px 24px; font-size: 14px; cursor: pointer; background: #6b7280; color: white; border: none; border-radius: 6px; margin-left: 8px;">
            Tutup
        </button>
    </div>

    <script>
        window.onload = function () { window.print(); };
    </script>

</body>
</html>
