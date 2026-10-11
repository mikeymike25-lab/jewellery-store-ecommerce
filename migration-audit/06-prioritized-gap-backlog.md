# Prioritized Gap Backlog: Éclore Jewellery Store Migration
**Document:** `migration-audit/06-prioritized-gap-backlog.md`  
**Target Architecture:** Laravel 12 Backend API + Ionic 8 Vue 3 Frontend  
**Audit Date:** October 11, 2026  
**Auditor:** Senior Application Migration Auditor & QA Lead  
**Document Purpose:** Remediation Backlog of Verified Audit Findings (No Fixes Implemented)

---

## 1. Prioritization Criteria

- **P0 (Critical / Blocker):** Blocks production compilation, prevents application bundling, causes fatal runtime exceptions, or introduces direct security / data integrity vulnerabilities.
- **P1 (High):** Major functional e-commerce workflows that are broken, disconnected, or mocked, preventing end-to-end customer completion.
- **P2 (Medium):** In-scope secondary or after-sales features, missing third-party integrations, or navigation mismatches that degrade user experience.
- **P3 (Low / Polish):** Editorial pages, non-essential widgets, aesthetic enhancements, or minor documentation discrepancies.

---

## 2. Master Gap Backlog Items

### GAP-01: TypeScript Compilation Failure Blocks Production Build
- **Priority:** **P0 (Critical)**
- **Portal / Module:** Customer Storefront / Build Pipeline (`frontend-ionic`)
- **Issue:** `npm run build` (`vue-tsc -b && vite build`) fails with 10 compilation errors across 4 files.
- **Impact:** Application cannot be bundled into production web assets or synchronized to Android Capacitor native packages (`npx cap sync android`).
- **Evidence Path:**
  - `frontend-ionic/src/components/EcloreHeader.vue:33` (`error TS2339: Property 'setIntendedRoute' does not exist`)
  - `frontend-ionic/src/components/QuickViewModal.vue:41` (`error TS2339: Property 'setIntendedRoute' does not exist`)
  - `frontend-ionic/src/views/ProductDetailView.vue:104, 124, 145` (`error TS2339: Property 'setIntendedRoute' does not exist`)
  - `frontend-ionic/src/views/CartView.vue:67, 75` (`error TS2339: Property 'setIntendedRoute' does not exist`)
  - `frontend-ionic/src/views/CartView.vue:224, 225` (`error TS2339: Property 'size' does not exist on type 'CartItem'`)
  - `frontend-ionic/src/components/EcloreHeader.vue:18` (`error TS6133: 'cartCount' is declared but never read`)
- **Acceptance Criteria for Future Fix:**
  1. `frontend-ionic/src/stores/auth.store.ts` implements `setIntendedRoute(route: string): void` and `intendedRoute: string | null` state, or calls are refactored to standard router navigation.
  2. `CartView.vue` accesses `ring_size` in accordance with `CartItem` definition in `src/types/index.ts`.
  3. `cartCount` in `EcloreHeader.vue` is either utilized in template badge or removed.
  4. Executing `npm run build` exits with code 0 and 0 TypeScript errors.
- **Dependencies:** None.
- **Suggested Validation:** Run `npm run build` in `frontend-ionic/` and verify clean `dist/` bundle generation.

---

### GAP-02: Disconnected Wishlist State on Homepage Hero & Carousel
- **Priority:** **P0 (Critical)**
- **Portal / Module:** Customer Storefront / Homepage (`HomeView.vue`)
- **Issue:** Tapping the heart icon on hero and carousel products toggles an isolated local component array (`const wishlist = ref<number[]>([])`) instead of calling `useWishlistStore().toggle(productId)`.
- **Impact:** Customer saves a masterpiece from the homepage, but the header wishlist counter remains unchanged, the item does not appear in the wishlist drawer, and the saved state is lost on navigation.
- **Evidence Path:**
  - `frontend-ionic/src/views/HomeView.vue:60-68` (`wishlist local mock state`)
  - `frontend-ionic/src/views/HomeView.vue:195` (`@click="toggleWishlist(leftProduct.id)"`)
  - `frontend-ionic/src/views/HomeView.vue:232` (`@click="toggleWishlist(carouselProducts[currentHeroSlide].id)"`)
