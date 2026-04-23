<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Booking Dikonfirmasi — Young 911 Autowerks</title>
  <style>
    body { margin: 0; padding: 0; background: #f3f4f6; font-family: 'Segoe UI', Arial, sans-serif; }
    .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .header { background: linear-gradient(135deg, #166534 0%, #14532d 100%); padding: 32px 40px; text-align: center; }
    .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; }
    .header p { color: #bbf7d0; margin: 6px 0 0; font-size: 14px; }
    .code-box { background: rgba(255,255,255,0.15); border: 2px dashed rgba(255,255,255,0.5); border-radius: 8px; padding: 12px 24px; margin-top: 16px; display: inline-block; }
    .code-box span { color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: 2px; }
    .body { padding: 32px 40px; }
    .greeting { font-size: 16px; color: #374151; margin-bottom: 20px; }
    .section-title { font-size: 13px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 12px; }
    table.info { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    table.info td { padding: 10px 12px; font-size: 14px; border-bottom: 1px solid #f3f4f6; }
    table.info td:first-child { color: #6b7280; width: 40%; font-weight: 500; }
    table.info td:last-child { color: #111827; font-weight: 600; }
    .status-badge { display: inline-block; background: #dcfce7; color: #166534; padding: 6px 18px; border-radius: 20px; font-weight: 700; font-size: 14px; }
    .track-box { background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 20px 24px; margin: 24px 0; text-align: center; }
    .track-box p { margin: 0 0 8px; font-size: 13px; color: #6b7280; }
    .track-code { font-size: 20px; font-weight: 800; color: #166534; letter-spacing: 2px; margin: 0 0 12px; }
    .btn { display: inline-block; background: linear-gradient(135deg, #166534 0%, #14532d 100%); color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 700; font-size: 14px; }
    .footer { background: #f9fafb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>✅ Booking Dikonfirmasi!</h1>
      <p>Young 911 Autowerks</p>
      <div class="code-box">
        <span>{{ $booking->booking_code }}</span>
      </div>
    </div>

    <div class="body">
      <p class="greeting">
        Halo <strong>{{ $booking->name }}</strong>, 🎉<br/>
        Booking Anda telah <strong>dikonfirmasi</strong> oleh tim Young 911 Autowerks. Kami siap melayani kendaraan Anda!
      </p>

      <p class="section-title">Detail Booking</p>
      <table class="info">
        <tr><td>Kode Booking</td><td><strong>{{ $booking->booking_code }}</strong></td></tr>
        <tr><td>Tipe Mobil</td><td>{{ $booking->car_model }}</td></tr>
        <tr><td>Layanan</td><td>{{ $booking->service_type }}</td></tr>
        <tr>
          <td>Jadwal</td>
          <td>
            @if($booking->scheduled_at)
              {{ $booking->scheduled_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
            @elseif($booking->preferred_date)
              {{ $booking->preferred_date->format('d M Y') }}
            @else
              Akan dikonfirmasi
            @endif
          </td>
        </tr>
        <tr><td>Status</td><td><span class="status-badge">✅ Dikonfirmasi</span></td></tr>
      </table>

      @if($booking->admin_notes)
      <p class="section-title">Catatan dari Admin</p>
      <div style="background:#f9fafb; border-left:4px solid #166534; padding:12px 16px; border-radius:4px; font-size:14px; color:#374151; margin-bottom:24px;">
        {{ $booking->admin_notes }}
      </div>
      @endif

      <div class="track-box">
        <p>Gunakan kode ini untuk melacak status booking Anda:</p>
        <div class="track-code">{{ $booking->booking_code }}</div>
        <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/tracking?code={{ $booking->booking_code }}" class="btn">
          🔍 Lacak Booking
        </a>
      </div>

      <p style="font-size:13px; color:#6b7280; margin-top:24px;">
        📍 <strong>Lokasi:</strong> Jl. Raya Utama No. 911, Jakarta<br/>
        📞 <strong>Kontak:</strong> +62 812 3456 7890<br/>
        🕐 <strong>Jam Operasional:</strong> Senin–Sabtu, 08.00–17.00 WIB
      </p>
    </div>

    <div class="footer">
      &copy; {{ date('Y') }} Young 911 Autowerks. Email ini dikirim otomatis, jangan dibalas.
    </div>
  </div>
</body>
</html>
