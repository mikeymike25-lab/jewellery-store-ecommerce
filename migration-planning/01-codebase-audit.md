# Deliverable 01: Éclore Codebase Audit & Architectural Baseline

**Project:** Éclore Luxury Jewellery E-Commerce Platform  
**Target Migration:** Laravel 12 Monolith → Laravel 12 REST API + Ionic Hybrid Frontend  
**Audit Date:** October 2026  
**Auditor Mode:** Read-Only Static Architectural & Codebase Inspection  
**Audit Document Version:** 1.0.0-final  

---

## 1. Repository-Root Confirmation & Workspace Topography

### 1.1 Workspace Directory Structure
- **Active Workspace Directory:** `c:\Users\Mike Angelo\OneDrive\Documents\jewellery-store`
- **Actual Git Repository Root:** `c:\Users\Mike Angelo\OneDrive\Documents\jewellery-store\jewellery-store-ecommerce`
- **Git Metadata Confirmation:**
  - Branch: `refs/heads/main`
  - Origin URL: `https://github.com/haniluvr/jewellery-store-ecommerce.git`
  - Workspace Context: The top-level workspace folder `jewellery-store` is a container wrapper without a `.git` folder. All application logic, database artifacts, vendor libraries, package manifests, and Git version control reside exclusively in the nested `jewellery-store-ecommerce/` directory.
  - Deliverables Target: `c:\Users\Mike Angelo\OneDrive\Documents\jewellery-store\jewellery-store-ecommerce\migration-planning/`

---

## 2. Validation of Preliminary Discovery Report (`PRE_MIGRATION_DISCOVERY_REPORT.md`)

The preliminary discovery report provided valuable architectural leads. A rigorous file-by-file static audit was performed against the codebase to validate or correct every assertion.

| Discovery Report Finding | Codebase Ground Truth (Verified Status) | File & Line Evidence | Architectural & Migration Impact |
|---|---|---|---|
| **CORS Configuration**<br>`allowed_origins => ['*']`, `supports_credentials => false` | **INCORRECT in Discovery Report**.<br>The actual codebase has `allowed_origins` restricted to specific hosts and `supports_credentials` set to `true`. | `config/cors.php:21-24, 34`:<br>`'allowed_origins' => ['http://127.0.0.1:8080', 'http://localhost:8080']`,<br>`'supports_credentials' => true` | An Ionic app running on `http://localhost:8100`, `capacitor://localhost`, or `ionic://localhost` will be **blocked by CORS** unless origins are updated in `config/cors.php`. |
| **Middleware Aliases**<br>Report claims `admin.auth` and `2fa` aliases in `bootstrap/app.php`. | **PARTIALLY INCORRECT**.<br>The registered admin middleware alias is `'admin'`, not `'admin.auth'`. No `'2fa'` alias exists in `bootstrap/app.php`. | `bootstrap/app.php:27-33`:<br>`'admin' => \App\Http\Middleware\AdminAuth::class`,<br>`'admin.subdomain'`, `'admin.permission'`, `'force.https'`, `'store.intended'` | Routes and documentation referencing `middleware('admin.auth')` or `middleware('2fa')` are inaccurate. Customer 2FA methods in `AccountController` are unrouted. |
| **Tailwind Build Pipeline**<br>Report claims Tailwind v4 Vite build is active. | **CRITICALLY BROKEN**.<br>`package.json:5` script `build:admin-css` calls `tailwindcss -i ./resources/css/admin.css -o ./public/css/admin.css`, but `./resources/css/admin.css` does not exist on disk. Both customer and admin Blade templates bypass local Tailwind and load `https://cdn.tailwindcss.com` directly. | `package.json:5`,<br>`resources/views/layouts/app.blade.php:18`,<br>`resources/views/admin/layouts/app.blade.php:15` | Ionic styling must not depend on external CDNs or broken local scripts; theme variables must be natively encapsulated in the Ionic app. |
| **Duplicated Frontend JS**<br>Report claims `public/frontend/js/` and `resources/js/legacy/` are identical. | **PARTIALLY INCORRECT (Diverged)**.<br>5 files are byte-for-byte identical, but `app.js` has diverged. `resources/js/legacy/app.js` (3,294 lines) has updated `proceedToCheckout()` with cart checkbox validation; `public/frontend/js/app.js` (3,254 lines) has older code. | `resources/js/legacy/app.js:2552-2636`<br>vs<br>`public/frontend/js/app.js:2552-2596` | Vite bundles the newer `legacy/app.js`, while `checkout/layout.blade.php:310` loads the older `public/frontend/js/app.js`. The selection logic in `legacy/app.js` represents the true business requirement. |
| **Missing Customer Views**<br>Report mentions views like `bespoke`, `wishlist`, `product-detail`. | **CONFIRMED MISSING**.<br>Customer views `bespoke.blade.php`, `wishlist.blade.php`, `products.blade.php`, `product-detail.blade.php`, `privacy.blade.php`, `terms.blade.php`, `craftsmanship.blade.php`, `faq.blade.php`, `shipping.blade.php`, `returns.blade.php`, `repair.blade.php` do not exist. | Checked on disk via filesystem inspection. | The application actually uses `home.blade.php`, `catalogue.blade.php`, `product/show.blade.php`, `privacy-policy.blade.php`, `terms-of-service.blade.php`, `care.blade.php`, `help.blade.php`, and offcanvas drawers for cart/wishlist. |
| **Route Duplication & Precedence**<br>Report notes duplicate `/api/*` routes in `web.php` and `api.php`. | **CONFIRMED & CRITICAL**.<br>Laravel 12 loads `routes/api.php` second in `bootstrap/app.php`, meaning `routes/api.php` **overwrites** matching routes in `routes/web.php`. | `bootstrap/app.php:11-16`,<br>`routes/api.php:13-108`,<br>`routes/web.php:487-559` | Calling `POST /api/wishlist/migrate` hits `api.php` where session-based `auth` middleware fails without cookies, returning 401. |

