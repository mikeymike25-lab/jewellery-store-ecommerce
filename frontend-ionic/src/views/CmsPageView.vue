<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { IonPage, IonContent } from '@ionic/vue';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import EcloreButton from '@/components/EcloreButton.vue';
import { apiClient } from '@/api/client';

interface CmsSection {
  heading: string;
  content: string;
}

interface PageData {
  title: string;
  subtitle?: string;
  last_updated?: string;
  sections?: CmsSection[];
  content?: string;
}

const route = useRoute();
const router = useRouter();

const currentSlug = computed(() => {
  // Infer slug from route path if not passed as param
  if (route.params.slug) return route.params.slug as string;
  const path = route.path.replace(/^\//, '');
  const pathMap: Record<string, string> = {
    'terms': 'terms-of-service',
    'privacy': 'privacy-policy',
    'care-guide': 'care',
    'sustainability': 'corporate-responsibility',
  };
  return pathMap[path] || path;
});

const pageData = ref<PageData | null>(null);
const loading = ref(false);
const activeSectionIndex = ref(0);

async function loadPageContent() {
  const slug = currentSlug.value;
  if (!slug) return;
  loading.value = true;

  try {
    const res = await apiClient.get<{ success: boolean; page: PageData }>(`/v1/content/${slug}`);
    pageData.value = res.data?.page || null;
  } catch (err) {
    console.warn(`Failed to load content for ${slug} from API, using fallback:`, err);
    // Generic luxury fallback
    pageData.value = {
      title: slug.replace(/-/g, ' ').toUpperCase(),
      subtitle: 'Official Maison Éclore Institutional Guidelines',
      last_updated: 'January 2026',
      sections: [
        {
          heading: '1. Atelier Commitment',
          content: 'Maison Éclore upholds the highest standards of craftsmanship, ethics, and transparency in all customer engagements.',
        },
        {
          heading: '2. Customer Inquiries',
          content: 'For assistance regarding policies, order tracking, or boutique consultations, our Concierge is at your disposal 7 days a week.',
        },
      ],
    };
  } finally {
    loading.value = false;
  }
}

function scrollToSection(index: number) {
  activeSectionIndex.value = index;
  const el = document.getElementById(`section-${index}`);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' });
  }
}

onMounted(() => {
  loadPageContent();
});

watch(
  () => route.path,
  () => {
    loadPageContent();
  }
);
</script>

<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-[#F3EFE7]">
      <EcloreHeader />

      <main class="min-h-screen bg-[#F3EFE7] text-[#1A1A1A] animate-fade-in">
        <!-- Breadcrumb & Header -->
        <header class="bg-[#1A1A1A] text-white py-16 sm:py-20 px-6 sm:px-12 border-b border-[#2C2C2C]">
          <div class="max-w-5xl mx-auto text-center space-y-4">
            <nav class="font-azeret text-[10px] uppercase tracking-[0.35em] text-[#B6965D] animate-fade-in">
              Maison Éclore &bull; Institutional & Legal Suite
            </nav>
            <h1 class="font-playfair text-3xl sm:text-5xl font-light tracking-wide animate-fade-up">
              {{ pageData?.title || 'Institutional Document' }}
            </h1>
            <p v-if="pageData?.subtitle" class="font-azeret text-xs sm:text-sm text-stone-300 uppercase tracking-widest max-w-xl mx-auto leading-relaxed animate-fade-up animation-delay-100">
              {{ pageData.subtitle }}
            </p>
            <p v-if="pageData?.last_updated" class="font-azeret text-[10px] text-stone-400 uppercase tracking-wider pt-2 animate-fade-up animation-delay-200">
              Effective Date &bull; {{ pageData.last_updated }}
            </p>
          </div>
        </header>

        <!-- Loading State -->
        <div v-if="loading" class="max-w-5xl mx-auto px-4 py-16 space-y-6 animate-pulse">
          <div class="h-6 bg-stone-300 w-1/4 rounded"></div>
          <div class="h-4 bg-stone-300 w-full rounded"></div>
          <div class="h-4 bg-stone-300 w-5/6 rounded"></div>
        </div>

        <!-- Main Content with Sticky Sidebar -->
        <div v-else-if="pageData" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            <!-- Left Sticky Table of Contents -->
            <aside v-if="pageData.sections && pageData.sections.length > 0" class="lg:col-span-4 lg:sticky lg:top-24">
              <div class="bg-[#FAF8F5] border border-[#E2DDD5] p-6 space-y-4 shadow-sm">
                <span class="font-azeret text-[10px] uppercase tracking-[0.25em] text-[#B6965D] font-bold block">
                  Table of Contents
                </span>
                <nav class="space-y-2 font-azeret text-xs">
                  <button
                    v-for="(sec, idx) in pageData.sections"
                    :key="idx"
                    type="button"
                    class="block w-full text-left py-2 px-3 rounded transition-all text-stone-600 hover:text-[#1A1A1A] hover:bg-[#F3EFE7]"
                    :class="activeSectionIndex === idx ? 'bg-[#F3EFE7] font-semibold text-[#1A1A1A] border-l-2 border-[#B6965D]' : ''"
                    @click="scrollToSection(idx)"
                  >
                    {{ sec.heading }}
                  </button>
                </nav>

                <div class="pt-4 border-t border-[#E2DDD5] space-y-2 font-azeret text-[11px] text-stone-500">
                  <p>Need specialized legal assistance?</p>
                  <EcloreButton variant="outline" size="sm" class="w-full text-center" @click="router.push('/help')">
                    Contact Atelier Legal
                  </EcloreButton>
                </div>
              </div>
            </aside>

            <!-- Right Content Body -->
            <div :class="pageData.sections && pageData.sections.length > 0 ? 'lg:col-span-8' : 'lg:col-span-12'" class="space-y-10">
              <!-- If structured sections exist -->
              <div v-if="pageData.sections && pageData.sections.length > 0" class="space-y-10">
                <section
                  v-for="(sec, idx) in pageData.sections"
                  :id="`section-${idx}`"
                  :key="idx"
                  class="bg-[#FAF8F5] border border-[#E2DDD5] p-6 sm:p-8 space-y-4 shadow-sm scroll-mt-28"
                >
                  <h2 class="font-playfair text-xl sm:text-2xl text-[#1A1A1A] font-semibold pb-2 border-b border-[#E2DDD5]/60">
                    {{ sec.heading }}
                  </h2>
                  <p class="font-azeret text-xs sm:text-sm text-stone-700 leading-relaxed font-light whitespace-pre-line">
                    {{ sec.content }}
                  </p>
                </section>
              </div>

              <!-- Raw HTML Content if present -->
              <div
                v-else-if="pageData.content"
                class="bg-[#FAF8F5] border border-[#E2DDD5] p-6 sm:p-8 prose prose-stone max-w-none font-azeret text-xs sm:text-sm leading-relaxed"
                v-html="pageData.content"
              ></div>
            </div>
          </div>
        </div>
      </main>

      <EcloreFooter />
    </ion-content>
  </ion-page>
</template>
