# Functional, API & Authentication Audit: Éclore Jewellery Store
**Document:** `migration-audit/03-functional-api-and-auth-audit.md`  
**Target Architecture:** Laravel 12 REST API + Ionic 8 Vue 3 Frontend  
**Audit Date:** October 11, 2026  
**Auditor:** Senior Application Migration Auditor & Laravel Architect

---

## 1. End-to-End Workflow Traces (UI → API → Controller → Outcome)

### Workflow 1: Anonymous Discovery & Shopping Bag Addition
```
[User on PDP: ProductDetailView.vue]
   │
   ├─► Selects ring size ('7') & metal ('18K Yellow Gold')
   ├─► Taps "ADD TO BAG" (line 99)
   │
[cart.store.ts::addToCart(productId, quantity, options)]
   │
   ├─► Axios Interceptor (`client.ts:70-81`):
   │      - Injects `X-Guest-Session-Id: <uuid>` (from @capacitor/preferences)
   │      - Injects `Accept: application/json`
   │
   ├─► HTTP POST /api/v1/cart/add
   │
[Laravel Backend: routes/api_v1.php:70 ──► CartController::addToCart]
   │
   ├─► Middleware `HandleGuestSessionHeader.php`:
   │      - Resolves `X-Guest-Session-Id` header
   │      - Binds or finds existing `GuestSession` model in DB
   │
   ├─► Controller Execution:
   │      - Validates `product_id`, `quantity`, `size`, `ring_size`, `metal_type`
   │      - Queries `Product::findOrFail(productId)`
   │      - Inserts or increments `CartItem` record associated with `guest_session_id`
   │      - Recalculates `total_price = unit_price * quantity`
   │
[Response: HTTP 200 JSON]
   │  { "success": true, "message": "Item added to bag", "data": { ... } }
   │
[Client UI: CartDrawer.vue opens automatically]
   └─► Renders new item with 12% Philippine VAT breakdown & subtotal in ₱
```
**Audit Finding:** **PASS / FULLY CONNECTED.** Verified in code and validated in `tests/Feature/Stage4CartWishlistMigrationTest.php`.

---

### Workflow 2: Post-Login Cart & Wishlist Migration
```
[Guest with 2 Bag Items & 1 Wishlist Item signs in via AuthModal.vue]
   │
   ├─► Submits email and password
   │
[auth.store.ts::login(credentials)]
   │
   ├─► HTTP POST /api/v1/auth/token
   │
[Laravel Backend: routes/api_v1.php:34 ──► ApiAuthController::token]
   │
   ├─► Authenticates user credentials via `Auth::attempt()`
   ├─► Issues Laravel Sanctum Plain-Text Bearer Token: `createToken('auth-token')->plainTextToken`
   ├─► Returns `{ "token": "...", "user": { ... } }`
   │
[auth.store.ts:70-80]
   ├─► Writes Bearer token to `@capacitor/preferences` via `setAuthToken(token)`
   ├─► Dispatches `Promise.allSettled([ cartStore.migrateGuestCart(), wishlistStore.migrateGuestWishlist() ])`
   │
[cartStore / wishlistStore: HTTP POST /api/v1/cart/migrate & /api/v1/wishlist/migrate]
   │
[Laravel Backend: CartController::migrate & WishlistController::migrate]
   ├─► Reads `X-Guest-Session-Id` and authenticated `auth('sanctum')->id()`
   ├─► Reassigns all `cart_items.user_id = Auth::id()` and clears `session_id`
   ├─► Reassigns `wishlist_items.user_id = Auth::id()` with duplicate key collision protection
   ├─► Purges stale `GuestSession` records
   │
[Client UI: CartDrawer.vue & WishlistView.vue]
   └─► Items seamlessly reflect under the authenticated user account without data loss.
```
**Audit Finding:** **PASS / FULLY CONNECTED.** Verified in `tests/Feature/Stage4CartWishlistMigrationTest.php` (`test_post_login_cart_and_wishlist_migration`).

---