---

## 3. Technology Stack & Runtime Verification

| Component | Repository Config / Manifest | Physical File Location | Runtime Status & Compatibility |
|---|---|---|---|
| **PHP Runtime** | `^8.2` | `composer.json:9`, `Dockerfile:1` | Fully modern PHP 8.2+ typed syntax, constructor promotion, attributes. |
| **Laravel Framework** | `v12.x` (`^12.0`) | `composer.json:13`, `composer.lock` | Laravel 12 application structure (`bootstrap/app.php`, no `Http/Kernel.php`). |
| **Database Engines** | SQLite (`database/database.sqlite`) & MySQL 8.0 / MariaDB | `.env:11`, `.env.example:11`, `config/database.php:19` | Local `.env` uses SQLite (774 KB populated file). MySQL documented for XAMPP. |
| **Asset Bundler** | Vite 7.0.4 + Laravel Vite Plugin 2.0.0 | `package.json:17,21`, `vite.config.js:1-24` | Vite build manifest present (`public/build/manifest.json`). |
| **CSS Framework** | Tailwind CSS v4 + Tailwind CDN v3 + Custom CSS | `package.json:10,20`, `public/frontend/style.css` (3,226 lines) | Hybrid setup with 3,226 lines of luxury editorial CSS in `style.css`. |
| **Document/PDF Engine**| DomPDF `^3.1` (`barryvdh/laravel-dompdf`) | `composer.json:11`, `app/Http/Controllers/Admin/OrderController.php:400` | Fully integrated for generating order invoice & packing slip PDFs. |
| **Payment Gateway** | Xendit Hosted Invoices (`POST https://api.xendit.co/v2/invoices`) | `app/Http/Controllers/Payments/XenditPaymentController.php:107` | Active code path. Adyen (`adyen/php-api-library`) is present in `composer.json` but **completely unused**. |
| **Realtime / Push** | Pusher PHP Server `^7.2` | `composer.json:18`, `config/broadcasting.php` | Broadcast driver set to `log` in `.env.example`. Pusher credentials empty. |
| **Cloud Storage** | Flysystem AWS S3 v3 (`league/flysystem-aws-s3-v3: ^3.30`) | `composer.json:16`, `config/filesystems.php:48-60` | Config has key mismatch (`AWS_S3_ACCESS_KEY_ID` vs `AWS_ACCESS_KEY_ID`). Falls back to `public` disk. |
| **Social Authentication**| Laravel Socialite `^5.23` | `composer.json:14`, `app/Http/Controllers/AuthController.php:440` | Google OAuth handler implemented; client ID/secret unset in `.env`. |

---

## 4. Application Architecture & Portal Topography

The monolithic application serves two distinct portals through a single codebase:

```
                                +-----------------------------------------------------+
                                |                Laravel 12 Monolith                  |
                                +-----------------------------------------------------+
                                                           |
                        +----------------------------------+----------------------------------+
                        |                                                                     |
                        v                                                                     v
          +----------------------------+                                        +----------------------------+
          |    Customer Storefront     |                                        |      Admin Backoffice      |
          +----------------------------+                                        +----------------------------+
          | Domain: eclore.test        |                                        | Domain: admin.eclore.test  |
          | Guard: web (users table)   |                                        | Fallback: root /dashboard  |
          | Architecture: Blade + AJAX |                                        | Guard: admin (employees)   |
          | Target: Full Ionic App     |                                        | Target: Retain Blade Web   |
          +----------------------------+                                        +----------------------------+
```

