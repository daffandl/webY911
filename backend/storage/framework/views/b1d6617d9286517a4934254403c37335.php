<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo e($invoice->invoice_number); ?> — Young 911 Autowerks</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            background: #f5f5f5;
            padding: 20px;
            position: relative;
        }

        .page {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 48px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
            border-radius: 8px;
            position: relative;
            overflow: hidden;
        }

        /* ── Security Watermark ── */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            font-weight: 900;
            color: rgba(22, 101, 52, 0.04);
            pointer-events: none;
            z-index: 0;
            white-space: nowrap;
            letter-spacing: 8px;
            text-transform: uppercase;
            user-select: none;
            -webkit-user-select: none;
        }

        .watermark-secondary {
            position: absolute;
            top: 20%;
            left: 10%;
            transform: rotate(-30deg);
            font-size: 40px;
            font-weight: 800;
            color: rgba(22, 101, 52, 0.03);
            pointer-events: none;
            z-index: 0;
            letter-spacing: 4px;
            user-select: none;
            -webkit-user-select: none;
        }

        .watermark-bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                repeating-linear-gradient(
                    45deg,
                    rgba(22, 101, 52, 0.02) 0px,
                    rgba(22, 101, 52, 0.02) 1px,
                    transparent 1px,
                    transparent 8px
                ),
                repeating-linear-gradient(
                    -45deg,
                    rgba(22, 101, 52, 0.02) 0px,
                    rgba(22, 101, 52, 0.02) 1px,
                    transparent 1px,
                    transparent 8px
                );
            pointer-events: none;
            z-index: 0;
        }

        .content-wrapper {
            position: relative;
            z-index: 1;
        }

        /* ── QR Code Section ── */
        .qr-verification {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, rgba(22, 101, 52, 0.05) 0%, rgba(22, 101, 52, 0.02) 100%);
            border: 2px dashed rgba(22, 101, 52, 0.3);
            border-radius: 8px;
            padding: 16px;
            margin-top: 24px;
        }

        .qr-info {
            flex: 1;
        }

        .qr-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #166534;
            margin-bottom: 4px;
        }

        .qr-desc {
            font-size: 10px;
            color: #6b7280;
            line-height: 1.5;
        }

        .qr-code {
            width: 80px;
            height: 80px;
            background: white;
            padding: 4px;
            border-radius: 4px;
            margin-left: 16px;
        }

        .qr-code img {
            width: 100%;
            height: 100%;
        }

        .security-hash {
            font-family: monospace;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
            margin-top: 8px;
            word-break: break-all;
        }

        /* ── Header ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 24px;
            border-bottom: 3px solid #166534;
        }

        .brand-name {
            font-size: 22px;
            font-weight: 800;
            color: #166534;
            letter-spacing: -0.5px;
        }

        .brand-sub {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: 800;
            color: #166534;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .invoice-number {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
            font-family: monospace;
        }

        /* ── Status Badge ── */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 8px;
        }
        .status-draft     { background: #f3f4f6; color: #6b7280; }
        .status-sent      { background: #dbeafe; color: #1d4ed8; }
        .status-paid      { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }

        /* ── Info Grid ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
        }

        .info-box-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .info-label { color: #6b7280; }
        .info-value { font-weight: 600; color: #111827; text-align: right; }

        /* ── Items Table ── */
        .items-section { margin-bottom: 24px; }

        .items-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #166534;
            color: white;
        }

        thead th {
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        thead th:last-child { text-align: right; }
        thead th.center { text-align: center; }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        tbody tr:nth-child(even) { background: #f9fafb; }

        tbody td {
            padding: 10px 12px;
            font-size: 12px;
            color: #374151;
        }

        tbody td:last-child { text-align: right; font-weight: 600; }
        tbody td.center { text-align: center; }

        .type-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
        }
        .type-jasa      { background: #dbeafe; color: #1d4ed8; }
        .type-sparepart { background: #fef3c7; color: #d97706; }

        /* ── Totals ── */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 32px;
        }

        .totals-box {
            width: 280px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 16px;
            font-size: 12px;
            border-bottom: 1px solid #f3f4f6;
        }

        .totals-row:last-child { border-bottom: none; }
        .totals-row.total-final {
            background: #166534;
            color: white;
            font-size: 14px;
            font-weight: 700;
            padding: 12px 16px;
        }

        .totals-label { color: #6b7280; }
        .totals-value { font-weight: 600; }
        .totals-row.total-final .totals-label,
        .totals-row.total-final .totals-value { color: white; }

        /* ── Notes ── */
        .notes-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 32px;
        }

        .notes-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 8px;
        }

        .notes-text { font-size: 12px; color: #374151; line-height: 1.6; }

        /* ── Footer ── */
        .footer {
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            font-size: 11px;
            color: #9ca3af;
            line-height: 1.8;
        }

        /* ── Print Button (hidden when printing) ── */
        .print-actions {
            text-align: center;
            margin-bottom: 24px;
        }

        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #166534;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-print:hover { background: #14532d; }

        /* ── Print Media ── */
        @media print {
            body { background: white; padding: 0; }
            .page { box-shadow: none; border-radius: 0; padding: 32px; }
            .print-actions { display: none !important; }
            /* Force background graphics to print */
            .watermark, .watermark-secondary, .watermark-bg-pattern {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <!-- Print Button (hidden when printing) -->
    <div class="print-actions">
        <button class="btn-print" onclick="window.print()">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <div class="page">

        <!-- Security Watermarks -->
        <div class="watermark-bg-pattern"></div>
        <div class="watermark">Young 911 Autowerks</div>
        <div class="watermark-secondary">ORIGINAL</div>

        <div class="content-wrapper">

        <!-- ── Header ── -->
        <div class="header">
            <div>
                <div class="brand-name">Young 911 Autowerks</div>
                <div class="brand-sub">Spesialis Land Rover Indonesia</div>
                <div class="brand-sub" style="margin-top:6px;">Jl. Raya Utama No. 911, Jakarta</div>
                <div class="brand-sub">📞 +62 812 3456 7890</div>
            </div>
            <div class="invoice-meta">
                <div class="invoice-title">Invoice</div>
                <div class="invoice-number"><?php echo e($invoice->invoice_number); ?></div>
                <div>
                    <span class="status-badge status-<?php echo e($invoice->status); ?>">
                        <?php echo e($invoice->status_label); ?>

                    </span>
                </div>
            </div>
        </div>

        <!-- ── Info Grid ── -->
        <div class="info-grid">
            <!-- Customer Info -->
            <div class="info-box">
                <div class="info-box-title">Tagihan Kepada</div>
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value"><?php echo e($invoice->booking->name); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">No. WhatsApp</span>
                    <span class="info-value"><?php echo e($invoice->booking->phone); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kendaraan</span>
                    <span class="info-value"><?php echo e($invoice->booking->car_model); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Layanan</span>
                    <span class="info-value"><?php echo e(\Illuminate\Support\Str::limit($invoice->booking->service_type, 30)); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kode Booking</span>
                    <span class="info-value" style="font-family:monospace;"><?php echo e($invoice->booking->booking_code); ?></span>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="info-box">
                <div class="info-box-title">Detail Invoice</div>
                <div class="info-row">
                    <span class="info-label">No. Invoice</span>
                    <span class="info-value" style="font-family:monospace;"><?php echo e($invoice->invoice_number); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal</span>
                    <span class="info-value"><?php echo e($invoice->issued_at?->format('d M Y') ?? '-'); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->due_at): ?>
                <div class="info-row">
                    <span class="info-label">Jatuh Tempo</span>
                    <span class="info-value"><?php echo e($invoice->due_at->format('d M Y')); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value"><?php echo e($invoice->status_label); ?></span>
                </div>
            </div>
        </div>

        <!-- ── Items Table ── -->
        <div class="items-section">
            <div class="items-title">Rincian Jasa & Sparepart</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Nama Item</th>
                        <th style="width:80px;">Tipe</th>
                        <th class="center" style="width:60px;">Qty</th>
                        <th class="center" style="width:50px;">Satuan</th>
                        <th style="width:120px; text-align:right;">Harga Satuan</th>
                        <th style="width:120px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td><?php echo e($i + 1); ?></td>
                        <td>
                            <strong><?php echo e($item->name); ?></strong>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->description): ?>
                                <br><small style="color:#9ca3af;"><?php echo e($item->description); ?></small>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td>
                            <span class="type-badge type-<?php echo e($item->type); ?>">
                                <?php echo e($item->type === 'jasa' ? '🔧 Jasa' : '⚙️ Part'); ?>

                            </span>
                        </td>
                        <td class="center"><?php echo e(number_format($item->qty, $item->qty == intval($item->qty) ? 0 : 2)); ?></td>
                        <td class="center"><?php echo e($item->unit ?? '-'); ?></td>
                        <td style="text-align:right;">Rp <?php echo e(number_format($item->unit_price, 0, ',', '.')); ?></td>
                        <td>Rp <?php echo e(number_format($item->subtotal, 0, ',', '.')); ?></td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:#9ca3af; padding:20px;">
                            Tidak ada item.
                        </td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ── Totals ── -->
        <div class="totals-section">
            <div class="totals-box">
                <div class="totals-row">
                    <span class="totals-label">Subtotal</span>
                    <span class="totals-value">Rp <?php echo e(number_format($invoice->subtotal, 0, ',', '.')); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->tax_percent > 0): ?>
                <div class="totals-row">
                    <span class="totals-label">PPN (<?php echo e(number_format($invoice->tax_percent, 0)); ?>%)</span>
                    <span class="totals-value">Rp <?php echo e(number_format($invoice->tax_amount, 0, ',', '.')); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->discount > 0): ?>
                <div class="totals-row">
                    <span class="totals-label">Diskon</span>
                    <span class="totals-value" style="color:#dc2626;">- Rp <?php echo e(number_format($invoice->discount, 0, ',', '.')); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="totals-row total-final">
                    <span class="totals-label">TOTAL</span>
                    <span class="totals-value">Rp <?php echo e(number_format($invoice->total, 0, ',', '.')); ?></span>
                </div>
            </div>
        </div>

        <!-- ── Notes ── -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->notes): ?>
        <div class="notes-section">
            <div class="notes-title">Catatan</div>
            <div class="notes-text"><?php echo e($invoice->notes); ?></div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- ── Payment Button (if pending) ── -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->isPending() && $invoice->payment_url): ?>
        <div class="payment-section" style="margin-top: 24px; text-align: center;">
            <div style="background: linear-gradient(135deg, #166534 0%, #15803d 100%); color: white; padding: 20px; border-radius: 12px; display: inline-block; min-width: 280px;">
                <div style="font-size: 16px; font-weight: 700; margin-bottom: 8px;">💳 Bayar Invoice Online</div>
                <div style="font-size: 12px; margin-bottom: 16px; opacity: 0.9;">Klik tombol di bawah untuk pembayaran yang aman</div>
                <a href="<?php echo e($invoice->payment_url); ?>" target="_blank" style="display: inline-block; background: white; color: #166534; padding: 12px 32px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    BAYAR SEKARANG →
                </a>
                <div style="font-size: 10px; margin-top: 12px; opacity: 0.8;">Diproses secara aman oleh Midtrans</div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- ── Footer ── -->
        <div class="footer">
            <p><strong>Young 911 Autowerks</strong> — Spesialis Land Rover Indonesia</p>
            <p>Jl. Raya Utama No. 911, Jakarta &bull; 📞 +62 812 3456 7890</p>
            <p style="margin-top:8px; font-size:10px;">
                Invoice ini diterbitkan secara elektronik dan sah tanpa tanda tangan.
            </p>
        </div>

        <!-- ── QR Code Verification ── -->
        <div class="qr-verification">
            <div class="qr-info">
                <div class="qr-title">🔐 Verifikasi Keaslian Invoice</div>
                <div class="qr-desc">
                    Scan QR code atau kunjungi <strong>young911autowerks.com/verify</strong><br>
                    dan masukkan kode: <strong style="color: #166534;"><?php echo e($invoice->invoice_number); ?></strong>
                </div>
                <div class="security-hash">
                    Security Hash: <?php echo e($shortHash); ?>

                </div>
            </div>
            <div class="qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo e(urlencode('young911autowerks.com/verify?invoice=' . $invoice->invoice_number . '&hash=' . $shortHash)); ?>" 
                     alt="QR Verification" 
                     style="display: block;">
            </div>
        </div>

        </div> <!-- Close content-wrapper -->

    </div>

</body>
</html>
<?php /**PATH /data/data/com.termux/files/home/wey911/backend/resources/views/invoices/print.blade.php ENDPATH**/ ?>