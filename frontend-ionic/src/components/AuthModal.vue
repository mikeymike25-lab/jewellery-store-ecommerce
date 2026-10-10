<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import EcloreModal from '@/components/EcloreModal.vue';
import EcloreButton from '@/components/EcloreButton.vue';
import { useAuthStore } from '@/stores/auth.store';

interface Props {
  isOpen: boolean;
  initialMode?: 'signin' | 'signup' | 'forgot';
}

const props = withDefaults(defineProps<Props>(), {
  isOpen: false,
  initialMode: 'signin',
});

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'success'): void;
}>();

const authStore = useAuthStore();

// Active mode: 'signin' | 'signup' | 'forgot'
const mode = ref<'signin' | 'signup' | 'forgot'>(props.initialMode);

watch(() => props.initialMode, (newMode) => {
  mode.value = newMode;
});

watch(() => props.isOpen, (open) => {
  if (open) {
    mode.value = props.initialMode;
    clearErrors();
  }
});

// Common state
const generalError = ref('');
const successMessage = ref('');

function clearErrors() {
  generalError.value = '';
  successMessage.value = '';
  usernameError.value = '';
}

// ----------------------------------------------------
// Sign In State
// ----------------------------------------------------
const loginLogin = ref('');
const loginPassword = ref('');
const showLoginPassword = ref(false);

async function handleLogin() {
  clearErrors();
  try {
    await authStore.login({
      email: loginLogin.value,
      password: loginPassword.value,
    });
    emit('success');
    emit('close');
  } catch (err: any) {
    generalError.value = err.response?.data?.message || 'Invalid credentials. Please try again.';
  }
}

