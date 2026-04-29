'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useAuth } from '../../components/AuthProvider';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

interface Booking {
  id: number;
  booking_code: string;
  car_model: string;
  service_type: string;
  status: string;
  preferred_date: string;
  scheduled_at: string | null;
  notes: string | null;
  admin_notes: string | null;
  payment_status: string;
  created_at: string;
  updated_at: string;
  has_review?: boolean;
}

export default function BookingsPage() {
  const { token } = useAuth();
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [filter, setFilter] = useState('all');
  const [meta, setMeta] = useState<{
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  } | null>(null);

  useEffect(() => {
    if (!token) return;

    const fetchBookings = async () => {
      setIsLoading(true);
      try {
        const url = filter === 'all'
          ? `${API_URL}/bookings/my`
          : `${API_URL}/bookings/my?status=${filter}`;

        const response = await fetch(url, {
          headers: { 
            Authorization: `Bearer ${token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
        });

        if (response.ok) {
          const data = await response.json();
          setBookings(data.data || []);
          setMeta(data.meta);
        } else {
          if (response.status === 401) {
            // Token expired, redirect to login
            window.location.href = '/login';
          }
        }
      } catch (error) {
        console.error('Error fetching bookings:', error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchBookings();
  }, [token, filter]);

  const getStatusBadge = (status: string) => {
    const badges: Record<string, string> = {
      pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
      confirmed: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
      in_progress: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
      completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
      cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
      rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
      issue: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
    };

    const labels: Record<string, string> = {
      pending: 'Menunggu',
      confirmed: 'Dikonfirmasi',
      in_progress: 'Dikerjakan',
      completed: 'Selesai',
      cancelled: 'Dibatalkan',
      rejected: 'Ditolak',
      issue: 'Ada Masalah',
    };

    return (
      <span className={`px-2 py-1 text-xs font-medium rounded-full ${badges[status] || badges.pending}`}>
        {labels[status] || status}
      </span>
    );
  };

  const getPaymentBadge = (paymentStatus: string) => {
    const badges: Record<string, string> = {
      pending: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400',
      paid: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
      failed: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    };

    const labels: Record<string, string> = {
      pending: 'Belum Bayar',
      paid: 'Lunas',
      failed: 'Gagal',
    };

    return (
      <span className={`px-2 py-1 text-xs font-medium rounded-full ${badges[paymentStatus] || badges.pending}`}>
        {labels[paymentStatus] || paymentStatus}
      </span>
    );
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">Riwayat Booking</h1>
          <p className="text-gray-600 dark:text-gray-400 mt-1">
            {meta ? `${meta.total} booking ditemukan` : 'Semua booking Anda'}
          </p>
        </div>
        <Link
          href="/dashboard/booking"
          className="btn-glow whitespace-nowrap"
        >
          <span>+ Booking Baru</span>
          <span className="btn-icon-circle ml-auto">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
          </span>
        </Link>
      </div>

      {/* Filter tabs */}
      <div className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-2 overflow-x-auto card-clip">
        <div className="flex gap-2">
          {[
            { key: 'all', label: 'Semua' },
            { key: 'pending', label: 'Menunggu' },
            { key: 'confirmed', label: 'Dikonfirmasi' },
            { key: 'in_progress', label: 'Dikerjakan' },
            { key: 'completed', label: 'Selesai' },
            { key: 'cancelled', label: 'Dibatalkan' },
          ].map((tab) => (
            <button
              key={tab.key}
              onClick={() => setFilter(tab.key)}
              className={`px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors ${
                filter === tab.key
                  ? 'bg-green-600 text-white'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-[#86efac]/30 dark:hover:bg-[#1a2e1a]'
              }`}
            >
              {tab.label}
            </button>
          ))}
        </div>
      </div>

      {/* Bookings list */}
      {isLoading ? (
        <div className="flex items-center justify-center min-h-[300px]">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-12 w-12 border-4 border-green-600 border-t-transparent"></div>
            <p className="mt-4 text-gray-600 dark:text-gray-400">Memuat booking...</p>
          </div>
        </div>
      ) : bookings.length === 0 ? (
        <div className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-12 text-center card-clip">
          <svg className="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <p className="mt-4 text-lg font-medium text-gray-900 dark:text-white">Tidak ada booking</p>
          <p className="text-gray-600 dark:text-gray-400 mt-1">
            {filter !== 'all' ? 'Tidak ada booking dengan status ini' : 'Belum ada booking. Mulai buat booking pertama Anda!'}
          </p>
          <Link
            href="/dashboard/booking"
            className="mt-4 inline-block btn-glow"
          >
            <span>Booking Baru</span>
            <span className="btn-icon-circle ml-auto">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
              </svg>
            </span>
          </Link>
        </div>
      ) : (
        <div className="space-y-4">
          {bookings.map((booking) => (
            <div key={booking.id} className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-4 lg:p-6 hover:shadow-md transition-shadow card-clip">
              <div className="flex items-start justify-between gap-4">
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2 mb-2 flex-wrap">
                    <span className="font-mono text-sm font-bold text-green-600 dark:text-green-400">
                      {booking.booking_code}
                    </span>
                    {getStatusBadge(booking.status)}
                    {booking.payment_status && getPaymentBadge(booking.payment_status)}
                  </div>
                  <h3 className="text-gray-900 dark:text-white font-semibold truncate">
                    {booking.car_model}
                  </h3>
                  <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">{booking.service_type}</p>
                  <div className="flex items-center gap-4 mt-3 text-sm text-gray-600 dark:text-gray-400">
                    <span className="flex items-center gap-1">
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      {new Date(booking.preferred_date).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric',
                      })}
                    </span>
                    <span className="flex items-center gap-1">
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      Dibuat {new Date(booking.created_at).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric',
                      })}
                    </span>
                  </div>
                </div>
                <div className="flex flex-col gap-2">
                  <Link
                    href={`/tracking?code=${booking.booking_code}`}
                    className="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors text-center whitespace-nowrap"
                    style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}
                  >
                    Lacak
                  </Link>
                  {booking.has_review && (
                    <span className="px-2 py-1 text-xs bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded text-center flex items-center gap-1 justify-center">
                      <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                      Reviewed
                    </span>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
