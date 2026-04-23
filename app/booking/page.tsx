'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import InvoiceSection from '../components/InvoiceSection';
import { useAuth } from '../components/AuthProvider';

// ─── Land Rover models (oldest → newest) ────────────────────────────────────
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
  'Land Rover Defender 90 (L663, 2020)',
  'Land Rover Defender 110 (L663, 2020)',
  'Land Rover Defender 130 (2022)',
  'Range Rover (5th Gen L460, 2022)',
  'Range Rover Sport (3rd Gen L461, 2022)',
  'Range Rover Evoque (2nd Gen, 2019)',
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
  'Perbaikan Bodi (Body Repair)',
  'Ban & Velg (Tire & Wheel)',
  'Lainnya (Custom)',
];

interface BookingResult {
  booking_code: string;
  status: string;
  status_label: string;
  name: string;
  car_model: string;
  service_type: string;
  preferred_date: string;
}

type FormStep = 'form' | 'success';

const clipStyle = {
  clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))',
};

const inputClass =
  'w-full px-4 py-3 border border-[#86efac] dark:border-[#1a2e1a] bg-[#bbf7d0] dark:bg-[#0a0f0a] text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-transparent transition-all text-sm';

// ─── Reusable SVG icons ───────────────────────────────────────────────────────
const Icons = {
  wrench: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
  ),
  search: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>
  ),
  clipboard: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
    </svg>
  ),
  calendar: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
  check: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
    </svg>
  ),
  phone: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
    </svg>
  ),
  plus: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
    </svg>
  ),
  clock: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  ),
  warning: (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>
  ),
  spinner: (
    <svg className="animate-spin w-5 h-5" viewBox="0 0 24 24" fill="none">
      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
    </svg>
  ),
};

