<script setup lang="ts">
import { useRouter } from 'vue-router';
import { IonPage, IonContent } from '@ionic/vue';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import EcloreButton from '@/components/EcloreButton.vue';

const router = useRouter();

const privileges = [
  {
    title: 'Personal Advisory',
    desc: 'Direct consultation with our principal gemologists for rare stone curation, provenance tracing, and bespoke commissions.',
    img: '/assets/vip-membership-6.webp',
  },
  {
    title: 'Private Salon Viewings',
    desc: 'Unrestricted after-hours access to our secluded high jewellery suites in Manila, Paris, London, and Tokyo.',
    img: '/assets/vip-membership-1.webp',
  },
  {
    title: 'Maison Collaborations',
    desc: 'Privileged access to limited editions developed in quiet synergy with premier European luxury ateliers.',
    img: '/assets/vip-membership-3.webp',
  },
  {
    title: 'Invitational Gatherings',
    desc: 'Intimate annual salon dinners, private collector vernissages, and first access to high joaillerie debuts.',
    img: '/assets/vip-membership-5.webp',
  },
];

const marqueeImages = [
  '/assets/vip-membership-1.webp',
  '/assets/vip-membership-2.webp',
  '/assets/vip-membership-3.webp',
  '/assets/vip-membership-4.webp',
  '/assets/vip-membership-5.webp',
];

const partnerLogos = [
  { name: 'Bottega Veneta', logo: '/assets/partner-bottega-veneta-logo.webp' },
  { name: 'Givenchy', logo: '/assets/partner-givenchy-logo.webp' },
  { name: 'Hermes', logo: '/assets/partner-hermes-logo.webp' },
  { name: 'Versace', logo: '/assets/partner-versace-logo.webp' },
  { name: 'Gucci', logo: '/assets/partner-gucci-logo.webp' },
];
</script>

