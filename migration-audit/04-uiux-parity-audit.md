# UI/UX & Visual Parity Audit: Éclore Jewellery Store
**Document:** `migration-audit/04-uiux-parity-audit.md`  
**Target Architecture:** Laravel 12 Blade Baseline vs. Ionic 8 Vue 3 Frontend  
**Audit Date:** October 11, 2026  
**Auditor:** Senior UI/UX Regression Auditor & Frontend Engineer  
**Inspection Mode:** Static Code & Component Architecture Verification (Runtime Browser Rendering Not Checked)

---

## 1. Luxury Brand Identity & Design Tokens

### 1.1 Palette & Color Token Parity
The Éclore brand identity is anchored in a signature Haute Joaillerie palette. Comparing `public/frontend/style.css` (Blade baseline) against `frontend-ionic/src/theme/variables.css` and `src/style.css`:

| Design Token | Color Swatch | Blade Monolith Definition | Ionic Vue Implementation | Parity Assessment |
|---|---|---|---|:---:|
| **Primary Cream Background** | `#F3EFE7` | `style.css:32` (`--bg-primary`) | `variables.css:12` (`--ion-background-color: #F3EFE7`) | **100% Match** |
| **Signature Champagne Gold** | `#B6965D` | `style.css:33` (`--color-gold`) | `variables.css:14` (`--eclore-gold: #B6965D`) | **100% Match** |
| **Dark Charcoal / Ink Text** | `#1A1A1A` | `style.css:34` (`--color-ink`) | `variables.css:15` (`--eclore-ink: #1A1A1A`) | **100% Match** |
| **Subtle Cream Card / Border**| `#E2DDD5` | `style.css:36` (`--border-subtle`)| Tailwind token `border-[#E2DDD5]` used across components | **100% Match** |
| **Pure Luxury Footer White** | `#FCFCFC` / `#FFFFFF` | `style.css:48` | `EcloreFooter.vue:22` (`bg-[#FCFCFC]`) | **100% Match** |
| **Atelier Dark Accent** | `#0F110E` | `style.css:52` (Partner section) | `HomeView.vue:614` (`bg-[#0F110E]`) | **100% Match** |

### 1.2 Typography & Font Hierarchy
- **Editorial Headings:** `Playfair Display` (serif, tracking `[0.2em]`, font-light). Both platforms configure Playfair Display for Maison brand titles, product names, and section headings.
- **Monospace Accent / Pricing / Labels:** `Azeret Mono` (tracking `[0.25em]`, uppercase). Used in both Blade and Ionic for SKU numbers, badges, prices, and technical metadata.
- **Body & UI Controls:** `Inter` (sans-serif, clean readability for form fields and descriptions).
- **Asset Bundling:** Fonts and luxury jewellery WebP assets have been packaged locally into `frontend-ionic/public/assets/`, removing third-party CDN latency and tracking dependencies.

---

## 2. Screen-by-Screen Component & Layout Parity

### 2.1 Top Navigation & Header (`EcloreHeader.vue` vs `navbar.blade.php`)
- **Blade Baseline:** Desktop 2-tier header with search toggle, customer dropdown, notification bell with unread badge, wishlist heart with count, cart bag with count, and desktop category menu.
- **Ionic Implementation:** Sticky header with `#F3EFE7/95` background and backdrop blur; brand logo centered; search trigger, account button, wishlist drawer trigger, and shopping bag trigger on the right; mobile hamburger toggle on the left opening `SideMenuDrawer.vue`.
- **Identified Differences:**
  - Notification bell is **missing** from Ionic header.
  - Black announcement bar from early mockups was intentionally removed to match the authentic web aesthetic.
  - `SideMenuDrawer.vue` slides out from the left matching web mobile behavior with signature gold VIP club styling.

