<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { IonPage, IonContent, onIonViewWillEnter } from '@ionic/vue';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import EcloreButton from '@/components/EcloreButton.vue';
import AccountTab from '@/views/AccountTab.vue';
import { useAuthStore } from '@/stores/auth.store';
import { useOrdersStore } from '@/stores/orders.store';

const authStore = useAuthStore();
const ordersStore = useOrdersStore();
const route = useRoute();
const router = useRouter();

// Modes: 'signin' | 'signup' | 'forgot'
const activeMode = ref<'signin' | 'signup' | 'forgot'>('signin');

// Common feedback state
const generalError = ref('');
const successMessage = ref('');

function clearFeedback() {
  generalError.value = '';
  successMessage.value = '';
  usernameError.value = '';
}

// ====================================================
// 1. Sign In State
// ====================================================
const loginLogin = ref('');
const loginPassword = ref('');
const showLoginPassword = ref(false);

async function handleLogin() {
  clearFeedback();
  try {
    await authStore.login({
      email: loginLogin.value.trim(),
      password: loginPassword.value,
    });
    const intended = authStore.consumeIntendedRoute();
    if (intended) {
      router.push(intended);
    }
  } catch (err: any) {
    if (!err.response) {
      generalError.value = `Unable to connect to the server (${import.meta.env.VITE_API_BASE_URL || 'API'}). Please ensure your device is connected to the same Wi-Fi and the backend server is reachable.`;
    } else {
      generalError.value = err.response?.data?.message || 'Invalid credentials. Please verify your email or password.';
    }
  }
}

// ====================================================
// 2. Sign Up (Create Account) State
// ====================================================
const firstName = ref('');
const lastName = ref('');
const signupEmail = ref('');
const signupUsername = ref('');
const signupPassword = ref('');
const confirmPassword = ref('');
const showSignupPassword = ref(false);
const showConfirmPassword = ref(false);
const agreeTerms = ref(false);

const isCheckingUsername = ref(false);
const isUsernameAvailable = ref<boolean | null>(null);
const usernameError = ref('');
let usernameCheckTimer: any = null;

function handleUsernameInput() {
  isUsernameAvailable.value = null;
  usernameError.value = '';
  clearTimeout(usernameCheckTimer);

  const clean = signupUsername.value.trim();
  if (clean.length === 0) return;

  if (clean.length < 3) {
    usernameError.value = 'Username must be at least 3 characters';
    isUsernameAvailable.value = false;
    return;
  }

  if (/[^a-zA-Z0-9_]/.test(clean)) {
    usernameError.value = 'Letters, numbers, and underscores only (no @ or symbols)';
    isUsernameAvailable.value = false;
    return;
  }

  usernameCheckTimer = setTimeout(async () => {
    isCheckingUsername.value = true;
    try {
      const res = await authStore.checkUsername(clean);
      isUsernameAvailable.value = res.available;
      if (!res.available) {
        usernameError.value = res.message || 'Username is already taken';
      }
    } catch {
      // Ignore network errors during debounce
    } finally {
      isCheckingUsername.value = false;
    }
  }, 400);
}

// Password Strength Evaluation
const ruleLength = computed(() => signupPassword.value.length >= 8);
const ruleLower = computed(() => /[a-z]/.test(signupPassword.value));
const ruleUpper = computed(() => /[A-Z]/.test(signupPassword.value));
const ruleNumber = computed(() => /[0-9]/.test(signupPassword.value));
const ruleSpecial = computed(() => /[^a-zA-Z0-9]/.test(signupPassword.value));

const strengthScore = computed(() => {
  let score = 0;
  if (ruleLength.value) score++;
  if (ruleLower.value) score++;
  if (ruleUpper.value) score++;
  if (ruleNumber.value) score++;
  if (ruleSpecial.value) score++;
  return score;
});

const strengthPercentage = computed(() => (strengthScore.value / 5) * 100);

