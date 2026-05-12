import "./globals.css";

export const metadata = {
  title: "ziak.dev — Premium Web Design & Branding",
  description:
    "Premium web design, branding, social media systems and modern digital experiences for brands that want to stand out online.",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" className="h-full antialiased">
      <body className="min-h-full flex flex-col">{children}</body>
    </html>
  );
}

