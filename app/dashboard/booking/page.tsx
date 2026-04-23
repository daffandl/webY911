'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '../../components/AuthProvider';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

const LAND_ROVER_MODELS = [
  'Land Rover Series I (1948)',
  'Land Rover Series II (1958)',
  'Land Rover Series IIA (1961)',
  'Land Rover Series III (1971)',
  'Range Rover Classic (1970)',
  'Land Rover Defender 90 (1983)',
  'Land Rover Defender 110 (1983)',
  'Land Rover Discovery 1 (1989)',
  'Range Rover (2nd Gen, 1994)',
  'Land Rover Freelander 1 (1997)',
  'Land Rover Discovery 2 (1998)',
  'Range Rover (3rd Gen L322, 2002)',
  'Land Rover Discovery 3 / LR3 (2004)',
  'Range Rover Sport (1st Gen, 2005)',
  'Land Rover Freelander 2 / LR2 (2006)',
  'Land Rover Discovery 4 / LR4 (2009)',
  'Range Rover Evoque (1st Gen, 2011)',
  'Range Rover (4th Gen L405, 2012)',
  'Range Rover Sport (2nd Gen, 2013)',
  'Land Rover Discovery Sport (2014)',
  'Land Rover Discovery 5 / LR5 (2017)',
  'Range Rover Velar (2017)',
  'Land Rover Defender (L663, 2020)',
  'Range Rover (5th Gen L460, 2022)',
  'Range Rover Sport (3rd Gen L461, 2022)',
];

const SERVICE_TYPES = [
  'Service Berkala (Regular Maintenance)',
  'Ganti Oli (Oil Change)',
  'Servis Rem (Brake Service)',
  'Diagnosa Mesin (Engine Diagnostic)',
  'Servis Transmisi (Transmission Service)',
  'Suspensi & Kemudi (Suspension & Steering)',
  'Servis AC (Air Conditioning)',
  'Sistem Kelistrikan (Electrical System)',
  'Ganti Ban (Tire Replacement)',
  'Tune Up',
  'Lainnya (Other)',
];

