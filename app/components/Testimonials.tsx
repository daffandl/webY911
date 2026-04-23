'use client';

import { useState, useEffect } from 'react';
import { useAuth } from './AuthProvider';
import { useRouter } from 'next/navigation';

interface ApiTestimonial {
  id: number;
  user_name: string;
  profile_photo?: string | null;
  vehicle_info: string;
  rating: number;
  comment: string;
  created_at: string;
}

interface DisplayTestimonial {
  id: number;
  name: string;
  vehicle: string;
  rating: number;
  comment: string;
  date: string;
  initials: string;
  profile_photo?: string | null;
}

const initialTestimonials: DisplayTestimonial[] = [
  {
    id: 1,
    name: 'Budi Santoso',
    vehicle: 'Range Rover Sport 2021',
    rating: 5,
    comment: 'Pelayanan sangat profesional! Teknisi mereka benar-benar ahli dalam menangani Land Rover. Mobil saya kembali prima setelah servis di sini.',
    date: 'March 2026',
    initials: 'BS',
    profile_photo: null,
  },
  {
    id: 2,
    name: 'Rina Wijaya',
    vehicle: 'Defender 110 2022',
    rating: 5,
    comment: 'Sudah 3 tahun servis di Young 911 dan tidak pernah kecewa. Harga transparan, spare part original, dan hasilnya selalu memuaskan.',
    date: 'February 2026',
    initials: 'RW',
    profile_photo: null,
  },
];

function transformTestimonial(t: ApiTestimonial): DisplayTestimonial {
  const date = new Date(t.created_at);
  const month = date.toLocaleString('en-US', { month: 'long' });
  const year = date.getFullYear();
  
  return {
    id: t.id,
    name: t.user_name,
    vehicle: t.vehicle_info || 'Land Rover',
    rating: t.rating,
    comment: t.comment,
    date: `${month} ${year}`,
    initials: t.user_name.split(' ').map((w) => w[0]).join('').toUpperCase().slice(0, 2),
    profile_photo: t.profile_photo,
  };
}

function StarRating({ rating, size = 'sm' }: { rating: number; size?: 'sm' | 'lg' }) {
  const sz = size === 'lg' ? 'w-5 h-5' : 'w-4 h-4';
  
  return (
    <div className="flex gap-0.5">
      {[1, 2, 3, 4, 5].map((star) => {
        const isFull = star <= rating;
        const isHalf = !isFull && star - 0.5 === rating;
        
        return (
          <div key={star} className="relative">
            {/* Background star (empty) */}
            <svg
              className={`${sz} text-gray-300 dark:text-gray-600`}
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            
            {/* Filled star (full or half) overlay */}
            {(isFull || isHalf) && (
              <svg
                className={`${sz} text-amber-400 absolute top-0 left-0`}
                fill="currentColor"
                viewBox="0 0 20 20"
                style={{ 
                  width: isHalf ? '50%' : '100%',
                  overflow: 'hidden'
                }}
              >
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            )}
          </div>
        );
      })}
    </div>
  );
}