- **Acceptance Criteria for Future Fix:**
  1. `HomeView.vue` imports and uses `useWishlistStore()`.
  2. Heart toggle buttons call `wishlistStore.toggle(product.id)`.
  3. Heart fill status is bound to `wishlistStore.isInWishlist(product.id)`.
  4. Header wishlist badge increments reactively upon clicking heart.
- **Dependencies:** None.
- **Suggested Validation:** Tap heart on home hero product; verify header counter updates; verify item appears in `WishlistDrawer.vue`.

---

### GAP-03: Standalone Shopping Cart View (`CartView.vue`) Unregistered & Disconnected
- **Priority:** **P1 (High)**
- **Portal / Module:** Customer Storefront / Routing & Cart
- **Issue:** A complete 359-line standalone cart page (`CartView.vue`) exists in `src/views/`, but is omitted from `frontend-ionic/src/router/index.ts`.
- **Impact:** Direct URL navigation to `/cart` fails (404/redirect). Code remains orphaned and unmaintained.
- **Evidence Path:**
  - `frontend-ionic/src/views/CartView.vue:1-359` (exists on disk)
  - `frontend-ionic/src/router/index.ts:5-74` (no route for `/cart`)
- **Acceptance Criteria for Future Fix:**
  1. Stakeholders decide whether `/cart` should be a standalone route or if the offcanvas `CartDrawer.vue` is the exclusive bag experience.
  2. If retained, register route `{ path: '/cart', component: () => import('@/views/CartView.vue') }` and fix its TypeScript errors.
  3. If deprecated, cleanly archive or delete `CartView.vue` to prevent dead code confusion.
- **Dependencies:** GAP-01.
- **Suggested Validation:** Verify `/cart` renders when entered in browser URL or confirm intentional drawer exclusivity.

---

### GAP-04: Customer Reviews System Implemented as Static In-Memory Mock
- **Priority:** **P1 (High)**
- **Portal / Module:** Customer Storefront / Product Detail Page (`ReviewsSection.vue`)
- **Issue:** `ReviewsSection.vue` renders hardcoded mock reviews for "Maria C. Santos", "Alexander V.", and "Sofia D.". Submitting a review unshifts to the local array in memory without calling the backend API.
- **Impact:** Real verified customer reviews from the database (`product_reviews` table) are never loaded, and customer-submitted reviews disappear upon page refresh.
- **Evidence Path:**
  - `frontend-ionic/src/components/ReviewsSection.vue:23-51` (hardcoded mock reviews array)
  - `frontend-ionic/src/components/ReviewsSection.vue:65-83` (`submitReview` modifies memory only)
  - `routes/api_v1.php:94-95` (`GET /api/v1/reviews/{id}` and `POST /api/v1/reviews/submit` exist on backend)
- **Acceptance Criteria for Future Fix:**
  1. `ReviewsSection.vue` calls `GET /api/v1/reviews/{props.productId}` on mount.
  2. Reviews list renders live database records from response.
  3. Form submission dispatches `POST /api/v1/reviews/submit` with Sanctum Bearer token and refreshes review list.
- **Dependencies:** GAP-01.
- **Suggested Validation:** Submit a review via UI; verify record is inserted into `product_reviews` database table.

---

### GAP-05: Customer In-App Notifications Feature Missing in Frontend
- **Priority:** **P1 (High)**
- **Portal / Module:** Customer Storefront / Navigation & Notifications
- **Issue:** Backend routes for notifications (`GET /api/v1/notifications`, `/unread-count`, `POST /{id}/read`, `DELETE /clear-all`) exist in `routes/api_v1.php:130-137`, but `frontend-ionic` has no bell icon, badge, or notification drawer.
- **Impact:** Customers have no mechanism to receive alerts regarding order fulfillment progress, courier dispatch, or RMA repair approvals.
- **Evidence Path:**
  - `resources/views/partials/navbar.blade.php:238-245` (Blade baseline notification bell)
  - `resources/views/partials/offcanvas-notification.blade.php:1-45` (Blade baseline notification drawer)
  - `frontend-ionic/src/components/EcloreHeader.vue` (contains no notification trigger)