### Workflow 3: Multi-Step Checkout & Order Execution
```
[User initiates acquisition: CheckoutView.vue]
   │
   ├─► STEP 1 (ShippingStep.vue):
   │      - Cascading PSGC selector: Region -> Province -> City -> Barangay
   │      - NCR logic automatically bypasses province selection
   │      - Calls `POST /api/v1/checkout/shipping-rates` for live courier estimates
   │
   ├─► STEP 2 (PaymentStep.vue):
   │      - Selects payment method: Xendit (GCash/Maya/Card) or COD
   │      - If cart total > ₱3,000, COD option is visually disabled with explanation badge
   │
   ├─► STEP 3 (ReviewStep.vue):
   │      - Verifies authoritative line items, delivery address, shipping fee
   │      - Reviews 12% Philippine VAT breakdown
   │      - Taps "AUTHORIZE ACQUISITION"
   │
[CheckoutView.vue::handleAcquisitionConfirm]
   │
   ├─► HTTP POST /api/v1/checkout/process
   │
[Laravel Backend: routes/api_v1.php:103 ──► ApiCheckoutController::process]
   │
   ├─► Database Transaction (`DB::beginTransaction()`):
   │      - Atomically validates stock availability with `lockForUpdate()`
   │      - Creates `Order` with status `pending`, payment_status `pending`
   │      - Creates child `OrderItem` records
   │      - Inserts `InventoryMovement` with type `order_reservation`
   │      - Decrements `products.stock_quantity` exactly once
   │      - If Xendit: dispatches invoice creation to `XenditPaymentController`
   │      - Emits `OrderCreated` event
   │      - Clears purchased items from cart
   │      - `DB::commit()`
   │
[Response: HTTP 200 JSON]
   │  { "success": true, "order_number": "ECL-20261011-XXXXXX", "invoice_url": "https://checkout.xendit.co/web/..." }
   │
[Step 4: OrderConfirmationView.vue]
   ├─► Opens invoice via `@capacitor/browser` on mobile (or popup window on web)
   ├─► Starts background polling: `GET /api/v1/orders/{orderNumber}/status`
   ├─► Listens for native Capacitor deep link `eclore://payments/xendit/return/success`
   └─► Polling / deep link detects `payment_status === 'paid'` and transitions UI to confirmed state
```
**Audit Finding:** **PASS / FULLY CONNECTED.** Verified in `tests/Feature/Stage5CheckoutTest.php`.

---

### Workflow 4: Official Tax Invoice PDF Generation & Access Control
```
[Client in AccountTab.vue taps "Official Tax Receipt" on Order Card]
   │
[OrderDetailModal.vue::handleDownloadReceipt ──► orders.store.ts::downloadReceiptPdf]
   │
   ├─► HTTP GET /api/v1/account/orders/{orderNumber}/receipt-pdf
   │      - Attached Header: `Authorization: Bearer <sanctum_token>`
   │
[Laravel Backend: routes/api_v1.php:116 ──► AccountController::downloadReceiptPdf]
   │
   ├─► Authorization Guard:
   │      - Queries `Order::where('order_number', $orderNumber)->firstOrFail()`
   │      - Checks ownership: `if ($order->user_id !== $user->id) return response()->json(['message' => 'Unauthorized'], 403);`
   │      - Checks payment: `if ($order->payment_status !== 'paid') return response()->json(['message' => 'Receipt only available for paid orders.'], 403);`
   │
   ├─► Binary Generation via DomPDF:
   │      - Renders `resources/views/receipt-pdf.blade.php` with A4 styling
   │      - Formats BIR registration permit, company TIN, 12% VAT breakdown
   │      - Streams binary response: Content-Type: `application/pdf`, `%PDF-1.4`
   │
[orders.store.ts]
   └─► Converts binary response into Blob object and triggers native browser/device download:
       `eclore-receipt-ECL-20261011-XXXXXX.pdf`
