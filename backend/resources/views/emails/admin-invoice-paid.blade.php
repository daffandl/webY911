<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Invoice Diterima</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #166534; padding: 30px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; }
        .header p { color: #bbf7d0; margin: 8px 0 0; font-size: 14px; }
        .body { padding: 32px 40px; }
        .success-box { background: #f0fdf4; border: 2px solid #166534; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
        .invoice-number { font-size: 24px; font-weight: 800; color: #166534; font-family: monospace; letter-spacing: 2px; }
        .total-amount { font-size: 28px; font-weight: 800; color: #166534; margin-top: 8px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        .info-table td:first-child { color: #6b7280; width: 40%; }
        .info-table td:last-child { color: #111827; font-weight: 500; }
        .footer { background-color: #f9fafb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>💰 Pembayaran Diterima</h1>
        <p>Young 911 Autowerks</p>
    </div>
    <div class="body">
        <p>Halo Admin,</p>
        <p>Pembayaran invoice telah diterima dari customer:</p>

        <div class="success-box">
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Nomor Invoice</div>
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            <div style="font-size:12px; color:#6b7280; margin-top:12px;">Total Pembayaran</div>
            <div class="total-amount">Rp {{ number_format($invoice->total, 0, ',', '.') }}</div>
            <div style="margin-top:12px; font-size:14px; color:#166534;">✅ <strong>LUNAS</strong></div>
        </div>

        <table class="info-table">
            <tr>
                <td>Customer</td>
                <td><strong>{{ $invoice->booking->name }}</strong></td>
            </tr>
            <tr>
                <td>Kendaraan</td>
                <td>{{ $invoice->booking->car_model }}</td>
            </tr>
            <tr>
                <td>Kode Booking</td>
                <td>{{ $invoice->booking->booking_code }}</td>
            </tr>
            <tr>
                <td>Tanggal Pembayaran</td>
                <td>{{ now()->format('d M Y H:i') }}</td>
            </tr>
        </table>

        <p style="margin-top:20px; font-size:13px; color:#6b7280;">Silakan lanjutkan proses layanan untuk kendaraan customer ini.</p>
    </div>
    <div class="footer">
        <p>Young 911 Autowerks &bull; Jl. Raya Utama No. 911, Jakarta</p>
        <p>📞 +62 812 3456 7890 &bull; Sistem Notifikasi Invoice</p>
    </div>
</div>
</body>
</html>
