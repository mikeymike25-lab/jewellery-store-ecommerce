<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class Stage5CheckoutTest extends TestCase
{
    private function createTestProduct(string $name = 'Test Ring', float $price = 45000, int $stock = 15): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'test-category'],
            ['name' => 'Test Category', 'description' => 'Test Category description']
        );

        return Product::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . uniqid(),
            'category_id' => $category->id,
            'description' => 'Exquisite handcrafted luxury jewellery',
            'short_description' => 'Luxury jewellery',
            'price' => $price,
            'sku' => 'TEST-ECL-' . rand(1000, 9999),
            'stock_quantity' => $stock,
            'is_active' => true,
        ]);
    }

    /**
     * Test checkout init returns user profile, cart summary, and shipping methods.
     */
    public function test_checkout_init_with_authenticated_user(): void
    {
        $user = User::factory()->create([
            'email' => 'atelier-shopper-' . uniqid() . '@eclore.test',
            'first_name' => 'Genevieve',
            'last_name' => 'Dubois',
            'street' => 'Ayala Avenue 101',
            'barangay' => 'Bel-Air',
            'city' => 'Makati City',
            'province' => 'Metro Manila',
            'region' => 'National Capital Region (NCR)',
            'zip_code' => '1224',
        ]);

        $product = $this->createTestProduct('Étoile Solitaire Ring', 2500, 10);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 2500,
            'total_price' => 2500,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
        ]);

        $token = $user->createToken('test-checkout')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/checkout/init');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.first_name', 'Genevieve')
            ->assertJsonPath('user.is_default_address_complete', true)
            ->assertJsonPath('currency', 'PHP')
            ->assertJsonPath('subtotal', 2500);

        $this->assertTrue($response->json('cod_eligible'));
    }

    /**
     * Test COD validation rejects order exceeding ₱3,000 threshold.
     */
    public function test_cod_order_rejected_when_total_exceeds_threshold(): void
    {
        $user = User::factory()->create([
            'street' => 'Ayala Avenue 101',
            'barangay' => 'Bel-Air',
            'city' => 'Makati City',
            'region' => 'National Capital Region (NCR)',
            'zip_code' => '1224',
        ]);

        // Product with price over ₱3,000
        $expensiveProduct = $this->createTestProduct('Grand Diamond Tiara', 50000, 5);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $expensiveProduct->id,
            'quantity' => 1,
            'unit_price' => 50000,
            'total_price' => 50000,
            'product_name' => $expensiveProduct->name,
            'product_sku' => $expensiveProduct->sku,
        ]);

        $token = $user->createToken('test-cod-reject')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/checkout/process', [
                'address_option' => 'default',
                'payment_method' => 'cod',
                'terms_accepted' => true,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertStringContainsString('3,000', $response->json('message'));
    }

    /**
     * Test successful COD order placement decrements inventory exactly once and clears ordered items.
     */
    public function test_successful_cod_order_placement_and_inventory_decrement(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Claire',
            'last_name' => 'Montagne',
            'street' => 'Paseo de Roxas 45',
            'barangay' => 'San Lorenzo',
            'city' => 'Makati City',
            'region' => 'National Capital Region (NCR)',
            'zip_code' => '1223',
        ]);

        $initialStock = 8;
        $product = $this->createTestProduct('Petit Or Pendant', 1500, $initialStock);

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 1500,
            'total_price' => 1500,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
        ]);

        $token = $user->createToken('test-cod-success')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/checkout/process', [
                'address_option' => 'default',
                'payment_method' => 'cod',
                'terms_accepted' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('payment_method', 'cod')
            ->assertJsonPath('currency', 'PHP');

        $orderNumber = $response->json('order_number');
        $this->assertNotEmpty($orderNumber);

        // Verify order in database
        $order = Order::where('order_number', $orderNumber)->first();
        $this->assertNotNull($order);
        $this->assertEquals('PHP', $order->currency);
        $this->assertEquals('Cash on Delivery', $order->payment_method);
        $this->assertEquals(1, $order->orderItems()->count());

        // Verify stock quantity decremented exactly once
        $product->refresh();
        $this->assertEquals($initialStock - 1, $product->stock_quantity);

        // Verify ordered cart item is deleted
        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    /**
     * Test Xendit payment flow creation and order status polling.
     */
    public function test_xendit_checkout_process_and_status_polling(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Henri',
            'last_name' => 'Laurent',
            'street' => 'Bonifacio High Street',
            'barangay' => 'Fort Bonifacio',
            'city' => 'Taguig City',
            'region' => 'National Capital Region (NCR)',
            'zip_code' => '1634',
        ]);

        $initialStock = 5;
        $product = $this->createTestProduct('Sapphire Royal Brooch', 45000, $initialStock);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 45000,
            'total_price' => 45000,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
        ]);

        $token = $user->createToken('test-xendit')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/checkout/process', [
                'address_option' => 'default',
                'payment_method' => 'xendit',
                'terms_accepted' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('payment_method', 'xendit');

        $orderNumber = $response->json('order_number');
        $this->assertNotEmpty($orderNumber);
        $this->assertNotEmpty($response->json('invoice_url'));

        // Inventory should NOT decrement until payment confirmation for Xendit
        $product->refresh();
        $this->assertEquals($initialStock, $product->stock_quantity);

        // Test status polling endpoint
        $statusResponse = $this->getJson("/api/v1/orders/{$orderNumber}/status");
        $statusResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.order_number', $orderNumber)
            ->assertJsonPath('order.payment_status', 'pending');

        // Test simulate-payment (atomic stock decrement with lock)
        $simResponse = $this->postJson("/api/v1/orders/{$orderNumber}/simulate-payment");
        $simResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.payment_status', 'paid');

        // Verify stock decremented after payment confirmation
        $product->refresh();
        $this->assertEquals($initialStock - 1, $product->stock_quantity);

        // Verify re-checking status reflects paid
        $statusAfterPaid = $this->getJson("/api/v1/orders/{$orderNumber}/status");
        $statusAfterPaid->assertJsonPath('order.payment_status', 'paid');
    }
}
