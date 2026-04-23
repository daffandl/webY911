const blogPosts = [
  {
    image: 'https://images.unsplash.com/photo-1498887960847-2a5e46312788?w=800&q=80',
    category: 'Maintenance',
    title: 'Essential Maintenance Tips for Your Land Rover',
    excerpt: 'Keep your Land Rover in peak condition with these expert maintenance tips and schedules.',
    date: 'March 15, 2026',
    readTime: '5 min read',
  },
  {
    image: 'https://images.unsplash.com/photo-1552510188-6c8c98d307c7?w=800&q=80',
    category: 'Tips',
    title: 'Signs Your Car Needs Immediate Attention',
    excerpt: 'Learn to recognize the warning signs that indicate your vehicle needs professional service.',
    date: 'March 10, 2026',
    readTime: '4 min read',
  },
  {
    image: 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=800&q=80',
    category: 'Technology',
    title: 'Modern Car Diagnostics: What You Need to Know',
    excerpt: 'Understanding how modern diagnostic tools help identify and fix car problems faster.',
    date: 'March 5, 2026',
    readTime: '6 min read',
  },
];

export default function Blog() {
  return (
    <section id="blog" className="py-14 sm:py-20 bg-[#dcfce7] dark:bg-[#0a0f0a] border-b border-[#86efac] dark:border-[#1a2e1a]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {/* Header */}
        <div className="text-center mb-12">
          <div className="inline-block bg-[#166534] text-white px-6 py-2 text-sm font-semibold mb-4"
            style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}>
            Blog
          </div>
          <h2 className="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
            Latest News &amp; Tips
          </h2>
          <p className="text-gray-600 dark:text-gray-300 text-lg max-w-2xl mx-auto">
            Stay informed with expert advice and industry insights
          </p>
        </div>

        {/* Blog Grid */}
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-8">
          {blogPosts.map((post, index) => (
            <article
              key={index}
              className="card-clip card-clip-light overflow-hidden cursor-pointer group"
            >
              <div className="relative overflow-hidden">
                <img
                  src={post.image}
                  alt={post.title}
                  className="w-full h-48 object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div className="absolute top-4 left-4">
                  <span
                    className="bg-[#166534] text-white px-3 py-1 text-xs font-semibold"
                    style={{ clipPath: 'polygon(0 0, calc(100% - 6px) 0, 100% 6px, 100% 100%, 6px 100%, 0 calc(100% - 6px))' }}
                  >
                    {post.category}
                  </span>
                </div>
              </div>
              <div className="p-6">
                <div className="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-3">
                  <span>{post.date}</span>
                  <span className="mx-2">•</span>
                  <span>{post.readTime}</span>
                </div>
                <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-3 line-clamp-2 group-hover:text-white transition-colors">
                  {post.title}
                </h3>
                <p className="text-gray-600 dark:text-gray-300 mb-4 line-clamp-2 group-hover:text-white/90 transition-colors">
                  {post.excerpt}
                </p>
                <a
                  href="#"
                  className="inline-flex items-center text-[#166534] dark:text-[#4ade80] font-semibold group-hover:text-white transition-colors"
                >
                  Read More
                  <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </a>
              </div>
            </article>
          ))}
        </div>

        {/* View All */}
        <div className="text-center mt-12">
          <a href="#" className="btn-outline text-gray-900 dark:text-white px-8 py-3">
            View All Posts
            <span className="btn-icon-circle" style={{ background: 'rgba(22,101,52,0.20)' }}>
              <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
            </span>
          </a>
        </div>
      </div>
    </section>
  );
}
