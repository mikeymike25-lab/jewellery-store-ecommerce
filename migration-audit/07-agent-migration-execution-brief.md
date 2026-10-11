# MASTER AGENT EXECUTION BRIEF: Complete Migration of Éclore Jewellery Store to Ionic Vue 3

> **TARGET AGENT INSTRUCTION:** You are an elite Full-Stack Mobile & Web Engineer (Laravel 12 + Ionic 8 + Vue 3 + TypeScript). Your mission is to remediate all identified build blockers, wire up mocked/disconnected components to live APIs, and migrate all remaining customer storefront modules from the original Laravel application into the Ionic application.
>
> Follow this brief in sequential order. Do not skip phases. Maintain strict type safety, zero regressions in existing tests, and total fidelity to the Éclore luxury design system.

---

## 1. Project Context & Workspace Anchors

| Context Component | Path / Configuration |
|---|---|
| **Repository Root (Laravel 12)** | `c:\Users\Mike Angelo\OneDrive\Documents\jewellery-store\jewellery-store-ecommerce` |
| **Ionic App Root (Vue 3 + Vite)** | `c:\Users\Mike Angelo\OneDrive\Documents\jewellery-store\jewellery-store-ecommerce\frontend-ionic` |
| **Backend REST API Route File** | `routes/api_v1.php` |
| **Backend Web/CMS Route File** | `routes/web.php` |
| **Existing Audit Evidence** | `migration-audit/` (Read `01` through `06` for deep background) |
| **Active Backend Test Command** | `php artisan test` (SQLite in-memory, must remain 100% passing) |
| **Frontend Build Command** | `npm run build` (in `frontend-ionic`, must compile with 0 TypeScript errors) |
| **Frontend Dev Server** | `npm run dev` (Runs on `http://localhost:5173`) |

---

## 2. Scope Boundaries & Architecture Rules

1. **Customer Storefront:** Primary migration target. Must achieve 100% feature parity with the original Laravel web application.
2. **Admin Backoffice:** **Intentionally retained** as a Laravel 12 Blade web portal at `admin.eclore.test`. **Do NOT attempt to rewrite the 18 admin modules into Ionic** unless explicitly instructed to build a lightweight mobile companion.
3. **Authentication Architecture:** 
   - Stateful Bearer Token (`authStore.token`) via Laravel Sanctum for authenticated users.
   - Stateless `X-Guest-Session-Id` header (UUID stored in `localStorage`) sent on all cart/wishlist requests for guest visitors.
   - Guest-to-customer merge occurs automatically on login via `/api/v1/cart/migrate` and `/api/v1/wishlist/migrate`.
4. **Luxury Design System Rules:**
   - Backgrounds: `#F3EFE7` (Silk warm parchment), `#1A1A1A` (Noir night).
   - Accents: `#B6965D` (Champagne antique gold).
   - Typography: `Playfair Display` for serif headings; `Plus Jakarta Sans` for body; `Azeret Mono` for SKU/prices/receipts.
   - Mobile Ergonomics: Minimum 48px interactive touch targets; proper handling of `safe-area-inset-top` and `safe-area-inset-bottom`.

---

## 3. Phase 1 — Build Blocker Remediation (P0: Urgent)

### Objective
Resolve all 10 TypeScript compilation errors in `frontend-ionic` so `npm run build` succeeds cleanly.

### Task 1.1: Add Missing Route Redirection Methods in `auth.store.ts`
- **File:** `frontend-ionic/src/stores/auth.store.ts`
- **Issue:** Components call `authStore.setIntendedRoute()` and `authStore.consumeIntendedRoute()`, but these methods are missing from `useAuthStore`.
- **Implementation:**
  1. Add reactive property `intendedRoute: ref<string | null>(null)` or persist in `sessionStorage`.
  2. Implement `setIntendedRoute(route: string): void`
  3. Implement `consumeIntendedRoute(defaultRoute = '/tabs/account'): string` that returns the intended route and resets it to `null`.
