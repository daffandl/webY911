'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useAuth } from '../../components/AuthProvider';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

interface Invoice {
  id: number;
  invoice_number: string;
  booking_code: string | null;
  total: number;
  status: 'draft' | 'sent' | 'paid' | 'cancelled';
  due_date: string | null;
  created_at: string;
  payment_status?: string;
  paid_amount?: number;
  due_at?: string;
  payment_url?: string;
}

export default function InvoicesPage() {
  const { token } = useAuth();
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [meta, setMeta] = useState<{
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  } | null>(null);

  useEffect(() => {
    if (!token) return;

    const fetchInvoices = async () => {
      setIsLoading(true);
      try {
        const response = await fetch(`${API_URL}/bookings/my/invoices`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        if (response.ok) {
          const data = await response.json();
          setInvoices(data.data);
          setMeta(data.meta);
        }
      } catch (error) {
        console.error('Error fetching invoices:', error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchInvoices();
  }, [token]);

  const getStatusBadge = (status: string) => {
    const badges: Record<string, string> = {
      draft: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400',
      sent: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
      paid: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
      cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    };

    const labels: Record<string, string> = {
      draft: 'Draft',
      sent: 'Dikirim',
      paid: 'Lunas',
      cancelled: 'Dibatalkan',
    };

    return (
      <span className={`px-2 py-1 text-xs font-medium rounded-full ${badges[status] || badges.draft}`}>
        {labels[status] || status}
      </span>
    );
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">Invoice</h1>
        <p className="text-gray-600 dark:text-gray-400 mt-1">
          {meta ? `${meta.total} invoice ditemukan` : 'Semua invoice Anda'}
        </p>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center min-h-[300px]">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-12 w-12 border-4 border-green-600 border-t-transparent"></div>
            <p className="mt-4 text-gray-600 dark:text-gray-400">Memuat invoice...</p>
          </div>
        </div>
      ) : invoices.length === 0 ? (
        <div className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-12 text-center card-clip">
          <svg className="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <p className="mt-4 text-lg font-medium text-gray-900 dark:text-white">Tidak ada invoice</p>
          <p className="text-gray-600 dark:text-gray-400 mt-1">Invoice akan tersedia setelah admin membuat invoice untuk booking Anda</p>
          <Link
            href="/dashboard/bookings"
            className="mt-4 inline-block text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 font-medium"
          >
            Lihat booking Anda →
          </Link>
        </div>
      ) : (
        <div className="space-y-4">
          {invoices.map((invoice) => (
            <div key={invoice.id} className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-4 lg:p-6 hover:shadow-md transition-shadow card-clip">
              <div className="flex items-start justify-between gap-4">
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2 mb-2 flex-wrap">
                    <span className="font-mono text-sm font-bold text-blue-600 dark:text-blue-400">
                      {invoice.invoice_number}
                    </span>
                    {getStatusBadge(invoice.status)}
                    {invoice.payment_status && (
                      <span className={`px-2 py-1 text-xs font-medium rounded-full ${
                        invoice.payment_status === 'paid'
                          ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                          : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400'
                      }`}>
                        {invoice.payment_status === 'paid' ? 'Lunas' : 'Belum Lunas'}
                      </span>
                    )}
                  </div>
                  {invoice.booking_code && (
                    <Link
                      href={`/tracking?code=${invoice.booking_code}`}
                      className="text-sm text-green-600 dark:text-green-400 hover:underline"
                    >
                      {invoice.booking_code}
                    </Link>
                  )}
                  <div className="mt-3 flex items-center gap-6 text-sm">
                    <div>
                      <p className="text-gray-600 dark:text-gray-400">Total</p>
                      <p className="text-gray-900 dark:text-white font-bold">{formatCurrency(invoice.total)}</p>
                    </div>
                    {invoice.paid_amount && (
                      <div>
                        <p className="text-gray-600 dark:text-gray-400">Terbayar</p>
                        <p className="text-green-600 dark:text-green-400 font-bold">{formatCurrency(invoice.paid_amount)}</p>
                      </div>
                    )}
                    {invoice.due_at && (
                      <div>
                        <p className="text-gray-600 dark:text-gray-400">Jatuh Tempo</p>
                        <p className="text-gray-900 dark:text-white">
                          {new Date(invoice.due_at).toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric',
                          })}
                        </p>
                      </div>
                    )}
                  </div>
                </div>
                <div className="flex flex-col gap-2">
                  {invoice.status !== 'paid' && invoice.payment_url && (
                    <a
                      href={invoice.payment_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="px-4 py-2 btn-glow text-sm whitespace-nowrap"
                    >
                      <span>Bayar Sekarang</span>
                      <span className="btn-icon-circle ml-auto">
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                      </span>
                    </a>
                  )}
                  <Link
                    href={`/tracking?code=${invoice.booking_code}`}
                    className="px-4 py-2 bg-[#86efac]/30 dark:bg-[#1a2e1a] hover:bg-[#86efac]/50 dark:hover:bg-[#1a2e1a]/80 text-gray-900 dark:text-white text-sm font-medium rounded-lg transition-colors text-center whitespace-nowrap"
                    style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}
                  >
                    Lihat Detail
                  </Link>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