const strengthLabel = computed(() => {
  if (strengthScore.value <= 1) return { text: 'Very Weak', color: 'text-red-600', bar: 'bg-red-500' };
  if (strengthScore.value === 2) return { text: 'Weak', color: 'text-amber-600', bar: 'bg-amber-500' };
  if (strengthScore.value === 3) return { text: 'Moderate', color: 'text-yellow-600', bar: 'bg-yellow-500' };
  if (strengthScore.value === 4) return { text: 'Good', color: 'text-[#B6965D]', bar: 'bg-[#B6965D]' };
  return { text: 'Strong', color: 'text-emerald-600', bar: 'bg-emerald-600' };
});

const passwordsMatch = computed(() => {
  if (!confirmPassword.value) return null;
  return signupPassword.value === confirmPassword.value;
});

const isSignupValid = computed(() => {
  const username = signupUsername.value.trim();
  return (
    firstName.value.trim().length > 0 &&
    lastName.value.trim().length > 0 &&
    signupEmail.value.includes('@') &&
    username.length >= 3 &&
    /^[a-zA-Z0-9_]+$/.test(username) &&
    isUsernameAvailable.value !== false &&
    ruleLength.value &&
    passwordsMatch.value === true &&
    agreeTerms.value
  );
});

async function handleRegister() {
  clearFeedback();
  if (!isSignupValid.value) return;

  try {
    await authStore.register({
      first_name: firstName.value.trim(),
      last_name: lastName.value.trim(),
      email: signupEmail.value.trim(),
      username: signupUsername.value.trim(),
      password: signupPassword.value,
      password_confirmation: confirmPassword.value,
    });
    const intended = authStore.consumeIntendedRoute();
    if (intended) {
      router.push(intended);
    }
  } catch (err: any) {
    const errorData = err.response?.data;
    if (errorData?.errors && typeof errorData.errors === 'object') {
      const messages = Object.values(errorData.errors).flat().filter(Boolean);
      generalError.value = (messages as string[]).join(' ') || errorData.message || 'Registration failed.';
    } else {
      generalError.value = errorData?.message || authStore.error || 'Registration failed. Please review your information.';
    }
  }
}

// ====================================================
// 3. Forgot Password State
// ====================================================
const forgotLogin = ref('');
const resetToken = ref('');
const resetNewPassword = ref('');
const resetConfirmPassword = ref('');
const isResetMode = ref(false);

async function handleForgotPassword() {
  clearFeedback();
  if (!forgotLogin.value.trim()) return;

  try {
    const res = await authStore.forgotPassword(forgotLogin.value.trim());
    successMessage.value = res.message || 'Password reset link sent to your registered email address.';
  } catch (err: any) {
    generalError.value = err.response?.data?.message || 'No account found with this email or username.';
  }
}

async function handleResetPassword() {
  clearFeedback();
  if (!resetToken.value.trim() || !resetNewPassword.value || resetNewPassword.value !== resetConfirmPassword.value) {
    generalError.value = 'Please provide the token and matching passwords (min 8 characters).';
    return;
  }

  try {
    const res = await authStore.resetPassword({
      token: resetToken.value.trim(),
      password: resetNewPassword.value,
      password_confirmation: resetConfirmPassword.value,
    });
    successMessage.value = res.message || 'Password reset successfully. You may now sign in with your new credentials.';
    isResetMode.value = false;
    activeMode.value = 'signin';
  } catch (err: any) {
    generalError.value = err.response?.data?.message || 'Invalid or expired password reset link.';
  }
}

// Sync mode with route query params
async function initFromRoute() {
  if (route.query.auth_token || (route.query.token && String(route.query.token).includes('|'))) {
    const token = String(route.query.auth_token || route.query.token);
    try {
      await authStore.handleOAuthCallback(token);
      router.replace({ path: '/tabs/account', query: {} });
      successMessage.value = 'Welcome back to Maison Éclore.';
    } catch (err: any) {
      generalError.value = 'Authentication verification failed. Please try signing in again.';
    }
  } else if (route.query.error) {
    generalError.value = decodeURIComponent(String(route.query.error));
  } else if (route.query.mode === 'signup') {
    activeMode.value = 'signup';
  } else if (route.query.mode === 'forgot') {
    activeMode.value = 'forgot';
  } else if (route.query.token) {
    activeMode.value = 'forgot';
    isResetMode.value = true;
    resetToken.value = String(route.query.token);
  }
}

