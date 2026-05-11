"use client";

import React, { useRef } from "react";
import Image from "next/image";
import { motion, useScroll, useTransform } from "framer-motion";
import { ArrowUpRight, Camera, Globe2, PenTool, Sparkle } from "lucide-react";

const normalizeBasePath = (value: string | undefined) => {
  if (!value) {
    return "";
  }

  const trimmed = value.trim().replace(/^\/+|\/+$/g, "");
  return trimmed ? `/${trimmed}` : "";
};

const basePath = normalizeBasePath(process.env.NEXT_PUBLIC_BASE_PATH);
const statueHeadSrc = `${basePath}/statue-head.png`;

function StatuePhoto() {
  return (
    <motion.div
      initial={{ opacity: 0, y: 40, scale: 0.94, filter: "blur(18px)" }}
      animate={{ opacity: 1, y: 0, scale: 1, filter: "blur(0px)" }}
      transition={{ duration: 1.25, delay: 0.45, ease: [0.16, 1, 0.3, 1] }}
      className="relative h-[50vh] w-[78vw] max-w-[720px] min-w-[240px] translate-y-[20vh] overflow-visible sm:h-[58vh] sm:w-[66vw] md:h-[74vh] md:w-[52vw] md:translate-y-[22vh] lg:h-[82vh] lg:w-[46vw]"
    >
      <div className="absolute inset-0 translate-y-10 scale-90 rounded-full bg-black/80 blur-3xl" />
      <div className="absolute inset-[-18%] bg-[radial-gradient(circle_at_50%_42%,rgba(255,255,255,0.24),transparent_34%),radial-gradient(circle_at_54%_62%,rgba(0,0,0,0.86),transparent_56%)] blur-xl" />
      <div className="absolute inset-[-8%] bg-[radial-gradient(circle_at_38%_22%,rgba(255,255,255,0.25),transparent_26%),radial-gradient(circle_at_72%_55%,rgba(255,255,255,0.08),transparent_20%)] mix-blend-screen" />

      <Image
        src={statueHeadSrc}
        alt="Classical statue head"
        fill
        priority
        sizes="(min-width: 1024px) 46vw, (min-width: 768px) 52vw, (min-width: 640px) 66vw, 78vw"
        className="relative z-10 object-contain object-center contrast-125 grayscale brightness-105 drop-shadow-[0_45px_70px_rgba(0,0,0,0.85)]"
      />

      <Image
        src={statueHeadSrc}
        alt=""
        aria-hidden="true"
        fill
        sizes="(min-width: 1024px) 46vw, (min-width: 768px) 52vw, (min-width: 640px) 66vw, 78vw"
        className="pointer-events-none absolute inset-0 z-0 translate-x-3 translate-y-1 object-contain object-center grayscale opacity-20 blur-[2px]"
      />

      <div className="pointer-events-none absolute inset-0 z-20 bg-[linear-gradient(90deg,rgba(0,0,0,0.38),transparent_34%,rgba(255,255,255,0.08)_48%,transparent_68%,rgba(0,0,0,0.48))] mix-blend-overlay" />
      <div className="pointer-events-none absolute inset-[-12%] z-30 bg-[radial-gradient(circle_at_50%_45%,rgba(255,255,255,0.12),transparent_34%),radial-gradient(circle_at_50%_50%,transparent_42%,rgba(0,0,0,0.28)_88%)] blur-xl" />

      {/* outside glow */}
      <div className="pointer-events-none absolute -inset-[10%] z-0 bg-[radial-gradient(circle_at_50%_38%,rgba(255,255,255,0.16),transparent_24%),radial-gradient(circle_at_45%_42%,rgba(92,114,255,0.18),transparent_28%),radial-gradient(circle_at_58%_48%,rgba(255,61,159,0.14),transparent_24%)] blur-3xl opacity-90" />
    </motion.div>
  );
}

