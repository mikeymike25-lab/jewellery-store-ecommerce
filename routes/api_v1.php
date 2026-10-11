<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Api\V1\AuthController as ApiAuthController;
use App\Http\Controllers\Api\V1\CheckoutController as ApiCheckoutController;
use App\Http\Controllers\Api\V1\ProductController as ApiProductController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\RefundRequestController;
use App\Http\Controllers\WishlistController;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Éclore Luxury Jewellery REST API v1 Routes
|--------------------------------------------------------------------------
|
| Stateless REST API for the Ionic Vue 3 frontend and native Capacitor clients.
| Authentication is managed via Laravel Sanctum personal access tokens.
| Guest cart/wishlist operations are tracked via the X-Guest-Session-Id header.
|
*/

// ==========================================
// 1. Authentication & Identity
// ==========================================
Route::prefix('auth')->group(function () {
    Route::post('/token', [ApiAuthController::class, 'token'])->name('api.v1.auth.token');
    Route::post('/register', [ApiAuthController::class, 'register'])->name('api.v1.auth.register');
    Route::post('/forgot-password', [ApiAuthController::class, 'forgotPassword'])->name('api.v1.auth.forgot-password');
    Route::post('/reset-password', [ApiAuthController::class, 'resetPassword'])->name('api.v1.auth.reset-password');
    Route::get('/check-username/{username}', [AuthController::class, 'checkUsername'])->name('api.v1.auth.check-username');
    Route::get('/check-email/{email}', [AuthController::class, 'checkEmail'])->name('api.v1.auth.check-email');
    Route::get('/google/redirect', [ApiAuthController::class, 'googleRedirect'])->name('api.v1.auth.google.redirect');
    Route::get('/google/callback', [ApiAuthController::class, 'googleCallback'])->name('api.v1.auth.google.callback');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [ApiAuthController::class, 'user'])->name('api.v1.auth.user');
        Route::post('/logout', [ApiAuthController::class, 'logout'])->name('api.v1.auth.logout');
    });
});

// ==========================================
// 2. Product Catalog, Categories & Search
// ==========================================
Route::get('/products', [ApiProductController::class, 'index'])->name('api.v1.products.index');
Route::get('/products/{idOrSlug}', [ApiProductController::class, 'show'])->name('api.v1.products.show');
Route::get('/categories', [ApiProductController::class, 'categories'])->name('api.v1.categories.index');
Route::get('/search', [ApiProductController::class, 'search'])->name('api.v1.search');

Route::post('/products/{id}/track-view', function ($id) {
    $product = Product::where('id', $id)->where('is_active', true)->first();
    if (! $product) {
        return response()->json(['success' => false, 'message' => 'Product not found'], 404);
    }
    $product->increment('view_count');

    return response()->json(['success' => true, 'view_count' => $product->view_count]);
})->name('api.v1.products.track-view');

// ==========================================
// 3. Shopping Cart (Guest & Authenticated)
// ==========================================
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('api.v1.cart.index');
    Route::post('/add', [CartController::class, 'addToCart'])->name('api.v1.cart.add');
    Route::put('/update', [CartController::class, 'updateCartItem'])->name('api.v1.cart.update');
    Route::delete('/remove', [CartController::class, 'removeFromCart'])->name('api.v1.cart.remove');
    Route::delete('/clear', [CartController::class, 'clearCart'])->name('api.v1.cart.clear');
    Route::post('/selected-items', [CartController::class, 'storeSelectedItems'])->name('api.v1.cart.selected-items');
    Route::post('/migrate', [CartController::class, 'migrate'])->name('api.v1.cart.migrate');
});

// ==========================================
// 4. Wishlist
// ==========================================
Route::prefix('wishlist')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('api.v1.wishlist.index');
    Route::post('/add', [WishlistController::class, 'add'])->name('api.v1.wishlist.add');
    Route::delete('/remove', [WishlistController::class, 'remove'])->name('api.v1.wishlist.remove');
    Route::get('/check/{productId}', [WishlistController::class, 'check'])->name('api.v1.wishlist.check');
    Route::post('/toggle', [WishlistController::class, 'toggle'])->name('api.v1.wishlist.toggle');
    Route::delete('/clear', [WishlistController::class, 'clear'])->name('api.v1.wishlist.clear');
    Route::post('/migrate', [WishlistController::class, 'migrate'])->name('api.v1.wishlist.migrate');
});