- **Acceptance Criteria for Future Fix:**
  1. Create `frontend-ionic/src/stores/notifications.store.ts` consuming `/api/v1/notifications/*`.
  2. Add notification bell icon with reactive unread badge to `EcloreHeader.vue`.
  3. Create `NotificationDrawer.vue` allowing customers to view, mark read, and clear notifications.
- **Dependencies:** GAP-01.
- **Suggested Validation:** Dispatch notification from admin; verify unread badge appears on mobile header.

---

### GAP-06: Newsletter Subscription Form Uses Inert Mock State
- **Priority:** **P2 (Medium)**
- **Portal / Module:** Customer Storefront / Footer (`EcloreFooter.vue`)
- **Issue:** Submitting the newsletter subscription form in `EcloreFooter.vue` only sets a local boolean flag `subscribed.value = true` without calling any backend endpoint.
- **Impact:** Customer newsletter emails are discarded without being stored in `newsletter_subscriptions` database table.
- **Evidence Path:**
  - `frontend-ionic/src/components/EcloreFooter.vue:9-14` (`handleSubscribe()` sets local state only)
  - `routes/web.php:448` (`Route::post('/newsletter/subscribe')` exists in web routes)
- **Acceptance Criteria for Future Fix:**
  1. Expose `POST /api/v1/newsletter/subscribe` in `routes/api_v1.php`.
  2. `EcloreFooter.vue` dispatches Axios POST request with email payload.
  3. Subscribed email is inserted into `newsletter_subscriptions` table.
- **Dependencies:** None.
- **Suggested Validation:** Submit email in footer; verify new row in `newsletter_subscriptions`.

---

### GAP-07: Missing Google OAuth Single Sign-On
- **Priority:** **P2 (Medium)**
- **Portal / Module:** Customer Storefront / Authentication
- **Issue:** Social login via Google is absent in `frontend-ionic` despite being outlined in the planning roadmap (Task 1.10).
- **Impact:** Customers cannot authenticate via Google SSO on mobile or web.
- **Evidence Path:**
  - `migration-planning/03-feature-migration-matrix.md` row 1.10 (defines `GoogleAuthButton.vue`)
  - `frontend-ionic/src/components/AuthModal.vue` (zero Google OAuth button or social handler)
  - `frontend-ionic/src/api/client.ts` (zero social auth callback endpoint)
- **Acceptance Criteria for Future Fix:**
  1. Provide Google OAuth button in `AuthModal.vue` and `AccountView.vue`.
  2. For web: redirect to `/auth/google`. For native: integrate `@codetrix-studio/capacitor-google-auth`.
  3. Handle callback via `POST /api/v1/auth/google/callback` issuing Sanctum Bearer token.
- **Dependencies:** Google Cloud Client ID credentials.
- **Suggested Validation:** Tap Google Sign-In; verify user session is created in database.

---

### GAP-08: Unmigrated Phase 7 Institutional & Legal Pages (14 Blade Views)
- **Priority:** **P2 (Medium)**
- **Portal / Module:** Customer Storefront / Static Pages & CMS
- **Issue:** 14 institutional, legal, and policy pages exist in Blade, but have no target views in Ionic. 14+ links in `EcloreFooter.vue` and `SideMenuDrawer.vue` redirect to `/catalogue`.
- **Impact:** Legal compliance risk (terms of service, privacy policy, and conditions of sale are inaccessible); customer care and sizing guides are unavailable.
- **Evidence Path:**
  - `resources/views/about.blade.php`, `care.blade.php`, `help.blade.php`, `privacy-policy.blade.php`, `terms-of-service.blade.php`, `conditions-of-sale.blade.php`, `cookie-policy.blade.php`, `cookie-center.blade.php`, `accessibility.blade.php`, `corporate-responsibility.blade.php`, `orders-payments.blade.php`, `vip-club.blade.php`, `collections.blade.php`
  - `frontend-ionic/src/components/EcloreFooter.vue:78-106` (`navigateTo('/catalogue')` on all links)
  - `frontend-ionic/src/components/SideMenuDrawer.vue:122` (`handleNavigate('/catalogue')` on About)
- **Acceptance Criteria for Future Fix:**
  1. Scaffold `frontend-ionic/src/views/StaticPageView.vue` (or individual views).
  2. Add routes `/about`, `/care`, `/help`, `/terms`, `/privacy`, `/conditions-of-sale`.
  3. Update `EcloreFooter.vue` and `SideMenuDrawer.vue` to navigate to authentic routes.
