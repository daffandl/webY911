<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Booking Baru — Young 911 Autowerks</title>
  <style>
    body { margin: 0; padding: 0; background: #f3f4f6; font-family: 'Segoe UI', Arial, sans-serif; }
    .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .header { background: linear-gradient(135deg, #166534 0%, #14532d 100%); padding: 32px 40px; text-align: center; }
    .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; letter-spacing: 0.5px; }
    .header p { color: #bbf7d0; margin: 6px 0 0; font-size: 14px; }
    .badge { display: inline-block; background: #fef08a; color: #713f12; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; margin-top: 12px; }
    .body { padding: 32px 40px; }
    .alert { background: #fef9c3; border-left: 4px solid #eab308; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; font-size: 14px; color: #713f12; }
    .section-title { font-size: 13px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 12px; }
    table.info { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    table.info td { padding: 10px 12px; font-size: 14px; border-bottom: 1px solid #f3f4f6; }
    table.info td:first-child { color: #6b7280; width: 40%; font-weight: 500; }
    table.info td:last-child { color: #111827; font-weight: 600; }
    .btn { display: inline-block; background: linear-gradient(135deg, #166534 0%, #14532d 100%); color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-weight: 700; font-size: 15px; margin: 8px 0; }
    .footer { background: #f9fafb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>🔔 Booking Baru Masuk!</h1>
      <p>Young 911 Autowerks — Admin Notification</p>
      <span class="badge"><?php echo e($booking->booking_code); ?></span>
    </div>

    <div class="body">
      <div class="alert">
        Ada booking baru yang perlu dikonfirmasi. Silakan buka Filament Admin Panel untuk memproses booking ini.
      </div>

      <p class="section-title">Informasi Customer</p>
      <table class="info">
        <tr><td>Nama</td><td><?php echo e($booking->name); ?></td></tr>
        <tr><td>WhatsApp</td><td><?php echo e($booking->phone); ?></td></tr>
        <tr><td>Email</td><td><?php echo e($booking->email); ?></td></tr>
      </table>

      <p class="section-title">Detail Booking</p>
      <table class="info">
        <tr><td>Kode Booking</td><td><strong><?php echo e($booking->booking_code); ?></strong></td></tr>
        <tr><td>Tipe Mobil</td><td><?php echo e($booking->car_model); ?></td></tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->vehicle_info): ?>
        <tr><td>Info Kendaraan</td><td><?php echo e($booking->vehicle_info); ?></td></tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <tr><td>Layanan</td><td><?php echo e($booking->service_type); ?></td></tr>
        <tr><td>Tanggal Pilihan</td><td><?php echo e($booking->preferred_date ? $booking->preferred_date->format('d M Y') : '-'); ?></td></tr>
        <tr><td>Catatan</td><td><?php echo e($booking->notes ?? '-'); ?></td></tr>
        <tr><td>Waktu Booking</td><td><?php echo e($booking->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i')); ?> WIB</td></tr>
      </table>

      <div style="text-align:center; margin-top: 24px;">
        <a href="<?php echo e(config('app.url')); ?>/admin/bookings/<?php echo e($booking->id); ?>" class="btn">
          Buka di Admin Panel →
        </a>
      </div>
    </div>

    <div class="footer">
      &copy; <?php echo e(date('Y')); ?> Young 911 Autowerks. Email ini dikirim otomatis, jangan dibalas.
    </div>
  </div>
</body>
</html>
<?php /**PATH /data/data/com.termux/files/home/wey911/backend/resources/views/emails/admin-new-booking.blade.php ENDPATH**/ ?>