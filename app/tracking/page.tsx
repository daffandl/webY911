'use client';

import { useState, useEffect, Suspense } from 'react';
import { useSearchParams } from 'next/navigation';
import Link from 'next/link';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import InvoiceSection from '../components/InvoiceSection';
import ReviewModal from '../components/ReviewModal';
import { useAuth } from '../components/AuthProvider';

// ─── Types ───────────────────────────────────────────────────────────────────
interface BookingData {
  id: number;
  booking_code: string;
  name: string;
  phone: string;
  car_model: string;
  vehicle_info?: string;
  service_type: string;
  preferred_date?: string;
  scheduled_at?: string;
  notes?: string;
  admin_notes?: string;
  status: string;
  status_label: string;
  created_at: string;
  has_review?: boolean;
}

// ─── SVG Icons ────────────────────────────────────────────────────────────────
const Icons = {
  search: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>
  ),
  searchLg: (
    <svg className="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>
  ),
  arrowRight: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
    </svg>
  ),
  arrowLeft: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
    </svg>
  ),
  refresh: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
    </svg>
  ),
  calendar: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
    </svg>
  ),
  check: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
    </svg>
  ),
  checkCircle: (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  ),
  clock: (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  ),
  xCircle: (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  ),
  wrench: (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
  ),
  star: (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
    </svg>
  ),
  ban: (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
    </svg>
  ),
  xError: (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  ),
  spinner: (
    <svg className="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
    </svg>
  ),
  spinnerLg: (
    <svg className="animate-spin w-8 h-8" viewBox="0 0 24 24" fill="none">
      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
    </svg>
  ),
  note: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
    </svg>
  ),
};

// ─── Status config ────────────────────────────────────────────────────────────
const STATUS_CONFIG: Record<
  string,
  {
    label: string;
    icon: React.ReactNode;
    color: string;
    bg: string;
    border: string;
    description: string;
  }
> = {
  pending: {
    label: 'Menunggu Konfirmasi',
    icon: Icons.clock,
    color: 'text-yellow-600 dark:text-yellow-400',
    bg: 'bg-yellow-50 dark:bg-yellow-900/20',
    border: 'border-yellow-200 dark:border-yellow-700',
    description: 'Booking Anda sedang ditinjau oleh tim kami. Kami akan segera menghubungi Anda.',
  },
  confirmed: {
    label: 'Dikonfirmasi',
    icon: Icons.checkCircle,
    color: 'text-green-600 dark:text-green-400',
    bg: 'bg-green-50 dark:bg-green-900/20',
    border: 'border-green-200 dark:border-green-700',
    description: 'Booking Anda telah dikonfirmasi! Silakan datang sesuai jadwal.',
  },
  rejected: {
    label: 'Ditolak',
    icon: Icons.xCircle,
    color: 'text-red-600 dark:text-red-400',
    bg: 'bg-red-50 dark:bg-red-900/20',
    border: 'border-red-200 dark:border-red-800',
    description: 'Mohon maaf, booking Anda tidak dapat diproses. Silakan buat booking baru.',
  },
  in_progress: {
    label: 'Sedang Dikerjakan',
    icon: Icons.wrench,
    color: 'text-blue-600 dark:text-blue-400',
    bg: 'bg-blue-50 dark:bg-blue-900/20',
    border: 'border-blue-200 dark:border-blue-700',
    description: 'Kendaraan Anda sedang dalam proses pengerjaan oleh teknisi kami.',
  },
  issue: {
    label: 'Ada Masalah',
    icon: (
      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
    ),
    color: 'text-amber-600 dark:text-amber-400',
    bg: 'bg-amber-50 dark:bg-amber-900/20',
    border: 'border-amber-200 dark:border-amber-700',
    description: 'Tim teknisi menemukan kendala pada kendaraan Anda. Kami akan segera menghubungi Anda.',
  },
  completed: {
    label: 'Selesai',
    icon: Icons.star,
    color: 'text-green-600 dark:text-green-400',
    bg: 'bg-green-50 dark:bg-green-900/20',
    border: 'border-green-200 dark:border-green-700',
    description: 'Layanan telah selesai. Terima kasih telah mempercayai Young 911 Autowerks!',
  },
  cancelled: {
    label: 'Dibatalkan',
    icon: Icons.ban,
    color: 'text-gray-600 dark:text-gray-400',
    bg: 'bg-[#bbf7d0] dark:bg-gray-900/20',
    border: 'border-[#86efac] dark:border-gray-700',
    description: 'Booking ini telah dibatalkan.',
  },
};