- **Dependencies:** None.
- **Suggested Validation:** Tap "Privacy Policy" in footer; verify legal charter renders.

---

### GAP-09: Unmigrated Phase 7 Boutique Appointments Booking View
- **Priority:** **P2 (Medium)**
- **Portal / Module:** Customer Storefront / Appointments
- **Issue:** Flagship salon directory and appointment booking flow (`views/boutiques.blade.php`) has no Ionic counterpart.
- **Impact:** Clients cannot schedule private consultations at flagship boutiques (Manila, Paris, Tokyo, New York).
- **Evidence Path:**
  - `resources/views/boutiques.blade.php:1-343`
  - `frontend-ionic/src/components/SideMenuDrawer.vue:132` (Boutiques & Appointments redirects to `/catalogue`)
  - `frontend-ionic/src/views/HomeView.vue:563` ("Book Now" button redirects to `/catalogue`)
- **Acceptance Criteria for Future Fix:**
  1. Build `BoutiqueAppointmentsView.vue` with location cards and date/time selector.
  2. Expose `POST /api/v1/appointments` in `routes/api_v1.php`.
  3. Register route `/boutiques-appointments` in `router/index.ts`.
- **Dependencies:** None.
- **Suggested Validation:** Book appointment in UI; verify new row in `appointments` database table.

---

### GAP-10: Unmigrated Phase 7 The Éclore Journal & Newsroom
- **Priority:** **P3 (Low)**
- **Portal / Module:** Customer Storefront / CMS Editorial
- **Issue:** Journal stories and editorial articles (`views/newsroom.blade.php`, `views/cms/show.blade.php`) are missing from Ionic.
- **Impact:** Editorial luxury content and press releases are missing from the mobile app.
- **Evidence Path:**
  - `resources/views/newsroom.blade.php:1-117`
  - `frontend-ionic/src/components/SideMenuDrawer.vue:142` (Newsroom redirects to `/catalogue`)
- **Acceptance Criteria for Future Fix:**
  1. Create `JournalArticleView.vue` and newsroom feed.
  2. Expose `GET /api/v1/cms/{slug}` returning parsed article HTML and headings.
- **Dependencies:** None.
- **Suggested Validation:** Open Newsroom link; verify article feed loads.

---

### GAP-11: Unmigrated Phase 7 Concierge Contact Form
- **Priority:** **P3 (Low)**
- **Portal / Module:** Customer Storefront / Customer Service
- **Issue:** Customer inquiry form (`views/contact.blade.php`) is missing in Ionic; footer link redirects to `/catalogue`.
- **Impact:** Customers cannot send direct general inquiry messages to the atelier.
- **Evidence Path:**
  - `resources/views/contact.blade.php:1-180`
  - `frontend-ionic/src/components/EcloreFooter.vue:78` ("CONTACT US" redirects to `/catalogue`)
- **Acceptance Criteria for Future Fix:**
  1. Create `ContactView.vue` with name, email, phone, subject, and message fields.
  2. Expose `POST /api/v1/contact` in `routes/api_v1.php`.
- **Dependencies:** None.
- **Suggested Validation:** Submit inquiry; verify record in `contact_messages` table.

---

### GAP-12: Unmigrated Noupe AI Chatbot Widget Wrapper
- **Priority:** **P3 (Low)**
- **Portal / Module:** Customer Storefront / Customer Service Widget
- **Issue:** Third-party chatbot script embedded in `layouts/app.blade.php:1221` is omitted from Ionic.
- **Impact:** Floating automated concierge assistance bubble is not present in mobile views.
- **Evidence Path:**
  - `resources/views/layouts/app.blade.php:1221`
  - `frontend-ionic/src/App.vue` (contains no chatbot wrapper)
- **Acceptance Criteria for Future Fix:**
  1. Build optional `ChatbotWidget.vue` conditional wrapper component.
  2. On mobile/Capacitor, open in In-App Browser tab to prevent iframe viewport distortion.
- **Dependencies:** Stakeholder confirmation of third-party script suitability for mobile app store guidelines.
- **Suggested Validation:** Verify chatbot badge toggles assistant window.