- **Target Files Affected:**
  - `frontend-ionic/src/components/EcloreHeader.vue` (line 162)
  - `frontend-ionic/src/components/QuickViewModal.vue` (line 71)
  - `frontend-ionic/src/views/ProductDetailView.vue` (line 120)
  - `frontend-ionic/src/views/CartView.vue` (line 225)

### Task 1.2: Fix Type Mismatches in `CartView.vue`
- **File:** `frontend-ionic/src/views/CartView.vue`
- **Issue:** Lines 68 and 71 access `item.size`, but `CartItem` model defines optional attributes under `item.selected_size` or `item.options?.size`.
- **Implementation:**
  Update template bindings to safely access sizes:
  ```typescript
  item.selected_size || item.options?.size || item.product?.size || null
  ```
- **Validation:**
  Run `npm run build` inside `frontend-ionic`. Exit code must be `0` with zero diagnostic errors.

---

## 4. Phase 2 — Wire Up Broken, Mocked & Disconnected Features (P1: High Priority)

### Task 2.1: Register Standalone `/cart` Route
- **File:** `frontend-ionic/src/router/index.ts`
- **Issue:** `CartView.vue` exists (359 lines) but cannot be accessed via direct URL `/cart`.
- **Implementation:**
  Register `/cart` as a child route or standalone view:
  ```typescript
  {
    path: '/cart',
    name: 'Cart',
    component: () => import('@/views/CartView.vue'),
    meta: { title: 'Shopping Bag — Éclore' }
  }
  ```
  Ensure all "View Bag" buttons across drawers and notifications link cleanly to `/cart`.

### Task 2.2: Wire Live Product Reviews to Backend API
- **Files:** 
  - `frontend-ionic/src/components/ReviewsSection.vue`
  - `routes/api_v1.php` (already has `GET /api/v1/reviews/{productId}` and `POST /api/v1/reviews/submit`)
- **Issue:** Currently displays static hardcoded reviews. Submitting a review only pushes to a local memory array.
- **Implementation:**
  1. Create `reviews.api.ts` in `frontend-ionic/src/api/` to call:
     - `GET /api/v1/reviews/{productId}` (fetch approved reviews, rating breakdown, average score).
     - `POST /api/v1/reviews/submit` (submit customer review with rating, title, comment, verified purchaser check).
  2. Update `ReviewsSection.vue`:
     - Load reviews on component mount when `productId` prop changes.
     - Implement loading skeletons and empty state ("Be the first to review this creation").
     - Submit review via API with user Sanctum token, display toast confirmation ("Review submitted for curation approval").

### Task 2.3: Connect Home Page Wishlist Toggles to Store
- **File:** `frontend-ionic/src/views/HomeView.vue`
- **Issue:** Heart buttons on featured creations toggle a local component boolean `product.is_wishlisted` without dispatching to `useWishlistStore()`.
- **Implementation:**
  1. Import `useWishlistStore` in `HomeView.vue`.
  2. Bind heart active state to `wishlistStore.isInWishlist(product.id)`.
  3. Replace toggle handler with `await wishlistStore.toggleItem(product)`.

### Task 2.4: Wire Newsletter Subscription in Footer
- **Files:** 
  - `frontend-ionic/src/components/EcloreFooter.vue`
  - `routes/api_v1.php`
- **Issue:** Footer newsletter form only updates local component boolean `subscribed = true`.
- **Implementation:**
  1. Expose `POST /api/v1/newsletter/subscribe` in `routes/api_v1.php` pointing to `\App\Http\Controllers\NewsletterController::class, 'subscribe'`.
  2. In `EcloreFooter.vue`, dispatch email payload to `/api/v1/newsletter/subscribe` using `apiClient`.
  3. Show luxury toast feedback ("You are now enrolled in the Éclore Gazette").

