import React, { useEffect, useState } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import Link from 'next/link';

interface Booking {
  id: number;
  booking_code: string;
  car_model: string;
  service_type: string;
  preferred_date: string;
  status: string;
  status_label: string;
  payment_status: string;
}

interface Invoice {
  id: number;
  invoice_number: string;
  status: string;
  status_label: string;
  total: number;
  issued_at: string;
  payment_method?: string;
}

interface Statistics {
  total_bookings: number;
  pending_bookings: number;
  completed_bookings: number;
  total_spent: number;
  pending_invoices: number;
}

interface Review {
  id: number;
  rating: number;
  comment: string;
  booking_code: string;
  created_at: string;
}

const CustomerDashboard = () => {
  const { token, apiUrl } = useAuth();
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [reviews, setReviews] = useState<Review[]>([]);
  const [stats, setStats] = useState<Statistics | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [activeTab, setActiveTab] = useState<'overview' | 'bookings' | 'invoices' | 'reviews'>('overview');

  useEffect(() => {
    if (!token) return;
    fetchDashboardData();
  }, [token]);

  const fetchDashboardData = async () => {
    try {
      setLoading(true);
      setError(null);

      const headers = {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      };

      // Fetch statistics
      const statsRes = await fetch(`${apiUrl}/auth/statistics`, { headers });
      if (statsRes.ok) {
        const statsData = await statsRes.json();
        setStats(statsData.data);
      }

      // Fetch bookings
      const bookingsRes = await fetch(`${apiUrl}/bookings/my`, { headers });
      if (bookingsRes.ok) {
        const bookingsData = await bookingsRes.json();
        setBookings(bookingsData.data || []);
      }

      // Fetch invoices
      const invoicesRes = await fetch(`${apiUrl}/bookings/my/invoices`, { headers });
      if (invoicesRes.ok) {
        const invoicesData = await invoicesRes.json();
        setInvoices(invoicesData.data || []);
      }

      // Fetch reviews
      const reviewsRes = await fetch(`${apiUrl}/bookings/my/reviews`, { headers });
      if (reviewsRes.ok) {
        const reviewsData = await reviewsRes.json();
        setReviews(reviewsData.data || []);
      }
    } catch (err) {
      setError('Failed to load dashboard data. Please try again later.');
      console.error('Dashboard error:', err);
    } finally {
      setLoading(false);
    }
  };

  const getStatusBadgeClass = (status: string): string => {
    const baseClass = 'px-3 py-1 rounded-full text-sm font-medium';
    switch (status) {
      case 'pending':
      case 'draft':
        return `${baseClass} bg-yellow-100 text-yellow-800`;
      case 'confirmed':
      case 'sent':
        return `${baseClass} bg-blue-100 text-blue-800`;
      case 'completed':
      case 'paid':
        return `${baseClass} bg-green-100 text-green-800`;
      case 'cancelled':
        return `${baseClass} bg-red-100 text-red-800`;
      case 'in_progress':
        return `${baseClass} bg-purple-100 text-purple-800`;
      default:
        return `${baseClass} bg-gray-100 text-gray-800`;
    }
  };

  const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Loading dashboard...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">My Dashboard</h1>
        <p className="text-gray-600">Welcome back! Here's your booking and service information.</p>
      </div>

      {error && (
        <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
          {error}
        </div>
      )}

      {/* Statistics Cards */}
      {stats && (
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
          <div className="bg-white rounded-lg shadow p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-600 text-sm">Total Bookings</p>
                <p className="text-2xl font-bold text-gray-900">{stats.total_bookings}</p>
              </div>
              <div className="text-3xl text-blue-500">📋</div>
            </div>
          </div>

          <div className="bg-white rounded-lg shadow p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-600 text-sm">Pending</p>
                <p className="text-2xl font-bold text-yellow-600">{stats.pending_bookings}</p>
              </div>
              <div className="text-3xl text-yellow-500">⏳</div>
            </div>
          </div>

          <div className="bg-white rounded-lg shadow p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-600 text-sm">Completed</p>
                <p className="text-2xl font-bold text-green-600">{stats.completed_bookings}</p>
              </div>
              <div className="text-3xl text-green-500">✅</div>
            </div>
          </div>

          <div className="bg-white rounded-lg shadow p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-600 text-sm">Total Spent</p>
                <p className="text-xl font-bold text-purple-600">{formatCurrency(stats.total_spent)}</p>
              </div>
              <div className="text-3xl text-purple-500">💰</div>
            </div>
          </div>
        </div>
      )}

      {/* Tabs */}
      <div className="mb-6 border-b border-gray-200">
        <div className="flex space-x-8">
          <button
            onClick={() => setActiveTab('overview')}
            className={`pb-4 font-medium text-sm ${
              activeTab === 'overview'
                ? 'text-blue-600 border-b-2 border-blue-600'
                : 'text-gray-600 hover:text-gray-900'
            }`}
          >
            Overview
          </button>
          <button
            onClick={() => setActiveTab('bookings')}
            className={`pb-4 font-medium text-sm ${
              activeTab === 'bookings'
                ? 'text-blue-600 border-b-2 border-blue-600'
                : 'text-gray-600 hover:text-gray-900'
            }`}
          >
            Bookings ({bookings.length})
          </button>
          <button
            onClick={() => setActiveTab('invoices')}
            className={`pb-4 font-medium text-sm ${
              activeTab === 'invoices'
                ? 'text-blue-600 border-b-2 border-blue-600'
                : 'text-gray-600 hover:text-gray-900'
            }`}
          >
            Invoices ({invoices.length})
          </button>
          <button
            onClick={() => setActiveTab('reviews')}
            className={`pb-4 font-medium text-sm ${
              activeTab === 'reviews'
                ? 'text-blue-600 border-b-2 border-blue-600'
                : 'text-gray-600 hover:text-gray-900'
            }`}
          >
            Reviews ({reviews.length})
          </button>
        </div>
      </div>

      {/* Tab Content */}
      <div>
        {/* Overview Tab */}
        {activeTab === 'overview' && (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Recent Bookings */}
            <div className="lg:col-span-2 bg-white rounded-lg shadow p-6">
              <h2 className="text-lg font-bold text-gray-900 mb-4">Recent Bookings</h2>
              {bookings.length > 0 ? (
                <div className="space-y-4">
                  {bookings.slice(0, 5).map((booking) => (
                    <div key={booking.id} className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                      <div className="flex justify-between items-start">
                        <div>
                          <p className="font-semibold text-gray-900">{booking.car_model}</p>
                          <p className="text-sm text-gray-600">{booking.service_type}</p>
                          <p className="text-xs text-gray-500 mt-1">Code: {booking.booking_code}</p>
                        </div>
                        <div className="text-right">
                          <div className={getStatusBadgeClass(booking.status)}>
                            {booking.status_label}
                          </div>
                          <p className="text-sm text-gray-600 mt-2">{formatDate(booking.preferred_date)}</p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-gray-600">No bookings yet.</p>
              )}
              {bookings.length > 5 && (
                <Link href="/dashboard?tab=bookings" className="mt-4 inline-block text-blue-600 hover:text-blue-800 font-medium">
                  View All Bookings →
                </Link>
              )}
            </div>

            {/* Pending Invoices */}
            <div className="bg-white rounded-lg shadow p-6">
              <h2 className="text-lg font-bold text-gray-900 mb-4">Pending Invoices</h2>
              {invoices.filter(inv => inv.status !== 'paid').length > 0 ? (
                <div className="space-y-3">
                  {invoices
                    .filter(inv => inv.status !== 'paid')
                    .slice(0, 5)
                    .map((invoice) => (
                      <div key={invoice.id} className="border border-yellow-200 bg-yellow-50 rounded-lg p-3">
                        <p className="font-semibold text-sm text-gray-900">{invoice.invoice_number}</p>
                        <p className="text-xs text-gray-600">{formatCurrency(invoice.total)}</p>
                        <div className={`mt-1 ${getStatusBadgeClass(invoice.status)} inline-block`}>
                          {invoice.status_label}
                        </div>
                      </div>
                    ))}
                </div>
              ) : (
                <p className="text-gray-600 text-sm">No pending invoices.</p>
              )}
            </div>
          </div>
        )}

        {/* Bookings Tab */}
        {activeTab === 'bookings' && (
          <div className="bg-white rounded-lg shadow p-6">
            <h2 className="text-lg font-bold text-gray-900 mb-4">All Bookings</h2>
            {bookings.length > 0 ? (
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead className="bg-gray-50 border-b border-gray-200">
                    <tr>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-900">Code</th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-900">Car</th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-900">Service</th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-900">Payment</th>
                    </tr>
                  </thead>
                  <tbody>
                    {bookings.map((booking) => (
                      <tr key={booking.id} className="border-b border-gray-200 hover:bg-gray-50">
                        <td className="px-4 py-3 text-sm font-medium text-blue-600">
                          <Link href={`/tracking/${booking.booking_code}`}>
                            {booking.booking_code}
                          </Link>
                        </td>
                        <td className="px-4 py-3 text-sm text-gray-900">{booking.car_model}</td>
                        <td className="px-4 py-3 text-sm text-gray-600">{booking.service_type}</td>
                        <td className="px-4 py-3 text-sm text-gray-600">{formatDate(booking.preferred_date)}</td>
                        <td className="px-4 py-3 text-sm">
                          <div className={getStatusBadgeClass(booking.status)}>
                            {booking.status_label}
                          </div>
                        </td>
                        <td className="px-4 py-3 text-sm">
                          <div className={getStatusBadgeClass(booking.payment_status)}>
                            {booking.payment_status}
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            ) : (
              <p className="text-gray-600">No bookings found.</p>
            )}
          </div>
        )}

        {/* Invoices Tab */}
        {activeTab === 'invoices' && (
          <div className="bg-white rounded-lg shadow p-6">
            <h2 className="text-lg font-bold text-gray-900 mb-4">All Invoices</h2>
            {invoices.length > 0 ? (
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead className="bg-gray-50 border-b border-gray-200">
                    <tr>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-900">Invoice</th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-900">Amount</th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-900">Payment Method</th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-900">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    {invoices.map((invoice) => (
                      <tr key={invoice.id} className="border-b border-gray-200 hover:bg-gray-50">
                        <td className="px-4 py-3 text-sm font-medium text-blue-600">
                          <Link href={`/verify?invoice=${invoice.invoice_number}`}>
                            {invoice.invoice_number}
                          </Link>
                        </td>
                        <td className="px-4 py-3 text-sm text-gray-600">{formatDate(invoice.issued_at)}</td>
                        <td className="px-4 py-3 text-sm font-medium text-gray-900">{formatCurrency(invoice.total)}</td>
                        <td className="px-4 py-3 text-sm">
                          <div className={getStatusBadgeClass(invoice.status)}>
                            {invoice.status_label}
                          </div>
                        </td>
                        <td className="px-4 py-3 text-sm text-gray-600">{invoice.payment_method || '-'}</td>
                        <td className="px-4 py-3 text-sm">
                          {invoice.status !== 'paid' && (
                            <Link href={`/payment/${invoice.invoice_number}`} className="text-blue-600 hover:text-blue-800 font-medium">
                              Pay Now
                            </Link>
                          )}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            ) : (
              <p className="text-gray-600">No invoices found.</p>
            )}
          </div>
        )}

        {/* Reviews Tab */}
        {activeTab === 'reviews' && (
          <div className="bg-white rounded-lg shadow p-6">
            <h2 className="text-lg font-bold text-gray-900 mb-4">My Reviews</h2>
            {reviews.length > 0 ? (
              <div className="space-y-4">
                {reviews.map((review) => (
                  <div key={review.id} className="border border-gray-200 rounded-lg p-4">
                    <div className="flex justify-between items-start">
                      <div>
                        <div className="flex items-center mb-2">
                          <div className="flex text-yellow-400">
                            {[...Array(5)].map((_, i) => (
                              <span key={i}>{i < review.rating ? '★' : '☆'}</span>
                            ))}
                          </div>
                          <span className="ml-2 text-sm text-gray-600">({review.rating}/5)</span>
                        </div>
                        <p className="text-gray-900">{review.comment}</p>
                        <p className="text-xs text-gray-500 mt-2">Booking: {review.booking_code}</p>
                      </div>
                      <p className="text-sm text-gray-600">{formatDate(review.created_at)}</p>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-gray-600">No reviews yet. <Link href="/booking" className="text-blue-600 hover:text-blue-800">Create a booking and leave a review.</Link></p>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export default CustomerDashboard;