### 4.1 Customer Storefront Portal
- **Domain & Routing:** Configured for `eclore.test:8080` (or `localhost:8080`). Defined in `routes/web.php:385-780` and `routes/api.php:1-373`.
- **Identity & Guard:** Guard `'web'` backed by `App\Models\User` on `users` table (`config/auth.php:38-41`).
- **Stateful Session Architecture:** Relies on `Illuminate\Session\Middleware\StartSession` and `laravel_session` encrypted cookie. Guest shoppers are tracked via `guest_sessions` table (`app/Models/GuestSession.php`) using `session()->getId()`.
- **UI Architecture:** 36 customer Blade views, 12 partials, 3 modals in `account.blade.php`, 6 global modals in `layouts/app.blade.php`, and 3 global offcanvas drawers (`offcanvas-cart`, `offcanvas-wishlist`, `offcanvas-notification`).

### 4.2 Admin Backoffice Portal
- **Domain & Routing:** Configured for `admin.eclore.test`, `admin.localhost`, `admin.eclorejewellery.shop` (`routes/web.php:375-377`).
- **Critical Routing Finding:** A 4th registration group in `routes/web.php:380-382` registers the entire `$adminRoutes` closure **without a domain constraint** under `admin.subdomain` middleware. Because `AdminSubdomainMiddleware.php:16-30` is a no-op returning `$next($request)`, admin routes are also accessible on the main domain root paths (`/dashboard`, `/login`, `/products`, `/orders`).
- **Identity & Guard:** Guard `'admin'` backed by `App\Models\Admin` (`app/Models/Admin.php:14`, which maps to table `employees`).
- **Authorization & RBAC:** Granular dot-notation permissions (`products.view`, `orders.edit`, etc.) enforced via `admin.permission` middleware (`app/Http/Middleware/CheckAdminPermission.php`).

---

## 5. Exhaustive Route & Endpoint Inventory

### 5.1 `routes/api.php` Complete Inventory
All routes in `routes/api.php` are prefixed with `/api` and assigned the stateless `api` middleware group. None define route names (`->name()`).

| Line Range | HTTP Method | Route URI | Action / Handler | Middleware | Auth Requirement | Purpose |
|---|---|---|---|---|---|---|
| `L10` | `GET` | `/api/newsroom` | `[PageController::class, 'newsroom']` | `api` | None | Returns JSON newsroom articles when `wantsJson()` is true |
| `L13-108` | `GET` | `/api/products` | `Closure(Request $request)` | `api` | None | Jewelry catalog filter (`color`, `material`, `gemstone`, `price`, `room`), sorting, pagination (8/page) |
| `L111-136` | `GET` | `/api/product/{id}` | `Closure($id)` | `api` | None | Fetch single active product by ID with category |
| `L139-185` | `GET` | `/api/products/id/{id}` | `Closure($id)` | `api` | None | Quick-view payload with category, reviews, and average rating |
| `L188-213` | `GET` | `/api/products/{slug}` | `Closure($slug)` | `api` | None | Fetch single active product by slug with category |
| `L216-234` | `GET` | `/api/categories` | `Closure()` | `api` | None | List active categories ordered by `sort_order` |
| `L237-270` | `GET` | `/api/search` | `Closure(Request $request)` | `api` | None | Keyword search on `name`, `description`, `short_description`, `material`, `sku` |
| `L273-279` | `GET` | `/api/test-cart` | `Closure()` | `api` | None | Diagnostics connectivity endpoint |
| `L284-309` | `POST` | `/api/cart/migrate` | `Closure(Request $request)` | `api`, `auth` | Guard: `web` | Reassigns guest `CartItem` rows to authenticated `$userId` |
| `L314` | `POST` | `/api/wishlist/migrate` | `[WishlistController::class, 'migrate']` | `api`, `auth` | Guard: `web` | Reassigns guest `WishlistItem` rows to authenticated `$userId` |
| `L317-345` | `POST` | `/api/products/{id}/track-view` | `Closure($id)` | `api` | None | Increments `view_count` on active product |
| `L348-373` | `POST` | `/api/cart/cleanup` | `Closure(Request $request)` | `api` | **Unprotected** | Deletes expired guest carts and orphaned cart items (Broken schema) |

### 5.2 API-Like Endpoints Defined in `routes/web.php`
These routes are defined inside `routes/web.php` under the `web` middleware group (with session cookies and CSRF).

