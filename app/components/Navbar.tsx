'use client';

import { useState, useEffect, useContext } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { ThemeContext } from './ThemeProvider';
import { useAuth } from './AuthProvider';

export default function Navbar() {
  const { user, isAuthenticated } = useAuth();
  const [isScrolled, setIsScrolled] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const themeContext = useContext(ThemeContext);
  const theme = themeContext?.theme || 'light';
  const toggleTheme = themeContext?.toggleTheme || (() => {});

  useEffect(() => {
    const handleScroll = () => setIsScrolled(window.scrollY > 50);
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const navLinks = [
    { href: '#about',    label: 'About'    },
    { href: '#services', label: 'Services' },
    { href: '#gallery',  label: 'Gallery'  },
    { href: '#faq',      label: 'FAQ'      },
    { href: '#blog',     label: 'Blog'     },
    { href: '#contact',  label: 'Contact'  },
  ];

  const isDark = theme === 'dark';

  /* ── Detect sub-pages (booking / tracking) ──
     On these pages the background is light green (#dcfce7), not a dark hero image.
     So in light mode we always show the solid navbar — even before scrolling. */
  const pathname = usePathname();
  const isSubPage = pathname === '/booking' || pathname === '/tracking';
  const solidNav = isScrolled || (isSubPage && !isDark);

  /* ── Background ── */
  const navBg = solidNav
    ? isDark ? 'bg-[#0a0f0a]' : 'bg-[#dcfce7]'
    : 'bg-transparent';

  /* ── Bottom border — matches divider style per scroll state ── */
  const navBorder = solidNav
    ? isDark ? 'border-b border-[#1a2e1a]' : 'border-b border-[#86efac]'
    : 'border-b border-white/20';

  /* ── Vertical divider colour ── */
  const divider = solidNav
    ? isDark ? 'border-[#1a2e1a]' : 'border-[#86efac]'
    : 'border-white/25';

  /* ── Text colours ── */
  const logoColor   = solidNav ? (isDark ? 'text-white' : 'text-gray-900') : 'text-white';
  const linkColor   = solidNav ? (isDark ? 'text-gray-300 hover:text-white' : 'text-gray-700 hover:text-gray-900') : 'text-white/90 hover:text-white';
  const actionColor = solidNav ? (isDark ? 'text-gray-300 hover:text-white' : 'text-gray-700 hover:text-gray-900') : 'text-white/90 hover:text-white';

  return (
    <>
      <nav className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${navBg} ${navBorder}`}>
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

          {/* ══════════════════════════════════════════
              DESKTOP  (md+)
              [Logo] | [Nav links] | [Theme switch] | [Booking text]
          ══════════════════════════════════════════ */}
          <div className="hidden md:flex items-center h-16">

            {/* Logo — left */}
            <Link href="/" className="flex items-center flex-shrink-0 pr-6">
              <span className={`text-xl font-bold transition-colors duration-300 ${logoColor}`}>
                Young 911<span className="text-green-700">.</span>
              </span>
            </Link>

            {/* Nav links — center, flex-1 */}
            <div className={`flex items-center flex-1 justify-center space-x-6 border-l border-r px-6 h-full ${divider}`}>
              {navLinks.map((link) => (
                <Link
                  key={link.href}
                  href={link.href}
                  className={`text-sm font-medium transition-colors duration-200 whitespace-nowrap ${linkColor}`}
                >
                  {link.label}
                </Link>
              ))}
            </div>

            {/* Theme switch — right side, with right divider */}
            <div className={`flex items-center h-full border-r px-5 ${divider}`}>
              <button
                onClick={toggleTheme}
                aria-label="Toggle theme"
                className={`flex items-center gap-2 text-sm font-medium transition-colors duration-200 ${actionColor}`}
              >
                {isDark ? (
                  /* Sun icon — switch to light */
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                      d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                ) : (
                  /* Moon icon — switch to dark */
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                      d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                  </svg>
                )}
                {/* Toggle pill */}
                <span className={`relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 ${isDark ? 'bg-green-700' : 'bg-gray-300'}`}>
                  <span className={`inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 ${isDark ? 'translate-x-4' : 'translate-x-0.5'}`} />
                </span>
              </button>
            </div>

            {/* Booking + Track / Dashboard — right */}
            <div className="flex items-center h-full pl-5 gap-4">
              {isAuthenticated ? (
                <>
                  <Link
                    href="/dashboard"
                    className={`text-sm font-semibold tracking-wide whitespace-nowrap transition-colors duration-200 ${actionColor}`}
                  >
                    Dashboard
                  </Link>
                  <Link
                    href="/dashboard/booking"
                    className={`text-sm font-medium tracking-wide whitespace-nowrap transition-colors duration-200 ${actionColor}`}
                  >
                    Booking
                  </Link>
                </>
              ) : (
                <>
                  <Link
                    href="/login"
                    className={`text-sm font-semibold tracking-wide whitespace-nowrap transition-colors duration-200 ${actionColor}`}
                  >
                    Login
                  </Link>
                  <Link
                    href="/booking"
                    className={`text-sm font-medium tracking-wide whitespace-nowrap transition-colors duration-200 ${actionColor}`}
                  >
                    Booking
                  </Link>
                </>
              )}
            </div>

          </div>

          {/* ══════════════════════════════════════════
              MOBILE  (below md)
              True 3-col grid: [Dashboard/Booking] | [Young 911] | [Menu]
              Logo is perfectly centred via grid
          ══════════════════════════════════════════ */}
          <div className="md:hidden grid h-16" style={{ gridTemplateColumns: '1fr auto 1fr' }}>

            {/* LEFT: Dashboard/Booking text */}
            <div className={`flex items-center justify-center border-r h-full ${divider}`}>
              {isAuthenticated ? (
                <Link
                  href="/dashboard"
                  className={`text-sm font-semibold tracking-wide whitespace-nowrap transition-colors duration-200 ${actionColor}`}
                >
                  Dashboard
                </Link>
              ) : (
                <Link
                  href="/login"
                  className={`text-sm font-semibold tracking-wide whitespace-nowrap transition-colors duration-200 ${actionColor}`}
                >
                  Login
                </Link>
              )}
            </div>

            {/* CENTER: Logo — auto width, truly centred */}
            <div className="flex items-center justify-center h-full px-5">
              <Link href="/" className="flex items-center">
                <span className={`text-xl font-bold transition-colors duration-300 ${logoColor}`}>
                  Young 911<span className="text-green-700">.</span>
                </span>
              </Link>
            </div>

            {/* RIGHT: Menu text */}
            <div className={`flex items-center justify-center border-l h-full ${divider}`}>
              <button
                onClick={() => setIsMobileMenuOpen(true)}
                className={`text-sm font-semibold tracking-wide whitespace-nowrap transition-colors duration-200 ${actionColor}`}
                aria-label="Open menu"
              >
                Menu
              </button>
            </div>

          </div>

        </div>
      </nav>

      {/* ── Overlay ── */}
      <div
        className={`fixed inset-0 bg-black/60 z-[55] transition-all duration-500 ${
          isMobileMenuOpen ? 'opacity-100 visible' : 'opacity-0 invisible pointer-events-none'
        }`}
        onClick={() => setIsMobileMenuOpen(false)}
      />

      {/* ── Sidebar ── */}
      <div
        className={`fixed inset-y-0 right-0 w-[280px] sm:w-72 z-[60] transform transition-transform duration-500 ease-out ${
          isMobileMenuOpen ? 'translate-x-0' : 'translate-x-full'
        } ${isDark ? 'bg-[#0a0f0a] border-l border-[#1a2e1a]' : 'bg-[#dcfce7] border-l border-[#86efac]'}`}
      >
        <div className="flex flex-col h-full p-6">

          {/* Header */}
          <div className="flex items-center justify-between mb-8">
            <span className={`text-lg font-bold ${isDark ? 'text-white' : 'text-gray-900'}`}>
              Young 911<span className="text-green-700">.</span>
            </span>
            <button
              onClick={() => setIsMobileMenuOpen(false)}
              className={`p-2 transition-colors ${
                isDark
                  ? 'text-gray-400 hover:text-white bg-white/5 hover:bg-white/10'
                  : 'text-gray-500 hover:text-gray-900 bg-[#bbf7d0] hover:bg-[#86efac]'
              }`}
              style={{ clipPath: 'polygon(0 0, calc(100% - 6px) 0, 100% 6px, 100% 100%, 6px 100%, 0 calc(100% - 6px))' }}
              aria-label="Close menu"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          {/* Nav Links */}
          <nav className="flex-1 flex flex-col space-y-1">
            {navLinks.map((link, index) => (
              <Link
                key={link.href}
                href={link.href}
                onClick={() => setIsMobileMenuOpen(false)}
                className={`flex items-center space-x-3 py-3 px-4 font-medium transition-all duration-200 ${
                  isDark
                    ? 'text-gray-300 hover:text-white hover:bg-white/5'
                    : 'text-gray-700 hover:text-gray-900 hover:bg-[#bbf7d0]'
                }`}
                style={{
                  transitionDelay: isMobileMenuOpen ? `${index * 40}ms` : '0ms',
                  opacity: isMobileMenuOpen ? 1 : 0,
                  transform: isMobileMenuOpen ? 'translateX(0)' : 'translateX(16px)',
                }}
              >
                <span className="w-1 h-5 bg-green-700 rounded-full" />
                <span>{link.label}</span>
              </Link>
            ))}
          </nav>

          {/* Theme Toggle */}
          <div className={`py-4 border-t border-b ${isDark ? 'border-[#1a2e1a]' : 'border-[#86efac]'}`}>
            <button
              onClick={toggleTheme}
              className={`w-full flex items-center justify-between px-4 py-3 font-medium transition-colors duration-200 ${
                isDark
                  ? 'text-gray-300 hover:text-white hover:bg-white/5'
                  : 'text-gray-700 hover:text-gray-900 hover:bg-[#bbf7d0]'
              }`}
            >
              <span className="flex items-center space-x-3">
                {isDark ? (
                  <>
                    <svg className="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Light Mode</span>
                  </>
                ) : (
                  <>
                    <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <span>Dark Mode</span>
                  </>
                )}
              </span>
              <span className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-300 ${isDark ? 'bg-green-700' : 'bg-gray-300'}`}>
                <span className={`inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-300 ${isDark ? 'translate-x-6' : 'translate-x-1'}`} />
              </span>
            </button>
          </div>

          {/* Booking CTA */}
          <div className="pt-4 flex flex-col gap-2">
            {isAuthenticated ? (
              <>
                <Link
                  href="/dashboard"
                  onClick={() => setIsMobileMenuOpen(false)}
                  className="btn-green w-full"
                >
                  <span>Dashboard</span>
                  <span className="btn-icon-circle ml-auto">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                  </span>
                </Link>
                <Link
                  href="/dashboard/booking"
                  onClick={() => setIsMobileMenuOpen(false)}
                  className="btn-glow w-full"
                >
                  <span>Booking Baru</span>
                  <span className="btn-icon-circle ml-auto">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                        d="M12 4v16m8-8H4" />
                    </svg>
                  </span>
                </Link>
              </>
            ) : (
              <>
                <Link
                  href="/login"
                  onClick={() => setIsMobileMenuOpen(false)}
                  className="btn-green w-full"
                >
                  <span>Login</span>
                  <span className="btn-icon-circle ml-auto">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                  </span>
                </Link>
                <Link
                  href="/booking"
                  onClick={() => setIsMobileMenuOpen(false)}
                  className="btn-glow w-full"
                >
                  <span>Booking</span>
                  <span className="btn-icon-circle ml-auto">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </span>
                </Link>
              </>
            )}
          </div>

        </div>
      </div>
    </>
  );
}