export default function BookingPage() {
  const { user } = useAuth();
  const router = useRouter();

  const [formData, setFormData] = useState({
    name: user?.name || '',
    phone: user?.phone || '',
    email: user?.email || '',
    car_model: '',
    vehicle_info: '',
    service_type: '',
    date: '',
    notes: '',
  });

  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState<{ code: string; data: any } | null>(null);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccess(null);
    setIsLoading(true);

    const token = localStorage.getItem('auth_token');
    if (!token) {
      router.push('/login');
      return;
    }

    try {
      const response = await fetch(`${API_URL}/bookings`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          name: formData.name,
          phone: formData.phone,
          email: formData.email,
          car_model: formData.car_model,
          vehicle_info: formData.vehicle_info || null,
          service_type: formData.service_type,
          date: formData.date,
          notes: formData.notes || null,
        }),
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        throw new Error(data.message || data.error || 'Gagal membuat booking');
      }

      setSuccess({ code: data.booking_code, data: data.data });
    } catch (err: any) {
      setError(err.message || 'Gagal membuat booking. Silakan coba lagi.');
    } finally {
      setIsLoading(false);
    }
  };

  const getTodayDate = () => {
    const today = new Date();
    return today.toISOString().split('T')[0];
  };

  if (success) {
    return (
      <div className="max-w-2xl mx-auto">
        <div className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-6 lg:p-8 card-clip">
          <div className="text-center mb-6">
            <div className="inline-flex items-center justify-center w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full mb-4">
              <svg className="w-8 h-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Booking Berhasil!</h2>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Kami akan segera menghubungi Anda</p>
          </div>

          <div className="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
            <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Kode Booking Anda:</p>
            <p className="text-2xl font-bold text-green-600 dark:text-green-400 font-mono">{success.code}</p>
          </div>

          <div className="space-y-3 mb-6">
            <div className="flex justify-between text-sm">
              <span className="text-gray-600 dark:text-gray-400">Mobil:</span>
              <span className="text-gray-900 dark:text-white font-medium">{success.data.car_model}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-gray-600 dark:text-gray-400">Layanan:</span>
              <span className="text-gray-900 dark:text-white font-medium">{success.data.service_type}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-gray-600 dark:text-gray-400">Tanggal:</span>
              <span className="text-gray-900 dark:text-white font-medium">
                {new Date(success.data.preferred_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}
              </span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-gray-600 dark:text-gray-400">Status:</span>
              <span className="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 rounded-full">
                Menunggu Konfirmasi
              </span>
            </div>
          </div>

          <div className="flex flex-col sm:flex-row gap-3">
            <Link
              href={`/tracking?code=${success.code}`}
              className="flex-1 btn-green"
            >
              <span>Lacak Booking</span>
              <span className="btn-icon-circle ml-auto">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
            </Link>
            <button
              onClick={() => {
                setSuccess(null);
                setFormData({ name: user?.name || '', phone: user?.phone || '', email: user?.email || '', car_model: '', vehicle_info: '', service_type: '', date: '', notes: '' });
              }}
              className="flex-1 py-3 px-4 bg-[#86efac]/30 dark:bg-[#1a2e1a] hover:bg-[#86efac]/50 dark:hover:bg-[#1a2e1a]/80 text-gray-900 dark:text-white font-semibold rounded-lg transition-colors"
              style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}
            >
              Booking Baru
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-3xl mx-auto">
      <div className="mb-6">
        <h1 className="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">Booking Baru</h1>
        <p className="text-gray-600 dark:text-gray-400 mt-1">Isi form di bawah untuk membuat jadwal service kendaraan Anda</p>
      </div>

      {error && (
        <div className="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
          <p className="text-sm text-red-600 dark:text-red-400">{error}</p>
        </div>
      )}

      <form onSubmit={handleSubmit} className="bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-6 lg:p-8 space-y-5 card-clip">
        {/* Personal Info */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Nama Lengkap <span className="text-red-500">*</span>
            </label>
            <input
              id="name"
              name="name"
              type="text"
              required
              value={formData.name}
              onChange={handleChange}
              className="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
              placeholder="John Doe"
            />
          </div>

          <div>
            <label htmlFor="phone" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              No. WhatsApp <span className="text-red-500">*</span>
            </label>
            <input
              id="phone"
              name="phone"
              type="tel"
              required
              value={formData.phone}
              onChange={handleChange}
              className="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
              placeholder="081234567890"
            />
          </div>
        </div>

        <div>
          <label htmlFor="email" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Email <span className="text-red-500">*</span>
          </label>
          <input
            id="email"
            name="email"
            type="email"
            required
            value={formData.email}
            onChange={handleChange}
            className="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
            placeholder="nama@email.com"
          />
        </div>

        {/* Vehicle Info */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label htmlFor="car_model" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Tipe Mobil <span className="text-red-500">*</span>
            </label>
            <select
              id="car_model"
              name="car_model"
              required
              value={formData.car_model}
              onChange={handleChange}
              className="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
            >
              <option value="">Pilih tipe mobil</option>
              {LAND_ROVER_MODELS.map((model) => (
                <option key={model} value={model}>{model}</option>
              ))}
            </select>
          </div>

          <div>
            <label htmlFor="vehicle_info" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Info Kendaraan <span className="text-gray-500">(opsional)</span>
            </label>
            <input
              id="vehicle_info"
              name="vehicle_info"
              type="text"
              value={formData.vehicle_info}
              onChange={handleChange}
              className="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
              placeholder="Warna, Tahun, No. Polisi"
            />
          </div>
        </div>

        {/* Service Type */}
        <div>
          <label htmlFor="service_type" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Tipe Layanan <span className="text-red-500">*</span>
          </label>
          <select
            id="service_type"
            name="service_type"
            required
            value={formData.service_type}
            onChange={handleChange}
            className="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
          >
            <option value="">Pilih tipe layanan</option>
            {SERVICE_TYPES.map((type) => (
              <option key={type} value={type}>{type}</option>
            ))}
          </select>
        </div>

        {/* Date */}
        <div>
          <label htmlFor="date" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Tanggal <span className="text-red-500">*</span>
          </label>
          <input
            id="date"
            name="date"
            type="date"
            required
            min={getTodayDate()}
            value={formData.date}
            onChange={handleChange}
            className="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
          />
        </div>

        {/* Notes */}
        <div>
          <label htmlFor="notes" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Detail / Catatan <span className="text-gray-500">(opsional)</span>
          </label>
          <textarea
            id="notes"
            name="notes"
            rows={4}
            value={formData.notes}
            onChange={handleChange}
            className="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all resize-none"
            placeholder="Deskripsikan keluhan atau permintaan khusus..."
          />
        </div>

        {/* Submit */}
        <button
          type="submit"
          disabled={isLoading}
          className="w-full btn-glow disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {isLoading ? (
            <span className="flex items-center justify-center gap-2">
              <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
              Memproses...
            </span>
          ) : (
            <span>Buat Booking</span>
          )}
          <span className="btn-icon-circle">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </span>
        </button>
      </form>
    </div>
  );
}
