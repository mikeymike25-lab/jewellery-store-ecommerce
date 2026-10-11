<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useAuthStore } from '@/stores/auth.store';
import { fetchReviews, submitReview, type ReviewItem } from '@/api/reviews.api';
import EcloreButton from '@/components/EcloreButton.vue';
import EcloreModal from '@/components/EcloreModal.vue';

interface Props {
  productId: number;
  productName: string;
}

const props = defineProps<Props>();

const authStore = useAuthStore();

const reviews = ref<ReviewItem[]>([]);
const averageRating = ref<number>(5.0);
const totalReviews = ref<number>(0);
const loading = ref(false);
const submitting = ref(false);
const submitError = ref<string | null>(null);
const submitSuccessMessage = ref<string | null>(null);

const isModalOpen = ref(false);
const newTitle = ref('');
const newRating = ref(5);
const newComment = ref('');

// Computed breakdown
const starCounts = computed(() => {
  const counts = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 };
  reviews.value.forEach((r) => {
    const star = Math.min(5, Math.max(1, Math.round(r.rating))) as 1 | 2 | 3 | 4 | 5;
    counts[star] = (counts[star] || 0) + 1;
  });
  return counts;
});

const starPercentages = computed(() => {
  const total = reviews.value.length || 1;
  return {
    5: Math.round(((starCounts.value[5] || 0) / total) * 100),
    4: Math.round(((starCounts.value[4] || 0) / total) * 100),
    3: Math.round(((starCounts.value[3] || 0) / total) * 100),
    2: Math.round(((starCounts.value[2] || 0) / total) * 100),
    1: Math.round(((starCounts.value[1] || 0) / total) * 100),
  };
});

async function loadReviews() {
  if (!props.productId) return;
  loading.value = true;
  try {
    const res = await fetchReviews(props.productId);
    reviews.value = res.reviews?.data || [];
    averageRating.value = Number(res.average_rating) || 5.0;
    totalReviews.value = Number(res.total_reviews) || reviews.value.length;
  } catch (err) {
    console.warn('Failed to load reviews from API, using default display:', err);
  } finally {
    loading.value = false;
  }
}

function openReviewModal() {
  if (!authStore.isAuthenticated) {
    authStore.setIntendedRoute(window.location.pathname);
    submitError.value = 'Please sign in to your Éclore account to submit a verified patron review.';
  } else {
    submitError.value = null;
  }
  submitSuccessMessage.value = null;
  isModalOpen.value = true;
}

async function handleSubmitReview() {
  if (!newComment.value || newComment.value.trim().length < 10) {
    submitError.value = 'Please write at least 10 characters describing your experience.';
    return;
  }

  if (!authStore.isAuthenticated) {
    submitError.value = 'You must be signed in to submit a review.';
    return;
  }

  submitting.value = true;
  submitError.value = null;

  try {
    const res = await submitReview({
      product_id: props.productId,
      rating: newRating.value,
      title: newTitle.value || undefined,
      review: newComment.value.trim(),
    });

    submitSuccessMessage.value = res.message || 'Thank you! Your testimonial has been submitted for curation approval.';
    
    // Add locally to the reviews list if returned
    if (res.review) {
      reviews.value.unshift(res.review);
      totalReviews.value += 1;
    }

    setTimeout(() => {
      isModalOpen.value = false;
      newTitle.value = '';
      newComment.value = '';
      submitSuccessMessage.value = null;
    }, 2000);
  } catch (err: any) {
    submitError.value =
      err.response?.data?.message || 'Failed to submit review. Please ensure you have purchased this creation.';
  } finally {
    submitting.value = false;
  }
}

function formatDate(dateStr: string): string {
  if (!dateStr) return '';
  try {
    return new Date(dateStr).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  } catch {
    return dateStr;
  }
}

onMounted(() => {
  loadReviews();
});

watch(
  () => props.productId,
  () => {
    loadReviews();
  }
);
</script>

