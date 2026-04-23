<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Invoice — Young 911 Autowerks</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding-top: 40px;
        }

        /* ── Header ── */
        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .brand-name {
            font-size: 28px;
            font-weight: 800;
            color: #166534;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .brand-sub {
            font-size: 14px;
            color: #6b7280;
        }

        /* ── Card ── */
        .card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 40px rgba(22, 101, 52, 0.15);
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-desc {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        /* ── Form ── */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
            font-family: monospace;
        }

        .form-input:focus {
            outline: none;
            border-color: #166534;
            box-shadow: 0 0 0 3px rgba(22, 101, 52, 0.1);
        }

        .form-input::placeholder {
            color: #9ca3af;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .btn-verify {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #166534 0%, #15803d 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-verify:hover {
            background: linear-gradient(135deg, #14532d 0%, #166534 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(22, 101, 52, 0.3);
        }

        .btn-verify:active {
            transform: translateY(0);
        }

        .btn-verify:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* ── Results ── */
        .result-section {
            margin-top: 24px;
            display: none;
        }

        .result-section.show {
            display: block;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .status-authentic {
            background: #dcfce7;
            color: #166534;
        }

        .status-likely-authentic {
            background: #fef9c3;
            color: #854d0e;
        }

        .status-exists-but-suspicious {
            background: #ffedd5;
            color: #9a3412;
        }

        .status-invalid {
            background: #fee2e2;
            color: #dc2626;
        }

        /* ── Invoice Details ── */
        .details-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .details-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 12px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
            border-bottom: 1px solid #f3f4f6;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #6b7280;
        }

        .detail-value {
            font-weight: 600;
            color: #111827;
            text-align: right;
        }

        /* ── Security Checks ── */
        .checks-list {
            margin-top: 16px;
        }

        .check-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .check-item:last-child {
            border-bottom: none;
        }

        .check-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .check-pass {
            background: #dcfce7;
            color: #166534;
        }

        .check-fail {
            background: #fee2e2;
            color: #dc2626;
        }

        .check-warning {
            background: #fef9c3;
            color: #854d0e;
        }

        .check-skip {
            background: #f3f4f6;
            color: #9ca3af;
        }

        .check-text {
            flex: 1;
            font-size: 13px;
            color: #374151;
        }

        .check-message {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* ── Security Score ── */
        .score-box {
            background: linear-gradient(135deg, #166534 0%, #15803d 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-top: 16px;
            text-align: center;
        }

        .score-value {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .score-label {
            font-size: 13px;
            opacity: 0.9;
        }

        /* ── Loading ── */
        .loading {
            display: none;
            text-align: center;
            padding: 40px 20px;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e5e7eb;
            border-top-color: #166534;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 16px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 14px;
            color: #6b7280;
        }

        /* ── Error ── */
        .error-box {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .error-box.show {
            display: block;
        }

        /* ── Footer ── */
        .footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .footer-text {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.6;
        }

        .footer-links {
            margin-top: 8px;
        }

        .footer-link {
            color: #166534;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-link:hover {
            text-decoration: underline;
        }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            .container {
                padding-top: 20px;
            }

            .card {
                padding: 20px;
            }

            .brand-name {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="brand-name">Young 911 Autowerks</div>
            <div class="brand-sub">Spesialis Land Rover Indonesia</div>
        </div>

        <!-- Card -->
        <div class="card">
            <h1 class="card-title">
                🔍 Verifikasi Invoice
            </h1>
            <p class="card-desc">
                Masukkan nomor invoice dan security hash untuk memverifikasi keaslian invoice Anda.
            </p>

            <!-- Error Box -->
            <div class="error-box" id="errorBox"></div>

            <!-- Form -->
            <form id="verifyForm">
                <div class="form-group">
                    <label class="form-label" for="invoiceNumber">
                        Nomor Invoice <span style="color: #dc2626;">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-input" 
                        id="invoiceNumber"
                        name="invoice"
                        placeholder="Contoh: INV-2024-001"
                        required
                        autocomplete="off"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="securityHash">
                        Security Hash (Opsional)
                    </label>
                    <input 
                        type="text" 
                        class="form-input" 
                        id="securityHash"
                        name="hash"
                        placeholder="16 karakter hash"
                        maxlength="16"
                        autocomplete="off"
                    >
                    <p style="font-size: 11px; color: #9ca3af; margin-top: 4px;">
                        Hash dapat ditemukan di bagian bawah invoice PDF
                    </p>
                </div>

                <button type="submit" class="btn-verify" id="verifyBtn">
                    <span>🔐</span>
                    <span>Verifikasi Sekarang</span>
                </button>
            </form>

            <!-- Loading -->
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <div class="loading-text">Memverifikasi invoice...</div>
            </div>

            <!-- Result -->
            <div class="result-section" id="resultSection">
                <!-- Status Badge -->
                <div class="status-badge" id="statusBadge"></div>

                <!-- Invoice Details -->
                <div class="details-box">
                    <div class="details-title">Detail Invoice</div>
                    <div id="invoiceDetails"></div>
                </div>

                <!-- Security Checks -->
                <div class="details-box">
                    <div class="details-title">Pemeriksaan Keamanan</div>
                    <div class="checks-list" id="checksList"></div>
                </div>

                <!-- Security Score -->
                <div class="score-box" id="scoreBox">
                    <div class="score-value" id="scoreValue"></div>
                    <div class="score-label">Skor Keamanan</div>
                </div>

                <!-- Verified At -->
                <p style="text-align: center; font-size: 11px; color: #9ca3af; margin-top: 12px;">
                    Diverifikasi pada: <span id="verifiedAt"></span>
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p class="footer-text">
                    Sistem verifikasi ini menggunakan teknologi hash SHA-256<br>
                    untuk memastikan keaslian invoice Anda.
                </p>
                <div class="footer-links">
                    <a href="/" class="footer-link">← Kembali ke Home</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('verifyForm');
        const verifyBtn = document.getElementById('verifyBtn');
        const loading = document.getElementById('loading');
        const resultSection = document.getElementById('resultSection');
        const errorBox = document.getElementById('errorBox');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Reset
            errorBox.classList.remove('show');
            resultSection.classList.remove('show');
            loading.classList.add('show');
            verifyBtn.disabled = true;

            const invoiceNumber = document.getElementById('invoiceNumber').value.trim();
            const securityHash = document.getElementById('securityHash').value.trim();

            try {
                const response = await fetch('/api/verify?invoice=' + encodeURIComponent(invoiceNumber) + '&hash=' + encodeURIComponent(securityHash), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!data.success) {
                    showError(data.message || 'Terjadi kesalahan saat verifikasi');
                    return;
                }

                showResult(data.data);
            } catch (error) {
                showError('Gagal terhubung ke server. Periksa koneksi internet Anda.');
            } finally {
                loading.classList.remove('show');
                verifyBtn.disabled = false;
            }
        });

        function showError(message) {
            errorBox.textContent = '❌ ' + message;
            errorBox.classList.add('show');
        }

        function showResult(data) {
            const { invoice, verification } = data;

            // Status Badge
            const statusBadge = document.getElementById('statusBadge');
            statusBadge.className = 'status-badge status-' + verification.status;
            statusBadge.innerHTML = getVerificationIcon(verification.status) + ' ' + verification.status_label;

            // Invoice Details
            const detailsHtml = `
                <div class="detail-row">
                    <span class="detail-label">Nomor Invoice</span>
                    <span class="detail-value">${escapeHtml(invoice.invoice_number)}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Nama Customer</span>
                    <span class="detail-value">${escapeHtml(invoice.customer_name || '-')}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Kendaraan</span>
                    <span class="detail-value">${escapeHtml(invoice.car_model || '-')}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Layanan</span>
                    <span class="detail-value">${escapeHtml(invoice.service_type || '-')}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tanggal</span>
                    <span class="detail-value">${escapeHtml(invoice.issued_at || '-')}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">${escapeHtml(invoice.status_label)}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total</span>
                    <span class="detail-value">Rp ${formatNumber(invoice.total)}</span>
                </div>
            `;
            document.getElementById('invoiceDetails').innerHTML = detailsHtml;

            // Security Checks
            const checksHtml = verification.checks.map(check => `
                <div class="check-item">
                    <div class="check-icon check-${check.status}">
                        ${getCheckIcon(check.status)}
                    </div>
                    <div class="check-text">
                        ${escapeHtml(check.check)}
                        ${check.message ? `<div class="check-message">${escapeHtml(check.message)}</div>` : ''}
                    </div>
                </div>
            `).join('');
            document.getElementById('checksList').innerHTML = checksHtml;

            // Security Score
            document.getElementById('scoreValue').textContent = verification.security_score + '/100';

            // Verified At
            document.getElementById('verifiedAt').textContent = verification.verified_at;

            // Show result
            resultSection.classList.add('show');
        }

        function getVerificationIcon(status) {
            const icons = {
                'authentic': '✅',
                'likely_authentic': '⚠️',
                'exists_but_suspicious': '❓',
                'invalid': '❌',
            };
            return icons[status] || '❓';
        }

        function getCheckIcon(status) {
            const icons = {
                'pass': '✓',
                'fail': '✕',
                'warning': '!',
                'skip': '-',
            };
            return icons[status] || '-';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Auto-fill from URL params
        const urlParams = new URLSearchParams(window.location.search);
        const invoiceParam = urlParams.get('invoice');
        const hashParam = urlParams.get('hash');

        if (invoiceParam) {
            document.getElementById('invoiceNumber').value = invoiceParam;
        }
        if (hashParam) {
            document.getElementById('securityHash').value = hashParam;
        }

        // Auto-verify if params present
        if (invoiceParam) {
            form.dispatchEvent(new Event('submit'));
        }
    </script>

</body>
</html>
