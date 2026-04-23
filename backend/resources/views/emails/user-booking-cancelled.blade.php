<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Dibatalkan</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #4b5563; padding: 30px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; }
        .header p { color: #d1d5db; margin: 8px 0 0; font-size: 14px; }
        .body { padding: 32px 40px; }
        .status-badge { display: inline-block; background-color: #f3f4f6; color: #4b5563; padding: 6px 16px; border-radius: 20px; font-weight: bold; font-size: 14px; margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        .info-table td:first-child { color: #6b7280; width: 40%; }
        .info-table td:last-child { color: #111827; font-weight: 500; }
        .reason-box { background-color: #f9fafb; border-left: 4px solid #6b7280; padding: 14px 16px; border-radius: 4px; margin: 20px 0; font-size: 14px; color: #374151; }
        .rebook-box { background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 14px 16px; border-radius: 6px; margin: 20px 0; font-size: 14px; color: #166534; }
        .btn { display: inline-block; background-color: #166534; color: #ffffff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 14px; margin-top: 20px; }
        .footer { background-color: #f9fafb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🚫 Booking Dibatalkan</h1>
        <p>Young 911 Autowerks</p>
    </div>
    <div class="body">
        <p>Halo <strong>{{ $booking->name }}</strong>,</p>
        <p>Kami ingin memberitahukan bahwa booking Anda telah dibatalkan.</p>

        <span class="status-badge">🚫 Dibatalkan</span>

        <table class="info-table">
            <tr>
                <td>Kode Booking</td>
                <td><strong>{{ $booking->booking_code }}</strong></td>
            </tr>
            <tr>
                <td>Tipe Mobil</td>
                <td>{{ $booking->car_model }}</td>
            </tr>
            <tr>
                <td>Layanan</td>
                <td>{{ $booking->service_type }}</td>
            </tr>
        </table>

        @if($booking->admin_notes)
        <div class="reason-box">
            <strong>📝 Keterangan:</strong><br>
            {{ $booking->admin_notes }}
        </div>
        @endif

        <div class="rebook-box">
            💡 Jika Anda ingin melakukan booking ulang atau memiliki pertanyaan, jangan ragu untuk menghubungi kami atau membuat booking baru.
        </div>

        <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/booking" class="btn">
            Buat Booking Baru
        </a>

        <p style="margin-top: 16px; font-size: 14px; color: #6b7280;">
            Pertanyaan? Hubungi kami di 📞 +62 812 3456 7890
        </p>
    </div>
    <div class="footer">
        <p>Young 911 Autowerks &bull; Jl. Raya Utama No. 911, Jakarta</p>
        <p>📞 +62 812 3456 7890 &bull; Email ini dikirim otomatis, mohon tidak membalas.</p>
    </div>
</div>
</body>
</html>