<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-[#F3EFE7]">
      <EcloreHeader />

      <main class="min-h-screen bg-[#F3EFE7] text-[#1A1A1A]">
        <!-- Hero Section -->
        <section class="relative bg-[#1A1A1A] text-white py-24 sm:py-32 px-6 sm:px-12 border-b border-[#2C2C2C] overflow-hidden">
          <!-- Background Ambient Glow -->
          <div class="absolute inset-0 opacity-20 pointer-events-none">
            <img src="/assets/vip-hero-1.webp" alt="Silk Parchment Atmosphere" class="w-full h-full object-cover filter grayscale contrast-125" />
          </div>

          <div class="relative z-10 max-w-4xl mx-auto text-center space-y-6">
            <nav class="font-azeret text-[10px] sm:text-xs uppercase tracking-[0.4em] text-[#B6965D]">
              Éclore Private Circle &bull; By Invitation
            </nav>
            <h1 class="font-playfair text-4xl sm:text-6xl lg:text-7xl font-light tracking-wide text-white leading-tight">
              A World Beyond Luxury,<br />Reserved For The Few.
            </h1>
            <p class="font-azeret text-xs sm:text-sm text-stone-300 uppercase tracking-widest max-w-2xl mx-auto leading-relaxed">
              An invitation-only circle created for connoisseurs who value quiet sophistication, discretion, and timeless high craftsmanship.
            </p>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-center gap-4">
              <EcloreButton variant="primary" size="md" @click="router.push('/appointments')">
                Request A Private Consultation
              </EcloreButton>
              <EcloreButton variant="outline" size="md" class="border-white text-white hover:bg-white hover:text-black" @click="router.push('/contact')">
                Inquire with Concierge
              </EcloreButton>
            </div>
          </div>
        </section>

        <!-- Founder's Letter & Invitation Card Section -->
        <section class="py-20 sm:py-28 px-4 sm:px-8 max-w-6xl mx-auto">
          <div class="bg-[#FAF8F5] border border-[#E2DDD5] p-8 sm:p-14 shadow-sm grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Left Portrait Polaroid -->
            <div class="lg:col-span-5 flex flex-col items-center">
              <div class="bg-white p-4 pb-8 border border-[#E2DDD5] shadow-lg rotate-[-1deg] max-w-xs w-full transition-transform hover:rotate-0 duration-500">
                <div class="aspect-[4/5] overflow-hidden bg-stone-200">
                  <img src="/assets/vip-ceo.webp" alt="Founder Portrait" class="w-full h-full object-cover" />
                </div>
                <div class="pt-4 text-center font-azeret text-[10px] uppercase tracking-[0.25em] text-[#767676]">
                  A House Of Timeless Beauty
                </div>
              </div>
            </div>

            <!-- Right Invitation Parchment -->
            <div class="lg:col-span-7 space-y-6">
              <span class="font-azeret text-[10px] uppercase tracking-[0.3em] text-[#B6965D] font-bold block">
                The Founder's Invitation
              </span>
              <blockquote class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A] italic leading-relaxed font-light">
                &ldquo;It is our utmost privilege to invite you to the Éclore Private Circle &mdash; a confidential sanctuary created for patrons who cherish eternity over ephemera.&rdquo;
              </blockquote>
              <div class="pt-2">
                <p class="font-playfair text-xl text-[#B6965D]">Charlotte Marais</p>
                <p class="font-azeret text-[10px] uppercase tracking-widest text-[#767676]">Creative Director &bull; Maison Éclore</p>
              </div>

              <div class="pt-6 border-t border-[#E2DDD5] flex flex-wrap gap-6 font-azeret text-[10px] uppercase tracking-[0.25em] text-stone-500">
                <span>TIMELESS.</span>
                <span>PERSONAL.</span>
                <span>PARIS &bull; MANILA.</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Four Pillars of Circle Privilege -->
        <section class="py-16 sm:py-24 bg-[#FAF8F5] border-y border-[#E2DDD5]">
          <div class="max-w-6xl mx-auto px-4 sm:px-8 space-y-16">
            <div class="text-center space-y-3 max-w-2xl mx-auto">
              <span class="font-azeret text-[10px] uppercase tracking-[0.3em] text-[#B6965D] font-bold">
                Privileges & Protocol
              </span>
              <h2 class="font-playfair text-3xl sm:text-5xl text-[#1A1A1A] font-light">
                Circle Privileges
              </h2>
              <p class="font-azeret text-xs text-[#767676] uppercase tracking-widest leading-relaxed">
                Elevated privileges designed for the discerning collector.
              </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 sm:gap-12">
              <article
                v-for="(priv, idx) in privileges"
                :key="idx"
                class="bg-white border border-[#E2DDD5] p-6 sm:p-8 space-y-5 shadow-xs flex flex-col justify-between group hover:border-[#B6965D] transition-colors"
              >
                <div class="aspect-[16/10] overflow-hidden bg-stone-100">
                  <img
                    :src="priv.img"
                    :alt="priv.title"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                  />
                </div>
                <div class="space-y-2">
                  <h3 class="font-playfair text-2xl text-[#1A1A1A] font-medium">{{ priv.title }}</h3>
                  <p class="font-azeret text-xs text-stone-600 leading-relaxed font-light">
                    {{ priv.desc }}
                  </p>
                </div>
                <div class="pt-2">
                  <button
                    type="button"
                    class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D] group-hover:text-[#1A1A1A] transition-colors font-semibold"
                    @click="router.push('/appointments')"
                  >
                    Arrange Experience &rarr;
                  </button>
                </div>
              </article>
            </div>
          </div>
        </section>

        <!-- Lifestyle Gallery Marquee -->
        <section class="py-20 bg-[#1A1A1A] text-white overflow-hidden space-y-10">
          <div class="text-center space-y-2 px-4">
            <span class="font-azeret text-[10px] uppercase tracking-[0.3em] text-[#B6965D]">
              Atelier Moments
            </span>
            <h2 class="font-playfair text-2xl sm:text-4xl text-white font-light">
              Haute Joaillerie &bull; In Situ
            </h2>
          </div>

          <!-- Continuous Infinite Luxury Marquee (from Main Web) -->
          <div class="relative w-full overflow-hidden py-2">
            <div class="animate-marquee flex gap-6 hover:[animation-play-state:paused]">
              <div
                v-for="(img, idx) in [...marqueeImages, ...marqueeImages]"
                :key="idx"
                class="w-64 sm:w-80 flex-shrink-0 aspect-[4/5] bg-stone-900 border border-stone-800 overflow-hidden shadow-xl"
              >
                <img :src="img" alt="VIP Lifestyle" class="w-full h-full object-cover grayscale-[20%] hover:grayscale-0 transition-all duration-700 select-none" />
              </div>
            </div>
          </div>
        </section>

        <!-- Partners & Friends Section -->
        <section class="py-20 sm:py-28 px-4 sm:px-8 max-w-5xl mx-auto text-center space-y-12">
          <div class="space-y-3">
            <span class="font-azeret text-[10px] uppercase tracking-[0.3em] text-[#B6965D] font-bold">
              Affiliated Ateliers
            </span>
            <h2 class="font-playfair text-3xl sm:text-4xl text-[#1A1A1A]">
              Partners & Friends
            </h2>
            <p class="font-azeret text-xs text-stone-500 uppercase tracking-widest max-w-lg mx-auto leading-relaxed">
              Our partners share the same quiet elegance. Together, we shape the language of modern luxury.
            </p>
          </div>

          <div class="flex flex-wrap justify-center items-center gap-6 sm:gap-8">
            <div
              v-for="p in partnerLogos"
              :key="p.name"
              class="w-28 h-28 sm:w-36 sm:h-36 bg-white border border-[#E2DDD5] flex items-center justify-center p-6 shadow-xs hover:border-[#B6965D] transition-colors"
            >
              <img :src="p.logo" :alt="p.name" class="max-w-full max-h-full object-contain opacity-70 hover:opacity-100 transition-opacity" />
            </div>
          </div>
        </section>

        <!-- Access & Membership CTA -->
        <section class="bg-white border-t border-[#E2DDD5] py-20 px-6 sm:px-12 text-center">
          <div class="max-w-3xl mx-auto space-y-6">
            <span class="font-azeret text-[10px] uppercase tracking-[0.35em] text-[#B6965D] font-bold">
              Membership Protocol
            </span>
            <h2 class="font-playfair text-3xl sm:text-5xl text-[#1A1A1A] font-light">
              Admission to the Circle
            </h2>
            <p class="font-azeret text-xs sm:text-sm text-stone-600 leading-relaxed font-light max-w-xl mx-auto">
              Membership to the Éclore Private Circle is extended personally following an acquisition or private consultation at any of our flagship salon locations.
            </p>
            <div class="pt-4 flex flex-col sm:flex-row justify-center gap-4">
              <EcloreButton variant="primary" size="md" @click="router.push('/appointments')">
                Schedule Boutique Consultation
              </EcloreButton>
              <EcloreButton variant="outline" size="md" @click="router.push('/catalogue')">
                Explore The High Collections
              </EcloreButton>
            </div>
          </div>
        </section>
      </main>

      <EcloreFooter />
    </ion-content>
  </ion-page>
</template>
