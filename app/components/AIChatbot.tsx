'use client';

import { useState, useEffect, useRef } from 'react';

interface Message {
  role: 'user' | 'assistant';
  content: string;
}

export default function AIChatbot() {
  const [isOpen, setIsOpen] = useState(false);
  const [messages, setMessages] = useState<Message[]>([
    {
      role: 'assistant',
      content: 'Hello! I\'m your AI assistant for Young 911 Autowerks. How can I help you with your Land Rover today?',
    },
  ]);
  const [input, setInput] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const sendMessage = async () => {
    if (!input.trim() || isLoading) return;

    const userMessage: Message = { role: 'user', content: input.trim() };
    setMessages(prev => [...prev, userMessage]);
    setInput('');
    setIsLoading(true);

    try {
      const apiKey = process.env.NEXT_PUBLIC_GEMINI_API_KEY;
      
      if (!apiKey || apiKey === 'AIzaSyC55A_TYUPIBXFK_LQ4c6t1hwjhzszoGQE') {
        // Provide a helpful fallback response when API key is not configured
        const fallbackResponses = [
          "Thanks for your message! To enable AI chat, please add your Gemini API key to the .env.local file. Get your free API key from: https://makersuite.google.com/app/apikey",
          "I'd love to help! The AI chatbot needs a Gemini API key to work. Please configure it in your .env.local file. Visit: https://makersuite.google.com/app/apikey",
          "Great question! Once you add your Gemini API key to .env.local, I'll be able to provide detailed answers. Get your key at: https://makersuite.google.com/app/apikey",
        ];
        
        await new Promise(resolve => setTimeout(resolve, 500));
        const randomResponse = fallbackResponses[Math.floor(Math.random() * fallbackResponses.length)];
        setMessages(prev => [...prev, { role: 'assistant', content: randomResponse }]);
        return;
      }

      const conversationHistory = messages.map(msg => 
        `${msg.role === 'user' ? 'User' : 'Assistant'}: ${msg.content}`
      ).join('\n');

      const systemPrompt = `You are a helpful AI assistant for Young 911 Autowerks, a premium Land Rover specialist service center. 
      
Your role:
- Help customers with questions about Land Rover services, maintenance, and repairs
- Provide information about common Land Rover issues and solutions
- Assist with booking appointments (direct them to the booking form)
- Be friendly, professional, and knowledgeable about automotive services

Key information:
- We specialize in Land Rover vehicles
- We offer: Regular Maintenance, Repair, Diagnostics, Oil Change, Brake Service
- We use genuine OEM parts
- We have certified technicians with 15+ years experience
- We provide warranty on all services
- Booking confirmation is done via WhatsApp

Keep responses concise (2-3 sentences max) and helpful. If you don't know something, suggest they contact us directly.`;

      const fullPrompt = `${systemPrompt}

Conversation history:
${conversationHistory}

User: ${userMessage.content}

Assistant:`;

      const response = await fetch(
        `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=${apiKey}`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            contents: [{
              parts: [{
                text: fullPrompt
              }]
            }],
            generationConfig: {
              temperature: 0.7,
              maxOutputTokens: 500,
            }
          }),
        }
      );

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        console.error('Gemini API Error:', errorData);
        throw new Error(`API Error: ${response.status}`);
      }

      const data = await response.json();
      const assistantMessage = data.candidates?.[0]?.content?.parts?.[0]?.text || 
        'I apologize, but I\'m having trouble processing your request. Please try again or contact us directly.';

      setMessages(prev => [...prev, { role: 'assistant', content: assistantMessage }]);
    } catch (error) {
      console.error('Chatbot error:', error);
      setMessages(prev => [...prev, { 
        role: 'assistant', 
        content: 'I apologize, but I\'m experiencing technical difficulties. Please contact us directly at +62 812 3456 7890 or visit our workshop for assistance.' 
      }]);
    } finally {
      setIsLoading(false);
    }
  };

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  };

  return (
    <>
      {/* Chat Toggle Button */}
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="fixed bottom-6 right-6 z-50 text-white p-2 transition-all duration-300 transform hover:scale-110 group"
        style={{
          background: 'linear-gradient(135deg, #c2410c 0%, #9a3412 100%)',
          clipPath: 'polygon(0 0, calc(100% - 7px) 0, 100% 7px, 100% 100%, 7px 100%, 0 calc(100% - 7px))',
        }}
        aria-label="Open Chat"
      >
        {isOpen ? (
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        ) : (
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
          </svg>
        )}

        {/* Tooltip */}
        <span className="absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-[#0a0f0a] dark:bg-[#dcfce7] text-white dark:text-gray-900 px-3 py-1.5 text-sm font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200"
          style={{ clipPath: 'polygon(0 0, calc(100% - 6px) 0, 100% 6px, 100% 100%, 6px 100%, 0 calc(100% - 6px))' }}>
          AI Assistant
        </span>

        {/* Notification Badge */}
        {!isOpen && messages.length > 1 && (
          <span className="absolute -top-1 -right-1 w-4 h-4 bg-[#166534] text-xs flex items-center justify-center animate-pulse"
            style={{ clipPath: 'polygon(0 0, calc(100% - 4px) 0, 100% 4px, 100% 100%, 4px 100%, 0 calc(100% - 4px))' }}>
            !
          </span>
        )}
      </button>

      {/* Chat Window */}
      <div
        className={`fixed bottom-24 right-6 w-80 sm:w-96 z-50 transform transition-all duration-300 ease-in-out ${
          isOpen ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-4 scale-95 pointer-events-none'
        }`}
      >
        <div className="bg-[#bbf7d0] dark:bg-[#0f1a0f] overflow-hidden border border-[#86efac] dark:border-[#1a2e1a]"
          style={{ clipPath: 'polygon(0 0, calc(100% - 16px) 0, 100% 16px, 100% 100%, 16px 100%, 0 calc(100% - 16px))' }}>
          {/* Header */}
          <div className="bg-[#166534] p-4">
            <div className="flex items-center space-x-3">
              <div className="w-10 h-10 bg-white/20 flex items-center justify-center"
                style={{ clipPath: 'polygon(0 0, calc(100% - 6px) 0, 100% 6px, 100% 100%, 6px 100%, 0 calc(100% - 6px))' }}>
                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <h3 className="text-white font-bold text-lg">AI Assistant</h3>
                <p className="text-white/80 text-xs">Young 911 Autowerks</p>
              </div>
            </div>
          </div>

          {/* Messages */}
          <div className="h-80 overflow-y-auto p-4 space-y-4 bg-[#dcfce7] dark:bg-[#0a0f0a]">
            {messages.map((message, index) => (
              <div
                key={index}
                className={`flex ${message.role === 'user' ? 'justify-end' : 'justify-start'}`}
              >
                <div
                  className={`max-w-[80%] px-4 py-2 ${
                    message.role === 'user'
                      ? 'bg-[#166534] text-white'
                      : 'bg-[#bbf7d0] dark:bg-[#0f1a0f] text-gray-900 dark:text-white border border-[#86efac] dark:border-[#1a2e1a]'
                  }`}
                  style={{ clipPath: 'polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px))' }}
                >
                  <p className="text-sm whitespace-pre-wrap">{message.content}</p>
                </div>
              </div>
            ))}
            {isLoading && (
              <div className="flex justify-start">
                <div className="bg-[#bbf7d0] dark:bg-[#0f1a0f] px-4 py-3 border border-[#86efac] dark:border-[#1a2e1a]">
                  <div className="flex space-x-1">
                    <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
                    <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
                    <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
                  </div>
                </div>
              </div>
            )}
            <div ref={messagesEndRef} />
          </div>

          {/* Input */}
          <div className="p-4 bg-[#bbf7d0] dark:bg-[#0f1a0f] border-t border-[#86efac] dark:border-[#1a2e1a]">
            <div className="flex items-center space-x-2">
              <input
                type="text"
                value={input}
                onChange={(e) => setInput(e.target.value)}
                onKeyPress={handleKeyPress}
                placeholder="Type your message..."
                disabled={isLoading}
                className="flex-1 px-4 py-2 border border-[#86efac] dark:border-[#1a2e1a] bg-[#dcfce7] dark:bg-[#0a0f0a] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#166534] focus:border-transparent transition-all text-sm"
                style={{ clipPath: 'polygon(0 0, calc(100% - 6px) 0, 100% 6px, 100% 100%, 6px 100%, 0 calc(100% - 6px))' }}
              />
              <button
                onClick={sendMessage}
                disabled={isLoading || !input.trim()}
                className="p-2 bg-[#c2410c] hover:bg-[#9a3412] disabled:bg-gray-400 text-white transition-all duration-200 transform hover:scale-105 disabled:transform-none"
                style={{ clipPath: 'polygon(0 0, calc(100% - 6px) 0, 100% 6px, 100% 100%, 6px 100%, 0 calc(100% - 6px))' }}
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
