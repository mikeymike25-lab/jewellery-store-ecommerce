<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\GuestSession;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Get or create guest session.
     */
    private function getOrCreateGuestSession(string $sessionId): GuestSession
    {
        return GuestSession::findOrCreateSession($sessionId);
    }

    /**
     * Resolve session identifier from header or PHP session.
     */
    private function resolveSessionId(Request $request): string
    {
        $guestHeader = $request->header('X-Guest-Session-Id');
        if (! empty($guestHeader) && is_string($guestHeader)) {
            return $guestHeader;
        }

        if (! session()->isStarted()) {
            session()->start();
        }

        return session()->getId();
    }

    /**
     * Resolve authenticated user ID from Sanctum token or session.
     */
    private function resolveUserId(Request $request): ?int
    {
        return $request->user('sanctum')?->id ?? $request->user()?->id ?? \Auth::id();
    }

    /**
     * Get cart items for current user or guest.
     */
    public function index(Request $request)
    {
        try {
            $sessionId = $this->resolveSessionId($request);
            $userId = $this->resolveUserId($request);

            if ($userId) {
                // User is logged in - get their cart items
                $cartItems = CartItem::forUser($userId)
                    ->with('product')
                    ->get();
            } else {
                // Guest user - get session cart items
                $guestSession = $this->getOrCreateGuestSession($sessionId);
                $cartItems = CartItem::forGuest($sessionId)
                    ->with('product')
                    ->get();
            }

            // Synchronize item prices with live product prices from Admin
            foreach ($cartItems as $item) {
                if ($item->product) {
                    $livePrice = (float) $item->product->price;
                    if ((float) $item->unit_price !== $livePrice) {
                        $item->unit_price = $livePrice;
                        $item->total_price = $livePrice * $item->quantity;
                        $item->save();
                    }
                }
            }

            $subtotal = $cartItems->sum('total_price');
            $totalItems = $cartItems->count(); // Count number of items, not quantity

            return response()->json([
                'success' => true,
                'data' => [
                    'cart_items' => $cartItems,
                    'subtotal' => $subtotal,
                    'total_items' => $totalItems,
                ],
                'session_id' => $sessionId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cart: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add item to cart.
     */
    public function addToCart(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'quantity' => 'integer|min:1',
            ]);

            $sessionId = $this->resolveSessionId($request);
            $productId = $request->product_id;
            $quantity = $request->quantity ?? 1;
            $userId = $this->resolveUserId($request);

            // Get the product
            $product = Product::findOrFail($productId);

            if ($userId) {
                // User is logged in
                $existingItem = CartItem::forUser($userId)
                    ->where('product_id', $productId)
                    ->first();

                if ($existingItem) {
                    // Update existing quantity
                    $existingItem->quantity += $quantity;
                    $existingItem->total_price = $existingItem->quantity * $existingItem->unit_price;
                    $existingItem->save();
                } else {
                    // Create new item
                    CartItem::create([
                        'user_id' => $userId,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'total_price' => $product->price * $quantity,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'product_data' => [
                            'image' => is_array($product->images) && ! empty($product->images) ? $product->images[0] : null,
                            'slug' => $product->slug,
                            'description' => $product->short_description,
                            'size' => $request->input('size') ?? $request->input('ring_size'),
                            'metal_type' => $request->input('metal_type'),
                        ],
                    ]);
                }
            } else {
                // Guest user
                $this->getOrCreateGuestSession($sessionId);

                $existingItem = CartItem::forGuest($sessionId)
                    ->where('product_id', $productId)
                    ->first();

                if ($existingItem) {
                    // Update existing quantity
                    $existingItem->quantity += $quantity;
                    $existingItem->total_price = $existingItem->quantity * $existingItem->unit_price;
                    $existingItem->save();
                } else {
                    // Create new item
                    CartItem::create([
                        'session_id' => $sessionId,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'total_price' => $product->price * $quantity,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'product_data' => [
                            'image' => is_array($product->images) && ! empty($product->images) ? $product->images[0] : null,
                            'slug' => $product->slug,
                            'description' => $product->short_description,
                            'size' => $request->input('size') ?? $request->input('ring_size'),
                            'metal_type' => $request->input('metal_type'),
                        ],
                    ]);
                }
            }

            // Force session save if started
            if (session()->isStarted()) {
                session()->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'session_id' => $sessionId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding to cart: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update cart item quantity.
     */
    public function updateCartItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        if ($request->quantity <= 0) {
            return $this->removeFromCart($request);
        }

        $userId = $this->resolveUserId($request);
        $sessionId = $this->resolveSessionId($request);

        if ($userId) {
            $cartItem = CartItem::forUser($userId)
                ->where('product_id', $request->product_id)
                ->first();
        } else {
            $cartItem = CartItem::forGuest($sessionId)
                ->where('product_id', $request->product_id)
                ->first();
        }

        if ($cartItem) {
            $cartItem->quantity = $request->quantity;
            $cartItem->total_price = $cartItem->quantity * $cartItem->unit_price;
            $cartItem->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $userId = $this->resolveUserId($request);
        $sessionId = $this->resolveSessionId($request);

        if ($userId) {
            CartItem::forUser($userId)
                ->where('product_id', $request->product_id)
                ->delete();
        } else {
            CartItem::forGuest($sessionId)
                ->where('product_id', $request->product_id)
                ->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully',
        ]);
    }

    /**
     * Clear cart.
     */
    public function clearCart(Request $request)
    {
        $userId = $this->resolveUserId($request);
        $sessionId = $this->resolveSessionId($request);

        if ($userId) {
            CartItem::forUser($userId)->delete();
        } else {
            CartItem::forGuest($sessionId)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
        ]);
    }

    /**
     * Migrate guest cart items to user account when they log in.
     */
    public function migrateCartToUser($userId, $sessionId)
    {
        try {
            DB::transaction(function () use ($userId, $sessionId) {
                // Get all guest cart items
                $guestCartItems = CartItem::forGuest($sessionId)->get();

                foreach ($guestCartItems as $guestItem) {
                    // Check if user already has this product in their cart
                    $existingUserItem = CartItem::forUser($userId)
                        ->where('product_id', $guestItem->product_id)
                        ->first();

                    if ($existingUserItem) {
                        // Merge quantities
                        $existingUserItem->quantity += $guestItem->quantity;
                        $existingUserItem->total_price = $existingUserItem->quantity * $existingUserItem->unit_price;
                        $existingUserItem->save();
                    } else {
                        // Transfer item to user
                        $guestItem->update([
                            'user_id' => $userId,
                            'session_id' => null,
                        ]);
                    }
                }

                // Delete any remaining guest items (duplicates that were merged)
                CartItem::forGuest($sessionId)->delete();

                // Clean up guest session
                GuestSession::where('session_id', $sessionId)->delete();
            });
        } catch (\Exception $e) {
            \Log::error('Cart migration failed', [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear user cart (called when user logs out).
     */
    public function clearUserCart($userId)
    {
        try {
            CartItem::forUser($userId)->delete();
        } catch (\Exception $e) {
            \Log::error('Failed to clear user cart', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store selected cart items in session for checkout.
     */
    public function storeSelectedItems(Request $request)
    {
        try {
            $selectedItems = $request->input('selectedItems', []);

            // Store selected items in session
            session(['selectedCartItems' => $selectedItems]);

            return response()->json([
                'success' => true,
                'message' => 'Selected items stored successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store selected items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Migrate guest cart to authenticated user via API.
     */
    public function migrate(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $guestSessionId = $request->input('guest_session_id') ?: $request->header('X-Guest-Session-Id');
        if (! $guestSessionId) {
            return response()->json(['success' => false, 'message' => 'Guest session ID required'], 400);
        }

        $this->migrateCartToUser($userId, $guestSessionId);

        return response()->json([
            'success' => true,
            'message' => 'Cart migrated successfully',
        ]);
    }
}