watch(() => route.query, () => {
  initFromRoute();
});

onMounted(() => {
  authStore.init();
  initFromRoute();
});

onIonViewWillEnter(async () => {
  await authStore.init();
  if (authStore.isAuthenticated) {
    await Promise.allSettled([
      authStore.fetchUser(),
      ordersStore.fetchOrders(1),
    ]);
  }
});
</script>

<template>
  <ion-page>
    <ion-content :fullscreen="true">
      <div class="min-h-screen flex flex-col bg-[#F3EFE7] text-[#1A1A1A]">
        <EcloreHeader />

        <main
          class="flex-grow mx-auto px-4 py-10 sm:py-14 w-full"
          :class="authStore.isAuthenticated ? 'max-w-4xl' : 'max-w-2xl'"
        >
          <!-- ---------------------------------------------------- -->
          <!-- AUTHENTICATED STATE: Atelier Customer Portal (Stage 6) -->
          <!-- ---------------------------------------------------- -->
          <AccountTab v-if="authStore.isAuthenticated" />

          <!-- ---------------------------------------------------- -->
          <!-- UNAUTHENTICATED STATE: Client Portal (Sign In / Sign Up / Forgot) -->
          <!-- ---------------------------------------------------- -->
          <div v-else class="bg-white border border-[#E2DDD5] p-6 sm:p-10 space-y-6 shadow-sm">
            <!-- Mode Switcher Tabs (Sign In / Create Account) -->
            <div v-if="activeMode !== 'forgot'" class="flex border-b border-[#E2DDD5] text-center font-azeret text-xs uppercase tracking-widest">
              <button
                type="button"
                class="flex-1 py-3.5 transition-colors relative"
                :class="activeMode === 'signin' ? 'text-[#1A1A1A] font-semibold' : 'text-[#767676] hover:text-[#1A1A1A]'"
                @click="activeMode = 'signin'; clearFeedback()"
              >
                Sign In
                <span
                  v-if="activeMode === 'signin'"
                  class="absolute bottom-0 left-0 right-0 h-[2px] bg-[#B6965D]"
                />
              </button>
              <button
                type="button"
                class="flex-1 py-3.5 transition-colors relative"
                :class="activeMode === 'signup' ? 'text-[#1A1A1A] font-semibold' : 'text-[#767676] hover:text-[#1A1A1A]'"
                @click="activeMode = 'signup'; clearFeedback()"
              >
                Create Account
                <span
                  v-if="activeMode === 'signup'"
                  class="absolute bottom-0 left-0 right-0 h-[2px] bg-[#B6965D]"
                />
              </button>
            </div>

            <!-- Forgot Password Return Breadcrumb -->
            <div v-else class="flex items-center justify-between border-b border-[#E2DDD5] pb-3.5">
              <button
                type="button"
                class="inline-flex items-center text-xs font-azeret uppercase tracking-wider text-[#767676] hover:text-[#B6965D] transition-colors"
                @click="activeMode = 'signin'; clearFeedback()"
              >
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Sign In
              </button>
              <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Atelier Recovery</span>
            </div>

            <!-- Feedback Alerts -->
            <div v-if="generalError" class="p-3.5 text-xs bg-red-50 text-red-700 border border-red-200 font-azeret flex items-start space-x-2">
              <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <span>{{ generalError }}</span>
            </div>
            <div v-if="successMessage" class="p-3.5 text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 font-azeret flex items-start space-x-2">
              <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>{{ successMessage }}</span>
            </div>

            <!-- =================================================== -->
            <!-- TAB 1: SIGN IN FORM -->
            <!-- =================================================== -->
            <div v-if="activeMode === 'signin'" class="space-y-6">
              <div class="text-center pb-2">
                <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Client Portal</span>
                <h2 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A] mt-1">Sign In to Éclore</h2>
                <p class="font-azeret text-xs text-[#767676] mt-1">Access your bespoke acquisitions, orders, and curated wishlist.</p>
              </div>

              <form class="space-y-5 font-azeret" @submit.prevent="handleLogin">
                <div>
                  <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
                    Email Address or Username
                  </label>
                  <input
                    v-model="loginLogin"
                    type="text"
                    required
                    autocomplete="username"
                    class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
                    placeholder="client@eclore.com or username"
                  />
                </div>

                <div>
                  <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs uppercase tracking-wider text-[#1A1A1A] font-medium">
                      Password
                    </label>
                    <button
                      type="button"
                      class="text-xs text-[#B6965D] hover:underline"
                      @click="activeMode = 'forgot'; clearFeedback()"
                    >
                      Forgot password?
                    </button>
                  </div>
                  <div class="relative">
                    <input
                      v-model="loginPassword"
                      :type="showLoginPassword ? 'text' : 'password'"
                      required
                      autocomplete="current-password"
                      class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 pr-11 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
                      placeholder="••••••••••••"
                    />
                    <button
                      type="button"
                      class="absolute inset-y-0 right-0 px-3.5 flex items-center text-[#767676] hover:text-[#1A1A1A]"
                      @click="showLoginPassword = !showLoginPassword"
                      tabindex="-1"
                    >
                      <svg v-if="showLoginPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </button>
                  </div>
                </div>

                <div class="pt-3">
                  <EcloreButton
                    type="submit"
                    variant="primary"
                    size="md"
                    block
                    :loading="authStore.loading"
                  >
                    Sign In
                  </EcloreButton>
                </div>
              </form>

              <div class="text-center pt-2 font-azeret text-xs text-[#767676]">
                Don't have an account?
                <button
                  type="button"
                  class="text-[#B6965D] hover:underline font-medium ml-1"
                  @click="activeMode = 'signup'; clearFeedback()"
                >
                  Create one here
                </button>
              </div>
            </div>

            <!-- =================================================== -->
            <!-- TAB 2: SIGN UP (CREATE ACCOUNT) FORM -->
            <!-- =================================================== -->
            <div v-else-if="activeMode === 'signup'" class="space-y-6">
              <div class="text-center pb-2">
                <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Maison Membership</span>
                <h2 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A] mt-1">Create Your Account</h2>
                <p class="font-azeret text-xs text-[#767676] mt-1">Join Éclore for exclusive creations, personal curation, and member privileges.</p>
              </div>

              <form class="space-y-5 font-azeret" @submit.prevent="handleRegister">
                <!-- First & Last Name -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
                      First Name
                    </label>
                    <input
                      v-model="firstName"
                      type="text"
                      required
                      class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
                      placeholder="Claire"
                    />
                  </div>
                  <div>
                    <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
                      Last Name
                    </label>
                    <input
                      v-model="lastName"
                      type="text"
                      required
                      class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
                      placeholder="Laurent"
                    />
                  </div>
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
                    Email Address
                  </label>
                  <input
                    v-model="signupEmail"
                    type="email"
                    required
                    autocomplete="email"
                    class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
                    placeholder="client@eclore.com"
                  />
                </div>

                <!-- Username -->
                <div>
                  <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs uppercase tracking-wider text-[#1A1A1A] font-medium">
                      Username
                    </label>
                    <span v-if="isCheckingUsername" class="text-[10px] text-[#767676]">Checking...</span>
                    <span v-else-if="isUsernameAvailable === true" class="text-[10px] text-emerald-600 font-medium">✓ Available</span>
                    <span v-else-if="usernameError" class="text-[10px] text-red-600">{{ usernameError }}</span>
                  </div>
                  <input
                    v-model="signupUsername"
                    type="text"
                    required
                    minlength="3"
                    maxlength="30"
                    autocomplete="username"
                    class="w-full bg-[#FAF8F5] border px-4 py-3 min-h-[48px] text-sm focus:outline-none transition-colors"
                    :class="usernameError ? 'border-red-400 focus:border-red-500' : 'border-[#E2DDD5] focus:border-[#B6965D]'"
                    placeholder="claire_laurent"
                    @input="handleUsernameInput"
                  />
                </div>

                <!-- Password -->
                <div>
                  <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
                    Password
                  </label>
                  <div class="relative">
                    <input
                      v-model="signupPassword"
                      :type="showSignupPassword ? 'text' : 'password'"
                      required
                      minlength="8"
                      autocomplete="new-password"
                      class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 pr-11 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
                      placeholder="At least 8 characters"
                    />
                    <button
                      type="button"
                      class="absolute inset-y-0 right-0 px-3.5 flex items-center text-[#767676] hover:text-[#1A1A1A]"
                      @click="showSignupPassword = !showSignupPassword"
                      tabindex="-1"
                    >
                      <svg v-if="showSignupPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </button>
                  </div>

                  <!-- Password Strength Meter -->
                  <div v-if="signupPassword.length > 0" class="mt-2.5 space-y-2 bg-[#FAF8F5] p-3 border border-[#E2DDD5]">
                    <div class="flex items-center justify-between text-xs">
                      <span class="text-[#767676]">Security Level:</span>
                      <span :class="strengthLabel.color" class="font-medium">{{ strengthLabel.text }}</span>
                    </div>
                    <div class="w-full bg-[#E2DDD5] h-1.5 rounded-full overflow-hidden">
                      <div
                        class="h-full transition-all duration-300"
                        :class="strengthLabel.bar"
                        :style="{ width: `${strengthPercentage}%` }"
                      />
                    </div>
                    <div class="grid grid-cols-2 gap-1.5 text-[10px] text-[#767676] pt-1">
                      <span :class="ruleLength ? 'text-emerald-600 font-medium' : ''">
                        {{ ruleLength ? '✓' : '•' }} 8+ Characters
                      </span>
                      <span :class="ruleNumber ? 'text-emerald-600 font-medium' : ''">
                        {{ ruleNumber ? '✓' : '•' }} Number
                      </span>
                      <span :class="ruleUpper && ruleLower ? 'text-emerald-600 font-medium' : ''">
                        {{ ruleUpper && ruleLower ? '✓' : '•' }} Upper & Lower
                      </span>
                      <span :class="ruleSpecial ? 'text-emerald-600 font-medium' : ''">
                        {{ ruleSpecial ? '✓' : '•' }} Special Symbol
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Confirm Password -->
                <div>
                  <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs uppercase tracking-wider text-[#1A1A1A] font-medium">
                      Confirm Password
                    </label>
                    <span v-if="passwordsMatch === true" class="text-[10px] text-emerald-600 font-medium">✓ Passwords match</span>
                    <span v-else-if="passwordsMatch === false" class="text-[10px] text-red-600">✕ Passwords do not match</span>
                  </div>
                  <div class="relative">
                    <input
                      v-model="confirmPassword"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      required
                      autocomplete="new-password"
                      class="w-full bg-[#FAF8F5] border px-4 py-3 pr-11 min-h-[48px] text-sm focus:outline-none transition-colors"
                      :class="passwordsMatch === false ? 'border-red-400 focus:border-red-500' : 'border-[#E2DDD5] focus:border-[#B6965D]'"
                      placeholder="Repeat password"
                    />
                    <button
                      type="button"
                      class="absolute inset-y-0 right-0 px-3.5 flex items-center text-[#767676] hover:text-[#1A1A1A]"
                      @click="showConfirmPassword = !showConfirmPassword"
                      tabindex="-1"
                    >
                      <svg v-if="showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </button>
                  </div>
                </div>

                <!-- Terms Agreement Checkbox -->
                <div class="flex items-start space-x-2.5 pt-1">
                  <input
                    id="terms-check"
                    v-model="agreeTerms"
                    type="checkbox"
                    required
                    class="mt-1 h-4 w-4 rounded-none border-[#E2DDD5] text-[#B6965D] focus:ring-0"
                  />
                  <label for="terms-check" class="text-xs text-[#767676] leading-relaxed">
                    I agree to the Éclore Maison Terms of Service and Privacy Policy.
                  </label>
                </div>

                <div class="pt-3">
                  <EcloreButton
                    type="submit"
                    variant="primary"
                    size="md"
                    block
                    :disabled="!isSignupValid || authStore.loading"
                    :loading="authStore.loading"
                  >
                    Create Account
                  </EcloreButton>
                </div>
              </form>

              <div class="text-center pt-2 font-azeret text-xs text-[#767676]">
                Already have an account?
                <button
                  type="button"
                  class="text-[#B6965D] hover:underline font-medium ml-1"
                  @click="activeMode = 'signin'; clearFeedback()"
                >
                  Sign In here
                </button>
              </div>
            </div>

            <!-- =================================================== -->
            <!-- TAB 3: FORGOT PASSWORD FORM -->
            <!-- =================================================== -->
            <div v-else-if="activeMode === 'forgot'" class="space-y-6">
              <div class="text-center pb-2">
                <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Atelier Recovery</span>
                <h2 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A] mt-1">Reset Your Password</h2>
                <p class="font-azeret text-xs text-[#767676] mt-1">
                  Enter your registered email address or username to receive a secure link to reset your account credentials.
                </p>
              </div>

              <!-- Request Reset Link Form -->
              <form v-if="!isResetMode" class="space-y-5 font-azeret" @submit.prevent="handleForgotPassword">
                <div>
                  <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
                    Email Address or Username
                  </label>
                  <input
                    v-model="forgotLogin"
                    type="text"
                    required
                    class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
                    placeholder="client@eclore.com or username"
                  />
                </div>

                <div class="pt-3">
                  <EcloreButton
                    type="submit"
                    variant="primary"
                    size="md"
                    block
                    :loading="authStore.loading"
                  >
                    Send Password Reset Link
                  </EcloreButton>
                </div>

                <div class="pt-4 border-t border-[#E2DDD5] text-center">
                  <button
                    type="button"
                    class="text-xs text-[#B6965D] hover:underline"
                    @click="isResetMode = true; clearFeedback()"
                  >
                    Have a reset token? Enter token to reset password directly
                  </button>
                </div>
              </form>

              <!-- Apply Token & Set New Password Form -->
              <form v-else class="space-y-5 font-azeret" @submit.prevent="handleResetPassword">
                <div>
                  <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
                    Reset Token
                  </label>
                  <input
                    v-model="resetToken"
                    type="text"
                    required
                    class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors font-mono"
                    placeholder="Paste the 64-character token from your email"
                  />
                </div>

                <div>
                  <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
                    New Password
                  </label>
                  <input
                    v-model="resetNewPassword"
                    type="password"
                    required
                    minlength="8"
                    class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
                    placeholder="Minimum 8 characters"
                  />
                </div>

                <div>
                  <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
                    Confirm New Password
                  </label>
                  <input
                    v-model="resetConfirmPassword"
                    type="password"
                    required
                    class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
                    placeholder="Re-enter new password"
                  />
                </div>

                <div class="pt-3">
                  <EcloreButton
                    type="submit"
                    variant="primary"
                    size="md"
                    block
                    :loading="authStore.loading"
                  >
                    Update Password
                  </EcloreButton>
                </div>

                <div class="text-center pt-2">
                  <button
                    type="button"
                    class="text-xs text-[#767676] hover:text-[#1A1A1A] underline"
                    @click="isResetMode = false; clearFeedback()"
                  >
                    ← Back to request reset link
                  </button>
                </div>
              </form>
            </div>
          </div>
        </main>

        <EcloreFooter />
      </div>
    </ion-content>
  </ion-page>
</template>
