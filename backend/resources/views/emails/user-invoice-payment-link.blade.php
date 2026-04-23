<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Pembayaran Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #2563eb; padding: 30px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; }
        .header p { color: #bfdbfe; margin: 8px 0 0; font-size: 14px; }
        .body { padding: 32px 40px; }
        .payment-box { background: #eff6ff; border: 2px solid #2563eb; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
        .invoice-number { font-size: 24px; font-weight: 800; color: #2563eb; font-family: monospace; letter-spacing: 2px; }
        .total-amount { font-size: 28px; font-weight: 800; color: #2563eb; margin-top: 8px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        .info-table td:first-child { color: #6b7280; width: 40%; }
        .info-table td:last-child { color: #111827; font-weight: 500; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff; padding: 16px 40px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 16px; margin-top: 20px; text-align: center; }
        .btn-secondary { display: inline-block; background-color: #6b7280; color: #ffffff; padding: 14px 32px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 15px; margin-top: 12px; }
        .payment-methods { background: #f9fafb; padding: 16px; border-radius: 6px; margin: 20px 0; font-size: 13px; }
        .payment-methods h3 { margin: 0 0 12px; color: #2563eb; font-size: 14px; }
        .payment-methods ul { margin: 0; padding-left: 20px; color: #374151; }
        .payment-methods li { margin: 6px 0; }
        .footer { background-color: #f9fafb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 4px; font-size: 13px; color: #92400e; margin: 16px 0; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>💳 Link Pembayaran</h1>
        <p>Young 911 Autowerks</p>
    </div>
    <div class="body">
        <p>Halo <strong>{{ $invoice->booking->name }}</strong>,</p>
        <p>Berikut adalah link pembayaran untuk invoice layanan kendaraan Anda:</p>

        <div class="payment-box">
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
            @if($invoice->due_at)
            <tr>
                <td>Jatuh Tempo</td>
                <td><strong>{{ $invoice->due_at->format('d M Y') }}</strong></td>
            </tr>
            @endif
        </table>

        <div class="warning">
            ⚠️ <strong>Penting:</strong> Link pembayaran ini hanya berlaku hingga invoice lunas atau dibatalkan.
            @if($invoice->due_at)
            Segera lakukan pembayaran sebelum tanggal jatuh tempo.
            @endif
        </div>

        <div style="text-align:center; margin-top:24px;">
            <a href="{{ $paymentUrl }}" class="btn" target="_blank" rel="noopener">
                💳 BAYAR SEKARANG
            </a>
        </div>

        <div class="payment-methods">
            <h3>🏦 Metode Pembayaran yang Tersedia:</h3>
            <ul>
                <li><strong>Transfer Bank</strong> - BCA, Mandiri, BNI, BRI, dll</li>
                <li><strong>Kartu Kredit/Debit</strong> - Visa, Mastercard</li>
                <li><strong>E-Wallet</strong> - GoPay, ShopeePay</li>
                <li><strong>QRIS</strong> - Scan QR untuk bayar</li>
                <li><strong>Indomaret & Alfamart</strong> - Bayar di toko</li>
            </ul>
        </div>

        <div style="text-align:center; margin-top:16px;">
            <a href="{{ url('/invoice/' . $invoice->invoice_number . '/print') }}" class="btn-secondary">
                🖨️ Lihat & Unduh Invoice
            </a>
        </div>

        @if($invoice->notes)
        <div style="margin-top:20px; padding:12px 16px; background:#f9fafb; border-left:4px solid #2563eb; border-radius:4px; font-size:13px; color:#374151;">
            <strong>Catatan:</strong> {{ $invoice->notes }}
        </div>
        @endif

        <div style="margin-top:24px; padding:16px; background:#f0fdf4; border-radius:6px; font-size:13px; color:#166534;">
            <strong>✅ Aman & Terpercaya:</strong> Pembayaran diproses melalui Midtrans - payment gateway yang aman dan terpercaya.
        </div>
    </div>
    <div class="footer">
        <p>Young 911 Autowerks &bull; Jl. Raya Utama No. 911, Jakarta</p>
        <p>📞 +62 812 3456 7890 &bull; Email ini dikirim otomatis, mohon tidak membalas.</p>
    </div>
</div>
</body>
</html>
