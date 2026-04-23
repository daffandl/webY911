'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useAuth } from '../../components/AuthProvider';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

interface Review {
  id: number;
  booking_code: string | null;
  car_model: string | null;
  service_type: string | null;
  rating: number;
  comment: string;
  status: 'approved';
  created_at: string;
}

export default function ReviewsPage() {
  const { token, user } = useAuth();
  const [reviews, setReviews] = useState<Review[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [meta, setMeta] = useState<{
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  } | null>(null);
  const [currentPage, setCurrentPage] = useState(1);

  useEffect(() => {
    if (!token) return;

    const fetchReviews = async () => {
      setIsLoading(true);
      try {
        const response = await fetch(`${API_URL}/bookings/my/reviews?page=${currentPage}`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        if (response.ok) {
          const data = await response.json();
          setReviews(data.data);
          setMeta(data.meta);
        }
      } catch (error) {
        console.error('Error fetching reviews:', error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchReviews();
  }, [token, currentPage]);

  const renderStars = (rating: number) => {
    return (
      <div className="flex gap-0.5">
        {[1, 2, 3, 4, 5].map((star) => (
          <svg
            key={star}
            className={`w-5 h-5 ${star <= rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-700'}`}
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        ))}
      </div>
    );
  };

  const renderProfilePhoto = () => {
    if (user?.profile_photo) {
      return (
        <img
          src={user.profile_photo}
          alt={user.name}
          className="w-12 h-12 rounded-full object-cover border-2 border-green-600 dark:border-green-400"
        />
      );
    }
    
    // Fallback to initials
    const initials = user?.name
      ? user.name.split(' ').map((w) => w[0]).join('').toUpperCase().slice(0, 2)
      : 'U';
    
    return (
      <div className="w-12 h-12 rounded-full bg-gradient-to-br from-green-600 to-green-700 flex items-center justify-center text-white font-bold">
        {initials}
      </div>
    );
  };

  return (
    <div className="space-y-6">
      {/* Header with profile photo */}
      <div className="flex items-start justify-between gap-4">
        <div className="flex-1">
          <h1 className="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">Ulasan Saya</h1>
          <p className="text-gray-600 dark:text-gray-400 mt-1">
            {meta ? `${meta.total} ulasan ditemukan` : 'Semua ulasan Anda'}
          </p>
        </div>
        {renderProfilePhoto()}
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center min-h-[300px]">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-12 w-12 border-4 border-green-600 border-t-transparent"></div>
            <p className="mt-4 text-gray-600 dark:text-gray-400">Memuat ulasan...</p>
          </div>
        </div>
      ) : reviews.length === 0 ? (
        <div className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-12 text-center card-clip">
          <svg className="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
          </svg>
          <p className="mt-4 text-lg font-medium text-gray-900 dark:text-white">Belum ada ulasan</p>
          <p className="text-gray-600 dark:text-gray-400 mt-1">Beri ulasan untuk booking yang sudah selesai</p>
          <Link
            href="/dashboard/bookings"
            className="mt-4 inline-block text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 font-medium"
          >
            Lihat booking Anda →
          </Link>
        </div>
      ) : (
        <div className="space-y-4">
          {reviews.map((review) => (
            <div key={review.id} className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-4 lg:p-6 card-clip">
              <div className="flex items-start justify-between gap-4">
                <div className="flex-1">
                  <div className="flex items-center gap-2 mb-2 flex-wrap">
                    {renderStars(review.rating)}
                  </div>
                  <p className="text-gray-900 dark:text-white font-medium">{review.comment}</p>
                  <div className="flex items-center gap-4 mt-3 text-sm text-gray-600 dark:text-gray-400 flex-wrap">
                    {review.booking_code && (
                      <Link
                        href={`/tracking?code=${review.booking_code}`}
                        className="font-mono text-green-600 dark:text-green-400 hover:underline"
                      >
                        {review.booking_code}
                      </Link>
                    )}
                    {review.car_model && (
                      <span className="flex items-center gap-1">
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        {review.car_model}
                      </span>
                    )}
                    <span className="flex items-center gap-1">
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      {new Date(review.created_at).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric',
                      })}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Pagination */}
      {meta && meta.last_page > 1 && (
        <div className="flex items-center justify-between gap-4">
          <p className="text-sm text-gray-600 dark:text-gray-400">
            Halaman {meta.current_page} dari {meta.last_page}
          </p>
          <div className="flex gap-2">
            <button
              onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
              disabled={currentPage === 1}
              className="px-4 py-2 bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] text-gray-900 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[#a7f3d0] dark:hover:bg-[#1a2e1a] transition-colors card-clip"
            >
              Sebelumnya
            </button>
            <button
              onClick={() => setCurrentPage((p) => Math.min(meta.last_page, p + 1))}
              disabled={currentPage === meta.last_page}
              className="px-4 py-2 bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] text-gray-900 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[#a7f3d0] dark:hover:bg-[#1a2e1a] transition-colors card-clip"
            >
              Berikutnya
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
