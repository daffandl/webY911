'use client';

import { useState } from 'react';

const faqs = [
  {
    question: 'What brands do you specialize in?',
    answer: 'We specialize in Land Rover vehicles, but our expert technicians are trained to work on various luxury car brands including Porsche, BMW, Mercedes-Benz, and more.',
  },
  {
    question: 'Do you use genuine parts?',
    answer: 'Yes, we only use genuine OEM parts or high-quality equivalents that meet manufacturer specifications to ensure your vehicle maintains its performance and warranty.',
  },
  {
    question: 'How long does a typical service take?',
    answer: "Routine maintenance like oil changes typically takes 1-2 hours. More complex services may take a full day. We'll provide you with an accurate timeline after diagnosis.",
  },
  {
    question: 'Do you offer warranty on your services?',
    answer: 'Absolutely! We provide a comprehensive warranty on all our services and parts. The warranty period varies depending on the service type, typically ranging from 6 months to 2 years.',
  },
  {
    question: 'Can I wait while my car is being serviced?',
    answer: 'Yes, we have a comfortable waiting area with WiFi, refreshments, and TV. For longer services, we can also arrange pickup and drop-off services.',
  },
  {
    question: 'How do I book a service appointment?',
    answer: 'You can book an appointment through our online booking system, call us directly, or visit our workshop. We recommend booking in advance, especially during peak seasons.',
  },
  {
    question: 'Do you provide loaner vehicles?',
    answer: 'For major services that take multiple days, we can arrange loaner vehicles or help coordinate alternative transportation. Please inquire when booking your appointment.',
  },
  {
    question: 'What payment methods do you accept?',
    answer: 'We accept cash, all major credit cards, bank transfers, and digital payments through Midtrans. We also offer installment plans for major repairs.',
  },
];

export default function FAQ() {
  const [openIndex, setOpenIndex] = useState<number | null>(0);

  return (
    <section id="faq" className="py-14 sm:py-20 bg-[#dcfce7] dark:bg-[#0a0f0a] border-b border-[#86efac] dark:border-[#1a2e1a]">
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {/* Header */}
        <div className="text-center mb-12">
          <div className="inline-block bg-[#166534] text-white px-6 py-2 text-sm font-semibold mb-4"
            style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}>
            FAQ
          </div>
          <h2 className="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
            Frequently Asked Questions
          </h2>
          <p className="text-gray-600 dark:text-gray-300 text-lg">
            Find answers to common questions about our services
          </p>
        </div>

        {/* FAQ Items */}
        <div className="space-y-3">
          {faqs.map((faq, index) => (
            <div
              key={index}
              className="card-clip card-clip-faq overflow-hidden"
            >
              <button
                onClick={() => setOpenIndex(openIndex === index ? null : index)}
                className="w-full px-4 sm:px-6 py-4 sm:py-5 flex items-center justify-between text-left hover:bg-[#a7f3d0] dark:hover:bg-[#0f1a0f] transition-colors"
              >
                <span className="font-semibold text-gray-900 dark:text-white pr-8">
                  {faq.question}
                </span>
                <svg
                  className={`w-5 h-5 text-[#166534] dark:text-[#4ade80] flex-shrink-0 transition-transform duration-300 ${
                    openIndex === index ? 'rotate-180' : ''
                  }`}
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div
                className={`overflow-hidden transition-all duration-300 ${
                  openIndex === index ? 'max-h-96' : 'max-h-0'
                }`}
              >
                <div className="px-6 pb-5 text-gray-600 dark:text-gray-300 leading-relaxed">
                  {faq.answer}
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
