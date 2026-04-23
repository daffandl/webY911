'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useAuth } from '../components/AuthProvider';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

interface Stats {
  total_bookings: number;
  pending: number;
  confirmed: number;
  in_progress: number;
  completed: number;
  cancelled: number;
  pending_payment: number;
  paid: number;
  total_invoices: number;
  total_reviews: number;
}

interface Booking {
  id: number;
  booking_code: string;
  car_model: string;
  service_type: string;
  status: string;
  preferred_date: string;
  created_at: string;
}

function StatCard({ title, value, icon, color }: { title: string; value: number; icon: string; color: string }) {
  const colors: Record<string, string> = {
    green: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
    blue: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
    orange: 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400',
    purple: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
    red: 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
    gray: 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
  };

  return (
    <div className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-4 lg:p-6 card-clip">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm text-gray-600 dark:text-gray-400">{title}</p>
          <p className="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mt-1">{value}</p>
        </div>
        <div className={`p-3 rounded-lg ${colors[color]}`} style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}>
          <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            {icon === 'booking' && <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />}
            {icon === 'pending' && <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />}
            {icon === 'progress' && <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />}
            {icon === 'completed' && <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />}
            {icon === 'invoice' && <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />}
            {icon === 'payment' && <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />}
          </svg>
        </div>
      </div>
    </div>
  );
}

export default function DashboardPage() {
  const { user, token } = useAuth();
  const [stats, setStats] = useState<Stats | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [recentBookings, setRecentBookings] = useState<Booking[]>([]);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchData = async () => {
      if (!token) {
        setIsLoading(false);
        setError('Token tidak ditemukan. Silakan login ulang.');
        return;
      }

      try {
        const headers = {
          Authorization: `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        };

        const [statsRes, bookingsRes] = await Promise.all([
          fetch(`${API_URL}/auth/statistics`, { headers }),
          fetch(`${API_URL}/bookings/my?per_page=5`, { headers }),
        ]);

        // Handle stats response
        if (statsRes.ok) {
          const statsData = await statsRes.json();
          setStats(statsData.data);
        } else {
          const errorData = await statsRes.json().catch(() => null);
          if (statsRes.status === 401) {
            setError('Sesi habis. Silakan login ulang.');
          } else if (statsRes.status === 404) {
            setError('Endpoint statistics tidak ditemukan. Pastikan backend sudah di-update.');
          } else {
            setError(`Gagal memuat statistik: ${errorData?.message || 'Unknown error'}`);
          }
        }

        // Handle bookings response
        if (bookingsRes.ok) {
          const bookingsData = await bookingsRes.json();
          const bookings = bookingsData.data || [];
          setRecentBookings(Array.isArray(bookings) ? bookings.slice(0, 5) : []);
        } else {
          const errorData = await bookingsRes.json().catch(() => null);
          if (bookingsRes.status === 401) {
            setError('Sesi habis. Silakan login ulang.');
          } else if (bookingsRes.status === 404) {
            setError('Endpoint bookings tidak ditemukan. Pastikan backend sudah berjalan.');
          } else {
            // Don't fail completely, just show empty state
            setRecentBookings([]);
          }
        }
      } catch (error: any) {
        console.error('Error fetching data:', error);
        setError(`Gagal memuat data: ${error.message || 'Unknown error'}. Pastikan backend sudah berjalan di ${API_URL}`);
      } finally {
        setIsLoading(false);
      }
    };

    fetchData();
  }, [token]);

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

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-4 border-green-600 border-t-transparent"></div>
          <p className="mt-4 text-gray-600 dark:text-gray-400">Memuat dashboard...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center max-w-md">
          <div className="inline-flex items-center justify-center w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
            <svg className="w-8 h-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-2">Gagal Memuat Dashboard</h2>
          <p className="text-gray-600 dark:text-gray-400 mb-4">{error}</p>
          <div className="flex gap-3 justify-center">
            <button
              onClick={() => window.location.reload()}
              className="px-4 py-2 btn-green"
            >
              <span>Coba Lagi</span>
            </button>
            <a
              href="/login"
              className="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-lg transition-colors"
              style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}
            >
              Login Ulang
            </a>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Welcome header */}
      <div>
        <h1 className="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
          Selamat Datang, {user?.name}! 👋
        </h1>
        <p className="text-gray-600 dark:text-gray-400 mt-1">
          Berikut ringkasan aktivitas booking Anda
        </p>
      </div>

      {/* Stats grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <StatCard
          title="Total Booking"
          value={stats?.total_bookings || 0}
          icon="booking"
          color="green"
        />
        <StatCard
          title="Menunggu Konfirmasi"
          value={stats?.pending || 0}
          icon="pending"
          color="orange"
        />
        <StatCard
          title="Sedang Dikerjakan"
          value={stats?.in_progress || 0}
          icon="progress"
          color="blue"
        />
        <StatCard
          title="Selesai"
          value={stats?.completed || 0}
          icon="completed"
          color="green"
        />
        <StatCard
          title="Invoice Pending"
          value={stats?.total_invoices || 0}
          icon="invoice"
          color="purple"
        />
        <StatCard
          title="Sudah Dibayar"
          value={stats?.paid || 0}
          icon="payment"
          color="green"
        />
      </div>

      {/* Recent bookings */}
      <div className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] card-clip">
        <div className="px-4 lg:px-6 py-4 border-b border-[#86efac] dark:border-[#1a2e1a]">
          <h2 className="text-lg font-semibold text-gray-900 dark:text-white">Booking Terbaru</h2>
        </div>
        <div className="divide-y divide-[#86efac] dark:divide-[#1a2e1a]">
          {recentBookings.length === 0 ? (
            <div className="px-4 lg:px-6 py-12 text-center">
              <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <p className="mt-2 text-gray-600 dark:text-gray-400">Belum ada booking</p>
              <Link
                href="/dashboard/booking"
                className="mt-4 inline-block text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 font-medium"
              >
                Buat booking pertama Anda →
              </Link>
            </div>
          ) : (
            recentBookings.map((booking) => (
              <div key={booking.id} className="px-4 lg:px-6 py-4 hover:bg-[#86efac]/30 dark:hover:bg-[#1a2e1a] transition-colors">
                <div className="flex items-center justify-between">
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2 mb-1">
                      <span className="font-mono text-sm font-semibold text-green-600 dark:text-green-400">
                        {booking.booking_code}
                      </span>
                      {getStatusBadge(booking.status)}
                    </div>
                    <p className="text-sm text-gray-900 dark:text-white font-medium truncate">
                      {booking.car_model} - {booking.service_type}
                    </p>
                    <p className="text-xs text-gray-600 dark:text-gray-400 mt-1">
                      {new Date(booking.preferred_date).toLocaleDateString('id-ID', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                      })}
                    </p>
                  </div>
                  <Link
                    href={`/tracking?code=${booking.booking_code}`}
                    className="ml-4 text-sm text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 font-medium whitespace-nowrap"
                  >
                    Lacak →
                  </Link>
                </div>
              </div>
            ))
          )}
        </div>
        {recentBookings.length > 0 && (
          <div className="px-4 lg:px-6 py-3 border-t border-[#86efac] dark:border-[#1a2e1a]">
            <Link
              href="/dashboard/bookings"
              className="text-sm text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 font-medium"
            >
              Lihat semua booking →
            </Link>
          </div>
        )}
      </div>
    </div>
  );
}
