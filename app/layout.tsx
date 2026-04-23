import type { Metadata } from "next";
import localFont from "next/font/local";
import "./globals.css";
import { ThemeProvider } from "./components/ThemeProvider";
import { AuthProvider } from "./components/AuthProvider";

const outfit = localFont({
  src: [
    {
      path: "../public/fonts/Outfit-Thin.woff2",
      weight: "100",
      style: "normal",
    },
    {
      path: "../public/fonts/Outfit-ExtraLight.woff2",
      weight: "200",
      style: "normal",
    },
    {
      path: "../public/fonts/Outfit-Light.woff2",
      weight: "300",
      style: "normal",
    },
    {
      path: "../public/fonts/Outfit-Regular.woff2",
      weight: "400",
      style: "normal",
    },
    {
      path: "../public/fonts/Outfit-Medium.woff2",
      weight: "500",
      style: "normal",
    },
    {
      path: "../public/fonts/Outfit-SemiBold.woff2",
      weight: "600",
      style: "normal",
    },
    {
      path: "../public/fonts/Outfit-Bold.woff2",
      weight: "700",
      style: "normal",
    },
    {
      path: "../public/fonts/Outfit-ExtraBold.woff2",
      weight: "800",
      style: "normal",
    },
    {
      path: "../public/fonts/Outfit-Black.woff2",
      weight: "900",
      style: "normal",
    },
  ],
  variable: "--font-outfit",
  display: "swap",
});

export const metadata: Metadata = {
  title: "Land Rover Specialist - Car Service & Repair",
  description: "Premium car service and repair specialist for Land Rover vehicles. Certified technicians, genuine parts, and state-of-the-art diagnostics.",
  keywords: ["Land Rover", "car service", "auto repair", "vehicle maintenance", "Young 911", "Autowerks"],
  authors: [{ name: "Young 911 Autowerks" }],
  openGraph: {
    title: "Land Rover Specialist - Car Service & Repair",
    description: "Premium car service and repair specialist for Land Rover vehicles",
    type: "website",
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html
      lang="en"
      className={`${outfit.variable} h-full antialiased scroll-smooth`}
      style={{ overflowX: 'hidden' }}
      suppressHydrationWarning
    >
      <body className="min-h-full flex flex-col overflow-x-hidden">
        <ThemeProvider>
          <AuthProvider>{children}</AuthProvider>
        </ThemeProvider>
      </body>
    </html>
  );
}
