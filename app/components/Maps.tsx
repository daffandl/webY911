export default function Maps() {
  const clipBadge = { clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' };
  const clipIcon  = { clipPath: 'polygon(0 0, calc(100% - 6px) 0, 100% 6px, 100% 100%, 6px 100%, 0 calc(100% - 6px))' };
  const clipCard  = { clipPath: 'polygon(0 0, calc(100% - 16px) 0, 100% 16px, 100% 100%, 16px 100%, 0 calc(100% - 16px))' };

  return (
    <section id="contact" className="py-14 sm:py-20 bg-[#dcfce7] dark:bg-[#0a0f0a] border-b border-[#86efac] dark:border-[#1a2e1a]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="text-center mb-12">
          <div className="inline-block bg-[#166534] text-white px-6 py-2 text-sm font-semibold mb-4" style={clipBadge}>
            Contact Us
          </div>
          <h2 className="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
            Visit Our Workshop
          </h2>
          <p className="text-gray-600 dark:text-gray-300 text-lg max-w-2xl mx-auto">
            Find us at our convenient location with ample parking space
          </p>
        </div>

        <div className="grid md:grid-cols-2 gap-8">
          {/* Map */}
          <div className="overflow-hidden border border-[#86efac] dark:border-[#1a2e1a] h-64 sm:h-96 bg-[#bbf7d0] dark:bg-[#0f1a0f]" style={clipCard}>
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3965.761940027963!2d106.6891079!3d-6.2949845!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69fb14b4b7bc59%3A0x16e1be0ab072085d!2sYoung911%20autowerks!5e0!3m2!1sid!2sid!4v1774030537680!5m2!1sid!2sid"
              width="100%"
              height="100%"
              style={{ border: 0 }}
              allowFullScreen
              loading="lazy"
              referrerPolicy="no-referrer-when-downgrade"
              className="w-full h-full"
            />
          </div>

          {/* Contact Info */}
          <div className="p-5 sm:p-8 border border-[#86efac] dark:border-[#1a2e1a] bg-[#bbf7d0] dark:bg-[#0f1a0f]" style={clipCard}>
            <h3 className="text-2xl font-bold text-gray-900 dark:text-white mb-6">Contact Information</h3>

            <div className="space-y-6">
              {[
                {
                  icon: <><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></>,
                  title: 'Address',
                  content: <>Jl. Jombang Astek No.911, RT.004/RW.003 <br/>Lengkong Gudang Tim., Kec. Serpong<br/>Kota Tangerang Selatan, Banten 15318</>,
                },
                {
                  icon: <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />,
                  title: 'Phone',
                  content: '+62 812 3456 7890',
                },
                {
                  icon: <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />,
                  title: 'Email',
                  content: 'info@young911autowerks.com',
                },
                {
                  icon: <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />,
                  title: 'Working Hours',
                  content: <>Monday - Saturday: 08:30 - 17:00<br />Sunday: By Appointment</>,
                },
              ].map((item, i) => (
                <div key={i} className="flex items-start space-x-4">
                  <div className="bg-[#166534] p-3 flex-shrink-0" style={clipIcon}>
                    <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      {item.icon}
                    </svg>
                  </div>
                  <div>
                    <h4 className="font-semibold text-gray-900 dark:text-white">{item.title}</h4>
                    <p className="text-gray-600 dark:text-gray-300">{item.content}</p>
                  </div>
                </div>
              ))}
            </div>

            {/* Get Directions Button */}
            <a
              href="https://maps.google.com"
              target="_blank"
              rel="noopener noreferrer"
              className="mt-8 inline-flex items-center btn-glow"
            >
              <span>Get Directions</span>
              <span className="btn-icon-circle">
                <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                </svg>
              </span>
            </a>
          </div>
        </div>
      </div>
    </section>
  );
}