// ----------------------------------------------------
// Sign Up State
// ----------------------------------------------------
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
  if (clean.length < 3) return;

  usernameCheckTimer = setTimeout(async () => {
    isCheckingUsername.value = true;
    try {
      const res = await authStore.checkUsername(clean);
      isUsernameAvailable.value = res.available;
      if (!res.available) {
        usernameError.value = res.message || 'Username is already taken';
      }
    } catch {
      // Ignored
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

const isSignupFormValid = computed(() => {
  return (
    firstName.value.trim().length > 0 &&
    lastName.value.trim().length > 0 &&
    signupEmail.value.includes('@') &&
    signupUsername.value.trim().length >= 3 &&
    isUsernameAvailable.value !== false &&
    ruleLength.value &&
    passwordsMatch.value === true &&
    agreeTerms.value
  );
});

async function handleRegister() {
  clearErrors();
  if (!isSignupFormValid.value) return;

  try {
    await authStore.register({
      first_name: firstName.value.trim(),
      last_name: lastName.value.trim(),
      email: signupEmail.value.trim(),
      username: signupUsername.value.trim(),
      password: signupPassword.value,
      password_confirmation: confirmPassword.value,
    });
    emit('success');
    emit('close');
  } catch (err: any) {
    generalError.value = err.response?.data?.message || 'Registration failed. Please review your information.';
  }
}

// ----------------------------------------------------
// Forgot Password State
// ----------------------------------------------------
const forgotLogin = ref('');
const resetToken = ref('');
const resetNewPassword = ref('');
const resetConfirmPassword = ref('');
const isResetMode = ref(false);

async function handleForgotPassword() {
  clearErrors();
  if (!forgotLogin.value.trim()) return;

  try {
    const res = await authStore.forgotPassword(forgotLogin.value.trim());
    successMessage.value = res.message || 'Password reset link sent to your email address.';
  } catch (err: any) {
    generalError.value = err.response?.data?.message || 'Could not find an account with those details.';
  }
}

async function handleResetPassword() {
  clearErrors();
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
    successMessage.value = res.message || 'Password reset successfully. You may now sign in.';
    isResetMode.value = false;
    mode.value = 'signin';
  } catch (err: any) {
    generalError.value = err.response?.data?.message || 'Invalid or expired token.';
  }
}
</script>

<template>
  <EcloreModal
    :is-open="isOpen"
    max-width="max-w-md"
    @close="emit('close')"
  >
    <div class="space-y-6">
      <!-- Mode Tabs (Sign In vs Create Account) -->
      <div v-if="mode !== 'forgot'" class="flex border-b border-[#E2DDD5] text-center font-azeret text-xs uppercase tracking-widest">
        <button
          type="button"
          class="flex-1 py-3 transition-colors relative"
          :class="mode === 'signin' ? 'text-[#1A1A1A] font-semibold' : 'text-[#767676] hover:text-[#1A1A1A]'"
          @click="mode = 'signin'; clearErrors()"
        >
          Sign In
          <span
            v-if="mode === 'signin'"
            class="absolute bottom-0 left-0 right-0 h-[2px] bg-[#B6965D]"
          />
        </button>
        <button
          type="button"
          class="flex-1 py-3 transition-colors relative"
          :class="mode === 'signup' ? 'text-[#1A1A1A] font-semibold' : 'text-[#767676] hover:text-[#1A1A1A]'"
          @click="mode = 'signup'; clearErrors()"
        >
          Create Account
          <span
            v-if="mode === 'signup'"
            class="absolute bottom-0 left-0 right-0 h-[2px] bg-[#B6965D]"
          />
        </button>
      </div>

      <!-- Forgot Password Breadcrumb -->
      <div v-else class="flex items-center justify-between border-b border-[#E2DDD5] pb-3">
        <button
          type="button"
          class="inline-flex items-center text-xs font-azeret uppercase tracking-wider text-[#767676] hover:text-[#B6965D] transition-colors"
          @click="mode = 'signin'; clearErrors()"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
          </svg>
          Back to Sign In
        </button>
        <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Recovery</span>
      </div>

      <!-- Feedback Banners -->
      <div v-if="generalError" class="p-3 text-xs bg-red-50 text-red-700 border border-red-200 font-azeret">
        {{ generalError }}
      </div>
      <div v-if="successMessage" class="p-3 text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 font-azeret">
        {{ successMessage }}
      </div>

      <!-- ======================================================= -->
      <!-- 1. SIGN IN FORM -->
      <!-- ======================================================= -->
      <form v-if="mode === 'signin'" class="space-y-5 font-azeret" @submit.prevent="handleLogin">
        <div>
          <label class="block text-[11px] uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
            Email or Username
          </label>
          <input
            v-model="loginLogin"
            type="text"
            required
            autocomplete="username"
            class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
            placeholder="client@eclore.com"
          />
        </div>

        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="text-[11px] uppercase tracking-wider text-[#1A1A1A] font-medium">
              Password
            </label>
            <button
              type="button"
              class="text-[11px] text-[#B6965D] hover:underline"
              @click="mode = 'forgot'; clearErrors()"
            >
              Forgot Password?
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
              class="absolute inset-y-0 right-0 px-3 flex items-center text-[#767676] hover:text-[#1A1A1A]"
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

        <div class="pt-2">
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

        <div class="text-center pt-2">
          <p class="text-xs text-[#767676]">
            Don't have an account?
            <button
              type="button"
              class="text-[#B6965D] hover:underline font-medium ml-1"
              @click="mode = 'signup'; clearErrors()"
            >
              Create Account
            </button>
          </p>
        </div>
      </form>

      <!-- ======================================================= -->
      <!-- 2. CREATE ACCOUNT (SIGN UP) FORM -->
      <!-- ======================================================= -->
      <form v-else-if="mode === 'signup'" class="space-y-4 font-azeret" @submit.prevent="handleRegister">
        <!-- Name Row -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-[11px] uppercase tracking-wider text-[#1A1A1A] font-medium mb-1">
              First Name
            </label>
            <input
              v-model="firstName"
              type="text"
              required
              class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-3 py-2.5 min-h-[46px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
              placeholder="Claire"
            />
          </div>
          <div>
            <label class="block text-[11px] uppercase tracking-wider text-[#1A1A1A] font-medium mb-1">
              Last Name
            </label>
            <input
              v-model="lastName"
              type="text"
              required
              class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-3 py-2.5 min-h-[46px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
              placeholder="Laurent"
            />
          </div>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-[11px] uppercase tracking-wider text-[#1A1A1A] font-medium mb-1">
            Email Address
          </label>
          <input
            v-model="signupEmail"
            type="email"
            required
            autocomplete="email"
            class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-3 py-2.5 min-h-[46px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
            placeholder="client@eclore.com"
          />
        </div>

        <!-- Username -->
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="text-[11px] uppercase tracking-wider text-[#1A1A1A] font-medium">
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
            class="w-full bg-[#FAF8F5] border px-3 py-2.5 min-h-[46px] text-sm focus:outline-none transition-colors"
            :class="usernameError ? 'border-red-400 focus:border-red-500' : 'border-[#E2DDD5] focus:border-[#B6965D]'"
            placeholder="claire_laurent"
            @input="handleUsernameInput"
          />
        </div>

        <!-- Password -->
        <div>
          <label class="block text-[11px] uppercase tracking-wider text-[#1A1A1A] font-medium mb-1">
            Password
          </label>
          <div class="relative">
            <input
              v-model="signupPassword"
              :type="showSignupPassword ? 'text' : 'password'"
              required
              minlength="8"
              autocomplete="new-password"
              class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-3 py-2.5 pr-10 min-h-[46px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
              placeholder="Min 8 characters"
            />
            <button
              type="button"
              class="absolute inset-y-0 right-0 px-3 flex items-center text-[#767676] hover:text-[#1A1A1A]"
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

          <!-- Password Strength Indicator -->
          <div v-if="signupPassword.length > 0" class="mt-2 space-y-1.5">
            <div class="flex items-center justify-between text-[10px]">
              <span class="text-[#767676]">Strength:</span>
              <span :class="strengthLabel.color" class="font-medium">{{ strengthLabel.text }}</span>
            </div>
            <div class="w-full bg-[#E2DDD5] h-1.5 rounded-full overflow-hidden">
              <div
                class="h-full transition-all duration-300"
                :class="strengthLabel.bar"
                :style="{ width: `${strengthPercentage}%` }"
              />
            </div>
            <!-- Checklist pills -->
            <div class="grid grid-cols-2 gap-1 text-[9px] text-[#767676] pt-1">
              <span :class="ruleLength ? 'text-emerald-600 font-medium' : ''">
                {{ ruleLength ? '✓' : '•' }} 8+ Characters
              </span>
              <span :class="ruleNumber ? 'text-emerald-600 font-medium' : ''">
                {{ ruleNumber ? '✓' : '•' }} One Number
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
          <div class="flex items-center justify-between mb-1">
            <label class="text-[11px] uppercase tracking-wider text-[#1A1A1A] font-medium">
              Confirm Password
            </label>
            <span v-if="passwordsMatch === true" class="text-[10px] text-emerald-600 font-medium">✓ Match</span>
            <span v-else-if="passwordsMatch === false" class="text-[10px] text-red-600">✕ Passwords differ</span>
          </div>
          <div class="relative">
            <input
              v-model="confirmPassword"
              :type="showConfirmPassword ? 'text' : 'password'"
              required
              autocomplete="new-password"
              class="w-full bg-[#FAF8F5] border px-3 py-2.5 pr-10 min-h-[46px] text-sm focus:outline-none transition-colors"
              :class="passwordsMatch === false ? 'border-red-400 focus:border-red-500' : 'border-[#E2DDD5] focus:border-[#B6965D]'"
              placeholder="Re-enter password"
            />
            <button
              type="button"
              class="absolute inset-y-0 right-0 px-3 flex items-center text-[#767676] hover:text-[#1A1A1A]"
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
        <div class="flex items-start space-x-2 pt-1">
          <input
            id="modal-terms"
            v-model="agreeTerms"
            type="checkbox"
            required
            class="mt-1 h-4 w-4 rounded-none border-[#E2DDD5] text-[#B6965D] focus:ring-0"
          />
          <label for="modal-terms" class="text-[11px] text-[#767676] leading-snug">
            I agree to the Éclore Maison Terms of Service and Privacy Policy.
          </label>
        </div>

        <div class="pt-2">
          <EcloreButton
            type="submit"
            variant="primary"
            size="md"
            block
            :disabled="!isSignupFormValid || authStore.loading"
            :loading="authStore.loading"
          >
            Create Account
          </EcloreButton>
        </div>

        <div class="text-center pt-2">
          <p class="text-xs text-[#767676]">
            Already have an account?
            <button
              type="button"
              class="text-[#B6965D] hover:underline font-medium ml-1"
              @click="mode = 'signin'; clearErrors()"
            >
              Sign In
            </button>
          </p>
        </div>
      </form>

      <!-- ======================================================= -->
      <!-- 3. FORGOT PASSWORD FORM -->
      <!-- ======================================================= -->
      <div v-else-if="mode === 'forgot'" class="space-y-5 font-azeret">
        <div class="text-center space-y-1">
          <h4 class="font-playfair text-xl text-[#1A1A1A]">Reset Your Password</h4>
          <p class="text-xs text-[#767676]">
            Enter your registered email address or username to receive a secure password reset link.
          </p>
        </div>

        <!-- Request Link Form -->
        <form v-if="!isResetMode" class="space-y-4" @submit.prevent="handleForgotPassword">
          <div>
            <label class="block text-[11px] uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
              Email or Username
            </label>
            <input
              v-model="forgotLogin"
              type="text"
              required
              class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
              placeholder="client@eclore.com or username"
            />
          </div>

          <div class="pt-2">
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

          <div class="text-center pt-2 border-t border-[#E2DDD5]">
            <button
              type="button"
              class="text-xs text-[#B6965D] hover:underline"
              @click="isResetMode = true; clearErrors()"
            >
              Have a reset token? Enter token to reset password
            </button>
          </div>
        </form>

        <!-- Apply Token & Reset Form -->
        <form v-else class="space-y-4" @submit.prevent="handleResetPassword">
          <div>
            <label class="block text-[11px] uppercase tracking-wider text-[#1A1A1A] font-medium mb-1">
              Reset Token
            </label>
            <input
              v-model="resetToken"
              type="text"
              required
              class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors font-mono"
              placeholder="Paste token from email"
            />
          </div>

          <div>
            <label class="block text-[11px] uppercase tracking-wider text-[#1A1A1A] font-medium mb-1">
              New Password
            </label>
            <input
              v-model="resetNewPassword"
              type="password"
              required
              minlength="8"
              class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
              placeholder="Min 8 characters"
            />
          </div>

          <div>
            <label class="block text-[11px] uppercase tracking-wider text-[#1A1A1A] font-medium mb-1">
              Confirm New Password
            </label>
            <input
              v-model="resetConfirmPassword"
              type="password"
              required
              class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
              placeholder="Confirm new password"
            />
          </div>

          <div class="pt-2">
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
              @click="isResetMode = false; clearErrors()"
            >
              ← Back to request reset link
            </button>
          </div>
        </form>
      </div>
    </div>
  </EcloreModal>
</template>
