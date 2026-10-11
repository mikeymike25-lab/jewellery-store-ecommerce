<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ReturnRepair;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Stage6AccountOrdersRmaTest extends TestCase
{
    private function createTestProduct(string $name = 'Diamond Bracelet', float $price = 35000, int $stock = 10): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'atelier-jewellery'],
            ['name' => 'Atelier Jewellery', 'description' => 'Luxury creations']
        );

        return Product::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . uniqid(),
            'category_id' => $category->id,
            'description' => 'Fine handcrafted luxury piece',
            'short_description' => 'Luxury piece',
            'price' => $price,
            'sku' => 'TEST-RMA-' . rand(1000, 9999),
            'stock_quantity' => $stock,
            'is_active' => true,
        ]);
    }

    private function createPaidOrder(User $user, Product $product, string $status = 'processing'): Order
    {
        $subtotal = $product->price;
        $tax = round($subtotal * 0.12, 2);
        $shipping = 0.00;
        $total = $subtotal + $tax + $shipping;

        $shippingAddress = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => '09171234567',
            'street' => '77 Ayala Avenue',
            'barangay' => 'Bel-Air',
            'city' => 'Makati City',
            'province' => 'Metro Manila',
            'region' => 'National Capital Region (NCR)',
            'zip_code' => '1209',
        ];

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ECL-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'status' => $status,
            'payment_status' => 'paid',
            'payment_method' => 'xendit',
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'shipping_amount' => $shipping,
            'total_amount' => $total,
            'currency' => 'PHP',
            'shipping_address' => $shippingAddress,
            'billing_address' => $shippingAddress,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => $product->price,
            'total_price' => $product->price,
        ]);

        return $order;
    }

    /**
     * Test 1: Account orders endpoint returns structured JSON with orders and pagination.
     */
    public function test_account_orders_returns_paginated_orders_json(): void
    {
        $user = User::factory()->create([
            'email' => 'client-orders-' . uniqid() . '@eclore.test',
        ]);
        Sanctum::actingAs($user);

        $product = $this->createTestProduct('Aura Pendant', 28000);
        $this->createPaidOrder($user, $product);

        $response = $this->getJson('/api/v1/account/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'orders',
                'pagination' => [
                    'total',
                    'current_page',
                    'last_page',
                    'per_page',
                ],
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('orders'));
        $this->assertEquals(1, $response->json('pagination.total'));
    }

    /**
     * Test 2: Customer can update profile details (names, phone).
     */
    public function test_account_profile_update(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Claire',
            'last_name' => 'Montagne',
            'email' => 'claire-' . uniqid() . '@eclore.test',
            'phone' => '09170001111',
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account/profile/update', [
            'first_name' => 'Claire-Marie',
            'last_name' => 'De La Tour',
            'phone' => '09189998888',
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals('Claire-Marie', $user->first_name);
        $this->assertEquals('De La Tour', $user->last_name);
        $this->assertEquals('09189998888', $user->phone);
    }

    /**
     * Test 3: Customer can change password with current password verification.
     */
    public function test_account_password_change(): void
    {
        $user = User::factory()->create([
            'email' => 'pass-user-' . uniqid() . '@eclore.test',
            'password' => Hash::make('CurrentP@ssw0rd!'),
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account/password/change', [
            'current_password' => 'CurrentP@ssw0rd!',
            'new_password' => 'NewLuxuryP@ssw0rd2026!',
            'new_password_confirmation' => 'NewLuxuryP@ssw0rd2026!',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $user->refresh();
        $this->assertTrue(Hash::check('NewLuxuryP@ssw0rd2026!', $user->password));
    }

    /**
     * Test 4: Customer can update Philippine address book details.
     */
    public function test_account_address_update_philippines(): void
    {
        $user = User::factory()->create([
            'email' => 'address-client-' . uniqid() . '@eclore.test',
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account/address/update', [
            'street' => 'Penthouse 32, Legaspi Towers 300, Roxas Boulevard',
            'region' => 'National Capital Region (NCR)',
            'province' => 'Metro Manila',
            'city' => 'Manila City',
            'barangay' => 'Barangay 699',
            'zip_code' => '1004',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $user->refresh();
        $this->assertEquals('Penthouse 32, Legaspi Towers 300, Roxas Boulevard', $user->street);
        $this->assertEquals('National Capital Region (NCR)', $user->region);
        $this->assertEquals('Manila City', $user->city);
        $this->assertEquals('Barangay 699', $user->barangay);
        $this->assertEquals('1004', $user->zip_code);
    }

    /**
     * Test 5: Customer can download official A4 tax receipt PDF for a paid order.
     */
    public function test_receipt_pdf_download_for_paid_order(): void
    {
        $user = User::factory()->create([
            'email' => 'receipt-shopper-' . uniqid() . '@eclore.test',
            'first_name' => 'Isabella',
            'last_name' => 'Reyes',
        ]);
        Sanctum::actingAs($user);

        $product = $this->createTestProduct('Lumiere Diamond Choker', 85000);
        $order = $this->createPaidOrder($user, $product);

        $response = $this->get('/api/v1/account/orders/' . $order->order_number . '/receipt-pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertNotEmpty($response->getContent());
    }

    /**
     * Test 6: Receipt download is rejected with 403 for unpaid orders.
     */
    public function test_receipt_pdf_forbidden_for_unpaid_order(): void
    {
        $user = User::factory()->create([
            'email' => 'unpaid-shopper-' . uniqid() . '@eclore.test',
        ]);
        Sanctum::actingAs($user);

        $product = $this->createTestProduct('Opal Brooch', 19000);
        $order = $this->createPaidOrder($user, $product);
        $order->update(['payment_status' => 'pending']);

        $response = $this->getJson('/api/v1/account/orders/' . $order->order_number . '/receipt-pdf');

        $response->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    /**
     * Test 7: Public order tracking endpoint returns 4-stage delivery timeline and order items.
     */
    public function test_public_order_tracking_endpoint(): void
    {
        $user = User::factory()->create([
            'email' => 'tracking-client-' . uniqid() . '@eclore.test',
            'first_name' => 'Sophia',
            'last_name' => 'Alvarez',
        ]);

        $product = $this->createTestProduct('Heritage Cuff', 42000);
        $order = $this->createPaidOrder($user, $product, 'shipped');
        $order->update([
            'tracking_number' => 'ARM-ECL-2026-9812',
            'courier' => 'Armored VIP Courier',
        ]);

        $response = $this->postJson('/api/v1/orders/track', [
            'order_number' => $order->order_number,
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'order' => [
                    'order_number',
                    'status',
                    'payment_status',
                    'tracking_number',
                    'courier',
                    'total_amount',
                    'currency',
                    'items',
                    'timeline',
                ],
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals($order->order_number, $response->json('order.order_number'));
        $this->assertEquals('ARM-ECL-2026-9812', $response->json('order.tracking_number'));

        // Verify 4-stage timeline
        $timeline = $response->json('order.timeline');
        $this->assertCount(4, $timeline);
        $this->assertEquals('placed', $timeline[0]['stage']);
        $this->assertEquals('crafting', $timeline[1]['stage']);
        $this->assertEquals('transit', $timeline[2]['stage']);
        $this->assertEquals('delivered', $timeline[3]['stage']);

        // Since status is 'shipped', transit stage should be completed & current
        $this->assertTrue($timeline[0]['completed']);
        $this->assertTrue($timeline[1]['completed']);
        $this->assertTrue($timeline[2]['completed']);
        $this->assertFalse($timeline[3]['completed']);
    }

    /**
     * Test 8: Public order tracking with wrong email or order number returns 404.
     */
    public function test_public_order_tracking_invalid_credentials_returns_404(): void
    {
        $response = $this->postJson('/api/v1/orders/track', [
            'order_number' => 'ECL-NONEXISTENT-999',
            'email' => 'random-visitor@eclore.test',
        ]);

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    /**
     * Test 9: Authenticated user can submit after-sales RMA return/repair claim.
     */
    public function test_rma_return_repair_request_creation(): void
    {
        $user = User::factory()->create([
            'email' => 'rma-shopper-' . uniqid() . '@eclore.test',
        ]);
        Sanctum::actingAs($user);

        $product = $this->createTestProduct('Solitaire Diamond Band', 55000);
        $order = $this->createPaidOrder($user, $product, 'delivered');
        $orderItem = $order->orderItems->first();

        $response = $this->postJson('/api/v1/account/returns-repairs', [
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'reason' => 'item_does_not_fit',
            'description' => 'The ring size needs atelier resizing from 6.5 to 7.0 for optimal comfort.',
            'customer_notes' => 'Please coordinate insured pickup at flagship salon.',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'rma_number',
            ]);

        $this->assertTrue($response->json('success'));
        $rmaNumber = $response->json('rma_number');
        $this->assertStringStartsWith('RMA-', $rmaNumber);

        // Verify record in database
        $this->assertDatabaseHas('returns_repairs', [
            'rma_number' => $rmaNumber,
            'order_id' => $order->id,
            'user_id' => $user->id,
            'status' => 'requested',
            'reason' => 'item_does_not_fit',
        ]);
    }

    /**
     * Test 10: Authenticated user can retrieve their RMA claim history.
     */
    public function test_account_returns_repairs_list(): void
    {
        $user = User::factory()->create([
            'email' => 'rma-list-user-' . uniqid() . '@eclore.test',
        ]);
        Sanctum::actingAs($user);

        $product = $this->createTestProduct('Sapphire Halo Ring', 62000);
        $order = $this->createPaidOrder($user, $product, 'delivered');
        $orderItem = $order->orderItems->first();

        ReturnRepair::create([
            'rma_number' => ReturnRepair::generateRmaNumber(),
            'order_id' => $order->id,
            'user_id' => $user->id,
            'type' => 'repair',
            'status' => 'requested',
            'reason' => 'defective',
            'description' => 'Prong inspection required after salon consultation.',
            'products' => [
                [
                    'product_id' => $orderItem->product_id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response = $this->getJson('/api/v1/account/returns-repairs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'returns_repairs',
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertCount(1, $response->json('returns_repairs'));
    }
}
