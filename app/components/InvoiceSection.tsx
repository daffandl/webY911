'use client';

import { useState } from 'react';

// ─── Types ────────────────────────────────────────────────────────────────────
interface InvoiceItem {
  name: string;
  type: 'jasa' | 'sparepart';
  qty: number;
  unit?: string;
  unit_price: number;
  subtotal: number;
}

interface InvoiceData {
  invoice_number: string;
  status: string;
  status_label: string;
  issued_at?: string;
  due_at?: string;
  subtotal: number;
  tax_percent: number;
  tax_amount: number;
  discount: number;
  total: number;
  notes?: string;
  print_url: string;
  payment_url?: string | null;
  is_paid?: boolean;
  items: InvoiceItem[];
  booking: {
    booking_code: string;
    name: string;
    car_model: string;
    service_type: string;
  };
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function formatRp(amount: number): string {
  return 'Rp ' + amount.toLocaleString('id-ID');
}

const clipStyle = {
  clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))',
};

// ─── Status config ────────────────────────────────────────────────────────────
const STATUS_CONFIG: Record<string, { label: string; color: string; bg: string; border: string }> = {
  draft:     { label: 'Draft',      color: 'text-gray-600 dark:text-gray-400',    bg: 'bg-gray-50 dark:bg-gray-900/30',    border: 'border-gray-200 dark:border-gray-700' },
  sent:      { label: 'Terkirim',   color: 'text-blue-600 dark:text-blue-400',    bg: 'bg-blue-50 dark:bg-blue-900/30',    border: 'border-blue-200 dark:border-blue-700' },
  paid:      { label: 'Lunas',      color: 'text-green-600 dark:text-green-400',  bg: 'bg-green-50 dark:bg-green-900/30',  border: 'border-green-200 dark:border-green-700' },
  cancelled: { label: 'Dibatalkan', color: 'text-red-600 dark:text-red-400',      bg: 'bg-red-50 dark:bg-red-900/30',      border: 'border-red-200 dark:border-red-700' },
};