### Task 2.5: Implement In-App Notifications Drawer
- **Files:**
  - `frontend-ionic/src/components/NotificationsDrawer.vue` (New)
  - `frontend-ionic/src/components/EcloreHeader.vue`
  - `routes/api_v1.php` (already defines `GET /api/v1/notifications`, `POST /{id}/read`, `DELETE /clear-all`)
- **Issue:** Notification bell icon exists in header but clicking it has no drawer or dropdown.
- **Implementation:**
  1. Create `NotificationsDrawer.vue` using `<ion-menu>` or `<ion-modal>`.
  2. Display customer notification cards: Order updates, RMA approvals, VIP event alerts.
  3. Wire "Mark all as read" and "Clear all" actions to API.

---

## 5. Phase 3 — Migrate Missing Customer Modules (P2: Full Parity)

### Module 3.1: Private Salon & Boutique Appointment Booking
- **Original Source:** 
  - `resources/views/boutiques.blade.php`
  - `App\Http\Controllers\AppointmentController::store`
  - `routes/web.php` (`/boutiques-appointments`)
- **Backend API Setup:**
  Add to `routes/api_v1.php`:
  ```php
  Route::get('/boutiques', [App\Http\Controllers\PageController::class, 'getBoutiquesApi']);
  Route::post('/appointments/book', [App\Http\Controllers\AppointmentController::class, 'storeApi']);
  Route::middleware('auth:sanctum')->get('/account/appointments', [App\Http\Controllers\AccountController::class, 'getAppointmentsApi']);
  ```
- **Ionic Implementation:**
  1. Create `frontend-ionic/src/views/AppointmentsView.vue`:
     - Boutique selector (Paris Place Vendôme, London New Bond St, New York Madison Ave, Tokyo Ginza).
     - Service selector (High Jewellery Viewing, Custom Bespoke Commission, Bridal Consultation, Restoration & Valuation).
     - Interactive calendar & time slot picker.
     - Guest & Authenticated booking submission form.
  2. Create appointment confirmation receipt card with option to add to native calendar (`@capacitor/calendar` or `.ics` download).
  3. Add "My Salon Appointments" card in `AccountView.vue`.
  4. Register route `/appointments` in `router/index.ts`.

### Module 3.2: Journal, Maison Heritage & Editorial Stories
- **Original Source:**
  - `resources/views/newsroom.blade.php`
  - `resources/views/about.blade.php`
  - `App\Http\Controllers\PageController::newsroom`
- **Backend API Setup:**
  Add to `routes/api_v1.php`:
  ```php
  Route::get('/stories', [App\Http\Controllers\PageController::class, 'getStoriesApi']);
  Route::get('/stories/{slug}', [App\Http\Controllers\PageController::class, 'getStoryDetailApi']);
  ```
- **Ionic Implementation:**
  1. Create `frontend-ionic/src/views/JournalView.vue`:
     - Editorial magazine grid with high-resolution imagery and pull-quotes.
     - Filter tabs: *High Jewellery*, *Maison Savoir-Faire*, *Gemstone Sourcing*, *Celebrity & Red Carpet*.
  2. Create `frontend-ionic/src/views/StoryDetailView.vue`:
     - Immersive reading mode with serif typography, image carousels, and "Curated Creations Featured in this Story" product cards linking to PDP.
  3. Register routes `/journal` and `/journal/:slug` in `router/index.ts`.

### Module 3.3: Institutional, Legal & Customer Care Suite (14 Pages)
- **Original Source:**
  14 Blade views (`privacy-policy`, `terms-of-service`, `conditions-of-sale`, `cookie-policy`, `cookie-center`, `care`, `corporate-responsibility`, `help`, `accessibility`, `orders-payments`, `vip-club`, etc.).