// ==========================================
// 5. Product Reviews
// ==========================================
Route::get('/reviews/{productId}', [ProductReviewController::class, 'index'])->name('api.v1.reviews.index');
Route::middleware('auth:sanctum')->post('/reviews/submit', [ProductReviewController::class, 'store'])->name('api.v1.reviews.store');

// ==========================================
// 6. Checkout, Orders & Status Polling
// ==========================================
Route::middleware('auth:sanctum')->prefix('checkout')->group(function () {
    Route::get('/init', [ApiCheckoutController::class, 'init'])->name('api.v1.checkout.init');
    Route::post('/shipping-rates', [ApiCheckoutController::class, 'shippingRates'])->name('api.v1.checkout.shipping-rates');
    Route::post('/process', [ApiCheckoutController::class, 'process'])->name('api.v1.checkout.process');
});

Route::get('/orders/{orderNumber}/status', [ApiCheckoutController::class, 'status'])->name('api.v1.orders.status');
Route::get('/orders/{orderNumber}', [ApiCheckoutController::class, 'show'])->name('api.v1.orders.show');
Route::post('/orders/track', [OrderController::class, 'trackOrderApi'])->name('api.v1.orders.track');
Route::post('/orders/{orderNumber}/simulate-payment', [ApiCheckoutController::class, 'simulatePayment'])->name('api.v1.orders.simulate-payment');

// ==========================================
// 7. Customer Account (Sanctum Protected)
// ==========================================
Route::middleware('auth:sanctum')->prefix('account')->group(function () {
    Route::get('/orders', [AccountController::class, 'getOrders'])->name('api.v1.account.orders');
    Route::get('/orders/{orderNumber}/receipt-pdf', [AccountController::class, 'downloadReceiptPdf'])->name('api.v1.account.orders.receipt-pdf');
    Route::post('/profile/update', [AccountController::class, 'updateProfile'])->name('api.v1.account.profile.update');
    Route::post('/address/update', [AccountController::class, 'updateAddress'])->name('api.v1.account.address.update');
    Route::post('/address/add', [AccountController::class, 'addAddress'])->name('api.v1.account.address.add');
    Route::post('/password/change', [AccountController::class, 'changePassword'])->name('api.v1.account.password.change');
    Route::get('/payment-methods', [AccountController::class, 'getPaymentMethods'])->name('api.v1.account.payment-methods');
    Route::delete('/payment-methods/{id}', [AccountController::class, 'removePaymentMethod'])->name('api.v1.account.payment-methods.remove');
    Route::get('/returns-repairs', [RefundRequestController::class, 'index'])->name('api.v1.account.returns-repairs.index');
    Route::post('/returns-repairs', [RefundRequestController::class, 'store'])->name('api.v1.account.returns-repairs.store');
    Route::get('/appointments', [AppointmentController::class, 'userAppointments'])->name('api.v1.account.appointments');
});

// ==========================================
// 8. Notifications (Sanctum Protected)
// ==========================================
Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'getUserNotifications'])->name('api.v1.notifications.index');
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('api.v1.notifications.unread-count');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.v1.notifications.read');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('api.v1.notifications.read-all');
    Route::delete('/{id}', [NotificationController::class, 'deleteNotification'])->name('api.v1.notifications.delete');
    Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('api.v1.notifications.clear-all');
});

// ==========================================
// 9. Newsletter
// ==========================================
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('api.v1.newsletter.subscribe');

// ==========================================
// 10. Boutiques & Salon Appointments
// ==========================================
Route::get('/boutiques', [PageController::class, 'getBoutiquesApi'])->name('api.v1.boutiques.index');
Route::post('/appointments/book', [AppointmentController::class, 'store'])->name('api.v1.appointments.book');

// ==========================================
// 11. Journal & Editorial Stories
// ==========================================
Route::get('/stories', [PageController::class, 'getStoriesApi'])->name('api.v1.stories.index');
Route::get('/stories/{slug}', [PageController::class, 'getStoryDetailApi'])->name('api.v1.stories.show');

// ==========================================
// 12. Institutional & CMS Pages
// ==========================================
Route::get('/content/{slug}', [PageController::class, 'getContentPageApi'])->name('api.v1.content.show');

// ==========================================
// 13. Noupe AI Concierge Chat
// ==========================================
Route::post('/concierge/chat', [PageController::class, 'conciergeChatApi'])->name('api.v1.concierge.chat');

// ==========================================
// 14. Customer Contact Inquiries
// ==========================================
Route::post('/contact', [ContactController::class, 'store'])->name('api.v1.contact.store');