<template>
  <section class="reviews-section py-12 border-t border-[#E2DDD5]">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center pb-8 border-b border-[#E2DDD5] gap-6">
      <div>
        <span class="font-azeret text-xs uppercase tracking-[0.25em] text-[#B6965D]">Atelier Testimonials</span>
        <h2 class="font-playfair text-3xl sm:text-4xl text-[#1A1A1A] mt-1">Client Reviews</h2>
      </div>

      <EcloreButton variant="outline" size="sm" @click="openReviewModal">
        Write A Review
      </EcloreButton>
    </div>

    <!-- Rating Summary Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 py-8 border-b border-[#E2DDD5] items-center">
      <div class="text-center md:text-left">
        <div class="font-playfair text-6xl text-[#1A1A1A] font-normal">
          {{ Number(averageRating).toFixed(1) }}
        </div>
        <div class="flex justify-center md:justify-start text-[#B6965D] mt-2 space-x-1">
          <svg v-for="i in 5" :key="i" class="w-4 h-4 fill-current" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        </div>
        <p class="font-azeret text-[10px] text-stone-500 uppercase tracking-widest mt-2">
          Based on {{ totalReviews }} verified experience{{ totalReviews === 1 ? '' : 's' }}
        </p>
      </div>

      <!-- Rating Bars -->
      <div class="md:col-span-2 space-y-2 font-azeret text-xs">
        <div v-for="star in [5, 4, 3, 2, 1] as const" :key="star" class="flex items-center gap-3">
          <span class="w-8 text-stone-600">{{ star }} ★</span>
          <div class="flex-grow bg-[#EBE6DC] h-2 rounded-full overflow-hidden">
            <div
              class="bg-[#B6965D] h-full transition-all duration-500"
              :style="{ width: `${starPercentages[star]}%` }"
            ></div>
          </div>
          <span class="w-8 text-right text-stone-500">{{ starPercentages[star] }}%</span>
        </div>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="py-12 space-y-4">
      <div v-for="i in 2" :key="i" class="animate-pulse space-y-2 py-4 border-b border-[#E2DDD5]/60">
        <div class="h-4 bg-[#E2DDD5]/50 w-1/4 rounded"></div>
        <div class="h-3 bg-[#E2DDD5]/40 w-full rounded"></div>
        <div class="h-3 bg-[#E2DDD5]/30 w-3/4 rounded"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="reviews.length === 0"
      class="py-16 text-center space-y-3 bg-[#FAF8F5] border border-[#E2DDD5] my-6 p-8"
    >
      <div class="w-12 h-12 mx-auto rounded-full bg-[#B6965D]/10 flex items-center justify-center text-[#B6965D]">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
        </svg>
      </div>
      <p class="font-playfair text-xl text-[#1A1A1A]">Be the First to Review This Creation</p>
      <p class="font-azeret text-xs text-[#767676] max-w-md mx-auto">
        Acquired this piece? Share your impressions on the cut, weight, and luster with our discerning clientele.
      </p>
      <div class="pt-2">
        <EcloreButton variant="outline" size="sm" @click="openReviewModal">
          Submit A Testimonial
        </EcloreButton>
      </div>
    </div>

    <!-- Review Cards -->
    <div v-else class="divide-y divide-[#E2DDD5]">
      <div v-for="review in reviews" :key="review.id" class="py-8 space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <span class="font-playfair text-lg text-[#1A1A1A]">
              {{ review.user?.name || review.author_name || 'Éclore Patron' }}
            </span>
            <span
              v-if="review.is_verified_purchase"
              class="inline-flex items-center text-[10px] font-azeret text-[#B6965D] uppercase tracking-wider bg-[#B6965D]/10 px-2 py-0.5 rounded"
            >
              Verified Patron
            </span>
            <span
              v-if="!review.is_approved"
              class="inline-flex items-center text-[9px] font-azeret text-amber-700 uppercase tracking-wider bg-amber-50 px-2 py-0.5 rounded border border-amber-200"
            >
              Pending Curation
            </span>
          </div>
          <span class="font-azeret text-xs text-stone-400">
            {{ formatDate(review.created_at) }}
          </span>
        </div>

        <div class="flex text-[#B6965D] space-x-1">
          <svg v-for="i in review.rating" :key="i" class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        </div>

        <h4 v-if="review.title" class="font-playfair text-base font-medium text-[#1A1A1A]">
          {{ review.title }}
        </h4>

        <p class="font-azeret text-xs sm:text-sm text-stone-700 leading-relaxed">
          "{{ review.review }}"
        </p>

        <div v-if="review.item_variant" class="font-azeret text-[10px] text-stone-400 uppercase tracking-widest">
          Acquired: {{ review.item_variant }}
        </div>
      </div>
    </div>

    <!-- Review Modal -->
    <EcloreModal
      :is-open="isModalOpen"
      title="Write A Review"
      subtitle="Share your atelier experience"
      @close="isModalOpen = false"
    >
      <div v-if="submitSuccessMessage" class="text-center py-8 font-azeret text-sm text-[#B6965D] uppercase tracking-widest">
        {{ submitSuccessMessage }}
      </div>

      <div v-else>
        <!-- Notice if not logged in -->
        <div v-if="!authStore.isAuthenticated" class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-900 text-xs font-azeret">
          <p class="font-medium">Sign in required</p>
          <p class="text-[11px] mt-1 text-stone-600">
            Reviews are reserved for verified clients. Please log in before submitting.
          </p>
        </div>

        <div v-if="submitError" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs font-azeret">
          {{ submitError }}
        </div>

        <form class="space-y-4 font-azeret" @submit.prevent="handleSubmitReview">
          <div>
            <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] mb-1">Headline / Title</label>
            <input
              v-model="newTitle"
              type="text"
              placeholder="e.g. Magnificence in every detail"
              class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-3 py-2 text-sm focus:outline-none focus:border-[#B6965D]"
            />
          </div>

          <div>
            <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] mb-1">Rating</label>
            <select
              v-model="newRating"
              class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-3 py-2 text-sm focus:outline-none focus:border-[#B6965D]"
            >
              <option :value="5">5 Stars — Exceptional Masterpiece</option>
              <option :value="4">4 Stars — Magnificent</option>
              <option :value="3">3 Stars — Satisfactory</option>
              <option :value="2">2 Stars — Below Expectations</option>
              <option :value="1">1 Star — Unsatisfactory</option>
            </select>
          </div>

          <div>
            <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] mb-1">Testimonial</label>
            <textarea
              v-model="newComment"
              rows="4"
              required
              class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-3 py-2 text-sm focus:outline-none focus:border-[#B6965D]"
              placeholder="Describe the beauty, luster, and feel of this creation (min 10 characters)..."
            ></textarea>
          </div>

          <div class="pt-2 flex justify-end gap-3">
            <EcloreButton variant="outline" size="sm" type="button" @click="isModalOpen = false">
              Cancel
            </EcloreButton>
            <EcloreButton
              variant="primary"
              size="sm"
              type="submit"
              :disabled="submitting || !authStore.isAuthenticated"
            >
              {{ submitting ? 'Submitting...' : 'Submit Review' }}
            </EcloreButton>
          </div>
        </form>
      </div>
    </EcloreModal>
  </section>
</template>