const heroLines = [
  "ZIAK.DEV",
  "DIGITAL",
  "MONOLITH",
  "WEB DESIGN",
  "SOCIAL MEDIA",
  "GRAPHIC SYSTEMS",
];

const services = [
  {
    title: "WEB DESIGN",
    text: "Modern landing pages, premium websites and digital experiences designed to elevate brands and drive conversions.",
    icon: Globe2,
  },
  {
    title: "SOCIAL MEDIA",
    text: "Content strategy, reels, visuals, profile management and consistent online brand growth across every platform.",
    icon: Camera,
  },
  {
    title: "GRAPHICS & VECTORS",
    text: "Logos, visual systems, social media graphics, ad creatives, vector assets and complete brand identities.",
    icon: PenTool,
  },
];

const projects = ["BRAND IDENTITY", "SOCIAL MEDIA SYSTEM", "WEBSITE EXPERIENCE"];

export default function ZiakDevChromaticLandingPage() {
  const container = useRef(null);
  const { scrollYProgress } = useScroll({ target: container, offset: ["start start", "end start"] });
  const heroScale = useTransform(scrollYProgress, [0, 1], [1, 0.9]);
  const heroOpacity = useTransform(scrollYProgress, [0, 0.75], [1, 0]);
  const textY = useTransform(scrollYProgress, [0, 1], [0, 140]);
  const objectY = useTransform(scrollYProgress, [0, 1], [0, -35]);

  return (
    <main className="min-h-screen overflow-hidden bg-black text-white selection:bg-white selection:text-black">
      <section ref={container} className="relative h-[105vh] min-h-[720px] overflow-hidden bg-black sm:h-[112vh] md:h-[118vh]">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_50%_46%,rgba(65,65,255,0.18),transparent_28%),radial-gradient(circle_at_58%_52%,rgba(255,30,125,0.13),transparent_26%),linear-gradient(180deg,#000_0%,#050505_58%,#000_100%)]" />
        <div className="absolute inset-0 opacity-[0.075] [background-image:linear-gradient(rgba(255,255,255,.8)_1px,transparent_1px)] [background-size:100%_4px]" />
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,transparent_0%,#000_78%)]" />

        <motion.nav
          initial={{ opacity: 0, y: -16 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.9 }}
          className="fixed left-4 right-4 top-4 z-50 flex items-center justify-between gap-3 text-[9px] font-black uppercase tracking-[0.12em] text-white sm:left-auto sm:right-6 sm:top-5 sm:justify-end sm:gap-5 sm:text-[10px] md:right-9 md:gap-6 md:tracking-[0.16em]"
        >
          <span className="hidden h-1.5 w-1.5 rounded-full bg-white sm:block" />
          <a href="#work" className="hover:opacity-60">Realizácie</a>
          <a href="#services" className="hover:opacity-60">Services</a>
          <a href="#contact" className="hover:opacity-60">Contact</a>
        </motion.nav>

        <motion.div style={{ scale: heroScale, opacity: heroOpacity }} className="relative z-10 flex h-screen items-center justify-center">
          <motion.div style={{ y: objectY }} className="absolute inset-0 z-20 flex items-center justify-center overflow-hidden">
            <StatuePhoto />
          </motion.div>

          <motion.div style={{ y: textY }} className="absolute inset-0 z-10 flex flex-col justify-center overflow-hidden pt-16 sm:pt-12 md:pt-10">
            <div className="-ml-[4vw] w-[112vw] select-none sm:-ml-[3vw] sm:w-[110vw]">
              {heroLines.map((line, index) => (
                <motion.div
                  key={line + index}
                  initial={{ opacity: 0, x: index % 2 === 0 ? -80 : 80, filter: "blur(10px)" }}
                  animate={{ opacity: 1, x: 0, filter: "blur(0px)" }}
                  transition={{ duration: 1.1, delay: index * 0.055, ease: [0.16, 1, 0.3, 1] }}
                  className={`font-black uppercase leading-[0.76] tracking-[-0.085em] text-[#cfcfcf] ${
                    index % 2 === 0 ? "text-left" : "text-right"
                  } text-[15.2vw] sm:text-[13vw] md:text-[7.3vw]`}
                >
                  {line}
                </motion.div>
              ))}
            </div>
          </motion.div>

          <div className="pointer-events-none absolute inset-x-0 bottom-6 z-30 flex items-end justify-between gap-4 px-5 sm:bottom-8 md:bottom-10 md:px-10">
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.9 }}
              className="max-w-[220px] text-[9px] font-bold uppercase leading-4 tracking-[0.12em] text-white/55 sm:max-w-[260px] sm:text-[11px] sm:leading-5 sm:tracking-[0.16em]"
            >
              ziak.dev — premium web design, social media systems, branding and modern digital visuals.
            </motion.div>
            <motion.a
              href="#contact"
              initial={{ opacity: 0, scale: 0.85 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ delay: 1.05 }}
              className="pointer-events-auto hidden h-20 w-20 items-center justify-center rounded-full border border-white/25 bg-white/10 text-[9px] font-black uppercase tracking-[0.14em] backdrop-blur-xl transition hover:bg-white hover:text-black sm:flex md:h-24 md:w-24 md:text-[10px] md:tracking-[0.16em]"
            >
              Start
            </motion.a>
          </div>
        </motion.div>
      </section>

      <section className="relative z-20 -mt-14 border-t border-white/10 bg-black px-5 py-18 sm:-mt-20 sm:py-24 md:-mt-24 md:px-10">
        <div className="mx-auto grid max-w-[1500px] gap-8 md:grid-cols-[1.25fr_0.75fr] md:items-end md:gap-10">
          <h1 className="text-[13vw] font-black uppercase leading-[0.82] tracking-[-0.075em] text-white sm:text-[11vw] md:text-[8.8vw] md:leading-[0.78] md:tracking-[-0.085em]">
            Premium digital presence for the modern era.
          </h1>
          <p className="max-w-xl text-sm font-medium leading-7 text-white/55 sm:text-base md:text-xl md:leading-8">
            ziak.dev creates premium websites, visual systems and social media experiences for brands that want to stand out online. The goal is simple: make your brand look memorable, modern and expensive.
          </p>
        </div>
      </section>

      <section id="services" className="bg-black px-5 py-18 sm:py-24 md:px-10">
        <div className="mx-auto max-w-[1500px]">
          <div className="mb-10 flex items-end justify-between gap-8">
            <h2 className="text-[13vw] font-black uppercase leading-[0.82] tracking-[-0.075em] sm:text-[11vw] md:text-[7vw] md:leading-[0.78] md:tracking-[-0.085em]">Služby</h2>
            <Sparkle className="hidden text-white/45 md:block" />
          </div>

          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {services.map((service, index) => {
              const Icon = service.icon;
              return (
                <motion.div
                  key={service.title}
                  initial={{ opacity: 0, y: 40 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true, margin: "-120px" }}
                  transition={{ duration: 0.8, delay: index * 0.1 }}
                  whileHover={{ y: -10, scale: 1.015 }}
                  className="group relative min-h-[280px] overflow-hidden rounded-[1.5rem] border border-white/10 bg-[#0b0b0b] p-6 sm:min-h-[320px] sm:rounded-[2rem] md:min-h-[360px] md:p-7"
                >
                  <div className="absolute inset-0 bg-[radial-gradient(circle_at_20%_0%,rgba(58,87,255,0.35),transparent_34%),radial-gradient(circle_at_85%_30%,rgba(255,47,139,0.24),transparent_36%)] opacity-0 transition duration-500 group-hover:opacity-100" />
                  <Icon className="relative mb-14 h-6 w-6 text-white/65 sm:mb-20 md:mb-24 md:h-7 md:w-7" />
                  <h3 className="relative mb-5 text-3xl font-black uppercase leading-[0.9] tracking-[-0.055em] text-white sm:text-4xl md:text-5xl md:leading-[0.85] md:tracking-[-0.06em]">
                    {service.title}
                  </h3>
                  <p className="relative max-w-sm text-sm font-medium leading-6 text-white/52 md:text-base md:leading-7">{service.text}</p>
                </motion.div>
              );
            })}
          </div>
        </div>
      </section>

      <section id="work" className="bg-black px-5 py-18 sm:py-24 md:px-10">
        <div className="mx-auto max-w-[1500px]">
          <p className="mb-8 text-[11px] font-black uppercase tracking-[0.28em] text-white/45">Selected Projects</p>
          <div className="space-y-4">
            {projects.map((project, index) => (
              <motion.div
                key={project}
                initial={{ opacity: 0, x: index % 2 === 0 ? -70 : 70 }}
                whileInView={{ opacity: 1, x: 0 }}
                viewport={{ once: true, margin: "-120px" }}
                transition={{ duration: 0.9, ease: [0.16, 1, 0.3, 1] }}
                className="group relative flex min-h-[190px] items-end overflow-hidden rounded-[1.5rem] border border-white/10 bg-[#080808] p-6 sm:min-h-[230px] sm:rounded-[2rem] md:min-h-[270px] md:p-10"
              >
                <div className="absolute inset-0 bg-[linear-gradient(120deg,#101010,#000),radial-gradient(circle_at_35%_30%,rgba(255,255,255,0.18),transparent_20%)] transition duration-700 group-hover:scale-105" />
                <div className="absolute right-7 top-7 flex h-12 w-12 items-center justify-center rounded-full border border-white/15 bg-white/5 transition group-hover:bg-white group-hover:text-black">
                  <ArrowUpRight size={18} />
                </div>
                <h3 className="relative max-w-5xl text-[11vw] font-black uppercase leading-[0.82] tracking-[-0.075em] text-[#d5d5d5] sm:text-[9vw] md:text-[6vw] md:leading-[0.78] md:tracking-[-0.085em]">
                  {project}
                </h3>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      <section id="contact" className="relative bg-black px-5 py-24 text-center sm:py-32 md:px-10">
        <div className="absolute left-1/2 top-12 h-[320px] w-[320px] -translate-x-1/2 rounded-full bg-white/10 blur-3xl" />
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(58,87,255,0.18),transparent_32%),radial-gradient(circle_at_60%_45%,rgba(255,47,139,0.13),transparent_30%)]" />
        <div className="relative mx-auto max-w-6xl">
          <p className="mb-7 text-[11px] font-black uppercase tracking-[0.32em] text-white/45">ziak.dev</p>
          <h2 className="text-[13vw] font-black uppercase leading-[0.82] tracking-[-0.075em] text-white sm:text-[11vw] md:text-[8vw] md:leading-[0.76] md:tracking-[-0.09em]">
            Build a brand people remember.
          </h2>
          <div className="mt-14 flex flex-col items-center gap-5">
          <a
            href="mailto:ziakmarek@outlook.sk"
            className="inline-flex max-w-full items-center gap-3 rounded-full border border-white/20 bg-white px-6 py-4 text-[10px] font-black uppercase tracking-[0.14em] text-black transition hover:scale-105 sm:px-8 sm:py-5 sm:text-xs sm:tracking-[0.22em]"
          >
            ziakmarek@outlook.sk <ArrowUpRight size={18} />
          </a>

          <a
            href="tel:+421949427120"
            className="text-xs font-bold tracking-[0.14em] text-white/58 transition hover:text-white sm:text-sm sm:tracking-[0.18em]"
          >
            +421 949 427 120
          </a>
        </div>
        </div>
      </section>
    </main>
  );
}
