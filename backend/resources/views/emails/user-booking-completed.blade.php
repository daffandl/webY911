<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Selesai</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #166534; padding: 30px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; }
        .header p { color: #bbf7d0; margin: 8px 0 0; font-size: 14px; }
        .body { padding: 32px 40px; }
        .status-badge { display: inline-block; background-color: #dcfce7; color: #166534; padding: 6px 16px; border-radius: 20px; font-weight: bold; font-size: 14px; margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        .info-table td:first-child { color: #6b7280; width: 40%; }
        .info-table td:last-child { color: #111827; font-weight: 500; }
        .success-box { background-color: #f0fdf4; border-left: 4px solid #166534; padding: 14px 16px; border-radius: 4px; margin: 20px 0; font-size: 14px; color: #14532d; }
        .pickup-box { background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 14px 16px; border-radius: 6px; margin: 20px 0; font-size: 14px; }
        .btn { display: inline-block; background-color: #166534; color: #ffffff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 14px; margin-top: 20px; }
        .footer { background-color: #f9fafb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>✨ Layanan Selesai!</h1>
        <p>Young 911 Autowerks</p>
    </div>
    <div class="body">
        <p>Halo <strong>{{ $booking->name }}</strong>,</p>
        <p>Kabar gembira! Kendaraan Anda telah selesai dikerjakan dan siap untuk diambil.</p>

        <span class="status-badge">✅ Selesai</span>

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
            @if($booking->admin_notes)
            <tr>
                <td>Catatan</td>
                <td>{{ $booking->admin_notes }}</td>
            </tr>
            @endif
        </table>

        <div class="success-box">
            ✅ Semua pekerjaan telah selesai dilakukan. Kendaraan Anda dalam kondisi prima dan siap diambil!
        </div>

        <div class="pickup-box">
            <strong>📍 Informasi Pengambilan:</strong><br>
            Lokasi: Jl. Raya Utama No. 911, Jakarta<br>
            Jam Operasional: Senin–Sabtu, 08.00–17.00 WIB<br>
            📞 +62 812 3456 7890
        </div>

        <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/tracking?code={{ $booking->booking_code }}" class="btn">
            Lihat Detail Booking
        </a>

        <p style="margin-top: 24px; font-size: 14px; color: #6b7280;">
            Terima kasih telah mempercayai Young 911 Autowerks. Kami berharap dapat melayani Anda kembali! 🙏
        </p>
    </div>
    <div class="footer">
        <p>Young 911 Autowerks &bull; Jl. Raya Utama No. 911, Jakarta</p>
        <p>📞 +62 812 3456 7890 &bull; Email ini dikirim otomatis, mohon tidak membalas.</p>
    </div>
</div>
</body>
</html>
