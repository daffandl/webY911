'use client';

import { useState } from 'react';
import Link from 'next/link';
import InvoiceSection from './InvoiceSection';

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
  preferred_date?: string;
}

const clipStyle = {
  clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))',
};

const inputClass =
  'w-full px-4 py-3 border border-[#86efac] dark:border-[#1a2e1a] bg-[#bbf7d0] dark:bg-[#0a0f0a] text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-transparent transition-all';

// ─── Inline SVG icons ─────────────────────────────────────────────────────────
const IcoCheck = () => (
  <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
  </svg>
);
const IcoSearch = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
  </svg>
);
const IcoCalendar = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
  </svg>
);
const IcoArrowLeft = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
  </svg>
);
const IcoArrowRight = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
  </svg>
);
const IcoPlus = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
  </svg>
);
const IcoClock = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>
);
const IcoWarning = () => (
  <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
  </svg>
);
const IcoSpinner = () => (
  <svg className="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
  </svg>
);
const IcoPhone = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
  </svg>
);
const IcoClockStatus = () => (
  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>
);

// ─────────────────────────────────────────────────────────────────────────────
export default function Booking() {
  const [step, setStep] = useState<'form' | 'success'>('form');
  const [result, setResult] = useState<BookingResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [customService, setCustomService] = useState('');
  const [showCustomService, setShowCustomService] = useState(false);

  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    email: '',
    car_model: '',
    service_type: '',
    date: '',
    notes: '',
  });

  const API_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api';
  const today = new Date().toISOString().split('T')[0];

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>
  ) => {
    const { name, value } = e.target;
    if (name === 'service_type') {
      const isCustom = value === 'Lainnya (Custom)';
      setShowCustomService(isCustom);
      if (!isCustom) setCustomService('');
    }
    setFormData((prev) => ({ ...prev, [name]: value }));
    setError(null);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    const serviceType =
      showCustomService && customService.trim() ? customService.trim() : formData.service_type;

    try {
      const res = await fetch(`${API_URL}/bookings`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({
          name: formData.name,
          phone: formData.phone,
          email: formData.email || `${formData.phone.replace(/\D/g, '')}@noemail.local`,
          car_model: formData.car_model,
          service_type: serviceType,
          date: formData.date,
          notes: formData.notes || undefined,
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

  // ── Success state ─────────────────────────────────────────────────────────────
  if (step === 'success' && result) {
    const trackUrl = `/tracking?code=${result.booking_code}`;

    return (
      <section id="booking" className="py-20 bg-[#dcfce7] dark:bg-[#0a0f0a] border-b border-[#d1fae5] dark:border-[#1a2e1a]">
        <div className="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="card-clip card-clip-light p-10 text-center">

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
              Booking Anda telah diterima. Notifikasi WhatsApp telah dikirim ke nomor Anda.
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

            {/* Status */}
            <div className="flex items-center justify-center gap-2 mb-6">
              <span className="text-yellow-600"><IcoClockStatus /></span>
              <span className="font-semibold text-yellow-600 dark:text-yellow-400">
                Menunggu Konfirmasi
              </span>
            </div>

            {/* Summary */}
            <div
              className="text-left bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a] p-5 mb-8"
              style={{ clipPath: 'polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 10px 100%, 0 calc(100% - 10px))' }}
            >
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
            {result && (
              <InvoiceSection bookingCode={result.booking_code} />
            )}

            {/* Buttons — icon selalu di kanan */}
            <div className="flex flex-col sm:flex-row gap-3">
              <Link href={trackUrl} className="flex-1 btn-green py-3 text-sm font-semibold">
                <span>Lacak Status Booking</span>
                <span className="btn-icon-circle ml-auto"><IcoSearch /></span>
              </Link>
              <button
                onClick={() => {
                  setStep('form');
                  setResult(null);
                  setFormData({ name: '', phone: '', email: '', car_model: '', service_type: '', date: '', notes: '' });
                }}
                className="flex-1 btn-outline py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center justify-between px-5"
                style={clipStyle}
              >
                <span className="text-green-600 dark:text-green-400"><IcoPlus /></span>
                <span>Booking Lagi</span>
              </button>
            </div>
          </div>
        </div>
      </section>
    );
  }

  // ── Form state ────────────────────────────────────────────────────────────────
  return (
    <section id="booking" className="py-20 bg-[#dcfce7] dark:bg-[#0a0f0a] border-b border-[#d1fae5] dark:border-[#1a2e1a]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid md:grid-cols-2 gap-12 items-start">

          {/* Content */}
          <div className="order-2 md:order-1">
            <div className="inline-block bg-[#166534] text-white px-6 py-2 text-sm font-semibold mb-4" style={clipStyle}>
              Book Now
            </div>
            <h2 className="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-6">
              Get Your Vehicle the Care It Deserves
            </h2>
            <p className="text-gray-600 dark:text-gray-300 text-lg mb-8">
              Fill out the form and our expert technicians will contact you via WhatsApp to confirm your appointment.
            </p>

            <div className="space-y-4 mb-8">
              {[
                'Certified Land Rover Specialists',
                'Genuine OEM Parts',
                'State-of-the-art Diagnostics',
                'Competitive Pricing',
                'Warranty on All Services',
              ].map((feature) => (
                <div key={feature} className="flex items-center space-x-3">
                  <div
                    className="bg-[#166534] p-1.5 flex-shrink-0"
                    style={{ clipPath: 'polygon(0 0, calc(100% - 5px) 0, 100% 5px, 100% 100%, 5px 100%, 0 calc(100% - 5px))' }}
                  >
                    <IcoCheck />
                  </div>
                  <span className="text-gray-700 dark:text-gray-300">{feature}</span>
                </div>
              ))}
            </div>

            {/* Track link — icon kanan */}
            <Link href="/tracking" className="btn-green inline-flex py-3 px-6 text-sm">
              <span>Lacak Booking Anda</span>
              <span className="btn-icon-circle ml-auto"><IcoSearch /></span>
            </Link>
          </div>

          {/* Form */}
          <div className="order-1 md:order-2 card-clip card-clip-light p-8">
            {error && (
              <div
                className="mb-5 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-500 dark:text-red-300 text-sm flex items-start gap-2"
                style={clipStyle}
              >
                <IcoWarning />
                <span>{error}</span>
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-5">

              {/* Nama */}
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Nama Lengkap <span className="text-red-500">*</span>
                </label>
                <input type="text" name="name" value={formData.name} onChange={handleChange}
                  required placeholder="Contoh: Budi Santoso"
                  className={inputClass} style={clipStyle} />
              </div>

              {/* Phone + Email */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    No. WhatsApp <span className="text-red-500">*</span>
                  </label>
                  <input type="tel" name="phone" value={formData.phone} onChange={handleChange}
                    required placeholder="08123456789"
                    className={inputClass} style={clipStyle} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email{' '}
                    <span className="text-gray-400 font-normal text-xs">(opsional)</span>
                  </label>
                  <input type="email" name="email" value={formData.email} onChange={handleChange}
                    placeholder="email@contoh.com"
                    className={inputClass} style={clipStyle} />
                </div>
              </div>

              {/* Tipe Mobil */}
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Tipe Mobil <span className="text-red-500">*</span>
                </label>
                <select name="car_model" value={formData.car_model} onChange={handleChange}
                  required className={inputClass} style={clipStyle}>
                  <option value="">— Pilih tipe Land Rover —</option>
                  {LAND_ROVER_MODELS.map((m) => (
                    <option key={m} value={m}>{m}</option>
                  ))}
                </select>
              </div>

              {/* Tipe Layanan */}
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Tipe Layanan <span className="text-red-500">*</span>
                </label>
                <select name="service_type" value={formData.service_type} onChange={handleChange}
                  required className={inputClass} style={clipStyle}>
                  <option value="">— Pilih tipe layanan —</option>
                  {SERVICE_TYPES.map((s) => (
                    <option key={s} value={s}>{s}</option>
                  ))}
                </select>
                {showCustomService && (
                  <input
                    type="text" value={customService}
                    onChange={(e) => setCustomService(e.target.value)}
                    required placeholder="Deskripsikan layanan yang Anda butuhkan..."
                    className={`${inputClass} mt-2`} style={clipStyle}
                  />
                )}
              </div>

              {/* Tanggal */}
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Tanggal yang Diinginkan <span className="text-red-500">*</span>
                </label>
                <input type="date" name="date" value={formData.date} onChange={handleChange}
                  required min={today}
                  className={inputClass} style={clipStyle} />
              </div>

              {/* Catatan */}
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Detail / Catatan{' '}
                  <span className="text-gray-400 font-normal text-xs">(opsional)</span>
                </label>
                <textarea name="notes" value={formData.notes} onChange={handleChange} rows={3}
                  placeholder="Ceritakan masalah atau kebutuhan kendaraan Anda..."
                  className={`${inputClass} resize-none`} style={clipStyle} />
              </div>

              {/* Submit — icon selalu di kanan */}
              <button
                type="submit"
                disabled={loading}
                className="w-full btn-glow py-4 text-lg disabled:opacity-60 disabled:cursor-not-allowed"
              >
                {loading ? (
                  <>
                    <span>Memproses...</span>
                    <span className="btn-icon-circle ml-auto"><IcoSpinner /></span>
                  </>
                ) : (
                  <>
                    <span>Book Appointment</span>
                    <span className="btn-icon-circle ml-auto"><IcoCalendar /></span>
                  </>
                )}
              </button>

              <p className="text-xs text-gray-500 dark:text-gray-400 text-center flex items-center justify-center gap-1.5">
                <span className="text-green-600 dark:text-green-400"><IcoClock /></span>
                Konfirmasi booking via WhatsApp dalam 1×24 jam kerja
              </p>
            </form>
          </div>
        </div>
      </div>
    </section>
  );
}
