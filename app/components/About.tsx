export default function About() {
  const stats = [
    { value: '15+', label: 'Years Experience' },
    { value: '5000+', label: 'Happy Clients' },
    { value: '100%', label: 'Satisfaction Rate' },
    { value: '24/7', label: 'Support' },
  ];

  const clipBadge = { clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' };
  const clipImg   = { clipPath: 'polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 10px 100%, 0 calc(100% - 10px))' };

  return (
    <section id="about" className="py-10 sm:py-14 bg-[#dcfce7] dark:bg-[#0a0f0a] border-b border-[#86efac] dark:border-[#1a2e1a]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid md:grid-cols-2 gap-8 lg:gap-12 items-center">

          {/* Image Grid */}
          <div className="relative mb-4 md:mb-0">
            <div className="grid grid-cols-2 gap-3">
              <div className="space-y-3">
                <div className="overflow-hidden h-48 sm:h-64" style={clipImg}>
                  <img src="https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?w=600&q=80" alt="Workshop" className="w-full h-full object-cover" />
                </div>
                <div className="overflow-hidden h-32 sm:h-40" style={clipImg}>
                  <img src="https://images.unsplash.com/photo-1487754180451-c456f719a1fc?w=600&q=80" alt="Team" className="w-full h-full object-cover" />
                </div>
              </div>
              <div className="space-y-3">
                <div className="overflow-hidden h-32 sm:h-40" style={clipImg}>
                  <img src="https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=600&q=80" alt="Service" className="w-full h-full object-cover" />
                </div>
                <div className="overflow-hidden h-48 sm:h-64" style={clipImg}>
                  <img src="https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?w=600&q=80" alt="Workshop" className="w-full h-full object-cover" />
                </div>
              </div>
            </div>
          </div>

          {/* Content */}
          <div className="pt-4 md:pt-0">
            <div className="inline-block bg-[#166534] text-white px-5 py-1.5 text-sm font-semibold mb-4" style={clipBadge}>
              About Us
            </div>
            <h2 className="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4 sm:mb-6 leading-tight">
              Your Trusted Land Rover Specialist
            </h2>
            <p className="text-gray-600 dark:text-gray-300 text-base sm:text-lg mb-4 sm:mb-6 leading-relaxed">
              Young 911 Autowerks is a premier automotive service center specializing in Land Rover vehicles.
              Our team of certified technicians brings decades of combined experience to ensure your luxury
              vehicle receives the expert care it deserves.
            </p>
            <p className="text-gray-600 dark:text-gray-300 text-base sm:text-lg mb-6 sm:mb-8 leading-relaxed">
              We combine cutting-edge technology with genuine parts to deliver service that meets
              manufacturer standards. From routine maintenance to complex repairs, we're committed to
              keeping your Land Rover performing at its best.
            </p>

            {/* Stats */}
            <div className="grid grid-cols-2 gap-3 sm:gap-6 mb-6 sm:mb-8">
              {stats.map((stat) => (
                <div key={stat.label} className="text-center p-3 sm:p-4 card-clip card-clip-light">
                  <div className="text-2xl sm:text-3xl font-bold text-[#166534] dark:text-[#4ade80]">{stat.value}</div>
                  <div className="text-gray-600 dark:text-gray-400 text-xs sm:text-sm mt-1">{stat.label}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