// ─── Timeline steps ───────────────────────────────────────────────────────────
const TIMELINE_STEPS: { key: string; label: string; icon: React.ReactNode }[] = [
  {
    key: 'pending',
    label: 'Booking Diterima',
    icon: (
      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>
    ),
  },
  {
    key: 'confirmed',
    label: 'Dikonfirmasi Admin',
    icon: (
      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    ),
  },
  {
    key: 'in_progress',
    label: 'Sedang Dikerjakan',
    icon: (
      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>
    ),
  },
  {
    key: 'completed',
    label: 'Selesai',
    icon: (
      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
          d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
      </svg>
    ),
  },
];

const STEP_ORDER = ['pending', 'confirmed', 'in_progress', 'completed'];

function getStepIndex(status: string): number {
  if (status === 'rejected' || status === 'cancelled') return -1;
  if (status === 'issue') return STEP_ORDER.indexOf('in_progress'); // issue is a sub-state of in_progress
  return STEP_ORDER.indexOf(status);
}

const clipStyle = {
  clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))',
};

const inputClass =
  'w-full px-4 py-3 border border-[#86efac] dark:border-[#1a2e1a] bg-[#bbf7d0] dark:bg-[#0a0f0a] text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-transparent transition-all text-sm';

// ─────────────────────────────────────────────────────────────────────────────
//  TrackingContent (uses useSearchParams)
// ─────────────────────────────────────────────────────────────────────────────
function TrackingContent() {
  const searchParams = useSearchParams();
  const initialCode = searchParams.get('code') ?? '';
  const { token } = useAuth();

  const [code, setCode] = useState(initialCode.toUpperCase());
  const [inputCode, setInputCode] = useState(initialCode.toUpperCase());
  const [booking, setBooking] = useState<BookingData | null>(null);
  const [bookingHasReview, setBookingHasReview] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [searched, setSearched] = useState(false);
  const [showReviewModal, setShowReviewModal] = useState(false);
  const [reviewSubmitting, setReviewSubmitting] = useState(false);
  const [availableSlots, setAvailableSlots] = useState<{
    available: boolean;
    slots_remaining: number;
    next_available_date: string;
  } | null>(null);

  const API_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api';

  useEffect(() => {
    if (initialCode) handleSearch(initialCode.toUpperCase());
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleSearch = async (searchCode?: string) => {
    const target = (searchCode ?? inputCode).trim().toUpperCase();
    if (!target) {
      setError('Masukkan kode booking terlebih dahulu.');
      return;
    }
    setLoading(true);
    setError(null);
    setBooking(null);
    setBookingHasReview(false);
    setSearched(true);
    setCode(target);

    try {
      const res = await fetch(`${API_URL}/bookings/track/${target}`, {
        headers: { Accept: 'application/json' },
      });
      const json = await res.json();
      if (!res.ok || !json.success) {
        setError(json.message ?? 'Booking tidak ditemukan.');
        return;
      }
      setBooking(json.data);
      
      // Check if booking already has a review (from backend response if available)
      // Note: The backend track endpoint should ideally return this info
      // For now, we assume it doesn't have a review until user submits one
    } catch {
      setError('Tidak dapat terhubung ke server. Periksa koneksi Anda.');
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    handleSearch();
  };

  const handleReviewSubmit = async (data: {
    user_name: string;
    vehicle_info: string;
    rating: number;
    comment: string;
  }) => {
    if (!booking) return;

    setReviewSubmitting(true);
    try {
      const headers: Record<string, string> = {
        'Content-Type': 'application/json',
      };
      
      // Add auth token if available
      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      const res = await fetch(`${API_URL}/bookings/${booking.booking_code}/review`, {
        method: 'POST',
        headers,
        body: JSON.stringify({
          user_name: data.user_name,
          vehicle_info: data.vehicle_info,
          rating: data.rating,
          comment: data.comment,
        }),
      });
      const json = await res.json();
      if (!res.ok || !json.success) {
        throw new Error(json.message ?? 'Gagal mengirim review');
      }
      // Indicate that review has been submitted
      setBookingHasReview(true);
    } catch (err) {
      throw err;
    } finally {
      setReviewSubmitting(false);
    }
  };

  const checkAvailability = async (date: string) => {
    try {
      const res = await fetch(`${API_URL}/bookings/availability?date=${date}`);
      const data = await res.json();
      setAvailableSlots(data);
    } catch (err) {
      console.error('Failed to check availability:', err);
    }
  };

  const sc = booking ? (STATUS_CONFIG[booking.status] ?? STATUS_CONFIG['pending']) : null;
  const stepIndex = booking ? getStepIndex(booking.status) : -1;
  const isTerminal = booking?.status === 'rejected' || booking?.status === 'cancelled';
  const canReview = booking?.status === 'completed' && !bookingHasReview;

  return (
    <div className="max-w-3xl mx-auto">

      {/* ── Search box ── */}
      <div className="card-clip card-clip-light p-8 mb-8">
        <div className="flex items-center gap-2 mb-2">
          <span className="text-green-600 dark:text-green-400">{Icons.search}</span>
          <h2 className="text-xl font-bold text-gray-900 dark:text-white">
            Lacak Status Booking
          </h2>
        </div>
        <p className="text-sm text-gray-600 dark:text-gray-400 mb-6">
          Masukkan kode booking yang Anda terima via WhatsApp atau email.
        </p>

        <form onSubmit={handleSubmit} className="flex gap-3">
          <input
            type="text"
            value={inputCode}
            onChange={(e) => setInputCode(e.target.value.toUpperCase())}
            placeholder="Contoh: YNG-20240321-001"
            className={`${inputClass} flex-1 font-mono tracking-wider`}
            style={clipStyle}
          />
          {/* Search button — icon kanan */}
          <button
            type="submit"
            disabled={loading}
            className="btn-green px-5 py-3 text-sm font-bold whitespace-nowrap disabled:opacity-60"
          >
            {loading ? (
              <>
                <span>Mencari</span>
                <span className="btn-icon-circle ml-auto">{Icons.spinner}</span>
              </>
            ) : (
              <>
                <span>Cari</span>
                <span className="btn-icon-circle ml-auto">{Icons.search}</span>
              </>
            )}
          </button>
        </form>
      </div>

      {/* ── Error state ── */}
      {error && searched && (
        <div className="card-clip p-6 mb-8 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
          <div className="flex items-start gap-3">
            <span className="text-red-500 flex-shrink-0 mt-0.5">{Icons.xError}</span>
            <div>
              <p className="font-semibold text-red-500 dark:text-red-400">Booking Tidak Ditemukan</p>
              <p className="text-sm text-red-500 dark:text-red-300 mt-1">{error}</p>
              <p className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                Pastikan kode booking benar. Format:{' '}
                <code className="font-mono bg-[#bbf7d0] dark:bg-gray-800 px-1 rounded">
                  YNG-YYYYMMDD-NNN
                </code>
              </p>
            </div>
          </div>
        </div>
      )}

      {/* ── Booking result ── */}
      {booking && sc && (
        <div className="space-y-6">

          {/* Status banner */}
          <div className={`card-clip p-6 border ${sc.border} ${sc.bg}`}>
            <div className="flex items-center gap-4">
              <span className={`${sc.color} flex-shrink-0`}
                style={{ transform: 'scale(1.6)', transformOrigin: 'center' }}>
                {sc.icon}
              </span>
              <div>
                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">
                  Status Booking — {booking.booking_code}
                </p>
                <p className={`text-2xl font-bold ${sc.color}`}>{sc.label}</p>
                <p className="text-sm text-gray-600 dark:text-gray-300 mt-1">{sc.description}</p>
              </div>
            </div>

            {booking.admin_notes && (
              <div className="mt-4 pt-4 border-t border-current/20">
                <div className="flex items-center gap-1.5 mb-1">
                  <span className="text-gray-500 dark:text-gray-400">{Icons.note}</span>
                  <p className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Catatan dari Admin
                  </p>
                </div>
                <p className="text-sm text-gray-700 dark:text-gray-300">{booking.admin_notes}</p>
              </div>
            )}
          </div>

          {/* Timeline */}
          {!isTerminal && (
            <div className="card-clip card-clip-light p-6">
              <h3 className="font-semibold text-gray-900 dark:text-white mb-5 text-sm uppercase tracking-wide">
                Progress Booking
              </h3>
              <div className="relative">
                <div className="absolute left-5 top-5 bottom-5 w-0.5 bg-[#86efac] dark:bg-[#1a2e1a]" />
                <div className="space-y-4">
                  {TIMELINE_STEPS.map((step, idx) => {
                    const isDone    = idx <= stepIndex;
                    const isCurrent = idx === stepIndex;
                    return (
                      <div key={step.key} className="flex items-center gap-4 relative">
                        <div
                          className={`w-10 h-10 flex items-center justify-center flex-shrink-0 z-10 border-2 transition-all ${
                            isCurrent
                              ? 'bg-[#166534] border-[#166534] text-white scale-110'
                              : isDone
                              ? 'bg-[#166534] border-[#166534] text-white'
                              : 'bg-[#dcfce7] dark:bg-[#0a0f0a] border-[#86efac] dark:border-[#1a2e1a] text-gray-500'
                          }`}
                          style={{ clipPath: 'polygon(0 0, calc(100% - 6px) 0, 100% 6px, 100% 100%, 6px 100%, 0 calc(100% - 6px))' }}
                        >
                          {isDone && !isCurrent ? Icons.check : step.icon}
                        </div>
                        <div>
                          <p className={`font-medium text-sm ${
                            isCurrent
                              ? 'text-green-600 dark:text-green-400'
                              : isDone
                              ? 'text-gray-900 dark:text-white'
                              : 'text-gray-400 dark:text-gray-600'
                          }`}>
                            {step.label}
                          </p>
                          {isCurrent && (
                            <p className="text-xs text-gray-500 dark:text-gray-400">Status saat ini</p>
                          )}
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
            </div>
          )}

          {/* Booking details */}
          <div className="card-clip card-clip-light p-6">
            <h3 className="font-semibold text-gray-900 dark:text-white mb-4 text-sm uppercase tracking-wide">
              Detail Booking
            </h3>
            <div className="space-y-3 text-sm">
              {[
                { label: 'Kode Booking', value: booking.booking_code, mono: true },
                { label: 'Nama', value: booking.name },
                { label: 'Tipe Mobil', value: booking.car_model },
                booking.vehicle_info ? { label: 'Info Kendaraan', value: booking.vehicle_info } : null,
                { label: 'Layanan', value: booking.service_type },
                booking.preferred_date
                  ? {
                      label: 'Tanggal Pilihan',
                      value: new Date(booking.preferred_date).toLocaleDateString('id-ID', {
                        day: 'numeric', month: 'long', year: 'numeric',
                      }),
                    }
                  : null,
                booking.scheduled_at
                  ? {
                      label: 'Jadwal Terkonfirmasi',
                      value:
                        new Date(booking.scheduled_at).toLocaleString('id-ID', {
                          day: 'numeric', month: 'long', year: 'numeric',
                          hour: '2-digit', minute: '2-digit',
                        }) + ' WIB',
                    }
                  : null,
                booking.notes ? { label: 'Catatan', value: booking.notes } : null,
                {
                  label: 'Dibuat',
                  value:
                    new Date(booking.created_at).toLocaleString('id-ID', {
                      day: 'numeric', month: 'long', year: 'numeric',
                      hour: '2-digit', minute: '2-digit',
                    }) + ' WIB',
                },
              ]
                .filter(Boolean)
                .map((item) => (
                  <div
                    key={item!.label}
                    className="flex justify-between gap-4 py-2 border-b border-[#86efac] dark:border-[#1a2e1a] last:border-0"
                  >
                    <span className="text-gray-500 dark:text-gray-400 flex-shrink-0">{item!.label}</span>
                    <span
                      className={`font-medium text-gray-900 dark:text-white text-right ${
                        item!.mono ? 'font-mono tracking-wider' : ''
                      }`}
                    >
                      {item!.value}
                    </span>
                  </div>
                ))}
            </div>
          </div>

          {/* Invoice Section */}
          <InvoiceSection bookingCode={booking.booking_code} />

          {/* Review Prompt for Completed Bookings */}
          {canReview && (
            <div className="card-clip p-6 border border-[#166534] bg-gradient-to-r from-[#166534]/10 to-[#166534]/5 dark:from-[#166534]/20 dark:to-[#166534]/10">
              <div className="flex items-start gap-4">
                <div
                  className="w-12 h-12 flex-shrink-0 flex items-center justify-center bg-[#166534] text-white"
                  style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}
                >
                  <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
                <div className="flex-1">
                  <h3 className="font-bold text-gray-900 dark:text-white text-lg mb-1">
                    Bagaimana Pengalaman Anda?
                  </h3>
                  <p className="text-sm text-gray-600 dark:text-gray-300 mb-3">
                    Bantu kami meningkatkan kualitas layanan dengan memberikan review tentang pengalaman Anda.
                  </p>
                  <button
                    onClick={() => setShowReviewModal(true)}
                    className="btn-glow py-2.5 text-sm font-semibold"
                  >
                    <span>Tulis Review Sekarang</span>
                    <span className="btn-icon-circle ml-auto">
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </span>
                  </button>
                </div>
              </div>
            </div>
          )}

          {/* Already reviewed message */}
          {booking?.has_review && (
            <div className="card-clip p-6 border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20">
              <div className="flex items-center gap-3">
                <svg className="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                </svg>
                <div>
                  <p className="font-semibold text-green-700 dark:text-green-400">
                    Terima Kasih atas Review Anda!
                  </p>
                  <p className="text-sm text-green-600 dark:text-green-300">
                    Review Anda telah dikirim dan akan muncul setelah disetujui.
                  </p>
                </div>
              </div>
            </div>
          )}

          {/* Action buttons — icon selalu di kanan */}
          <div className="flex flex-col sm:flex-row gap-3">
            {booking.status === 'rejected' && (
              <Link href="/booking" className="flex-1 btn-glow py-3 text-sm font-semibold">
                <span>Buat Booking Baru</span>
                <span className="btn-icon-circle ml-auto">{Icons.calendar}</span>
              </Link>
            )}
            <button
              onClick={() => handleSearch(code)}
              className="flex-1 btn-green py-3 text-sm font-semibold"
            >
              <span>Refresh Status</span>
              <span className="btn-icon-circle ml-auto">{Icons.refresh}</span>
            </button>
            <Link
              href="/"
              className="flex-1 btn-outline py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center justify-between px-5"
              style={clipStyle}
            >
              <span className="text-green-600 dark:text-green-400">{Icons.arrowLeft}</span>
              <span>Beranda</span>
            </Link>
          </div>
        </div>
      )}

      {/* ── Empty state ── */}
      {!searched && !booking && (
        <div className="text-center py-16 text-gray-400 dark:text-gray-600">
          <div className="flex justify-center mb-4 opacity-30">
            {Icons.searchLg}
          </div>
          <p className="text-lg font-medium text-gray-500 dark:text-gray-400">
            Masukkan kode booking untuk melihat status
          </p>
          <p className="text-sm mt-2">
            Belum punya booking?{' '}
            <Link
              href="/booking"
              className="text-green-600 dark:text-green-400 font-medium hover:underline"
            >
              Buat booking sekarang
            </Link>
          </p>
        </div>
      )}

      {/* Review Modal */}
      {booking && (
        <ReviewModal
          isOpen={showReviewModal}
          onClose={() => setShowReviewModal(false)}
          onSubmit={handleReviewSubmit}
          bookingCode={booking.booking_code}
          customerName={booking.name}
          vehicleInfo={booking.vehicle_info || booking.car_model}
        />
      )}
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
//  Page
// ─────────────────────────────────────────────────────────────────────────────
export default function TrackingPage() {
  return (
    <>
      <Navbar />
      <main className="min-h-screen bg-[#dcfce7] dark:bg-[#0a0f0a] pt-24 pb-20">
        <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

          <div className="text-center mb-12">
            <div
              className="inline-block bg-[#166534] text-white px-6 py-2 text-sm font-semibold mb-4"
              style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}
            >
              Track Booking
            </div>
            <h1 className="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
              Lacak Status Booking
            </h1>
            <p className="text-gray-600 dark:text-gray-300 text-lg max-w-xl mx-auto">
              Pantau status booking servis Land Rover Anda secara real-time.
            </p>
          </div>

          <Suspense
            fallback={
              <div className="text-center py-20 text-gray-400 flex flex-col items-center gap-4">
                <span className="text-green-600 dark:text-green-400">{
                  <svg className="animate-spin w-10 h-10" viewBox="0 0 24 24" fill="none">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                  </svg>
                }</span>
                <p>Memuat...</p>
              </div>
            }
          >
            <TrackingContent />
          </Suspense>
        </div>
      </main>
      <Footer />
    </>
  );
}