| Line Range | HTTP Method | Route URI | Action / Handler | Middleware | Route Name | Purpose |
|---|---|---|---|---|---|---|
| `L451` | `GET` | `/api/check-username/{username}` | `AuthController@checkUsername` | `web`, `api.session` | `check.username` | Live username uniqueness check |
| `L452` | `GET` | `/api/check-email/{email}` | `AuthController@checkEmail` | `web`, `api.session` | `check.email` | Live email uniqueness check |
| `L453` | `POST` | `/api/store-intended-url` | `AuthController@storeIntendedUrl` | `web`, `api.session` | `store.intended.url` | Stores intended checkout redirect URL in session |
| `L464` | `GET` | `/api/cart` | `CartController@index` | `web` | — | Returns cart items, subtotal, items count, debug payload |
| `L465` | `POST` | `/api/cart/add` | `CartController@addToCart` | `web` | — | Adds item or increments quantity in `cart_items` |
| `L466` | `PUT` | `/api/cart/update` | `CartController@updateCartItem` | `web` | — | Updates quantity of specific item in `cart_items` |
| `L467` | `DELETE` | `/api/cart/remove` | `CartController@removeFromCart` | `web` | — | Removes item from `cart_items` |
| `L468` | `DELETE` | `/api/cart/clear` | `CartController@clearCart` | `web` | — | Empties user or guest cart |
| `L470` | `POST` | `/api/store-selected-cart-items` | `CartController@storeSelectedItems` | `web` | — | Saves selected item IDs for checkout to session |
| `L474` | `GET` | `/api/wishlist` | `WishlistController@index` | `web`, `api.session` | — | Returns array of user/guest wishlist items |
| `L475` | `POST` | `/api/wishlist/add` | `WishlistController@add` | `web`, `api.session` | — | Adds product to `wishlist_items` |
| `L476` | `DELETE` | `/api/wishlist/remove` | `WishlistController@remove` | `web`, `api.session` | — | Removes product from `wishlist_items` |
| `L477` | `GET` | `/api/wishlist/check/{productId}`| `WishlistController@check` | `web`, `api.session` | — | Returns `{ in_wishlist: boolean }` |
| `L478` | `POST` | `/api/wishlist/toggle` | `WishlistController@toggle` | `web`, `api.session` | — | Toggles product in/out of `wishlist_items` |
| `L479` | `DELETE` | `/api/wishlist/clear` | `WishlistController@clear` | `web`, `api.session` | — | Clears all wishlist items for user/guest |
| `L480` | `POST` | `/api/wishlist/migrate` | `WishlistController@migrate` | `web`, `api.session` | — | Merges guest wishlist into authenticated user account |
| `L484` | `GET` | `/api/weather` | `ApiController@weather` | `web` | `api.weather` | Fetches weather for Paris/Manila from OpenWeatherMap |
| `L487-559` | `GET` | `/api/products` | `Closure(Request $request)` | `web` | — | Catalog product filter (Overwritten by `routes/api.php`) |
| `L569` | `POST` | `/api/account/profile/update` | `AccountController@updateProfile` | `web`, `auth` | — | Updates customer first name, last name, phone, email |
| `L570` | `POST` | `/api/account/password/change` | `AccountController@changePassword`| `web`, `auth` | — | Validates and updates customer password |
| `L571` | `GET` | `/api/account/login-activity` | `AccountController@getLoginActivity`| `web`, `auth` | — | Stub mock data returning IP addresses |
| `L572` | `GET` | `/api/account/payment-methods` | `AccountController@getPaymentMethods`| `web`, `auth` | — | Stub mock data returning dummy card list |
| `L573` | `DELETE` | `/api/account/payment-methods/{id}`| `AccountController@removePaymentMethod`| `web`, `auth` | — | Stub mock endpoint |
| `L574` | `POST` | `/api/account/newsletter/update` | `AccountController@updateNewsletterPreferences`| `web`, `auth` | — | Updates newsletter subscription preferences |
| `L575` | `POST` | `/api/account/address/add` | `AccountController@addAddress` | `web`, `auth` | — | Saves new default Philippine address to `users` |
| `L576` | `POST` | `/api/account/address/update` | `AccountController@updateAddress` | `web`, `auth` | — | Updates existing Philippine address on `users` |
| `L577` | `POST` | `/api/account/archive` | `AccountController@archiveAccount`| `web`, `auth` | — | Soft-deletes user, archives to `archived_users` |
| `L578` | `POST` | `/api/account/logout` | `AccountController@logout` | `web`, `auth` | — | Destroys session, returns JSON success |
| `L579` | `GET` | `/api/account/orders` | `AccountController@getOrders` | `web`, `auth` | — | Returns paginated customer order history partial |
| `L583` | `GET` | `/api/notifications` | `NotificationController@getUserNotifications`| `web`, `auth` | — | Returns user notifications list |
| `L584` | `GET` | `/api/notifications/unread-count`| `NotificationController@getUnreadCount`| `web`, `auth` | — | Returns unread notification badge count |
| `L585` | `POST` | `/api/notifications/{id}/read` | `NotificationController@markAsRead` | `web`, `auth` | — | Marks single notification as read |
| `L586` | `POST` | `/api/notifications/read-all` | `NotificationController@markAllAsRead`| `web`, `auth` | — | Marks all user notifications as read |
| `L587` | `DELETE` | `/api/notifications/{id}` | `NotificationController@deleteNotification`| `web`, `auth` | — | Deletes single notification |
| `L588` | `DELETE` | `/api/notifications/clear-all` | `NotificationController@clearAll` | `web`, `auth` | — | Deletes all notifications (Security bug: deletes global) |
| `L591` | `POST` | `/api/reviews/submit` | `ProductReviewController@store` | `web`, `auth` | `reviews.store` | Submits verified customer review with rating |
| `L615-619` | `ANY` | `/api/payment-methods/*` | `PaymentMethodController` | `web`, `auth` | `payment-methods.*` | Real CRUD for saved payment methods (`card`, `gcash`) |
| `L623` | `GET` | `/api/reviews/{productId}` | `ProductReviewController@index` | `web` | `api.reviews.index` | Returns approved reviews for product ID |
| `L717-759` | `GET` | `/api/user/check` | `Closure` | `web` | — | Diagnostic endpoint checking session authentication state |