```
**Audit Finding:** **PASS / FULLY CONNECTED.** Verified in `tests/Feature/Stage6AccountOrdersRmaTest.php`.

---

## 2. Route & Endpoint Architecture Comparison

### 2.1 Route Files Structure
The Laravel backend currently maintains three route files:
1. `routes/api_v1.php` (138 lines): The modern, stateless REST API v1 dedicated to the Ionic Vue 3 frontend and native Capacitor clients. Loaded under prefix `/api/v1` in `routes/api.php:8`.
2. `routes/api.php` (371 lines): Legacy AJAX API endpoints used by the original web frontend JavaScript (`resources/js/legacy/api.js`).
3. `routes/web.php` (791 lines): Full server-rendered Blade routes, customer checkout/account session routes, and Admin Backoffice routes (`admin.eclore.test`).

### 2.2 Duplication & Route Shadowing Analysis
- **API Versioning Separation:** The introduction of `/api/v1/*` routes cleanly separates Ionic REST consumers from legacy session-cookie routes.
- **Residual Legacy Endpoints:** `routes/web.php` lines 482–501 and `routes/api.php` lines 13–370 still contain unversioned `/api/cart`, `/api/wishlist`, and `/api/products` endpoints with session-binding. While these do not conflict with `/api/v1/*`, they represent dead code maintenance overhead once Blade is phased out.
- **CMS Route Shadowing Guard:** `routes/web.php:480` properly applies a negative lookahead regex to prevent `/{slug}` from shadowing API, auth, or admin routes:
  ```php
  ->where('slug', '^(?!admin$|test-route$|health$|login$|verify-email-sent$|verify-email$|reset-password$|auth$|checkout$|account$|catalogue$|collections$|newsroom$|api$|contact$|privacy-policy$|terms-of-service$|corporate-responsibility$|help$|accessibility$|orders-payments$|track-order$|boutiques-appointments$)[a-zA-Z0-9\-]+$');
  ```

---

## 3. Authentication, Sessions & Token Architecture

### 3.1 Token Strategy vs Stateful Cookies
| Metric | Original Laravel Storefront | Migrated Ionic Architecture | Parity Status |
|---|---|---|:---:|
| **Identity Mechanism** | Session cookies (`laravel_session`, `XSRF-TOKEN`) | Laravel Sanctum Personal Access Tokens (Bearer) | **Parity Achieved** |
| **Client Storage** | Browser HTTP-Only Cookie jar | Encrypted device storage via `@capacitor/preferences` | **Native Ready** |
| **Guest Identification** | `session()->getId()` binding | Client-generated UUID passed via `X-Guest-Session-Id` header | **Stateless Ready** |
| **Token Revocation** | `Auth::logout()` session flush | Sanctum token deletion (`$user->currentAccessToken()->delete()`) | **Parity Achieved** |
| **CORS / Preflight** | Default Laravel session CORS | Explicit origins: `capacitor://localhost`, `http://localhost:8100`, `supports_credentials: true` | **Configured** |

### 3.2 Guest Session Header Middleware (`HandleGuestSessionHeader.php`)
The middleware extracts `X-Guest-Session-Id` from incoming requests and registers the session in the Laravel container:
```php
if ($guestSessionId = $request->header('X-Guest-Session-Id')) {
    $guestSession = GuestSession::firstOrCreate(
        ['session_id' => $guestSessionId],
        ['last_activity' => now(), 'user_agent' => $request->userAgent(), 'ip_address' => $request->ip()]
    );
    app()->instance('guest.session_id', $guestSessionId);
}
```
This ensures anonymous shopping bags and wishlists persist consistently across mobile restarts without relying on browser cookies.

---

## 4. Disconnected, Mocked & Missing Integrations

### 4.1 Mocked Reviews Section (`ReviewsSection.vue`)
- **Backend Availability:** `GET /api/v1/reviews/{productId}` (`ProductReviewController::index`) and `POST /api/v1/reviews/submit` (`ProductReviewController::store`) are fully defined in `routes/api_v1.php:94-95` and tested in PHPUnit.
- **Frontend Defect:** `frontend-ionic/src/components/ReviewsSection.vue:23-51` ignores the API entirely. It initializes a static Vue ref containing 3 mock reviews and appends newly written reviews to local component memory only (`reviews.value.unshift(...)`).
- **Severity:** High.

### 4.2 Mocked Newsletter Form (`EcloreFooter.vue`)
- **Backend Availability:** `POST /newsletter/subscribe` in `routes/web.php:448`.
- **Frontend Defect:** `frontend-ionic/src/components/EcloreFooter.vue:9-14` has no Axios call. Submitting the form executes:
  ```ts
  function handleSubscribe() {
    if (newsletterEmail.value.trim()) {
      subscribed.value = true;
      newsletterEmail.value = '';
    }
  }
  ```
- **Severity:** Medium.

### 4.3 Disconnected Home Wishlist (`HomeView.vue`)
- **Backend & Store Availability:** `useWishlistStore` (`stores/wishlist.store.ts`) implements reactive item counts and API synchronization.
- **Frontend Defect:** `frontend-ionic/src/views/HomeView.vue:60-68` defines a redundant local array:
  ```ts
  const wishlist = ref<number[]>([]);
  function toggleWishlist(id: number) { ... }
  ```
  Tapping the heart icon on hero products updates local state only; it does not increment the header wishlist counter badge and is not saved to the backend.
- **Severity:** High.

### 4.4 Completely Missing Frontend Features
1. **Google OAuth SSO:** Socialite backend exists, but no Google Sign-In button or `@codetrix-studio/capacitor-google-auth` plugin is configured in `frontend-ionic`.
2. **Customer Notifications:** `NotificationController` and `/api/v1/notifications/*` endpoints are fully functional on the backend, but the Ionic header has no notification bell icon and no `NotificationDrawer.vue`.
3. **Phase 7 Editorial & Institutional Workflows:** Appointments, Journal, Static Pages, Concierge Contact, and Noupe Chatbot widget have no corresponding Vue components.