// ─── SVG Icons ────────────────────────────────────────────────────────────────
const IcoDocument = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
  </svg>
);
const IcoDownload = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
  </svg>
);
const IcoPayment = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
  </svg>
);
const IcoArrowRight = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
  </svg>
);
const IcoSpinner = () => (
  <svg className="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
  </svg>
);
const IcoX = () => (
  <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
      d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>
);
const IcoChevron = ({ open }: { open: boolean }) => (
  <svg className={`w-4 h-4 transition-transform ${open ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
  </svg>
);

// ─────────────────────────────────────────────────────────────────────────────
//  InvoiceSection Component
// ─────────────────────────────────────────────────────────────────────────────
interface InvoiceSectionProps {
  bookingCode: string;
  /** Optional: pre-fetched invoice data (skip the fetch button) */
  initialInvoice?: InvoiceData | null;
}

export default function InvoiceSection({ bookingCode, initialInvoice }: InvoiceSectionProps) {
  const [invoice, setInvoice] = useState<InvoiceData | null>(initialInvoice ?? null);
  const [loading, setLoading] = useState(false);
  const [checked, setChecked] = useState(!!initialInvoice);
  const [error, setError] = useState<string | null>(null);
  const [showItems, setShowItems] = useState(false);

  const API_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api';

  const checkInvoice = async () => {
    setLoading(true);
    setError(null);
    setChecked(true);

    try {
      const res = await fetch(`${API_URL}/bookings/track/${bookingCode}/invoice`, {
        headers: { Accept: 'application/json' },
      });
      const json = await res.json();

      if (json.has_invoice && json.data) {
        setInvoice(json.data);
      } else {
        setInvoice(null);
      }
    } catch {
      setError('Tidak dapat terhubung ke server.');
    } finally {
      setLoading(false);
    }
  };

  const sc = invoice ? (STATUS_CONFIG[invoice.status] ?? STATUS_CONFIG['draft']) : null;

  return (
    <div
      className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-5"
      style={clipStyle}
    >
      {/* ── Header ── */}
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center gap-2">
          <span className="text-green-600 dark:text-green-400">
            <IcoDocument />
          </span>
          <h4 className="font-semibold text-gray-900 dark:text-white text-sm">Invoice</h4>
        </div>

        {/* Check button — only show if not yet checked */}
        {!checked && (
          <button
            onClick={checkInvoice}
            disabled={loading}
            className="flex items-center gap-1.5 px-3 py-1.5 bg-[#166534] text-white text-xs font-semibold rounded hover:bg-[#14532d] transition-colors disabled:opacity-60"
            style={clipStyle}
          >
            {loading ? <IcoSpinner /> : <IcoDocument />}
            <span>{loading ? 'Memeriksa...' : 'Cek Invoice'}</span>
          </button>
        )}

        {/* Refresh button — show after checked */}
        {checked && (
          <button
            onClick={checkInvoice}
            disabled={loading}
            className="flex items-center gap-1 text-xs text-green-600 dark:text-green-400 hover:underline disabled:opacity-60"
          >
            {loading ? <IcoSpinner /> : null}
            <span>{loading ? 'Memuat...' : 'Refresh'}</span>
          </button>
        )}
      </div>

      {/* ── Error ── */}
      {error && (
        <div className="flex items-center gap-2 text-red-500 text-xs p-2 bg-red-50 dark:bg-red-900/20 rounded">
          <IcoX />
          <span>{error}</span>
        </div>
      )}

      {/* ── Not yet checked ── */}
      {!checked && !loading && (
        <p className="text-xs text-gray-500 dark:text-gray-400">
          Klik tombol di atas untuk memeriksa apakah invoice sudah tersedia.
        </p>
      )}

      {/* ── Loading ── */}
      {loading && (
        <div className="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs py-2">
          <IcoSpinner />
          <span>Memeriksa invoice...</span>
        </div>
      )}

      {/* ── Invoice Not Found ── */}
      {checked && !loading && !invoice && !error && (
        <div className="text-center py-3">
          <p className="text-sm font-medium text-gray-500 dark:text-gray-400">
            <svg className="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Invoice belum dibuat
          </p>
          <p className="text-xs text-gray-400 dark:text-gray-500 mt-1">
            Invoice akan tersedia setelah admin menerbitkannya.
          </p>
        </div>
      )}

      {/* ── Invoice Found ── */}
      {checked && !loading && invoice && sc && (
        <div className="space-y-3">
          {/* Status + Number */}
          <div className={`flex items-center justify-between p-3 rounded border ${sc.border} ${sc.bg}`} style={clipStyle}>
            <div>
              <p className="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Nomor Invoice</p>
              <p className="font-mono font-bold text-sm text-gray-900 dark:text-white">{invoice.invoice_number}</p>
            </div>
            <span className={`text-sm font-semibold ${sc.color}`}>{sc.label}</span>
          </div>

          {/* Total */}
          <div className="flex items-center justify-between">
            <span className="text-xs text-gray-500 dark:text-gray-400">Total Tagihan</span>
            <span className="text-lg font-bold text-green-700 dark:text-green-400">{formatRp(invoice.total)}</span>
          </div>

          {/* Meta info */}
          <div className="grid grid-cols-2 gap-2 text-xs">
            {invoice.issued_at && (
              <div>
                <span className="text-gray-500 dark:text-gray-400">Tanggal: </span>
                <span className="font-medium text-gray-900 dark:text-white">{invoice.issued_at}</span>
              </div>
            )}
            {invoice.due_at && (
              <div>
                <span className="text-gray-500 dark:text-gray-400">Jatuh Tempo: </span>
                <span className="font-medium text-gray-900 dark:text-white">{invoice.due_at}</span>
              </div>
            )}
          </div>

          {/* Items toggle */}
          {invoice.items.length > 0 && (
            <div>
              <button
                onClick={() => setShowItems(!showItems)}
                className="flex items-center gap-1.5 text-xs text-green-600 dark:text-green-400 font-medium hover:underline w-full"
              >
                <span>{showItems ? 'Sembunyikan' : 'Lihat'} {invoice.items.length} item rincian</span>
                <IcoChevron open={showItems} />
              </button>

              {showItems && (
                <div className="mt-2 space-y-1.5">
                  {invoice.items.map((item, i) => (
                    <div
                      key={i}
                      className="flex items-center justify-between text-xs py-1.5 border-b border-[#86efac] dark:border-[#1a2e1a] last:border-0"
                    >
                      <div className="flex-1 min-w-0 pr-2">
                        <span className="font-medium text-gray-900 dark:text-white">{item.name}</span>
                        <span className={`ml-1.5 px-1.5 py-0.5 rounded text-xs ${
                          item.type === 'jasa'
                            ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'
                            : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300'
                        }`}>
                          {item.type === 'jasa' ? (
                            <svg className="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                          ) : (
                            <svg className="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                          )}
                        </span>
                        <span className="text-gray-400 dark:text-gray-500 ml-1">
                          {item.qty}{item.unit ? ` ${item.unit}` : ''} × {formatRp(item.unit_price)}
                        </span>
                      </div>
                      <span className="font-semibold text-gray-900 dark:text-white flex-shrink-0">
                        {formatRp(item.subtotal)}
                      </span>
                    </div>
                  ))}

                  {/* Totals summary */}
                  <div className="pt-2 space-y-1 text-xs">
                    {invoice.tax_percent > 0 && (
                      <div className="flex justify-between text-gray-500 dark:text-gray-400">
                        <span>PPN ({invoice.tax_percent}%)</span>
                        <span>{formatRp(invoice.tax_amount)}</span>
                      </div>
                    )}
                    {invoice.discount > 0 && (
                      <div className="flex justify-between text-red-500">
                        <span>Diskon</span>
                        <span>- {formatRp(invoice.discount)}</span>
                      </div>
                    )}
                    <div className="flex justify-between font-bold text-gray-900 dark:text-white border-t border-[#86efac] dark:border-[#1a2e1a] pt-1">
                      <span>Total</span>
                      <span>{formatRp(invoice.total)}</span>
                    </div>
                  </div>
                </div>
              )}
            </div>
          )}

          {/* Notes */}
          {invoice.notes && (
            <p className="text-xs text-gray-500 dark:text-gray-400 italic">
              {invoice.notes}
            </p>
          )}

          {/* Payment Button (if pending) */}
          {!invoice.is_paid && invoice.status !== 'cancelled' && invoice.payment_url && (
            <a
              href={`/payment/${invoice.invoice_number}`}
              className="flex items-center justify-between w-full px-4 py-2.5 bg-gradient-to-r from-green-600 to-green-700 text-white text-sm font-semibold hover:from-green-700 hover:to-green-800 transition-all shadow-lg shadow-green-600/20"
              style={clipStyle}
            >
              <span className="flex items-center gap-2">
                <IcoPayment />
                Bayar Invoice Sekarang
              </span>
              <span className="flex items-center gap-1">
                <IcoArrowRight />
              </span>
            </a>
          )}

          {/* Download / View button */}
          <a
            href={invoice.print_url}
            target="_blank"
            rel="noopener noreferrer"
            className="flex items-center justify-between w-full px-4 py-2.5 bg-[#166534] text-white text-sm font-semibold hover:bg-[#14532d] transition-colors"
            style={clipStyle}
          >
            <span>Lihat & Unduh Invoice</span>
            <span className="flex items-center gap-1">
              <IcoDownload />
            </span>
          </a>
        </div>
      )}
    </div>
  );
}