---

## 6. Critical Security, Session & Integration Audit

### 6.1 Session & Cookie Leakage in `CartController.php`
- **Location:** `app/Http/Controllers/CartController.php:64-71` (`index()`) and `lines 176-184` (`addToCart()`).
- **Code:**
  ```php
  'session_id' => $sessionId,
  'debug' => [
      'session_id' => $sessionId,
      'user_id' => $userId,
      'items_count' => $cartItems->count(),
      'request_cookies' => $request->cookies->all(),
      'session_cookie' => $request->cookie('laravel_session'),
  ],
  ```
- **Vulnerability:** `config/session.php:192` configures `http_only => true` to prevent client-side JavaScript from accessing the session cookie. However, serializing `$request->cookies->all()` and `$request->cookie('laravel_session')` directly into the JSON body completely breaks `HttpOnly` protection. Any client-side XSS vulnerability would immediately obtain the plaintext session cookie and hijack customer accounts.
- **Remediation:** Remove the entire `debug` payload and `session_cookie` from both responses.

### 6.2 Xendit Payment Integration & Double-Decrement Idempotency Bug
- **Location:** `app/Http/Controllers/Payments/XenditPaymentController.php:233-287` (`returnSuccess()`) and `lines 399-452` (`webhook()`).
- **Mechanism:**
  1. Customer completes payment on Xendit.
  2. Xendit dispatches an asynchronous server-to-server webhook to `POST /webhooks/xendit`.
  3. Customer browser simultaneously redirects to `GET /payments/xendit/return/success?order=ORD-...`.
- **Flaw:** Neither method verifies whether `$order->payment_status` is already `'paid'` before taking action. If both execute, both invoke `InventoryMovement::recordStockOut(...)` and dispatch duplicate order notifications. If Xendit retries a webhook, stock is decremented repeatedly.
- **Timing Attack on Callback Token:** `XenditPaymentController.php:362` checks `$token !== $expectedToken` using standard `!==` instead of `hash_equals()`.
- **Arbitrary Failure Status:** `returnFailed()` (`lines 332-333`) marks `$order->payment_status = 'failed'` without checking if the order was already paid or validating with Xendit.

### 6.3 Notification Isolation & Data Corruption Bug
- **Location:** `app/Http/Controllers/NotificationController.php:24-28, 77-83, 104-110, 132-138, 159-164`.
- **Mechanism:** Queries chain `->orWhere('recipient_type', 'all')` at the top level of the query builder without wrapping in a parameter grouping closure.
  ```php
  // Generated SQL: WHERE (id = ? AND (recipient_type = 'user' AND recipient_id = ?)) OR recipient_type = 'all'
  ```
- **Flaw:** In `markAsRead($id)` and `deleteNotification($id)`, if any global broadcast notification exists, it satisfies the `OR` branch regardless of `$id`. In `clearAll()`, any authenticated user executing `DELETE /api/notifications/clear-all` deletes all global notifications for **all users** in the database.

### 6.4 AWS S3 Environment Configuration Key Mismatch
- **Location:** `config/filesystems.php:50-51`.
- **Code:**
  ```php
  'key' => env('AWS_S3_ACCESS_KEY_ID'),
  'secret' => env('AWS_S3_SECRET_ACCESS_KEY'),
  ```
- **Flaw:** Standard AWS conventions and the project's own `.env.example:40-41` use `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY`. Because of this naming mismatch, setting standard AWS environment variables will silently fail to authenticate with S3, causing file uploads to crash or fall back unexpectedly.

