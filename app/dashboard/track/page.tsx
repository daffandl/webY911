'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

export default function TrackBookingPage() {
  const [bookingCode, setBookingCode] = useState('');
  const [booking, setBooking] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');
  const router = useRouter();

  const statusSteps = [
    { key: 'pending', label: 'Menunggu', icon: (
      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    )},
    { key: 'confirmed', label: 'Dikonfirmasi', icon: (
      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
      </svg>
    )},
    { key: 'in_progress', label: 'Dikerjakan', icon: (
      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>
    )},
    { key: 'completed', label: 'Selesai', icon: (
      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    )},
  ];

  const statusColors: Record<string, string> = {
    pending: 'bg-yellow-500',
    confirmed: 'bg-blue-500',
    in_progress: 'bg-purple-500',
    completed: 'bg-green-500',
    cancelled: 'bg-red-500',
    rejected: 'bg-red-500',
    issue: 'bg-orange-500',
  };

  const statusLabels: Record<string, string> = {
    pending: 'Menunggu Konfirmasi',
    confirmed: 'Dikonfirmasi',
    in_progress: 'Sedang Dikerjakan',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
    rejected: 'Ditolak',
    issue: 'Ada Masalah',
  };

  const handleSearch = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!bookingCode.trim()) return;

    setError('');
    setBooking(null);
    setIsLoading(true);

    try {
      const response = await fetch(`${API_URL}/bookings/track/${bookingCode.toUpperCase()}`);
      const data = await response.json();

      if (!response.ok || !data.success) {
        throw new Error(data.message || 'Booking tidak ditemukan');
      }

      setBooking(data.data);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setIsLoading(false);
    }
  };

  const getStatusIndex = (status: string) => {
    return statusSteps.findIndex(step => step.key === status);
  };

  return (
    <div className="max-w-3xl mx-auto space-y-6">
      <div>
        <h1 className="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">Lacak Booking</h1>
        <p className="text-gray-600 dark:text-gray-400 mt-1">Masukkan kode booking untuk melihat status</p>
      </div>

      {/* Search form */}
      <form onSubmit={handleSearch} className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-6 card-clip">
        <div className="flex gap-3">
          <input
            type="text"
            value={bookingCode}
            onChange={(e) => setBookingCode(e.target.value.toUpperCase())}
            placeholder="Masukkan kode booking (contoh: YNG-20260403-001)"
            className="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent uppercase font-mono"
          />
          <button
            type="submit"
            disabled={isLoading || !bookingCode.trim()}
            className="btn-glow disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
          >
            {isLoading ? <span>Mencari...</span> : <span>Cari</span>}
            {!isLoading && (
              <span className="btn-icon-circle">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
            )}
          </button>
        </div>
      </form>

      {error && (
        <div className="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
          <p className="text-sm text-red-600 dark:text-red-400">{error}</p>
        </div>
      )}

      {/* Booking details */}
      {booking && (
        <div className="space-y-6">
          {/* Status timeline */}
          <div className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-6 card-clip">
            <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status Booking</h2>

            {['cancelled', 'rejected', 'issue'].includes(booking.status) ? (
              <div className="text-center py-6">
                <div className={`inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 ${statusColors[booking.status]}`}>
                  {booking.status === 'cancelled' || booking.status === 'rejected' ? (
                    <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  ) : (
                    <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                  )}
                </div>
                <p className="text-xl font-bold text-gray-900 dark:text-white">{statusLabels[booking.status]}</p>
                {booking.admin_notes && (
                  <p className="text-sm text-gray-600 dark:text-gray-400 mt-2">{booking.admin_notes}</p>
                )}
              </div>
            ) : (
              <div className="flex items-center justify-between">
                {statusSteps.map((step, index) => {
                  const currentIndex = getStatusIndex(booking.status);
                  const isCompleted = index <= currentIndex;
                  const isCurrent = index === currentIndex;

                  return (
                    <div key={step.key} className="flex-1 flex items-center">
                      <div className="flex flex-col items-center">
                        <div
                          className={`w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold transition-all ${
                            isCompleted ? statusColors[step.key] : 'bg-gray-300 dark:bg-gray-700'
                          } ${isCurrent ? 'ring-4 ring-green-200 dark:ring-green-900' : ''}`}
                        >
                          {isCompleted ? step.icon : index + 1}
                        </div>
                        <span className={`text-xs mt-2 text-center ${isCompleted ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-600 dark:text-gray-400'}`}>
                          {step.label}
                        </span>
                      </div>
                      {index < statusSteps.length - 1 && (
                        <div className={`flex-1 h-1 mx-2 rounded ${index < currentIndex ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-700'}`} />
                      )}
                    </div>
                  );
                })}
              </div>
            )}
          </div>

          {/* Booking info */}
          <div className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-6 card-clip">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-lg font-semibold text-gray-900 dark:text-white">Detail Booking</h2>
              <span className="font-mono text-sm font-bold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-3 py-1 rounded">
                {booking.booking_code}
              </span>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400">Mobil</p>
                <p className="text-gray-900 dark:text-white font-medium">{booking.car_model}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400">Layanan</p>
                <p className="text-gray-900 dark:text-white font-medium">{booking.service_type}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400">Tanggal</p>
                <p className="text-gray-900 dark:text-white font-medium">
                  {new Date(booking.preferred_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}
                </p>
              </div>
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400">Dibuat pada</p>
                <p className="text-gray-900 dark:text-white font-medium">
                  {new Date(booking.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}
                </p>
              </div>
            </div>

            {booking.notes && (
              <div className="mt-4 pt-4 border-t border-[#86efac] dark:border-[#1a2e1a]">
                <p className="text-sm text-gray-600 dark:text-gray-400">Catatan</p>
                <p className="text-gray-900 dark:text-white">{booking.notes}</p>
              </div>
            )}
          </div>

          {/* Actions */}
          <div className="flex gap-3">
            {['pending', 'confirmed'].includes(booking.status) && (
              <>
                <button
                  onClick={() => router.push(`/tracking?code=${booking.booking_code}&action=reschedule`)}
                  className="flex-1 py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
                  style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}
                >
                  Reschedule
                </button>
                <button
                  onClick={() => router.push(`/tracking?code=${booking.booking_code}&action=cancel`)}
                  className="flex-1 py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors"
                  style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}
                >
                  Batalkan
                </button>
              </>
            )}
            {booking.status === 'completed' && !booking.has_review && (
              <button
                onClick={() => router.push(`/tracking?code=${booking.booking_code}&action=review`)}
                className="flex-1 py-3 px-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors"
                style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}
              >
                Beri Ulasan
              </button>
            )}
            <a
              href={`/tracking?code=${booking.booking_code}`}
              className="flex-1 py-3 px-4 bg-[#86efac]/30 dark:bg-[#1a2e1a] hover:bg-[#86efac]/50 dark:hover:bg-[#1a2e1a]/80 text-gray-900 dark:text-white font-semibold rounded-lg text-center transition-colors"
              style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}
            >
              Lihat Detail Lengkap
            </a>
          </div>
        </div>
      )}
    </div>
  );
}
