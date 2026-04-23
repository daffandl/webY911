'use client';

import { useState } from 'react';

const galleryImages = [
  { src: 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800&q=80', category: 'workshop', title: 'Workshop'       },
  { src: 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=800&q=80', category: 'cars',     title: 'Luxury Cars'   },
  { src: 'https://images.unsplash.com/photo-1583121274602-3e2820c69888?w=800&q=80', category: 'service',  title: 'Car Service'   },
  { src: 'https://images.unsplash.com/photo-1502877338535-766e1452684a?w=800&q=80', category: 'cars',     title: 'Sports Car'    },
  { src: 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?w=800&q=80', category: 'workshop', title: 'Engine Work'   },
  { src: 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?w=800&q=80', category: 'team',     title: 'Our Team'      },
  { src: 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=800&q=80', category: 'service',  title: 'Detailing'     },
  { src: 'https://images.unsplash.com/photo-1530046339160-7115b3e03eb1?w=800&q=80', category: 'cars',     title: 'Classic Car'   },
  { src: 'https://images.unsplash.com/photo-1552510188-6c8c98d307c7?w=800&q=80',    category: 'cars',     title: 'Luxury Interior'},
];

const categories = [
  { id: 'all',      label: 'All'      },
  { id: 'cars',     label: 'Cars'     },
  { id: 'workshop', label: 'Workshop' },
  { id: 'service',  label: 'Service'  },
  { id: 'team',     label: 'Team'     },
];

export default function Gallery() {
  const [activeCategory, setActiveCategory] = useState('all');

  const filteredImages = activeCategory === 'all'
    ? galleryImages
    : galleryImages.filter((img) => img.category === activeCategory);

  return (
    <section id="gallery" className="py-14 sm:py-20 bg-[#dcfce7] dark:bg-[#0a0f0a] border-b border-[#86efac] dark:border-[#1a2e1a]">

      {/* Header — inside container */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-8 sm:mb-10">
          <div className="inline-block bg-[#166534] text-white px-6 py-2 text-sm font-semibold mb-4"
            style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}>
            Gallery
          </div>
          <h2 className="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
            Our Work &amp; Facility
          </h2>
          <p className="text-gray-600 dark:text-gray-300 text-lg max-w-2xl mx-auto">
            Take a look inside our state-of-the-art facility and see our expert team in action
          </p>
        </div>
      </div>

      {/* ── Scrollable Tab Bar — full width, edge to edge ── */}
      <div className="mb-10 sm:mb-12">
        <div className="w-full h-px bg-[#86efac] dark:bg-[#1a2e1a]" />
        <div className="overflow-x-auto">
          <div className="flex min-w-max">
            {categories.map((cat, idx) => {
              const isActive = activeCategory === cat.id;
              return (
                <button
                  key={cat.id}
                  onClick={() => setActiveCategory(cat.id)}
                  className={[
                    'flex-shrink-0 px-5 sm:px-7 py-3 text-sm tracking-wide transition-all duration-200 whitespace-nowrap',
                    idx < categories.length - 1 ? 'border-r border-[#86efac] dark:border-[#1a2e1a]' : '',
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
        <div className="w-full h-px bg-[#86efac] dark:bg-[#1a2e1a]" />
      </div>

      {/* Gallery Grid — back inside container */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
          {filteredImages.map((image, index) => (
            <div
              key={index}
              className={`relative group overflow-hidden cursor-pointer ${
                index === 0 || index === 3 ? 'md:col-span-2 md:row-span-2' : ''
              }`}
              style={{ clipPath: 'polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 12px 100%, 0 calc(100% - 12px))' }}
            >
              <div className="aspect-square">
                <img
                  src={image.src}
                  alt={image.title}
                  className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                />
              </div>
              <div className="absolute inset-0 bg-black/0 group-hover:bg-black/50 transition-all duration-300 flex items-end">
                <div className="p-4 translate-y-4 group-hover:translate-y-0 opacity-0 group-hover:opacity-100 transition-all duration-300">
                  <h3 className="font-bold text-lg text-white">{image.title}</h3>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