// ─────────────────────────────────────────────────────────────────────────────
//  Main Page
// ─────────────────────────────────────────────────────────────────────────────
export default function BookingPage() {
  const router = useRouter();
  const { isAuthenticated, isLoading, user, token, logout } = useAuth();
  const [step, setStep] = useState<FormStep>('form');
  const [result, setResult] = useState<BookingResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [customService, setCustomService] = useState('');
  const [showCustomService, setShowCustomService] = useState(false);

  const [form, setForm] = useState({
    name: '',
    phone: '',
    email: '',
    car_model: '',
    service_type: '',
    vehicle_info: '',
    date: '',
    notes: '',
  });

  const API_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api';

  // Prefill form with user data when authenticated
  useEffect(() => {
    if (user && !form.name && !form.email && !form.phone) {
      setForm(prev => ({
        ...prev,
        name: user.name || '',
        email: user.email || '',
        phone: user.phone || '',
      }));
    }
  }, [user]);

  // Redirect to login if not authenticated
  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      router.push('/login?redirect=/dashboard/booking');
    }
  }, [isAuthenticated, isLoading, router]);

  // Show loading while checking auth
  if (isLoading) {
    return (
      <>
        <Navbar />
        <main className="min-h-screen bg-[#dcfce7] dark:bg-[#0a0f0a] pt-24 pb-20 flex items-center justify-center">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-12 w-12 border-4 border-green-600 border-t-transparent"></div>
            <p className="mt-4 text-gray-600 dark:text-gray-400">Memeriksa autentikasi...</p>
          </div>
        </main>
        <Footer />
      </>
    );
  }

  // Don't render if not authenticated (will redirect)
  if (!isAuthenticated) {
    return null;
  }

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>
  ) => {
    const { name, value } = e.target;
    if (name === 'service_type') {
      const isCustom = value === 'Lainnya (Custom)';
      setShowCustomService(isCustom);
      if (!isCustom) setCustomService('');
    }
    setForm((prev) => ({ ...prev, [name]: value }));
    setError(null);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    const serviceType =
      showCustomService && customService.trim() ? customService.trim() : form.service_type;

    try {
      const headers: Record<string, string> = { 
        'Content-Type': 'application/json', 
        Accept: 'application/json' 
      };
      
      // Add auth token if available
      if (token) {
        headers.Authorization = `Bearer ${token}`;
      }

      const res = await fetch(`${API_URL}/bookings`, {
        method: 'POST',
        headers,
        body: JSON.stringify({
          name: form.name,
          phone: form.phone,
          email: form.email,
          car_model: form.car_model,
          vehicle_info: form.vehicle_info || undefined,
          service_type: serviceType,
          date: form.date,
          notes: form.notes || undefined,
        }),
      });

      const json = await res.json();

      if (!res.ok) {
        const msg =
          json?.message ||
          (json?.errors ? Object.values(json.errors).flat().join(' ') : null) ||
          'Terjadi kesalahan. Silakan coba lagi.';
        setError(msg);
        return;
      }

      setResult(json.data);
      setStep('success');
    } catch {
      setError('Tidak dapat terhubung ke server. Periksa koneksi Anda.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <Navbar />
      <main className="min-h-screen bg-[#dcfce7] dark:bg-[#0a0f0a] pt-24 pb-20">
        <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

          {/* Header */}
          <div className="text-center mb-12">
            <div className="inline-block bg-[#166534] text-white px-6 py-2 text-sm font-semibold mb-4" style={clipStyle}>
              Book Service
            </div>
            <h1 className="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
              Booking Servis
            </h1>
            <p className="text-gray-600 dark:text-gray-300 text-lg max-w-2xl mx-auto">
              {user 
                ? `Halo ${user.name}! Silakan isi form di bawah ini. Tim kami akan menghubungi Anda via WhatsApp untuk konfirmasi jadwal.`
                : 'Isi form di bawah ini. Tim kami akan menghubungi Anda via WhatsApp untuk konfirmasi jadwal.'
              }
            </p>
            {user && (
              <div className="mt-4 flex items-center justify-center gap-2 text-sm text-green-600 dark:text-green-400">
                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Login sebagai <strong>{user.name}</strong></span>
                <button
                  onClick={async () => {
                    await logout();
                    router.push('/login?redirect=/dashboard/booking');
                  }}
                  className="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 underline"
                >
                  (Logout)
                </button>
              </div>
            )}
          </div>

          {step === 'form' ? (
            <BookingForm
              form={form}
              loading={loading}
              error={error}
              customService={customService}
              showCustomService={showCustomService}
              onCustomServiceChange={setCustomService}
              onChange={handleChange}
              onSubmit={handleSubmit}
            />
          ) : (
            <SuccessState result={result!} />
          )}
        </div>
      </main>
      <Footer />
    </>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
//  Booking Form
// ─────────────────────────────────────────────────────────────────────────────
function BookingForm({
  form,
  loading,
  error,
  customService,
  showCustomService,
  onCustomServiceChange,
  onChange,
  onSubmit,
}: {
  form: Record<string, string>;
  loading: boolean;
  error: string | null;
  customService: string;
  showCustomService: boolean;
  onCustomServiceChange: (v: string) => void;
  onChange: (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => void;
  onSubmit: (e: React.FormEvent) => void;
}) {
  const today = new Date().toISOString().split('T')[0];

  return (
    <div className="grid md:grid-cols-5 gap-10 items-start">

      {/* ── Left panel ── */}
      <div className="md:col-span-2">
        <div className="card-clip card-clip-light p-6">
          <h3 className="font-bold text-gray-900 dark:text-white text-lg mb-3">
            Sudah punya kode booking?
          </h3>
          <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Lacak status booking Anda secara real-time.
          </p>
          <Link href="/tracking" className="btn-green w-full py-3 text-sm">
            <span>Lacak Booking</span>
            <span className="btn-icon-circle ml-auto">{Icons.search}</span>
          </Link>
        </div>
      </div>

      {/* ── Right: Form ── */}
      <div className="md:col-span-3 card-clip card-clip-light p-8">
        {error && (
          <div
            className="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-500 dark:text-red-300 text-sm flex items-start gap-2"
            style={clipStyle}
          >
            <span className="flex-shrink-0 mt-0.5">{Icons.warning}</span>
            <span>{error}</span>
          </div>
        )}

        <form onSubmit={onSubmit} className="space-y-5">

          {/* Nama */}
          <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
              Nama Lengkap <span className="text-red-500">*</span>
            </label>
            <input
              type="text" name="name" value={form.name} onChange={onChange}
              required placeholder="Contoh: Budi Santoso"
              className={inputClass} style={clipStyle}
            />
          </div>

          {/* No WhatsApp */}
          <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
              No. WhatsApp <span className="text-red-500">*</span>
            </label>
            <input
              type="tel" name="phone" value={form.phone} onChange={onChange}
              required placeholder="Contoh: 08123456789"
              className={inputClass} style={clipStyle}
            />
            <p className="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
              <span className="text-green-600 dark:text-green-400">{Icons.phone}</span>
              Konfirmasi booking akan dikirim ke nomor ini.
            </p>
          </div>

          {/* Email */}
          <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
              Email <span className="text-red-500">*</span>
            </label>
            <input
              type="email" name="email" value={form.email} onChange={onChange}
              required placeholder="Contoh: budi@email.com"
              className={inputClass} style={clipStyle}
            />
            <p className="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
              <span className="text-green-600 dark:text-green-400">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </span>
              Konfirmasi &amp; update status booking akan dikirim ke email ini.
            </p>
          </div>

          {/* Tipe Mobil */}
          <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
              Tipe Mobil <span className="text-red-500">*</span>
            </label>
            <select
              name="car_model" value={form.car_model} onChange={onChange}
              required className={inputClass} style={clipStyle}
            >
              <option value="">— Pilih tipe Land Rover —</option>
              {LAND_ROVER_MODELS.map((m) => (
                <option key={m} value={m}>{m}</option>
              ))}
            </select>
          </div>

          {/* Info Kendaraan */}
          <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
              Info Kendaraan{' '}
              <span className="text-gray-400 font-normal">(opsional)</span>
            </label>
            <input
              type="text" name="vehicle_info" value={form.vehicle_info} onChange={onChange}
              placeholder="Contoh: Warna putih, plat B 1234 XY, tahun 2021"
              className={inputClass} style={clipStyle}
            />
          </div>

          {/* Tipe Layanan */}
          <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
              Tipe Layanan <span className="text-red-500">*</span>
            </label>
            <select
              name="service_type" value={form.service_type} onChange={onChange}
              required className={inputClass} style={clipStyle}
            >
              <option value="">— Pilih tipe layanan —</option>
              {SERVICE_TYPES.map((s) => (
                <option key={s} value={s}>{s}</option>
              ))}
            </select>
            {showCustomService && (
              <input
                type="text"
                value={customService}
                onChange={(e) => onCustomServiceChange(e.target.value)}
                required
                placeholder="Deskripsikan layanan yang Anda butuhkan..."
                className={`${inputClass} mt-2`}
                style={clipStyle}
              />
            )}
          </div>

          {/* Tanggal */}
          <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
              Tanggal yang Diinginkan <span className="text-red-500">*</span>
            </label>
            <input
              type="date" name="date" value={form.date} onChange={onChange}
              required min={today}
              className={inputClass} style={clipStyle}
            />
          </div>

          {/* Catatan */}
          <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
              Detail / Catatan{' '}
              <span className="text-gray-400 font-normal">(opsional)</span>
            </label>
            <textarea
              name="notes" value={form.notes} onChange={onChange}
              rows={3} placeholder="Ceritakan masalah atau kebutuhan kendaraan Anda..."
              className={`${inputClass} resize-none`} style={clipStyle}
            />
          </div>

          {/* Submit — icon kanan */}
          <button
            type="submit"
            disabled={loading}
            className="w-full btn-glow py-4 text-base font-bold disabled:opacity-60 disabled:cursor-not-allowed"
          >
            {loading ? (
              <>
                <span>Memproses...</span>
                <span className="btn-icon-circle ml-auto">{Icons.spinner}</span>
              </>
            ) : (
              <>
                <span>Kirim Booking</span>
                <span className="btn-icon-circle ml-auto">{Icons.calendar}</span>
              </>
            )}
          </button>

          <p className="text-xs text-gray-500 dark:text-gray-400 text-center flex items-center justify-center gap-1">
            <span className="text-green-600 dark:text-green-400">{Icons.clock}</span>
            Kami akan konfirmasi via WhatsApp dalam 1×24 jam kerja.
          </p>
        </form>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
//  Success State
// ─────────────────────────────────────────────────────────────────────────────
function SuccessState({ result }: { result: BookingResult }) {
  const trackUrl = `/tracking?code=${result.booking_code}`;

  const statusConfig: Record<string, { label: string; color: string; icon: React.ReactNode }> = {
    pending: {
      label: 'Menunggu Konfirmasi',
      color: 'text-yellow-600 dark:text-yellow-400',
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      ),
    },
    confirmed: {
      label: 'Dikonfirmasi',
      color: 'text-green-600 dark:text-green-400',
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      ),
    },
    rejected: {
      label: 'Ditolak',
      color: 'text-red-600 dark:text-red-400',
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      ),
    },
    in_progress: {
      label: 'Sedang Dikerjakan',
      color: 'text-blue-600 dark:text-blue-400',
      icon: Icons.wrench,
    },
    completed: {
      label: 'Selesai',
      color: 'text-green-600 dark:text-green-400',
      icon: Icons.check,
    },
    cancelled: {
      label: 'Dibatalkan',
      color: 'text-gray-500 dark:text-gray-400',
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
        </svg>
      ),
    },
  };

  const sc = statusConfig[result.status] ?? {
    label: result.status_label,
    color: 'text-gray-500',
    icon: Icons.clipboard,
  };

  return (
    <div className="max-w-2xl mx-auto">
      <div className="card-clip card-clip-light p-10 text-center mb-8">

        {/* Success icon */}
        <div
          className="w-20 h-20 bg-[#166534] flex items-center justify-center mx-auto mb-6"
          style={{ clipPath: 'polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 12px 100%, 0 calc(100% - 12px))' }}
        >
          <svg className="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" />
          </svg>
        </div>

        <h2 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
          Booking Berhasil!
        </h2>
        <p className="text-gray-600 dark:text-gray-300 mb-8">
          Booking Anda telah diterima. Notifikasi WhatsApp &amp; Email telah dikirim ke nomor dan email Anda.
        </p>

        {/* Booking code */}
        <div
          className="bg-[#166534] text-white p-6 mb-6"
          style={{ clipPath: 'polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 12px 100%, 0 calc(100% - 12px))' }}
        >
          <p className="text-green-200 text-sm font-medium mb-1">Kode Booking Anda</p>
          <p className="text-3xl font-black tracking-widest">{result.booking_code}</p>
          <p className="text-green-200 text-xs mt-2">Simpan kode ini untuk melacak status booking</p>
        </div>

        {/* Status badge */}
        <div className="flex items-center justify-center gap-2 mb-6">
          <span className={sc.color}>{sc.icon}</span>
          <span className={`font-semibold text-lg ${sc.color}`}>{sc.label}</span>
        </div>

        {/* Booking details */}
        <div
          className="text-left bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-5 mb-8"
          style={{ clipPath: 'polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 10px 100%, 0 calc(100% - 10px))' }}
        >
          <h4 className="font-semibold text-gray-900 dark:text-white mb-3 text-sm uppercase tracking-wide">
            Detail Booking
          </h4>
          <div className="space-y-2 text-sm">
            {[
              { label: 'Nama', value: result.name },
              { label: 'Tipe Mobil', value: result.car_model },
              { label: 'Layanan', value: result.service_type },
              result.preferred_date
                ? {
                    label: 'Tanggal',
                    value: new Date(result.preferred_date).toLocaleDateString('id-ID', {
                      day: 'numeric', month: 'long', year: 'numeric',
                    }),
                  }
                : null,
            ]
              .filter(Boolean)
              .map((item) => (
                <div key={item!.label} className="flex justify-between">
                  <span className="text-gray-500 dark:text-gray-400">{item!.label}</span>
                  <span className="font-medium text-gray-900 dark:text-white">{item!.value}</span>
                </div>
              ))}
          </div>
        </div>

        {/* Invoice Section */}
        <InvoiceSection bookingCode={result.booking_code} />

        {/* Action buttons — icon selalu di kanan */}
        <div className="flex flex-col sm:flex-row gap-3">
          <Link href={trackUrl} className="flex-1 btn-green py-3 text-sm font-semibold">
            <span>Lacak Status Booking</span>
            <span className="btn-icon-circle ml-auto">{Icons.search}</span>
          </Link>
          <Link
            href="/"
            className="flex-1 btn-outline py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center justify-between px-5"
            style={clipStyle}
          >
            <span className="text-green-600 dark:text-green-400">{Icons.arrowLeft}</span>
            <span>Kembali ke Beranda</span>
          </Link>
        </div>
      </div>

      {/* Info note */}
      <div className="text-center text-sm text-gray-500 dark:text-gray-400 space-y-1">
        <p className="flex items-center justify-center gap-1.5">
          <span className="text-green-600 dark:text-green-400">{Icons.phone}</span>
          Konfirmasi akan dikirim via WhatsApp dalam 1×24 jam kerja.
        </p>
        <p>
          Pertanyaan?{' '}
          <a
            href="https://wa.me/6281234567890"
            target="_blank"
            rel="noopener noreferrer"
            className="text-green-600 dark:text-green-400 font-medium hover:underline"
          >
            Hubungi kami via WhatsApp
          </a>
        </p>
      </div>
    </div>
  );
}
