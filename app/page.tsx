import Navbar from './components/Navbar';
import Hero from './components/Hero';
import About from './components/About';
import Services from './components/Services';
import Gallery from './components/Gallery';
import Testimonials from './components/Testimonials';
import FAQ from './components/FAQ';
import Maps from './components/Maps';
import Blog from './components/Blog';
import Footer from './components/Footer';
import AIChatbot from './components/AIChatbot';

export default function Home() {
  return (
    <main className="flex-1">
      <Navbar />
      <Hero />
      <About />
      <Services />
      <Gallery />
      <Testimonials />
      <FAQ />
      <Maps />
      <Blog />
      <Footer />
      <AIChatbot />
    </main>
  );
}
