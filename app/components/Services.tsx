'use client';

import { useState } from 'react';

const serviceCategories = [
  { id: 'all',          label: 'All Services'      },
  { id: 'diagnostics',  label: 'Diagnostics'       },
  { id: 'maintenance',  label: 'Maintenance'       },
  { id: 'suspension',   label: 'Suspension'        },
  { id: 'electrical',   label: 'Electrical'        },
  { id: 'brakes',       label: 'Brakes'            },
  { id: 'transmission', label: 'Transmission'      },
];

const services = [
  {
    category: 'diagnostics',
    icon: (
      <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>
    ),
    title: 'Engine Diagnostics',
    description: 'Advanced computer diagnostics to identify and resolve engine issues with precision.',
    price: 'From Rp 500K',
  },
  {
    category: 'maintenance',
    icon: (
      <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
      </svg>
    ),
    title: 'Oil Change & Maintenance',
    description: 'Premium synthetic oil changes and comprehensive maintenance services.',
    price: 'From Rp 1.2M',
  },
  {
    category: 'suspension',
    icon: (
      <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
      </svg>
    ),
    title: 'Suspension & Steering',
    description: 'Expert suspension and steering system repairs for optimal handling.',
    price: 'From Rp 800K',
  },
  {
    category: 'electrical',
    icon: (
      <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M13 10V3L4 14h7v7l9-11h-7z" />
      </svg>
    ),
    title: 'Electrical Systems',
    description: 'Complete electrical system diagnostics and repairs for all vehicle electronics.',
    price: 'From Rp 600K',
  },
  {
    category: 'brakes',
    icon: (
      <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    ),
    title: 'Brake Services',
    description: 'Professional brake inspection, pad replacement, and system maintenance.',
    price: 'From Rp 700K',
  },
  {
    category: 'transmission',
    icon: (
      <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M5 3v4M3 7h4M3 17v4m14-14v4m-2 4h4M3 13h4m14 4v4M5 9v4m14 4v4M5 5h4m10 0h4" />
      </svg>
    ),
    title: 'Transmission Service',
    description: 'Specialized transmission maintenance and repair for smooth performance.',
    price: 'From Rp 1.5M',
  },
];

export default function Services() {
  const [activeTab, setActiveTab] = useState('all');

  const filtered = activeTab === 'all'
    ? services
    : services.filter((s) => s.category === activeTab);

  return (
    <section id="services" className="py-14 sm:py-20 bg-[#dcfce7] dark:bg-[#0a0f0a] border-b border-[#86efac] dark:border-[#1a2e1a]">

      {/* Header — inside container */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-8 sm:mb-10">
          <div className="inline-block bg-[#166534] text-white px-6 py-2 text-sm font-semibold mb-4"
            style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}>
            Our Services
          </div>
          <h2 className="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
            Premium Car Service Solutions
          </h2>
          <p className="text-gray-600 dark:text-gray-300 text-lg max-w-2xl mx-auto">
            Comprehensive care for your Land Rover with genuine parts and expert technicians
          </p>
        </div>
      </div>

      {/* ── Scrollable Tab Bar — full width, edge to edge ── */}
      <div className="mb-10 sm:mb-14">
        {/* Top border */}
        <div className="w-full h-px bg-[#86efac] dark:bg-[#1a2e1a]" />

        {/* Tabs row — scrollable, no scrollbar */}
        <div className="overflow-x-auto">
          <div className="flex min-w-max">
            {serviceCategories.map((cat, idx) => {
              const isActive = activeTab === cat.id;
              return (
                <button
                  key={cat.id}
                  onClick={() => setActiveTab(cat.id)}
                  className={[
                    'flex-shrink-0 px-5 sm:px-7 py-3 text-sm tracking-wide transition-all duration-200 whitespace-nowrap',
                    idx < serviceCategories.length - 1
                      ? 'border-r border-[#86efac] dark:border-[#1a2e1a]'
                      : '',
                    isActive
                      ? 'font-bold text-[#166534] dark:text-[#4ade80] bg-[#bbf7d0] dark:bg-[#0a0f0a]'
                      : 'font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-[#bbf7d0] dark:hover:bg-[#0a0f0a]',
                  ].join(' ')}
                >
                  {cat.label}
                </button>
              );
            })}
          </div>
        </div>

        {/* Bottom border */}
        <div className="w-full h-px bg-[#86efac] dark:bg-[#1a2e1a]" />
      </div>

      {/* Services Grid — back inside container */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Services Grid */}
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-8">
          {filtered.map((service, index) => (
            <div
              key={index}
              className="group card-clip card-clip-light p-5 sm:p-8 cursor-pointer"
            >
              <div className="text-[#166534] dark:text-[#4ade80] group-hover:text-white transition-colors duration-300 mb-6">
                {service.icon}
              </div>
              <h3 className="text-xl font-bold text-gray-900 dark:text-white group-hover:text-white transition-colors duration-300 mb-3">
                {service.title}
              </h3>
              <p className="text-gray-600 dark:text-gray-300 group-hover:text-white/90 transition-colors duration-300 mb-4">
                {service.description}
              </p>
              <div className="text-[#166534] dark:text-[#4ade80] group-hover:text-white font-semibold transition-colors duration-300">
                {service.price}
              </div>
            </div>
          ))}
        </div>

      </div>
    </section>
  );
}
