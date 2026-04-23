<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ada Masalah pada Kendaraan Anda</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #d97706; padding: 30px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; }
        .header p { color: #fef3c7; margin: 8px 0 0; font-size: 14px; }
        .body { padding: 32px 40px; }
        .status-badge { display: inline-block; background-color: #fef3c7; color: #d97706; padding: 6px 16px; border-radius: 20px; font-weight: bold; font-size: 14px; margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        .info-table td:first-child { color: #6b7280; width: 40%; }
        .info-table td:last-child { color: #111827; font-weight: 500; }
        .issue-box { background-color: #fffbeb; border-left: 4px solid #d97706; padding: 14px 16px; border-radius: 4px; margin: 20px 0; font-size: 14px; color: #92400e; }
        .contact-box { background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 14px 16px; border-radius: 6px; margin: 20px 0; font-size: 14px; }
        .btn { display: inline-block; background-color: #d97706; color: #ffffff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 14px; margin-top: 20px; }
        .footer { background-color: #f9fafb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>⚠️ Ada Masalah pada Kendaraan Anda</h1>
        <p>Young 911 Autowerks</p>
    </div>
    <div class="body">
        <p>Halo <strong>{{ $booking->name }}</strong>,</p>
        <p>Tim teknisi kami menemukan kendala pada kendaraan Anda yang memerlukan perhatian lebih lanjut.</p>

        <span class="status-badge">⚠️ Ada Masalah</span>

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
        <div class="issue-box">
            <strong>⚠️ Detail Masalah:</strong><br>
            {{ $booking->admin_notes }}
        </div>
        @else
        <div class="issue-box">
            ⚠️ Tim kami akan segera menghubungi Anda untuk memberikan informasi lebih lanjut mengenai kendala yang ditemukan.
        </div>
        @endif

        <div class="contact-box">
            <strong>Mohon segera hubungi kami:</strong><br>
            📞 +62 812 3456 7890<br>
            📍 Jl. Raya Utama No. 911, Jakarta
        </div>

        <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/tracking?code={{ $booking->booking_code }}" class="btn">
            Cek Status Booking
        </a>
    </div>
    <div class="footer">
        <p>Young 911 Autowerks &bull; Jl. Raya Utama No. 911, Jakarta</p>
        <p>📞 +62 812 3456 7890 &bull; Email ini dikirim otomatis, mohon tidak membalas.</p>
    </div>
</div>
</body>
</html>