### 2.2 Homepage (`HomeView.vue` vs `home.blade.php`)
- **Blade Baseline:** Autoplay HTML5 video banner (`hero-jewelry.mp4`), 4-card Bento category grid, curated icon product grid, "Legacy in Every Detail" heritage teaser, interactive bespoke masterpiece selector, "Éclore Experience" service cards, and brand partner marquee.
- **Ionic Implementation:**
  - Hero section adapts desktop video to high-fidelity responsive imagery with interactive slide switching.
  - Bento category cards feature responsive 1-column mobile / 3-column desktop layout.
  - Bespoke customizer (`#masterpiece`) implements interactive tab switching across Engravings, Straps, Stones, and Finishing with animated preview circle.
  - Service cards and brand partner buttons match 1:1.
- **Identified Differences:**
  - Quick actions ("Book An Appointment", "Learn More", "Contact Us") in Section 7 route to `/catalogue` instead of dedicated workflows.
  - Heart toggle buttons on home products do not sync with the global wishlist store.

### 2.3 Catalogue & Filtering (`CatalogueView.vue` vs `catalogue.blade.php`)
- **Blade Baseline:** Sticky 280px left sidebar with checkboxes for categories, materials, stones, carats, and price ranges. 4-column product card grid.
- **Ionic Implementation:**
  - Desktop left sidebar adapted into a mobile slide-over drawer (`FilterDrawer.vue`) with smooth backdrop blur.
  - Refine selection facet pills in gold `#B6965D`.
  - Floating sort modal (`SortModal.vue`) replaces standard HTML `<select>` with touch-friendly action sheet.
  - Infinite scroll (`IonInfiniteScroll`) replaces static pagination buttons.
  - Empty state and offline fallback products implemented.
- **UI/UX Parity:** **Equivalent (Superior Mobile Adaptation).**

### 2.4 Product Detail Page (`ProductDetailView.vue` vs `product/show.blade.php`)
- **Blade Baseline:** 2-column layout with vertical thumbnail strip, sticky buy box, ring size dropdown, specifications accordion, verified reviews, and related products carousel.
- **Ionic Implementation:**
  - Full-width swipeable touch carousel with pinch-to-zoom modal and fullscreen image lightbox.
  - Ring size pills with size guide modal.
  - Authoritative 12% Philippine VAT breakdown.
  - Sticky bottom mobile acquisition bar ("ADD TO BAG").
- **Identified Differences:**
  - Customer reviews section is visually present but displays mock data.
  - Compile errors on `setIntendedRoute` prevent production bundle generation.

### 2.5 Shopping Bag & Drawer (`CartDrawer.vue` vs `offcanvas-cart.blade.php`)
- **Blade Baseline:** jQuery slide-over drawer from right with per-item checkboxes, live subtotal, and checkout button.
- **Ionic Implementation:**
  - Smooth Teleport-based slide drawer (`CartDrawer.vue`) with backdrop blur.
  - Per-item checkbox selection allowing selective checkout.
  - Dynamic subtotal with authoritative 12% VAT calculation.
  - Quantity increment/decrement buttons with 48px touch targets.
  - Free armored insured delivery progress bar.
- **UI/UX Parity:** **Equivalent.**

### 2.6 Multi-Step Acquisition Checkout (`CheckoutView.vue` vs `checkout/*.blade.php`)
- **Blade Baseline:** Monolithic multi-page checkout (`/checkout` -> `/checkout/payment` -> `/checkout/review` -> `/checkout/confirmation`).
- **Ionic Implementation:**
  - Single-page fluid container with step indicator (`Shipping` -> `Payment` -> `Review` -> `Confirmation`).
  - Cascading Philippine PSGC action sheets (Region, Province, City, Barangay) with NCR auto-adaptation.
  - Radio cards for Xendit, Saved Cards, and COD.
  - Authoritative order summary in Philippine Pesos (`₱`), fixing the legacy Euro (`€`) symbol bug.
  - In-app browser handling for Xendit hosted payment portal.
- **UI/UX Parity:** **Equivalent.**

### 2.7 Customer Account Dashboard (`AccountView.vue` vs `account.blade.php`)
- **Blade Baseline:** 3,447 lines of monolithic Blade template with tabbed navigation via jQuery.
- **Ionic Implementation:**
  - Decomposed into modular Vue components: `AccountTab.vue`, `OrderHistoryList.vue`, `OrderDetailModal.vue`, `RmaRequestModal.vue`.
  - Luxury gold avatar badge with initials.
  - Philippine address book editor.
  - Official A4 tax PDF download button.
  - RMA claim wizard with photo uploader.