### 6.5 Username & Email Enumeration
- **Location:** `app/Http/Controllers/AuthController.php:68-89`.
- **Flaw:** `GET /api/check-username/{username}` and `GET /api/check-email/{email}` are unauthenticated, unthrottled endpoints that directly return whether a given username or email exists in the database.

---

## 7. Database Verification: Tables, Seeders & Inconsistencies

### 7.1 Verified Table Inventory (35 Tables)
The database structure is defined across 3 migration files in `database/migrations/` (`2026_03_25_000000_create_unified_production_schema.php`, `2026_03_28_150722_update_cms_pages_type_enum.php`, `2026_04_21_000000_update_2fa_default_on_employees.php`):

1. `users` (Customer identity, soft deletes)
2. `archived_users` (Anonymized account deletion records)
3. `password_reset_tokens` (Auth password resets)
4. `magic_links` (Passwordless & verification tokens)
5. `guest_sessions` (Guest cart/wishlist tracking)
6. `sessions` (Laravel database session driver)
7. `cache` (Database cache driver)
8. `cache_locks` (Database cache locking)
9. `jobs` (Database queue jobs)
10. `job_batches` (Database queue batches)
11. `failed_jobs` (Failed job tracking)
12. `employees` (Staff/admin identity, auth guard: `admin`)
13. `admin_permissions` (Granular RBAC permissions)
14. `categories` (Jewellery categories & subcategories)
15. `products` (Jewellery catalog with JSON attributes)
16. `product_reviews` (Verified customer reviews & ratings)
17. `product_popularity` (Popularity score cache table)
18. `wishlist_items` (User and guest wishlist entries)
19. `cart_items` (User and guest shopping cart items)
20. `orders` (Order header, status, payment details)
21. `order_items` (Order line items & product snapshot)
22. `order_fulfillment` (Pick, pack, ship lifecycle)
23. `order_activities` (Audit trail of order state changes)
24. `returns_repairs` (RMA after-sales service tickets)
25. `shipping_methods` (Delivery rates & zones)
26. `payment_gateways` (Gateway definitions & config)
27. `payment_methods` (Saved customer cards & GCash)
28. `inventory_movements` (Stock in/out/adjustment ledger)
29. `cms_pages` (Institutional pages, blog journal)
30. `contact_messages` (Inquiries & concierge contact)
31. `newsletter_subscriptions` (Email marketing subscriptions)
32. `appointments` (Private salon bookings)
33. `notifications` (User & admin alert center)
34. `settings` (Key-value system configuration)
35. `audit_logs` (Administrative audit trail)

---

### 7.2 The 8 Target Discrepancies + Additional Bugs Audit

#### Discrepancy 1: Orphan `Cart` Model & Broken `/api/cart/cleanup` Route
- **File & Lines:** `app/Models/Cart.php:1-30`, `routes/api.php:348-373`.
- **Status:** **CONFIRMED REAL BUG**.
- **Evidence:** `app/Models/Cart.php` maps to a non-existent `carts` table and defines `$this->hasMany(CartItem::class)`. `routes/api.php:351` executes `\App\Models\Cart::whereNull('user_id')->delete()`.
- **Impact:** Calling `POST /api/cart/cleanup` throws `SQLSTATE[42S02]: Base table or view not found: carts`.
- **Remediation:** Remove `app/Models/Cart.php`. Rewrite `/api/cart/cleanup` to target `guest_sessions` and `cart_items` (`CartItem::whereNull('user_id')->where('created_at', '<', now()->subDays(30))->delete()`).

#### Discrepancy 2: Orphan `Wishlist` Model
- **File & Lines:** `app/Models/Wishlist.php:1-25`.
- **Status:** **CONFIRMED REAL DISCREPANCY**.
- **Evidence:** `app/Models/Wishlist.php` points to table `wishlists`. The actual schema uses `wishlist_items` via `App\Models\WishlistItem`. `Wishlist.php` is never imported or used anywhere in `app/` or `routes/`.
- **Remediation:** Deprecate/delete `app/Models/Wishlist.php`. Retain `App\Models\WishlistItem`.

#### Discrepancy 3: Missing `Coupon` Model & `Order::coupons()` Relationship
- **File & Lines:** `app/Models/Order.php:79-84`.
- **Status:** **CONFIRMED REAL DISCREPANCY**.
- **Evidence:** `Order.php:81` defines `$this->belongsToMany(Coupon::class, 'coupon_usage')`. Neither `App\Models\Coupon` nor `coupon_usage` table exists.
- **Additional Bug:** `Order.php:69` defines relationship `orderItems()`, but `Admin\OrderController.php:404, 422` and `Admin\BulkActionController.php:262, 284` call `$order->load('items.product')` and `$order->items->count()`. Calling invoice/packing slip downloads throws `RelationNotFoundException`.
- **Remediation:** Remove `coupons()` relationship or add feature in future phase. Add alias `public function items() { return $this->orderItems(); }` in `app/Models/Order.php`.

