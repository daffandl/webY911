<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Siap</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #166534; padding: 30px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; }
        .header p { color: #bbf7d0; margin: 8px 0 0; font-size: 14px; }
        .body { padding: 32px 40px; }
        .invoice-box { background: #f0fdf4; border: 2px solid #166534; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
        .invoice-number { font-size: 24px; font-weight: 800; color: #166534; font-family: monospace; letter-spacing: 2px; }
        .total-amount { font-size: 28px; font-weight: 800; color: #166534; margin-top: 8px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        .info-table td:first-child { color: #6b7280; width: 40%; }
        .info-table td:last-child { color: #111827; font-weight: 500; }
        .items-table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 12px; }
        .items-table th { background: #166534; color: white; padding: 8px 10px; text-align: left; }
        .items-table th:last-child { text-align: right; }
        .items-table td { padding: 8px 10px; border-bottom: 1px solid #f3f4f6; }
        .items-table td:last-child { text-align: right; font-weight: 600; }
        .items-table tr:nth-child(even) td { background: #f9fafb; }
        .btn { display: inline-block; background-color: #166534; color: #ffffff; padding: 14px 32px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 15px; margin-top: 20px; }
        .footer { background-color: #f9fafb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🧾 Invoice Siap</h1>
        <p>Young 911 Autowerks</p>
    </div>
    <div class="body">
        <p>Halo <strong>{{ $invoice->booking->name }}</strong>,</p>
        <p>Invoice untuk layanan kendaraan Anda telah diterbitkan. Berikut rinciannya:</p>

        <div class="invoice-box">
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Nomor Invoice</div>
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            <div style="font-size:12px; color:#6b7280; margin-top:12px;">Total Tagihan</div>
            <div class="total-amount">Rp {{ number_format($invoice->total, 0, ',', '.') }}</div>
        </div>

        <table class="info-table">
            <tr>
                <td>Kode Booking</td>
                <td><strong>{{ $invoice->booking->booking_code }}</strong></td>
            </tr>
            <tr>
                <td>Kendaraan</td>
                <td>{{ $invoice->booking->car_model }}</td>
            </tr>
            <tr>
                <td>Layanan</td>
                <td>{{ $invoice->booking->service_type }}</td>
            </tr>
            <tr>
                <td>Tanggal Invoice</td>
                <td>{{ $invoice->issued_at?->format('d M Y') ?? '-' }}</td>
            </tr>
            @if($invoice->due_at)
            <tr>
                <td>Jatuh Tempo</td>
                <td>{{ $invoice->due_at->format('d M Y') }}</td>
            </tr>
            @endif
            <tr>
                <td>Status</td>
                <td><strong>{{ $invoice->status_label }}</strong></td>
            </tr>
        </table>

        @if($invoice->items->count() > 0)
        <p style="font-size:13px; font-weight:600; color:#374151; margin-bottom:8px;">Rincian Item:</p>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ number_format($item->qty, 0) }} {{ $item->unit }}</td>
                    <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div style="text-align:center; margin-top:24px;">
            <a href="{{ url('/invoice/' . $invoice->invoice_number . '/print') }}" class="btn">
                🖨️ Lihat & Unduh Invoice
            </a>
        </div>

        @if($invoice->notes)
        <div style="margin-top:20px; padding:12px 16px; background:#f9fafb; border-left:4px solid #166534; border-radius:4px; font-size:13px; color:#374151;">
            <strong>Catatan:</strong> {{ $invoice->notes }}
        </div>
        @endif
    </div>
    <div class="footer">
        <p>Young 911 Autowerks &bull; Jl. Raya Utama No. 911, Jakarta</p>
        <p>📞 +62 812 3456 7890 &bull; Email ini dikirim otomatis, mohon tidak membalas.</p>
    </div>
</div>
</body>
</html>