- **Ionic Architecture Decision:**
  Implement a versatile dynamic CMS viewer:
  1. Create `frontend-ionic/src/views/CmsPageView.vue`:
     - Reads slug from route parameters (`/page/:slug`).
     - Fetches structured HTML/Markdown from `GET /api/v1/content/{slug}` or fallback to local luxury templates.
     - Features sticky section navigation (e.g. Terms table of contents) and smooth scroll.
  2. Register legal and institutional routes in `router/index.ts`:
     - `/terms` -> `terms-of-service`
     - `/privacy` -> `privacy-policy`
     - `/care-guide` -> `care`
     - `/sustainability` -> `corporate-responsibility`
     - `/boutiques` -> Store locator with interactive map coordinates.
  3. Ensure all footer links in `EcloreFooter.vue` navigate to these routes.

### Module 3.4: Noupe Concierge AI Assistant
- **Original Source:**
  Laravel frontend chatbot integration widget for customer concierge inquiries.
- **Ionic Implementation:**
  1. Create `frontend-ionic/src/components/NoupeConciergeModal.vue`:
     - Elegant floating concierge pill ("Éclore Concierge") in bottom-right corner.
     - Smooth slide-over chat interface with gold-accented bubbles.
     - Quick prompts: *"Help me find an engagement solitaire"*, *"Inquire about boutique hours"*, *"Track my consignment"*, *"Care advice for emeralds"*.
  2. Connect to backend concierge endpoint or streaming AI handler.

### Module 3.5: Google OAuth Single Sign-On
- **Original Source:**
  `routes/web.php` (`/auth/google`, `/auth/google/callback` using Laravel Socialite).
- **Ionic Implementation:**
  1. Use `@capacitor/browser` or `@codetrix-studio/capacitor-google-auth`.
  2. On mobile/web, redirect to `/api/v1/auth/google/redirect`, capture the callback containing the Sanctum Bearer token via custom URL scheme (`eclore://auth/callback?token=...`).
  3. Store token in `auth.store.ts` and trigger guest cart merge.

---

## 6. Phase 4 — Admin Backoffice Directive

> [!IMPORTANT]
> The Éclore Admin Backoffice comprises 18 comprehensive operational modules:
> Analytics, Dashboard, Catalogue, Categories, Inventory, Orders, Fulfillment, RMA Returns, Customer CRM, Messages/Inquiries, Reviews Curation, Coupons, Appointments, CMS Content, Media Library, Employees/RBAC, Audit Logs, and Global Settings.
> 
> **Architectural Standard:**
> - These modules are **desktop-optimized** and **remain hosted by Laravel 12 Blade** on `admin.eclore.test`.
> - Do **not** migrate the entire admin suite into the customer Ionic app.
> - If an administrative view is needed on mobile devices, build a standalone `admin-portal` or configure responsive viewport CSS within the existing Blade templates.

---

## 7. Verification & Definition of Done

Every task must pass the following verification gates before concluding:

1. **Compilation Gate:**
   ```bash
   cd frontend-ionic
   npm run build
   ```
   Must exit with code 0 and 0 TypeScript diagnostic errors.

2. **Backend Regression Gate:**
   ```bash
   php artisan test
   ```
   Must pass all 36 feature and unit tests with 0 failures and 200+ assertions.

3. **Runtime & UI/UX Checklist:**
   - [ ] No `console.error` logs on initial load or view transitions.
   - [ ] Cart drawer, Wishlist drawer, Search modal, and Notification drawer open and close smoothly.
   - [ ] Direct URL navigation to `/cart`, `/appointments`, `/journal`, `/terms` works cleanly.
   - [ ] Touch targets are at least 48px high on iOS and Android viewports.
   - [ ] Top status bar and bottom home indicators respect `safe-area-inset`.
   - [ ] Luxury typography (`Playfair Display`, `Azeret Mono`) renders without layout shifts.
   - [ ] Submitting a review immediately reflects in the product view with pending curation badge.
   - [ ] Guest cart automatically migrates to customer account upon login.

---
*Generated by Éclore Architecture & Migration Audit Lead.*