#### Discrepancy 4: Legacy `products.room_category` Queries
- **File & Lines:** `app/Http/Controllers/ProductController.php:23-30`, `routes/api.php:59-64`, `routes/web.php:500-507`.
- **Status:** **CONFIRMED REAL BUG (Inherited from David's Wood Furniture)**.
- **Evidence:** When `?room=` parameter is provided, code runs:
  ```php
  $query->whereNotNull('room_category')
      ->where('room_category', '!=', '[]')
      ->whereJsonContains('room_category', $roomValue);
  ```
  The `products` table has no `room_category` column.
- **Impact:** Filtering by room causes a fatal SQL error `no such column: room_category`.
- **Remediation:** Remove `room_category` query branches from all controllers and routes. Replace with jewelry filters (`material`, `gemstone`, `collection`).

#### Discrepancy 5: `Employee` & `Admin` Models vs `employees` Table Schema
- **File & Lines:** `app/Models/Employee.php:15, 26-28, 126-131`, `app/Models/Admin.php:17, 28-29, 38`.
- **Status:** **CONFIRMED REAL DISCREPANCY**.
- **Evidence:** `Employee.php` defines `$fillable` with `employee_id`, `hire_date`, `salary`, `employment_status`. `scopeActive()` requires `where('employment_status', 'active')` which crashes on `employees` table. `Admin.php:38` includes `notification_preferences`, which causes `Admin\ProfileController.php:374` to crash when saving notification settings.
- **Remediation:** Align models with `employees` table (`status` enum: `active`, `inactive`, `suspended`). Update `Employee::scopeActive()` to match `Admin::scopeActive()` (`where('status', 'active')`).

#### Discrepancy 6: `Admin\MessageController` & `ContactMessage` Column Mismatches
- **File & Lines:** `app/Http/Controllers/Admin/MessageController.php:17, 65, 87, 139`, `app/Models/ContactMessage.php:16-21`.
- **Status:** **CONFIRMED REAL BUG**.
- **Evidence:** `contact_messages` table only has `admin_notes` and `timestamps`. `MessageController` queries and updates `internal_notes`, `tags`, `assigned_to`, `read_at`, `responded_at`, `responded_by`. Furthermore, lines 87 and 139 validate `'assigned_to' => 'exists:admins,id'`, but the table is `employees`, throwing `Base table or view not found: admins`.
- **Remediation:** Update validation rules to `'exists:employees,id'`. Add missing columns to `contact_messages` or align controller with existing `admin_notes` column.

#### Discrepancy 7: `User` Model `$fillable` vs `users` Table Philippine Columns
- **File & Lines:** `app/Models/User.php:32-46`, `app/Http/Controllers/AuthController.php:56`, `app/Http/Controllers/Admin/UserController.php:122, 207-225`.
- **Status:** **CONFIRMED REAL BUG**.
- **Evidence:** `users` table uses Philippine columns (`street`, `barangay`, `city`, `province`, `region`, `zip_code`). However, `User::$fillable` contains US address columns (`address_line_1`, `address_line_2`, `state`, `postal_code`, `country`, `date_of_birth`, `marketing_emails`).
- **Impact:** `AuthController.php:56` inserts `'marketing_emails' => ...` during registration, which crashes if strict SQL mode is enabled or throws column-not-found error. `Admin\UserController.php` also crashes when updating user profiles.
- **Remediation:** Clean `User::$fillable` to match `users` migration columns; add `tags` and `admin_notes` to `$fillable`.

#### Discrepancy 8: MySQL-Specific SQL Functions on SQLite
- **File & Lines:** `app/Http/Controllers/Admin/DashboardController.php:104, 110`, `app/Http/Controllers/Admin/AnalyticsController.php:747`.
- **Status:** **CONFIRMED REAL INCOMPATIBILITY**.
- **Evidence:** `DashboardController.php:104` executes `selectRaw('MONTH(created_at) as month, SUM(total_amount) as revenue')`. `MONTH()` does not exist in SQLite (requires `strftime('%m', created_at)`).
- **Impact:** Loading the Admin Dashboard while `.env` is set to `DB_CONNECTION=sqlite` throws a fatal SQL error `no such function: MONTH`.
- **Remediation:** Use portable query expressions branching on `DB::connection()->getDriverName()` as already implemented elsewhere in `AnalyticsController.php:522`.

#### Additional Discovered Bugs
- **Seeder Unique Constraint Violation:** `database/seeders/WishlistItemSeeder.php:19-25` seeds 400 random `(user_id, product_id)` pairs without checking for duplicates, triggering `Integrity constraint violation: UNIQUE constraint failed: wishlist_items.user_id, wishlist_items.product_id`.
- **Seeder Column Error:** `database/seeders/FilipinoUserSeeder.php:46` attempts to seed `'marketing_emails'`, which does not exist in `users`.
- **Missing Seeder Record:** `database/seeders/PaymentGatewaySeeder.php` seeds `paymongo`, `paypal`, and `bank_transfer`, but fails to seed `xendit`, breaking Xendit lookups.
- **Broken Mailable Images:** `app/Mail/AbandonedCartMail.php:69` eager-loads `product.images`, but `images` is a JSON attribute on `Product`, not an Eloquent relation.

---

## 8. Legacy Codebase Lineage (`davids-wood-furniture`) & Artifacts

The application was adapted from a prior project (`davids-wood-furniture`). Residual artifacts were cataloged:

| Category | File Path | Line(s) | Residual Content / Hardcoded Path | Recommended Action |
|---|---|---|---|---|
| **App Code** | `app/Http/Controllers/AuthController.php` | `L527` | `"Welcome to David's Wood Furniture."` | Update copy to Éclore Maison. |
| **App Mail** | `app/Mail/AdminWelcomeMail.php` | `L35` | `"Welcome to David's Wood Admin Panel!"` | Update copy to Éclore Maison. |
| **Health Check** | `public/health.php` | `L13` | `'service' => 'davids-wood-furniture'` | Update service name to `eclore-jewellery`. |
| **Admin Settings** | `admin/settings/index.blade.php` | `L59` | `"Premium wood furniture for your home"` | Update default placeholder to luxury jewellery copy. |
| **Email Previews** | `app/Http/Controllers/Admin/EmailPreviewController.php` | `L153, 191, 202, 229` | `"DW-2024-001"`, `"Oak Dining Table"`, `"Oak Dining Chairs"` | Update preview mocks to Éclore jewellery creations. |
| **Apache Config** | `.apache/eclore.conf` | `L10, 13, 34, 37` | `DocumentRoot "d:/Projects/eclore/public"` | Update documentation with local machine instructions. |
| **Deployment** | `deploy.sh` | `L2, 9, 10` | `"David's Wood Furniture E-commerce"`, `/var/www/davids-wood-furniture` | Update deployment variables for Éclore. |
| **Server Setup** | `scripts/setup-ec2-server.sh` | `L177, 188, 310` | `/var/www/davids-wood-furniture`, `dbname=eclore_furniture` | Update server provisioning scripts. |
| **Documentation**| `README.md` | Multiple | 18 references to `davids-wood-furniture` and `C:/xampp/...` paths | Modernize README for Éclore Ionic architecture. |

---

## 9. Test Suite & Coverage Assessment

### 9.1 Existing PHPUnit Setup (`phpunit.xml`)
- **Configuration:** Sets `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `CACHE_STORE=array`, `SESSION_DRIVER=array`, `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`.
- **Base Test Case:** `tests/TestCase.php:10` includes `use RefreshDatabase;`, meaning any test extending `TestCase` executes migrations against in-memory SQLite.

### 9.2 Test Inventory
- `tests/Feature/ExampleTest.php`:
  - `test_the_application_returns_a_successful_response()`: Tests `GET /test-route` -> asserts 200.
  - `test_health_endpoint_works()`: Tests `GET /health` -> asserts 200 and JSON status structure.
- `tests/Unit/ExampleTest.php`:
  - `test_that_true_is_true()`: Basic assertion.
- **Coverage Verdict:** Automated test coverage for business logic (cart calculations, inventory decrement, Xendit verification, address validation, authentication) is currently **0%**. A comprehensive API contract and feature test suite must be introduced as part of the migration quality gates.

---

## 10. Audit Conclusions & Migration Implications

1. **Backend Viability:** The Laravel 12 backend is rich, feature-complete, and fully capable of serving an Ionic frontend once decoupled from stateful web sessions. Wholesale backend replacement is unnecessary and counterproductive.
2. **Sanctum Prerequisite:** Transitioning to Ionic requires installing `laravel/sanctum` for Bearer token authentication to support mobile and cross-origin PWA clients.
3. **API Normalization:** Duplicate `/api/*` routes between `routes/web.php` and `routes/api.php` must be consolidated under a standardized, versioned `/api/v1/*` namespace with dedicated FormRequests.
4. **Targeted Backend Remediation:** The 8 verified schema discrepancies and controller bugs must be fixed in the backend compatibility phase before pointing the Ionic application at those endpoints.
5. **Brand Asset Fidelity:** All luxury design tokens, typography, and visual assets are verified on disk and ready for direct porting into the Ionic design system.