export default function Testimonials() {
  const { isAuthenticated, user, isLoading: authLoading } = useAuth();
  const router = useRouter();
  const [testimonials, setTestimonials] = useState<DisplayTestimonial[]>(initialTestimonials);
  const [showForm, setShowForm] = useState(false);
  const [showAll, setShowAll] = useState(false);
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState({ total: 0, average_rating: 0, rating_distribution: {} as Record<number, number> });
  const [form, setForm] = useState({
    vehicle: '',
    rating: 5,
    comment: '',
  });
  const [submitted, setSubmitted] = useState(false);
  const [formSubmitting, setFormSubmitting] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);

  const API_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api';

  useEffect(() => {
    fetchReviews();
    fetchStats();
  }, []);

  const fetchReviews = async () => {
    try {
      const res = await fetch(`${API_URL}/reviews?per_page=50`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        credentials: 'omit', // Don't send cookies
      });
      if (!res.ok) {
        const errorData = await res.text();
        console.error('Fetch reviews error:', errorData);
        throw new Error('Failed to fetch reviews');
      }
      const json = await res.json();
      if (json.success && json.data) {
        const transformed = json.data.map(transformTestimonial);
        // Combine with initial testimonials if no API reviews
        setTestimonials(transformed.length > 0 ? transformed : initialTestimonials);
      }
    } catch (error) {
      console.error('Failed to fetch reviews:', error);
      // Keep initial testimonials on error
    } finally {
      setLoading(false);
    }
  };

  const fetchStats = async () => {
    try {
      const res = await fetch(`${API_URL}/reviews/statistics`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        credentials: 'omit', // Don't send cookies
      });
      if (!res.ok) {
        const errorData = await res.text();
        console.error('Fetch stats error:', errorData);
        throw new Error('Failed to fetch stats');
      }
      const json = await res.json();
      if (json.success && json.data) {
        setStats({
          total: json.data.total || 0,
          average_rating: json.data.average_rating || 0,
          rating_distribution: json.data.rating_distribution || {},
        });
      }
    } catch (error) {
      console.error('Failed to fetch review stats:', error);
    }
  };

  const handleFormSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    // Check if user is authenticated
    if (!isAuthenticated || !user) {
      setFormError('Anda harus login terlebih dahulu untuk memberikan review');
      return;
    }

    if (!form.comment.trim() || form.comment.trim().length < 10) {
      setFormError('Komentar minimal 10 karakter');
      return;
    }

    setFormSubmitting(true);
    setFormError(null);

    // Get token from localStorage
    const token = localStorage.getItem('auth_token');

    const payload = {
      user_name: user.name,
      vehicle_info: form.vehicle.trim() || user.name || 'Land Rover',
      rating: form.rating,
      comment: form.comment.trim(),
    };

    try {
      const res = await fetch(`${API_URL}/reviews`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
        body: JSON.stringify(payload),
      });

      // Check if response is OK before parsing JSON
      if (!res.ok) {
        const errorText = await res.text();
        console.error('Submit review error response:', errorText);
        try {
          const errorJson = JSON.parse(errorText);
          throw new Error(errorJson.message || 'Gagal mengirim review');
        } catch {
          throw new Error(`Server error (${res.status}): ${errorText.substring(0, 100)}`);
        }
      }

      const json = await res.json();

      if (!json.success) {
        throw new Error(json.message || 'Gagal mengirim review');
      }

      setSubmitted(true);
      // Refresh reviews after submission
      setTimeout(() => {
        fetchReviews();
        fetchStats();
      }, 3000);
    } catch (err: any) {
      console.error('Review submission error:', err);
      setFormError(err.message || 'Gagal mengirim review. Silakan coba lagi.');
    } finally {
      setFormSubmitting(false);
    }
  };

  const avgRating = stats.average_rating > 0 
    ? stats.average_rating.toFixed(1)
    : (testimonials.length > 0 
        ? (testimonials.reduce((s, t) => s + t.rating, 0) / testimonials.length).toFixed(1)
        : '0.0');

  const displayTotal = stats.total > 0 ? stats.total : testimonials.length;

  const clipBadge = { clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' };
  const clipInput = { clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' };

  const inputClass =
    'w-full px-4 py-3 border border-[#86efac] dark:border-[#1a2e1a] bg-[#dcfce7] dark:bg-[#0a0f0a] text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-transparent transition-all text-sm';

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!form.comment.trim()) return;

    setSubmitted(true);

    // Note: This is just a UI form. For actual review submission from completed booking,
    // users should use the tracking page review modal.
    // This form could be enhanced to submit to API in the future.

    setTimeout(() => {
      setSubmitted(false);
      setShowForm(false);
      setForm({ vehicle: '', rating: 5, comment: '' });
    }, 2000);
  };

  return (
    <section id="testimonials" className="py-14 sm:py-20 bg-[#dcfce7] dark:bg-[#0a0f0a] border-b border-[#86efac] dark:border-[#1a2e1a]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {/* Header */}
        <div className="text-center mb-10 sm:mb-14">
          <h2 className="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
            Trusted by Our Customers
          </h2>
          <p className="text-gray-600 dark:text-gray-300 text-lg max-w-2xl mx-auto mb-6">
            Ribuan pelanggan telah mempercayakan kendaraan Land Rover mereka kepada kami
          </p>

          {/* Stats row */}
          <div className="inline-flex items-center gap-6 flex-wrap justify-center mb-6">
            <div className="flex items-center gap-2">
              <StarRating rating={parseFloat(avgRating)} size="lg" />
              <span className="text-2xl font-bold text-gray-900 dark:text-white">{avgRating}</span>
              <span className="text-gray-500 dark:text-gray-400 text-sm">/ 5.0</span>
            </div>
            <div className="w-px h-6 bg-[#86efac] dark:bg-[#1a2e1a]" />
            <span className="text-gray-600 dark:text-gray-300 text-sm font-medium">
              {displayTotal} Reviews Terverifikasi
            </span>
            {stats.total > 0 && (
              <>
                <div className="w-px h-6 bg-[#86efac] dark:bg-[#1a2e1a]" />
                <span className="text-green-600 dark:text-green-400 text-xs font-semibold flex items-center gap-1">
                  <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                  </svg>
                  Dari pelanggan kami
                </span>
              </>
            )}
          </div>

        </div>

        {/* CTA — Leave a Comment (di atas grid) */}
        <div className="text-center mb-8">
          {stats.total > 0 && (
            <p className="text-sm text-gray-600 dark:text-gray-400 mb-4 max-w-lg mx-auto">
              Bergabunglah dengan {displayTotal}+ pelanggan yang telah memberikan review tentang layanan kami.
            </p>
          )}
          {!showForm && !authLoading && (
            <button
              onClick={() => {
                if (!isAuthenticated) {
                  router.push('/login');
                  return;
                }
                setShowForm(true);
              }}
              className="btn-green"
            >
              <span>{isAuthenticated ? 'Tulis Review Anda' : 'Login untuk Review'}</span>
              <span className="btn-icon-circle">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </span>
            </button>
          )}
        </div>

        {/* Comment Form — muncul di atas grid saat tombol diklik */}
        {showForm && (
          <div className="max-w-2xl mx-auto card-clip card-clip-faq p-6 sm:p-8 text-left mb-10">
            <div className="flex items-center justify-between mb-6">
            <div>
              <h3 className="text-lg font-bold text-gray-900 dark:text-white">Tulis Review</h3>
              {user && (
                <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                  Sebagai: <span className="font-medium text-gray-700 dark:text-gray-300">{user.name}</span>
                </p>
              )}
            </div>
            <button
                onClick={() => {
                  setShowForm(false);
                  setForm({ vehicle: '', rating: 5, comment: '' });
                  setSubmitted(false);
                  setFormError(null);
                }}
                className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            {submitted ? (
              <div className="text-center py-8">
                <div className="w-14 h-14 bg-[#166534] flex items-center justify-center mx-auto mb-4"
                  style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}>
                  <svg className="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <p className="text-gray-900 dark:text-white font-semibold text-lg mb-1">Terima Kasih!</p>
                <p className="text-gray-500 dark:text-gray-400 text-sm">
                  Review Anda telah berhasil dikirim dan langsung ditampilkan.
                </p>
              </div>
            ) : !isAuthenticated ? (
              <div className="text-center py-8">
                <div className="w-16 h-16 bg-[#166534] flex items-center justify-center mx-auto mb-4"
                  style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}>
                  <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-14V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <p className="text-gray-900 dark:text-white font-semibold text-lg mb-2">Login Diperlukan</p>
                <p className="text-gray-500 dark:text-gray-400 text-sm mb-4">
                  Anda harus login terlebih dahulu untuk memberikan review.
                </p>
                <div className="flex gap-3 justify-center">
                  <button
                    onClick={() => router.push('/login')}
                    className="px-6 py-2 bg-[#166534] text-white font-semibold hover:bg-[#14532d] transition-colors"
                    style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}
                  >
                    Login
                  </button>
                  <button
                    onClick={() => router.push('/register')}
                    className="px-6 py-2 bg-white dark:bg-gray-800 text-[#166534] border border-[#166534] font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}
                  >
                    Daftar
                  </button>
                </div>
              </div>
            ) : (
              <form onSubmit={handleFormSubmit} className="space-y-4">
                {/* Vehicle */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Tipe Kendaraan
                  </label>
                  <input
                    type="text"
                    value={form.vehicle}
                    onChange={(e) => setForm({ ...form, vehicle: e.target.value })}
                    placeholder="Contoh: Range Rover Sport 2022"
                    className={inputClass}
                    style={clipInput}
                  />
                </div>

                {/* Rating */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Rating <span className="text-red-400">*</span>
                  </label>
                  <div className="flex gap-2">
                    {[1, 2, 3, 4, 5].map((star) => (
                      <button
                        key={star}
                        type="button"
                        onClick={() => setForm({ ...form, rating: star })}
                        className="transition-transform hover:scale-110"
                      >
                        <svg
                          className={`w-8 h-8 transition-colors ${
                            star <= form.rating ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600'
                          }`}
                          fill="currentColor"
                          viewBox="0 0 20 20"
                        >
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                      </button>
                    ))}
                  </div>
                </div>

                {/* Comment */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Komentar <span className="text-red-400">*</span>
                  </label>
                  <textarea
                    value={form.comment}
                    onChange={(e) => setForm({ ...form, comment: e.target.value })}
                    required
                    rows={4}
                    placeholder="Ceritakan pengalaman Anda tentang layanan kami..."
                    className={`${inputClass} resize-none`}
                    style={clipInput}
                  />
                  <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Minimal 10 karakter
                  </p>
                </div>

                {/* Error Message */}
                {formError && (
                  <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3">
                    <p className="text-sm text-red-600 dark:text-red-400">{formError}</p>
                  </div>
                )}

                {/* Submit Button */}
                <button
                  type="submit"
                  disabled={formSubmitting}
                  className="w-full btn-glow py-3 justify-center disabled:opacity-60"
                >
                  {formSubmitting ? (
                    <>
                      <svg className="animate-spin w-5 h-5" viewBox="0 0 24 24" fill="none">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                      </svg>
                      <span>Mengirim...</span>
                    </>
                  ) : (
                    <>
                      <span>Kirim Review</span>
                      <svg className="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                      </svg>
                    </>
                  )}
                </button>
              </form>
            )}
          </div>
        )}

        {/* Loading State */}
        {loading && (
          <div className="text-center py-12">
            <svg className="animate-spin w-8 h-8 mx-auto text-green-600 dark:text-green-400 mb-4" viewBox="0 0 24 24" fill="none">
              <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
              <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
            </svg>
            <p className="text-gray-600 dark:text-gray-400">Memuat reviews dari pelanggan...</p>
          </div>
        )}

        {/* Testimonial Cards Grid */}
        {!loading && testimonials.length > 0 && (
          <>
            <div className="grid sm:grid-cols-2 gap-5 sm:gap-6 mb-8">
              {testimonials.slice(0, showAll ? testimonials.length : 4).map((t) => (
                <div key={t.id} className="card-clip card-clip-faq p-5 sm:p-6 flex flex-col gap-4">
                  {/* Top row: avatar + name + rating */}
                  <div className="flex items-start gap-3">
                    {/* Avatar */}
                    {t.profile_photo ? (
                      <img
                        src={t.profile_photo}
                        alt={t.name}
                        className="w-11 h-11 flex-shrink-0 object-cover"
                        style={{ clipPath: 'polygon(0 0, calc(100% - 6px) 0, 100% 6px, 100% 100%, 6px 100%, 0 calc(100% - 6px))' }}
                      />
                    ) : (
                      <div
                        className="w-11 h-11 flex-shrink-0 flex items-center justify-center bg-[#166534] text-white text-sm font-bold"
                        style={{ clipPath: 'polygon(0 0, calc(100% - 6px) 0, 100% 6px, 100% 100%, 6px 100%, 0 calc(100% - 6px))' }}
                      >
                        {t.initials}
                      </div>
                    )}
                    <div className="flex-1 min-w-0">
                      <p className="font-semibold text-gray-900 dark:text-white text-sm truncate">{t.name}</p>
                      <p className="text-[#166534] dark:text-[#4ade80] text-xs truncate">{t.vehicle}</p>
                    </div>
                    <span className="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0">{t.date}</span>
                  </div>

                  {/* Stars */}
                  <StarRating rating={t.rating} />

                  {/* Comment */}
                  <p className="text-gray-600 dark:text-gray-300 text-sm leading-relaxed flex-1">
                    &ldquo;{t.comment}&rdquo;
                  </p>
                </div>
              ))}
            </div>

            {/* Tombol All Comments */}
            {testimonials.length > 4 && (
              <div className="text-center">
                <button
                  onClick={() => setShowAll((prev) => !prev)}
                  className="btn-outline text-gray-900 dark:text-white px-8 py-3"
                >
                  {showAll ? 'Sembunyikan' : `Lihat Semua (${testimonials.length})`}
                  <span className="btn-icon-circle" style={{ background: 'rgba(22,101,52,0.20)' }}>
                    <svg
                      className={`w-4 h-4 transition-transform duration-300 ${showAll ? 'rotate-180' : ''}`}
                      fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                  </span>
                </button>
              </div>
            )}
          </>
        )}

        {!loading && testimonials.length === 0 && (
          <div className="text-center py-12 text-gray-500 dark:text-gray-400">
            <p>Belum ada review. Jadilah yang pertama memberikan review!</p>
          </div>
        )}

      </div>
    </section>
  );
}