- **UI/UX Parity:** **Equivalent.**

---

## 3. Mobile Ergonomics & Touch Target Compliance

In accordance with project guidelines established during Stage 2 through Stage 6:
1. **Minimum Touch Target Sizes:** All interactive elements (`EcloreButton.vue`, header icons, facet pills, and size selectors) maintain a minimum hit area of **48px x 48px**, preventing collapsed buttons on mobile screens.
2. **Action Button Stylistic Details:** Action buttons feature centered typography paired with rounded contrast dots (`bg-white` dot on black buttons, `bg-black` dot on white buttons) with balanced horizontal padding.
3. **Safe Area Insets:** Layouts respect `env(safe-area-inset-top)` and `env(safe-area-inset-bottom)`, preventing notch overlap on modern iOS and Android devices.
4. **Android Status Bar:** Configured with light theme and `#F3EFE7` blending via `@capacitor/status-bar`.

---

## 4. Visual Parity Status Summary by Screen Pair

| Screen Pair | Visual Parity Status | Evidence & Key Observations |
|---|:---:|---|
| **Header / Navigation** | **Partial** | Visual aesthetic is 1:1, but notification bell is missing; 14 menu links redirect to `/catalogue`. |
| **Home Editorial Showcase** | **Equivalent** | Luxury typography, video-to-image fallback, bento grid, and bespoke customizer match web baseline. |
| **Product Catalogue & Grid** | **Equivalent** | 4-column / 2-column mobile grid, gold facet pills, and slide drawer provide parity. |
| **Product Detail Page (PDP)** | **Partial** | Acquisition bar, gallery, and VAT breakdown match; reviews section is mocked; TS errors block build. |
| **Quick View Modal** | **Equivalent** | Centered desktop dialog and mobile bottom sheet match web quick view. |
| **Search Autocomplete Modal** | **Equivalent** | Debounced search overlay with category tags matches web modal. |
| **Offcanvas Shopping Bag** | **Equivalent** | Slide drawer with per-item checkboxes and VAT breakdown matches web cart. |
| **Standalone Cart Page** | **Not checked** | Unregistered in router; unreachable at runtime. |
| **Wishlist Tab & Drawer** | **Equivalent** | Dedicated saved items curation matches web wishlist offcanvas. |
| **Authentication Modals** | **Equivalent** | Sign in, sign up with strength meter, and password recovery match web modal dialogs. |
| **Checkout Flow (Steps 1–3)** | **Equivalent** | PSGC cascading address, COD rules, and review card match web checkout views. |
| **Order Confirmation** | **Equivalent** | Status timeline, payment polling, and deep linking match confirmation template. |
| **Public Order Tracking** | **Equivalent** | 4-stage fulfillment timeline matches web `track-order.blade.php`. |
| **Customer Account Portal** | **Equivalent** | Decomposed tabs match web `account.blade.php`. |
| **A4 PDF Tax Invoice** | **Equivalent** | DomPDF binary matches official invoice template. |
| **RMA Claims Wizard** | **Equivalent** | Photo uploader and reference code match web claim modal. |
| **Customer Reviews Section** | **Partial** | Visual presentation exists, but backed by hardcoded mock data. |
| **Newsletter Subscription** | **Partial** | Luxury white footer form exists, but submission is mock-only. |
| **Boutique Appointments** | **Missing** | No Ionic screen exists; links route to `/catalogue`. |
| **The Éclore Journal** | **Missing** | No Ionic screen exists; links route to `/catalogue`. |
| **Institutional & Legal (14)** | **Missing** | No Ionic screen exists; links route to `/catalogue`. |
| **Concierge Contact Form** | **Missing** | No Ionic screen exists; links route to `/catalogue`. |
| **Noupe Chatbot Widget** | **Missing** | No widget wrapper exists in Ionic. |
