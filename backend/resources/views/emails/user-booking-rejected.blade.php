<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Booking Ditolak — Young 911 Autowerks</title>
  <style>
    body { margin: 0; padding: 0; background: #f3f4f6; font-family: 'Segoe UI', Arial, sans-serif; }
    .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .header { background: linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%); padding: 32px 40px; text-align: center; }
    .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; }
    .header p { color: #fecaca; margin: 6px 0 0; font-size: 14px; }
    .code-box { background: rgba(255,255,255,0.15); border: 2px dashed rgba(255,255,255,0.5); border-radius: 8px; padding: 10px 24px; margin-top: 16px; display: inline-block; }
    .code-box span { color: #ffffff; font-size: 20px; font-weight: 800; letter-spacing: 2px; }
    .body { padding: 32px 40px; }
    .greeting { font-size: 16px; color: #374151; margin-bottom: 20px; }
    .section-title { font-size: 13px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 12px; }
    table.info { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    table.info td { padding: 10px 12px; font-size: 14px; border-bottom: 1px solid #f3f4f6; }
    table.info td:first-child { color: #6b7280; width: 40%; font-weight: 500; }
    table.info td:last-child { color: #111827; font-weight: 600; }
    .reason-box { background: #fef2f2; border-left: 4px solid #ef4444; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; font-size: 14px; color: #7f1d1d; }
    .status-badge { display: inline-block; background: #fee2e2; color: #991b1b; padding: 6px 18px; border-radius: 20px; font-weight: 700; font-size: 14px; }
    .btn { display: inline-block; background: linear-gradient(135deg, #166534 0%, #14532d 100%); color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 700; font-size: 14px; }
    .footer { background: #f9fafb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>❌ Booking Tidak Dapat Diproses</h1>
      <p>Young 911 Autowerks</p>
      <div class="code-box">
        <span>{{ $booking->booking_code }}</span>
      </div>
    </div>

    <div class="body">
      <p class="greeting">
        Halo <strong>{{ $booking->name }}</strong>,<br/>
        Mohon maaf, booking Anda dengan kode <strong>{{ $booking->booking_code }}</strong> tidak dapat kami proses saat ini.
      </p>

      @if($booking->admin_notes)
      <p class="section-title">Alasan Penolakan</p>
      <div class="reason-box">
        {{ $booking->admin_notes }}
      </div>
      @endif

      <p class="section-title">Detail Booking</p>
      <table class="info">
        <tr><td>Kode Booking</td><td>{{ $booking->booking_code }}</td></tr>
        <tr><td>Tipe Mobil</td><td>{{ $booking->car_model }}</td></tr>
        <tr><td>Layanan</td><td>{{ $booking->service_type }}</td></tr>
        <tr><td>Tanggal Pilihan</td><td>{{ $booking->preferred_date ? $booking->preferred_date->format('d M Y') : '-' }}</td></tr>
        <tr><td>Status</td><td><span class="status-badge">❌ Ditolak</span></td></tr>
      </table>

      <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:20px 24px; margin:24px 0; text-align:center;">
        <p style="margin:0 0 12px; font-size:14px; color:#374151;">
          Ingin membuat booking baru? Kami siap membantu Anda!
        </p>
        <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/booking" class="btn">
          📅 Buat Booking Baru
        </a>
      </div>

      <p style="font-size:13px; color:#6b7280; margin-top:24px;">
        Jika ada pertanyaan, silakan hubungi kami:<br/>
        📞 <strong>+62 812 3456 7890</strong><br/>
        📧 <strong>info@young911autowerks.com</strong>
      </p>
    </div>

    <div class="footer">
      &copy; {{ date('Y') }} Young 911 Autowerks. Email ini dikirim otomatis, jangan dibalas.
    </div>
  </div>
</body>
</html>
