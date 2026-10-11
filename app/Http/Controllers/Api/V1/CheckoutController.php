<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Mail\OrderCreatedMail;
use App\Models\CartItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class CheckoutController extends Controller
{
    /**
     * Get initial checkout state, address, cart summary, and shipping methods.
     */
    public function init(Request $request): JsonResponse
    {
        $user = $request->user();

        // Retrieve cart items for the authenticated user
        $cartQuery = CartItem::forUser($user->id)->with('product.category');

        if ($request->has('selected_ids') && is_array($request->selected_ids) && count($request->selected_ids) > 0) {
            $cartQuery->whereIn('id', $request->selected_ids);
        }

        $cartItems = $cartQuery->get();

        if ($cartItems->isEmpty()) {
            // Fallback: check all user cart items if specific filter yielded 0
            $cartItems = CartItem::forUser($user->id)->with('product.category')->get();
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

        $subtotal = (float) $cartItems->sum('total_price');
        $totalWeight = (float) $cartItems->sum(function ($item) {
            return ($item->product->weight ?? 0) * $item->quantity;
        });

        // Resolve available shipping methods
        $shippingMethods = $this->resolveShippingMethods($subtotal, $totalWeight);
        $defaultShipping = $shippingMethods['default_method'];
        $defaultShippingCost = (float) $shippingMethods['default_cost'];

        // 12% Philippine VAT (VAT inclusive)
        $taxAmount = round($subtotal * 0.12, 2);
        $total = $subtotal + $defaultShippingCost + $taxAmount;

        $isDefaultAddressComplete = $this->isDefaultAddressComplete($user);
        $codEligible = $total <= 3000;

        $paymentMethods = $user->paymentMethods()->orderBy('is_default', 'desc')->get();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'street' => $user->street,
                'barangay' => $user->barangay,
                'city' => $user->city,
                'province' => $user->province,
                'region' => $user->region,
                'zip_code' => $user->zip_code,
                'country' => 'Philippines',
                'is_default_address_complete' => $isDefaultAddressComplete,
            ],
            'cart_items' => $cartItems,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'default_shipping_cost' => $defaultShippingCost,
            'total' => $total,
            'currency' => 'PHP',
            'cod_eligible' => $codEligible,
            'available_shipping_methods' => $shippingMethods['available'],
            'default_shipping_method' => $defaultShipping,
            'payment_methods' => $paymentMethods,
        ]);
    }

    /**
     * Compute real-time shipping rates based on location and weight.
     */
    public function shippingRates(Request $request): JsonResponse
    {
        $user = $request->user();
        $cartItems = CartItem::forUser($user->id)->with('product')->get();

        $subtotal = (float) $cartItems->sum('total_price');
        $totalWeight = (float) $cartItems->sum(function ($item) {
            return ($item->product->weight ?? 0) * $item->quantity;
        });

        $rates = $this->resolveShippingMethods($subtotal, $totalWeight);

        return response()->json([
            'success' => true,
            'subtotal' => $subtotal,
            'total_weight' => $totalWeight,
            'available_shipping_methods' => $rates['available'],
            'default_shipping_method' => $rates['default_method'],
            'default_shipping_cost' => $rates['default_cost'],
        ]);
    }

    /**
     * Process acquisition and execute transactional order placement.
     */
    public function process(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Validation
        $request->validate([
            'address_option' => 'required|string|in:default,custom',
            'address_line_1' => 'required_if:address_option,custom|nullable|string|max:255',
            'city' => 'required_if:address_option,custom|nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'region' => 'required_if:address_option,custom|nullable|string|max:255',
            'barangay' => 'required_if:address_option,custom|nullable|string|max:255',
            'zip_code' => 'required_if:address_option,custom|nullable|string|max:20',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'shipping_method_id' => 'nullable|exists:shipping_methods,id',
            'payment_method' => 'required|string|in:cod,xendit,existing',
            'payment_method_id' => 'required_if:payment_method,existing|nullable|exists:payment_methods,id',
            'notes' => 'nullable|string|max:1000',
            'terms_accepted' => 'required|accepted',
        ]);

        // 2. Resolve items
        $cartQuery = CartItem::forUser($user->id)->with('product');
        if ($request->has('selected_ids') && is_array($request->selected_ids) && count($request->selected_ids) > 0) {
            $cartQuery->whereIn('id', $request->selected_ids);
        }
        $cartItems = $cartQuery->get();

        if ($cartItems->isEmpty()) {
            $cartItems = CartItem::forUser($user->id)->with('product')->get();
        }

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your shopping bag is empty.',
            ], 422);
        }

        // Validate product status, stock availability, and synchronize live product prices
        foreach ($cartItems as $cartItem) {
            if (! $cartItem->product || ! $cartItem->product->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => "The creation \"{$cartItem->product_name}\" is currently unavailable in the atelier catalog.",
                ], 422);
            }

            if ($cartItem->product->stock_quantity < $cartItem->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "The creation \"{$cartItem->product->name}\" only has {$cartItem->product->stock_quantity} pieces remaining in stock.",
                ], 422);
            }

            $livePrice = (float) $cartItem->product->price;
            if ((float) $cartItem->unit_price !== $livePrice) {
                $cartItem->unit_price = $livePrice;
                $cartItem->total_price = $livePrice * $cartItem->quantity;
                $cartItem->save();
            }
        }

        $subtotal = (float) $cartItems->sum('total_price');
        $totalWeight = (float) $cartItems->sum(function ($item) {
            return ($item->product->weight ?? 0) * $item->quantity;
        });

        // 3. Resolve shipping details
        $shippingResolution = $this->resolveShippingMethods($subtotal, $totalWeight);
        $shippingMethod = null;
        $shippingCost = 0;

        if ($request->filled('shipping_method_id')) {
            $foundMethod = ShippingMethod::find($request->shipping_method_id);
            if ($foundMethod) {
                $shippingMethod = $foundMethod;
                $shippingCost = (float) $foundMethod->calculateCost($subtotal, $totalWeight);
            }
        }

        if (! $shippingMethod) {
            $shippingMethod = $shippingResolution['default_method'];
            $shippingCost = (float) $shippingResolution['default_cost'];
        }

        $taxAmount = round($subtotal * 0.12, 2);
        $total = $subtotal + $shippingCost + $taxAmount;

        // 4. Validate COD eligibility
        if ($request->payment_method === 'cod' && $total > 3000) {
            return response()->json([
                'success' => false,
                'message' => 'Cash on Delivery is strictly available for acquisitions ₱3,000 and below.',
            ], 422);
        }

        // 5. Build shipping data
        if ($request->address_option === 'default') {
            if (! $this->isDefaultAddressComplete($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your primary atelier profile address is incomplete. Please specify a delivery destination.',
                ], 422);
            }

            $firstName = $request->filled('first_name') ? $request->first_name : $user->first_name;
            $lastName = $request->filled('last_name') ? $request->last_name : $user->last_name;
            $phone = $request->filled('phone') ? $request->phone : $user->phone;
            $fullName = trim(($firstName ?? '') . ' ' . ($lastName ?? '')) ?: ($user->name ?? '');

            $shippingData = [
                'name' => $fullName,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $user->email,
                'phone' => $phone,
                'street' => $user->street,
                'address_line_1' => $user->street,
                'address_line_2' => null,
                'city' => $user->city,
                'province' => $user->province,
                'region' => $user->region,
                'barangay' => $user->barangay,
                'zip_code' => $user->zip_code,
                'postal_code' => $user->zip_code,
                'country' => 'Philippines',
                'address_option' => 'default',
                'shipping_method_id' => $shippingMethod ? $shippingMethod->id : null,
                'shipping_method_name' => $shippingMethod ? $shippingMethod->name : 'Standard Delivery',
                'shipping_cost' => $shippingCost,
                'estimated_delivery_days' => $shippingMethod ? $shippingMethod->getEstimatedDeliveryDays() : '3-5 days',
            ];
        } else {
            $firstName = $request->first_name ?: $user->first_name;
            $lastName = $request->last_name ?: $user->last_name;
            $fullName = trim(($firstName ?? '') . ' ' . ($lastName ?? '')) ?: ($user->name ?? '');
            $street = $request->address_line_1;

            $shippingData = [
                'name' => $fullName,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $user->email,
                'phone' => $request->phone ?: $user->phone,
                'street' => $street,
                'address_line_1' => $street,
                'address_line_2' => null,
                'city' => $request->city,
                'province' => $request->province,
                'region' => $request->region,
                'barangay' => $request->barangay,
                'zip_code' => $request->zip_code,
                'postal_code' => $request->zip_code,
                'country' => 'Philippines',
                'address_option' => 'custom',
                'shipping_method_id' => $shippingMethod ? $shippingMethod->id : null,
                'shipping_method_name' => $shippingMethod ? $shippingMethod->name : 'Standard Delivery',
                'shipping_cost' => $shippingCost,
                'estimated_delivery_days' => $shippingMethod ? $shippingMethod->getEstimatedDeliveryDays() : '3-5 days',
            ];

            if ($request->boolean('save_as_default')) {
                $user->update([
                    'street' => $shippingData['address_line_1'],
                    'city' => $shippingData['city'],
                    'province' => $shippingData['province'],
                    'region' => $shippingData['region'],
                    'barangay' => $shippingData['barangay'],
                    'zip_code' => $shippingData['zip_code'],
                ]);
            }
        }

        // 6. Execute atomic database transaction
        try {
            DB::beginTransaction();

            $orderNumber = Order::generateOrderNumber();
            $paymentMethodName = $this->resolvePaymentMethodName($request->payment_method, $request->payment_method_id);

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'status' => 'processing',
                'fulfillment_status' => 'pending',
                'requires_approval' => false,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingCost,
                'shipping_method' => $shippingData['shipping_method_name'],
                'shipping_cost' => $shippingCost,
                'discount_amount' => 0,
                'total_amount' => $total,
                'currency' => 'PHP',
                'billing_address' => $shippingData,
                'shipping_address' => $shippingData,
                'payment_method' => $paymentMethodName,
                'payment_status' => 'pending',
                'notes' => $request->notes,
            ]);

            $orderedCartItemIds = [];

            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'total_price' => $cartItem->total_price,
                    'product_name' => $cartItem->product_name ?? ($cartItem->product->name ?? 'Fine Jewellery'),
                    'product_sku' => $cartItem->product_sku ?? ($cartItem->product->sku ?? 'ECL-JEW'),
                ]);

                $orderedCartItemIds[] = $cartItem->id;

                // For COD orders, decrement inventory immediately upon order placement
                if ($request->payment_method === 'cod') {
                    try {
                        InventoryMovement::recordStockOut(
                            $cartItem->product_id,
                            $cartItem->quantity,
                            'order',
                            "Order #{$order->order_number} - COD acquisition placement",
                            'App\Models\Order',
                            $order->id,
                            null
                        );
                    } catch (\Exception $ex) {
                        Log::warning('Stock out notice for COD order', [
                            'order_id' => $order->id,
                            'product_id' => $cartItem->product_id,
                            'error' => $ex->getMessage(),
                        ]);
                    }
                }
            }

            $order->admin_notes = 'OrderedCartItemIds: '.json_encode($orderedCartItemIds);
            $order->save();

            // Clear ordered items from cart for COD
            if ($request->payment_method === 'cod') {
                CartItem::whereIn('id', $orderedCartItemIds)->delete();
            }

            // Dispatch order confirmation email
            try {
                Mail::to($user->email)->send(new OrderCreatedMail($order));
            } catch (\Exception $e) {
                Log::warning('Order confirmation email suppressed in checkout API', ['error' => $e->getMessage()]);
            }

            event(new OrderCreated($order));

            // Generate Xendit invoice URL if requested
            $invoiceUrl = null;
            if ($request->payment_method === 'xendit') {
                $invoiceUrl = $this->createXenditInvoice($order);
            }

            DB::commit();

            $isSimulatedInvoice = $invoiceUrl && str_starts_with($invoiceUrl, 'simulated:');

            return response()->json([
                'success' => true,
                'order_number' => $order->order_number,
                'payment_method' => $request->payment_method,
                'payment_status' => $order->payment_status,
                'total' => $total,
                'currency' => 'PHP',
                'invoice_url' => $invoiceUrl,
                'redirect_url' => $isSimulatedInvoice ? null : ($invoiceUrl ?: null),
                'order' => $order->load('orderItems.product'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout processing error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'There was an error processing your acquisition: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Poll order payment and fulfillment status.
     */
    public function status(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        // If pending and Xendit invoice is available, check with Xendit API directly
        if ($order->payment_status === 'pending') {
            $secret = Setting::get('xendit_secret_key', config('services.xendit.secret_key'));
            if (! empty($secret) && $order->admin_notes && preg_match('/Xendit Invoice: ([a-f0-9]+)/', $order->admin_notes, $matches)) {
                $invoiceId = $matches[1];
                try {
                    $response = Http::withBasicAuth($secret, '')->timeout(5)
                        ->get('https://api.xendit.co/v2/invoices/'.$invoiceId);

                    if ($response->successful()) {
                        $invoice = $response->json();
                        if (isset($invoice['status']) && in_array(strtoupper($invoice['status']), ['PAID', 'SETTLED'])) {
                            DB::transaction(function () use ($order) {
                                $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
                                if ($lockedOrder && $lockedOrder->payment_status !== 'paid') {
                                    $lockedOrder->payment_status = 'paid';
                                    $lockedOrder->status = 'processing';
                                    $lockedOrder->save();

                                    foreach ($lockedOrder->orderItems as $item) {
                                        try {
                                            InventoryMovement::recordStockOut(
                                                $item->product_id,
                                                $item->quantity,
                                                'order',
                                                "Order #{$lockedOrder->order_number} - Xendit payment confirmed",
                                                'App\Models\Order',
                                                $lockedOrder->id,
                                                null
                                            );
                                        } catch (\Exception $e) {
                                            Log::warning('Stock decrement error in status check', ['error' => $e->getMessage()]);
                                        }
                                    }

                                    // Clear ordered cart items
                                    if ($lockedOrder->admin_notes && preg_match('/OrderedCartItemIds: (\[.*?\])/', $lockedOrder->admin_notes, $m)) {
                                        $cartIds = json_decode($m[1], true);
                                        if (is_array($cartIds) && count($cartIds) > 0) {
                                            CartItem::whereIn('id', $cartIds)->delete();
                                        }
                                    }
                                }
                            });
                            $order->refresh();
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed checking Xendit status for order '.$orderNumber, ['error' => $e->getMessage()]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'fulfillment_status' => $order->fulfillment_status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'total' => (float) $order->total_amount,
                'currency' => $order->currency ?? 'PHP',
                'created_at' => $order->created_at,
            ],
        ]);
    }

    /**
     * Get complete order details.
     */
    public function show(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['orderItems.product'])
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    /**
     * Simulate or finalize payment for testing or webhook simulation.
     */
    public function simulatePayment(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)->first();
        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $alreadyPaid = false;

        DB::transaction(function () use ($order, &$alreadyPaid) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
            if (! $lockedOrder || $lockedOrder->payment_status === 'paid') {
                $alreadyPaid = true;

                return;
            }

            $lockedOrder->payment_status = 'paid';
            $lockedOrder->status = 'processing';
            $lockedOrder->save();

            // Decrement inventory once with lock
            foreach ($lockedOrder->orderItems as $item) {
                try {
                    InventoryMovement::recordStockOut(
                        $item->product_id,
                        $item->quantity,
                        'order',
                        "Order #{$lockedOrder->order_number} - Xendit payment confirmed",
                        'App\Models\Order',
                        $lockedOrder->id,
                        null
                    );
                } catch (\Exception $e) {
                    Log::warning('Stock decrement error in simulatePayment', ['error' => $e->getMessage()]);
                }
            }

            // Clear ordered cart items
            if ($lockedOrder->admin_notes && preg_match('/OrderedCartItemIds: (\[.*?\])/', $lockedOrder->admin_notes, $matches)) {
                $cartIds = json_decode($matches[1], true);
                if (is_array($cartIds) && count($cartIds) > 0) {
                    CartItem::whereIn('id', $cartIds)->delete();
                }
            }
        });

        $order->refresh();

        return response()->json([
            'success' => true,
            'already_paid' => $alreadyPaid,
            'order' => $order,
        ]);
    }

    /**
     * Generate Xendit hosted invoice or return secure fallback.
     */
    private function createXenditInvoice(Order $order): string
    {
        $secret = Setting::get('xendit_secret_key', config('services.xendit.secret_key'));

        $successUrl = URL::route('payments.xendit.return.success', [
            'order' => $order->order_number,
            'source' => 'app',
        ]);
        $failedUrl = URL::route('payments.xendit.return.failed', [
            'order' => $order->order_number,
            'source' => 'app',
        ]);

        if (! empty($secret)) {
            try {
                $response = Http::withBasicAuth($secret, '')
                    ->asJson()
                    ->post('https://api.xendit.co/v2/invoices', [
                        'external_id' => $order->order_number,
                        'amount' => (float) $order->total_amount,
                        'currency' => 'PHP',
                        'payer_email' => optional($order->user)->email,
                        'description' => 'Payment for order '.$order->order_number,
                        'success_redirect_url' => $successUrl,
                        'failure_redirect_url' => $failedUrl,
                        'should_send_email' => true,
                    ]);

                if ($response->successful()) {
                    $invoiceData = $response->json();
                    if (! empty($invoiceData['invoice_url'])) {
                        $order->admin_notes = trim(($order->admin_notes ? $order->admin_notes."\n" : '').'Xendit Invoice: '.($invoiceData['id'] ?? 'N/A'));
                        $order->save();

                        return $invoiceData['invoice_url'];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Xendit live invoice creation failed, falling back', ['error' => $e->getMessage()]);
            }
        }

        // Return simulated identifier for Ionic app instead of a web URL
        return "simulated:xendit:{$order->order_number}";
    }

    /**
     * Resolve shipping methods and calculate best default.
     */
    private function resolveShippingMethods(float $subtotal, float $totalWeight): array
    {
        $freeShippingMethod = ShippingMethod::active()
            ->where('type', 'free_shipping')
            ->where(function ($query) use ($subtotal) {
                $query->whereNull('free_shipping_threshold')
                    ->orWhere('free_shipping_threshold', '<=', $subtotal);
            })
            ->first();

        $weightBasedMethod = ShippingMethod::active()
            ->where('type', 'weight_based')
            ->where(function ($query) use ($subtotal) {
                $query->whereNull('minimum_order_amount')
                    ->orWhere('minimum_order_amount', '<=', $subtotal);
            })
            ->first();

        $flatRateMethods = ShippingMethod::active()
            ->where('type', 'flat_rate')
            ->ordered()
            ->get();

        $available = [];

        if ($freeShippingMethod) {
            $available[] = [
                'id' => $freeShippingMethod->id,
                'name' => $freeShippingMethod->name,
                'description' => $freeShippingMethod->description,
                'type' => 'free_shipping',
                'cost' => 0.0,
                'estimated_days' => $freeShippingMethod->getEstimatedDeliveryDays() ?: '3-5 business days',
            ];
            $defaultMethod = $freeShippingMethod;
            $defaultCost = 0.0;
        } elseif ($weightBasedMethod && $totalWeight > 0) {
            $cost = (float) $weightBasedMethod->calculateCost($subtotal, $totalWeight);
            $available[] = [
                'id' => $weightBasedMethod->id,
                'name' => $weightBasedMethod->name,
                'description' => $weightBasedMethod->description,
                'type' => 'weight_based',
                'cost' => $cost,
                'estimated_days' => $weightBasedMethod->getEstimatedDeliveryDays() ?: '3-5 business days',
            ];
            $defaultMethod = $weightBasedMethod;
            $defaultCost = $cost;
        } else {
            foreach ($flatRateMethods as $method) {
                $cost = (float) $method->calculateCost($subtotal, $totalWeight);
                $available[] = [
                    'id' => $method->id,
                    'name' => $method->name,
                    'description' => $method->description,
                    'type' => 'flat_rate',
                    'cost' => $cost,
                    'estimated_days' => $method->getEstimatedDeliveryDays() ?: '3-5 business days',
                ];
            }
            $defaultMethod = $flatRateMethods->first();
            $defaultCost = $defaultMethod ? (float) $defaultMethod->calculateCost($subtotal, $totalWeight) : 0.0;
        }

        return [
            'available' => $available,
            'default_method' => $defaultMethod,
            'default_cost' => $defaultCost,
        ];
    }

    /**
     * Check if user's profile address has essential Philippine fields.
     */
    private function isDefaultAddressComplete($user): bool
    {
        $requiredFields = ['street', 'barangay', 'city', 'region', 'zip_code'];
        foreach ($requiredFields as $field) {
            if (empty($user->$field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Map payment method code to human-readable descriptor.
     */
    private function resolvePaymentMethodName(string $paymentMethod, ?int $paymentMethodId): string
    {
        switch ($paymentMethod) {
            case 'cod':
                return 'Cash on Delivery';
            case 'xendit':
                return 'Digital Treasury Gateway (Xendit)';
            case 'existing':
                $pm = $paymentMethodId ? PaymentMethod::find($paymentMethodId) : null;

                return $pm ? $pm->getDisplayName() : 'Saved Payment Method';
            default:
                return 'Electronic Payment';
        }
    }
}
