<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #166534 0%, #15803d 100%); padding: 30px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .header p { color: #bbf7d0; margin: 8px 0 0; font-size: 14px; }
        .success-icon { width: 80px; height: 80px; background: #ffffff; border-radius: 50%; margin: 20px auto; display: flex; align-items: center; justify-content: center; font-size: 40px; }
        .body { padding: 32px 40px; }
        .payment-box { background: #f0fdf4; border: 2px solid #166534; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .payment-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #bbf7d0; }
        .payment-row:last-child { border-bottom: none; }
        .payment-row strong { color: #166534; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        .info-table td:first-child { color: #6b7280; width: 40%; }
        .info-table td:last-child { color: #111827; font-weight: 500; }
        .midtrans-box { background: #f9fafb; padding: 16px; border-radius: 6px; margin: 20px 0; font-size: 12px; }
        .midtrans-box h3 { margin: 0 0 12px; color: #2563eb; font-size: 14px; }
        .midtrans-row { display: flex; justify-content: space-between; padding: 4px 0; }
        .btn { display: inline-block; background-color: #166534; color: #ffffff; padding: 14px 32px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 15px; margin-top: 20px; }
        .btn-secondary { display: inline-block; background-color: #6b7280; color: #ffffff; padding: 14px 32px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 15px; margin-top: 12px; margin-right: 8px; }
        .footer { background-color: #f9fafb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
        .highlight { background: #fef3c7; padding: 12px 16px; border-left: 4px solid #f59e0b; border-radius: 4px; margin: 16px 0; font-size: 13px; color: #92400e; }
        .status-badge { display: inline-block; background: #166534; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="success-icon">✅</div>
        <h1>Pembayaran Berhasil!</h1>
        <p>Young 911 Autowerks</p>
    </div>
    <div class="body">
        <p>Halo <strong>{{ $payment->booking->name }}</strong>,</p>
        <p>Terima kasih! Pembayaran Anda telah berhasil diterima. Kami akan segera memproses layanan kendaraan Anda.</p>

        <div class="payment-box">
            <div class="payment-row">
                <span>📋 No. Invoice</span>
                <strong>{{ $payment->invoice->invoice_number }}</strong>
            </div>
            <div class="payment-row">
                <span>🔢 Kode Booking</span>
                <strong>{{ $payment->booking->booking_code }}</strong>
            </div>
            <div class="payment-row">
                <span>💳 Metode Pembayaran</span>
                <strong>{{ $payment->payment_method_label }}@if($payment->bank) ({{ strtoupper($payment->bank) }})@endif</strong>
            </div>
            @if($payment->va_number)
            <div class="payment-row">
                <span>🔢 No. VA</span>
                <strong>{{ $payment->va_number }}</strong>
            </div>
            @endif
            <div class="payment-row">
                <span>💵 Jumlah Pembayaran</span>
                <strong style="color: #166534; font-size: 18px;">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
            </div>
            <div class="payment-row">
                <span>⏰ Waktu Pembayaran</span>
                <strong>{{ $payment->paid_at?->format('d M Y H:i') }}</strong>
            </div>
            <div class="payment-row">
                <span>✅ Status</span>
                <span class="status-badge">LUNAS</span>
            </div>
        </div>

        <table class="info-table">
            <tr>
                <td>Nama Customer</td>
                <td><strong>{{ $payment->booking->name }}</strong></td>
            </tr>
            <tr>
                <td>Email</td>
                <td>{{ $payment->booking->email }}</td>
            </tr>
            <tr>
                <td>WhatsApp</td>
                <td>{{ $payment->booking->phone }}</td>
            </tr>
            <tr>
                <td>Kendaraan</td>
                <td>{{ $payment->booking->car_model }}</td>
            </tr>
            <tr>
                <td>Layanan</td>
                <td>{{ $payment->booking->service_type }}</td>
            </tr>
        </table>

        @if(count($midtransData) > 0)
        <div class="midtrans-box">
            <h3>🔗 Detail Transaksi Midtrans</h3>
            <div class="midtrans-row">
                <span>Transaction ID:</span>
                <strong>{{ $midtransData['transaction_id'] ?? '-' }}</strong>
            </div>
            <div class="midtrans-row">
                <span>Order ID:</span>
                <strong>{{ $midtransData['order_id'] ?? '-' }}</strong>
            </div>
            <div class="midtrans-row">
                <span>Transaction Status:</span>
                <strong>{{ $midtransData['transaction_status'] ?? '-' }}</strong>
            </div>
            <div class="midtrans-row">
                <span>Fraud Status:</span>
                <strong>{{ $midtransData['fraud_status'] ?? '-' }}</strong>
            </div>
            <div class="midtrans-row">
                <span>Transaction Time:</span>
                <strong>{{ $midtransData['transaction_time'] ?? '-' }}</strong>
            </div>
            @if(isset($midtransData['settlement_time']))
            <div class="midtrans-row">
                <span>Settlement Time:</span>
                <strong>{{ $midtransData['settlement_time'] }}</strong>
            </div>
            @endif
        </div>
        @endif

        <div class="highlight">
            ⚠️ <strong>Catatan Penting:</strong> Simpan email ini sebagai bukti pembayaran yang sah. Tunjukkan email ini saat mengambil kendaraan Anda.
        </div>

        <div style="text-align:center; margin-top:24px;">
            <a href="{{ url('/tracking?code=' . $payment->booking->booking_code) }}" class="btn">
                📊 Lacak Status Servis
            </a>
            <a href="{{ url('/invoice/' . $payment->invoice->invoice_number . '/print') }}" class="btn-secondary">
                🖨️ Unduh Invoice
            </a>
        </div>

        <div style="margin-top:24px; padding:16px; background:#f0fdf4; border-radius:6px; font-size:13px; color:#166534;">
            <strong>✅ Langkah Selanjutnya:</strong>
            <ul style="margin: 8px 0 0 20px;">
                <li>Tim kami akan segera menghubungi Anda untuk konfirmasi jadwal</li>
                <li>Kendaraan akan diproses sesuai layanan yang dipilih</li>
                <li>Anda akan menerima update status melalui WhatsApp</li>
                <li>Kendaraan siap diambil setelah servis selesai</li>
            </ul>
        </div>
    </div>
    <div class="footer">
        <p>Young 911 Autowerks &bull; Jl. Raya Utama No. 911, Jakarta</p>
        <p>📞 +62 812 3456 7890 &bull; Email ini dikirim otomatis sebagai bukti pembayaran yang sah.</p>
    </div>
</div>
</body>
</html>
