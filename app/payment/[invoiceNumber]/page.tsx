'use client';

import { useState, useEffect } from 'react';
import { useParams } from 'next/navigation';
import Navbar from '../../components/Navbar';
import Footer from '../../components/Footer';

interface Invoice {
  invoice_number: string;
  status: string;
  status_label: string;
  total: number;
  paid_amount: number | null;
  is_paid: boolean;
  payment_url: string | null;
  paid_at: string | null;
}

interface Booking {
  name: string;
  car_model: string;
  service_type: string;
  email: string;
  phone: string;
}

export default function PaymentPage() {
  const params = useParams();
  const invoiceNumber = params.invoiceNumber as string;

  const [invoice, setInvoice] = useState<Invoice | null>(null);
  const [booking, setBooking] = useState<Booking | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [processingPayment, setProcessingPayment] = useState(false);

  const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

  useEffect(() => {
    fetchInvoiceStatus();
    // Auto-refresh every 10 seconds for pending payments
    const interval = setInterval(() => {
      if (!invoice?.is_paid) {
        fetchInvoiceStatus();
      }
    }, 10000);

    return () => clearInterval(interval);
  }, [invoiceNumber]);

  const fetchInvoiceStatus = async () => {
    try {
      const response = await fetch(`${API_URL}/payment/${invoiceNumber}/status`);
      const data = await response.json();

      if (data.success) {
        setInvoice(data.invoice);
        // Fetch booking details from invoice
        fetchBookingDetails(invoiceNumber);
      } else {
        setError('Invoice tidak ditemukan');
      }
    } catch (err) {
      setError('Gagal memuat status pembayaran');
    } finally {
      setLoading(false);
    }
  };

  const fetchBookingDetails = async (invNumber: string) => {
    try {
      // Extract booking code from invoice number if needed
      // For now, we'll rely on the invoice data
    } catch (err) {
      console.error('Failed to fetch booking details:', err);
    }
  };

  const generatePaymentLink = async () => {
    setProcessingPayment(true);
    try {
      const response = await fetch(`${API_URL}/payment/${invoiceNumber}/generate`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      });

      const data = await response.json();

      if (data.success && data.payment_url) {
        window.open(data.payment_url, '_blank');
        setTimeout(() => {
          fetchInvoiceStatus();
        }, 2000);
      } else {
        throw new Error(data.message || 'Gagal membuat link pembayaran');
      }
    } catch (err: any) {
      setError(err.message || 'Terjadi kesalahan saat memproses pembayaran');
    } finally {
      setProcessingPayment(false);
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'paid':
        return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
      case 'cancelled':
        return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
      default:
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'paid':
        return (
          <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        );
      case 'cancelled':
        return (
          <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        );
      default:
        return (
          <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        );
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Navbar />
        <div className="flex items-center justify-center min-h-screen">
          <div className="text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto mb-4"></div>
            <p className="text-gray-600 dark:text-gray-400">Memuat status pembayaran...</p>
          </div>
        </div>
        <Footer />
      </div>
    );
  }

  if (error || !invoice) {
    return (
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Navbar />
        <div className="flex items-center justify-center min-h-screen">
          <div className="text-center max-w-md mx-auto px-4">
            <div className="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
            <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">Error</h2>
            <p className="text-gray-600 dark:text-gray-400 mb-4">{error || 'Invoice tidak ditemukan'}</p>
            <a
              href="/booking"
              className="inline-block bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-6 rounded-lg transition-colors"
            >
              Buat Booking Baru
            </a>
          </div>
        </div>
        <Footer />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      <Navbar />
      
      <main className="py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-2xl mx-auto">
          {/* Header */}
          <div className="text-center mb-8">
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
              Young 911 Autowerks
            </h1>
            <p className="text-gray-600 dark:text-gray-400">
              Pembayaran Invoice
            </p>
          </div>

          {/* Status Card */}
          <div className="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 sm:p-8 mb-6">
            {/* Status Badge */}
            <div className="flex items-center justify-center gap-2 mb-6">
              <div className={`flex items-center gap-2 px-4 py-3 rounded-xl ${getStatusColor(invoice.status)}`}>
                {getStatusIcon(invoice.status)}
                <span className="font-semibold">{invoice.status_label}</span>
              </div>
            </div>

            {/* Invoice Details */}
            <div className="space-y-4 mb-6">
              <div className="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
                <span className="text-gray-600 dark:text-gray-400">Nomor Invoice</span>
                <span className="font-mono font-semibold text-gray-900 dark:text-white">{invoice.invoice_number}</span>
              </div>

              <div className="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
                <span className="text-gray-600 dark:text-gray-400">Status Pembayaran</span>
                <span className={`px-3 py-1 rounded-full text-sm font-medium ${getStatusColor(invoice.is_paid ? 'paid' : 'pending')}`}>
                  {invoice.is_paid ? 'Lunas' : 'Belum Dibayar'}
                </span>
              </div>

              <div className="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
                <span className="text-gray-600 dark:text-gray-400">Total Tagihan</span>
                <span className="text-2xl font-bold text-primary-600 dark:text-primary-400">
                  Rp {invoice.total.toLocaleString('id-ID')}
                </span>
              </div>

              {invoice.paid_amount && (
                <div className="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
                  <span className="text-gray-600 dark:text-gray-400">Jumlah Dibayar</span>
                  <span className="text-lg font-semibold text-green-600 dark:text-green-400">
                    Rp {invoice.paid_amount.toLocaleString('id-ID')}
                  </span>
                </div>
              )}

              {invoice.paid_at && (
                <div className="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
                  <span className="text-gray-600 dark:text-gray-400">Tanggal Pembayaran</span>
                  <span className="font-medium text-gray-900 dark:text-white">
                    {new Date(invoice.paid_at).toLocaleDateString('id-ID', {
                      day: 'numeric',
                      month: 'long',
                      year: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit',
                    })}
                  </span>
                </div>
              )}
            </div>
          </div>

          {/* Payment Action Card */}
          {!invoice.is_paid && invoice.status !== 'cancelled' && (
            <div className="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 sm:p-8">
              <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-4 text-center">
                Selesaikan Pembayaran Anda
              </h3>
              
              {processingPayment ? (
                <div className="flex items-center justify-center gap-2 text-gray-600 dark:text-gray-400 py-4">
                  <svg className="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>Memproses pembayaran...</span>
                </div>
              ) : (
                <button
                  onClick={generatePaymentLink}
                  className="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-4 px-6 rounded-xl text-center transition-colors duration-200 flex items-center justify-center gap-2"
                >
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                  Bayar Sekarang
                </button>
              )}

              <p className="text-xs text-gray-500 dark:text-gray-400 text-center mt-4">
                Pembayaran diproses melalui Midtrans. Anda akan diarahkan ke halaman pembayaran yang aman.
              </p>

              <div className="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                <div className="flex items-start gap-3">
                  <svg className="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <div className="text-sm text-gray-700 dark:text-gray-300">
                    <p className="font-medium text-blue-900 dark:text-blue-300 mb-1">Informasi Pembayaran:</p>
                    <ul className="list-disc list-inside space-y-1 text-blue-800 dark:text-blue-400">
                      <li>Transfer Bank (BCA, Mandiri, BNI)</li>
                      <li>Kartu Kredit/Debit</li>
                      <li>E-Wallet (GoPay, OVO, Dana, ShopeePay)</li>
                      <li>QRIS</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* Paid Success Card */}
          {invoice.is_paid && (
            <div className="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 sm:p-8 text-center">
              <div className="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Pembayaran Berhasil!
              </h3>
              <p className="text-gray-600 dark:text-gray-400 mb-6">
                Terima kasih! Pembayaran Anda telah kami terima.
              </p>
              <div className="flex flex-col sm:flex-row gap-3 justify-center">
                <a
                  href={`${API_URL}/invoice/${invoiceNumber}/print`}
                  target="_blank"
                  className="inline-flex items-center justify-center gap-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-medium py-3 px-6 rounded-xl hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors"
                >
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                  </svg>
                  Unduh Invoice
                </a>
                <a
                  href="/tracking"
                  className="inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-medium py-3 px-6 rounded-xl transition-colors"
                >
                  Lacak Booking
                </a>
              </div>
            </div>
          )}

          {/* Cancelled Card */}
          {invoice.status === 'cancelled' && (
            <div className="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 sm:p-8 text-center">
              <div className="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>
              <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Invoice Dibatalkan
              </h3>
              <p className="text-gray-600 dark:text-gray-400 mb-6">
                Invoice ini telah dibatalkan. Silakan hubungi kami untuk informasi lebih lanjut.
              </p>
              <a
                href="/booking"
                className="inline-block bg-primary-600 hover:bg-primary-700 text-white font-medium py-3 px-6 rounded-xl transition-colors"
              >
                Buat Booking Baru
              </a>
            </div>
          )}

          {/* Help Section */}
          <div className="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
            <p>Butuh bantuan? Hubungi kami di</p>
            <p className="font-medium text-gray-900 dark:text-white">+62 812 3456 7890</p>
          </div>
        </div>
      </main>

      <Footer />
    </div>
  );
}
