'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';

const heroImages = [
  'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1920&q=80',
  'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1920&q=80',
  'https://images.unsplash.com/photo-1583121274602-3e2820c69888?w=1920&q=80',
  'https://images.unsplash.com/photo-1502877338535-766e1452684a?w=1920&q=80',
];

/* All Land Rover model names */
const landroverModels = [
  'Range Rover',
  'Range Rover Sport',
  'Range Rover Velar',
  'Range Rover Evoque',
  'Defender',
  'Discovery',
  'Discovery Sport',
  'Freelander',
  'Series I',
  'Series II',
  'Series III',
  'Defender 90',
  'Defender 110',
  'Defender 130',
];

/* Duplicate for seamless infinite scroll */
const tickerItems = [...landroverModels, ...landroverModels];

export default function Hero() {
  const [currentImageIndex, setCurrentImageIndex] = useState(0);

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentImageIndex((prev) => (prev + 1) % heroImages.length);
    }, 5000);
    return () => clearInterval(interval);
  }, []);

  return (
    <section className="relative h-screen min-h-[560px] overflow-hidden">
      {/* Background Images */}
      {heroImages.map((src, index) => (
        <div
          key={src}
          className={`absolute inset-0 transition-opacity duration-1000 ${
            index === currentImageIndex ? 'opacity-100' : 'opacity-0'
          }`}
        >
          <div className="absolute inset-0 bg-cover bg-center" style={{ backgroundImage: `url(${src})` }} />
          <div className="absolute inset-0 bg-black/55" />
        </div>
      ))}

      {/* Main Content — vertically centered, centered text */}
      <div className="relative z-10 flex flex-col items-center justify-center h-full text-center px-4 sm:px-8 -mt-10">

        {/* Logo — di tengah, tepat di atas title */}
        <div className="mb-5">
          <img
            src="/hero-logo.png"
            alt="Young 911 Logo"
            className="h-16 sm:h-20 lg:h-24 w-auto object-contain mx-auto"
            onError={(e) => {
              (e.currentTarget as HTMLImageElement).style.display = 'none';
            }}
          />
        </div>

        {/* Title */}
        <h1
          className="text-white tracking-tight leading-tight mb-4"
          style={{ fontSize: 'clamp(2.25rem, 7.5vw, 5.5rem)' }}
        >
          <span className="font-bold">Land Rover Specialist.</span>{' '}
          <span className="font-light">Car Service &amp; Repair</span>
        </h1>

        {/* Subtitle */}
        <p
          className="text-white/80 font-light mb-8 max-w-2xl"
          style={{ fontSize: 'clamp(1rem, 2.5vw, 1.375rem)' }}
        >
          Expert care for your luxury vehicle — certified technicians,
          genuine OEM parts &amp; state-of-the-art diagnostics.
        </p>

        {/* CTA Buttons */}
        <div className="flex flex-row gap-3 sm:gap-4 items-center">
          {/* Orange — Booking */}
          <Link href="/booking" className="btn-glow">
            <span>Booking</span>
            <span className="btn-icon-circle">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </span>
          </Link>

          {/* Green — Track Booking */}
          <Link href="/tracking" className="btn-green">
            <span>Track Booking</span>
            <span className="btn-icon-circle btn-icon-circle--green">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </span>
          </Link>
        </div>
      </div>

      {/* ── Land Rover Models Ticker — absolute bottom, full width ── */}
      <div className="absolute bottom-0 left-0 right-0 z-20 bg-black/20">
        {/* Top border line */}
        <div className="w-full h-px bg-white/30" />

        {/* Scrolling ticker */}
        <div className="overflow-hidden w-full">
          <div className="ticker-track">
            {tickerItems.map((model, i) => (
              <span
                key={i}
                className="ticker-item inline-flex items-center px-5 py-2.5 text-white/80 text-xs sm:text-sm font-medium tracking-widest whitespace-nowrap"
                style={{ borderRight: '1px solid rgba(255,255,255,0.30)' }}
              >
                {model}
              </span>
            ))}
          </div>
        </div>

        {/* Bottom border line */}
        <div className="w-full h-px bg-white/30" />
      </div>
    </section>
  );
}
