<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderAddressAdminSyncTest extends TestCase
{
    private function createTestProduct(string $name = 'Solitaire Diamond Ring', float $price = 2800): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'rings'],
            ['name' => 'Rings', 'description' => 'Fine Rings']
        );

        return Product::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . uniqid(),
            'category_id' => $category->id,
            'description' => 'Luxury handcrafted jewellery',
            'short_description' => 'Luxury jewellery',
            'price' => $price,
            'sku' => 'ECL-RING-' . rand(1000, 9999),
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
    }

    private function createTestAdmin(): Admin
    {
        return Admin::firstOrCreate(
            ['email' => 'admin-test@eclore.co'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => bcrypt('password'),
                'role' => 'super_admin',
                'status' => 'active',
                'department' => 'Management',
                'position' => 'Administrator',
            ]
        );
    }

    /**
     * Test checkout stores complete address keys matching both API and Admin panel expectations.
     */
    public function test_checkout_saves_complete_unified_address_data(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'street' => '123 Rizal Street',
            'barangay' => 'Mandulog',
            'city' => 'City of Iligan',
            'province' => 'Lanao del Norte',
            'region' => 'Region X (Northern Mindanao)',
            'zip_code' => '9200',
        ]);

        $product = $this->createTestProduct('Eclore Diamond Band', 2000);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 2000,
            'total_price' => 2000,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
        ]);

        $token = $user->createToken('test-address-sync')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/checkout/process', [
                'address_option' => 'default',
                'payment_method' => 'cod',
                'terms_accepted' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $orderNumber = $response->json('order_number');
        $order = Order::where('order_number', $orderNumber)->first();

        $this->assertNotNull($order);
        $shipping = $order->shipping_address;

        $this->assertEquals('Maria Santos', $shipping['name']);
        $this->assertEquals('123 Rizal Street', $shipping['street']);
        $this->assertEquals('123 Rizal Street', $shipping['address_line_1']);
        $this->assertEquals('Mandulog', $shipping['barangay']);
        $this->assertEquals('City of Iligan', $shipping['city']);
        $this->assertEquals('Lanao del Norte', $shipping['province']);
        $this->assertEquals('Region X (Northern Mindanao)', $shipping['region']);
        $this->assertEquals('9200', $shipping['zip_code']);
        $this->assertEquals('Philippines', $shipping['country']);
    }

    /**
     * Test checkout respects customized orderer name when using default profile address.
     */
    public function test_checkout_allows_customizing_orderer_name_with_default_address(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Mike',
            'last_name' => 'Salamat',
            'street' => 'dfgdfg',
            'barangay' => 'Mandulog',
            'city' => 'City of Iligan',
            'province' => 'Lanao del Norte',
            'region' => 'Region X (Northern Mindanao)',
            'zip_code' => 'd76',
        ]);

        $product = $this->createTestProduct('Gift Diamond Ring', 1800);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 1800,
            'total_price' => 1800,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
        ]);

        $token = $user->createToken('test-custom-name')->plainTextToken;

        // User edits the orderer name to "Gabriel Silang" while keeping their default profile address
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/checkout/process', [
                'address_option' => 'default',
                'first_name' => 'Gabriel',
                'last_name' => 'Silang',
                'phone' => '+63 912 345 6789',
                'payment_method' => 'cod',
                'terms_accepted' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $orderNumber = $response->json('order_number');
        $order = Order::where('order_number', $orderNumber)->first();

        $this->assertNotNull($order);
        $shipping = $order->shipping_address;

        $this->assertEquals('Gabriel Silang', $shipping['name']);
        $this->assertEquals('Gabriel', $shipping['first_name']);
        $this->assertEquals('Silang', $shipping['last_name']);
        $this->assertEquals('+63 912 345 6789', $shipping['phone']);
        $this->assertEquals('dfgdfg', $shipping['street']);
        $this->assertEquals('City of Iligan', $shipping['city']);
        $this->assertEquals('Mandulog', $shipping['barangay']);
        $this->assertEquals('Region X (Northern Mindanao)', $shipping['region']);
        $this->assertEquals('d76', $shipping['zip_code']);
    }

    /**
     * Test admin order edit view pre-renders customer address data.
     */
    public function test_admin_order_edit_view_renders_address_fields(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Antonio',
            'last_name' => 'Luna',
            'street' => 'General Luna St',
            'barangay' => 'Mandulog',
            'city' => 'City of Iligan',
            'province' => 'Lanao del Norte',
            'region' => 'Region X (Northern Mindanao)',
            'zip_code' => '9200',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST-' . uniqid(),
            'status' => 'pending',
            'fulfillment_status' => 'pending',
            'requires_approval' => false,
            'subtotal' => 2000,
            'tax_amount' => 240,
            'shipping_amount' => 150,
            'shipping_cost' => 150,
            'discount_amount' => 0,
            'total_amount' => 2390,
            'currency' => 'PHP',
            'billing_address' => [
                'name' => 'Antonio Luna',
                'street' => 'General Luna St',
                'barangay' => 'Mandulog',
                'city' => 'City of Iligan',
                'province' => 'Lanao del Norte',
                'region' => 'Region X (Northern Mindanao)',
                'zip_code' => '9200',
                'country' => 'Philippines',
            ],
            'shipping_address' => [
                'name' => 'Antonio Luna',
                'street' => 'General Luna St',
                'barangay' => 'Mandulog',
                'city' => 'City of Iligan',
                'province' => 'Lanao del Norte',
                'region' => 'Region X (Northern Mindanao)',
                'zip_code' => '9200',
                'country' => 'Philippines',
            ],
            'payment_method' => 'Cash on Delivery',
            'payment_status' => 'pending',
        ]);

        $admin = $this->createTestAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->get(admin_route('orders.edit', $order));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Antonio Luna', $content);
        $this->assertStringContainsString('General Luna St', $content);
        $this->assertStringContainsString('Region X (Northern Mindanao)', $content);
        $this->assertStringContainsString('Lanao del Norte', $content);
        $this->assertStringContainsString('City of Iligan', $content);
        $this->assertStringContainsString('Mandulog', $content);
        $this->assertStringContainsString('9200', $content);
    }

    /**
     * Test admin order update preserves address fields against accidental blank overwrites.
     */
    public function test_admin_order_update_merges_and_preserves_addresses(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Elena',
            'last_name' => 'Reyes',
        ]);

        $originalAddress = [
            'name' => 'Elena Reyes',
            'street' => '789 Emerald Avenue',
            'barangay' => 'San Antonio',
            'city' => 'Pasig City',
            'province' => 'N/A (NCR)',
            'region' => 'National Capital Region (NCR)',
            'zip_code' => '1600',
            'country' => 'Philippines',
        ];

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-MERGE-' . uniqid(),
            'status' => 'pending',
            'fulfillment_status' => 'pending',
            'requires_approval' => false,
            'subtotal' => 2000,
            'tax_amount' => 240,
            'shipping_amount' => 0,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'total_amount' => 2240,
            'currency' => 'PHP',
            'billing_address' => $originalAddress,
            'shipping_address' => $originalAddress,
            'payment_method' => 'Cash on Delivery',
            'payment_status' => 'pending',
        ]);

        $admin = $this->createTestAdmin();

        // Submit update with some blank dropdown/inputs (e.g. region/city wasn't re-selected)
        $response = $this->actingAs($admin, 'admin')
            ->put(admin_route('orders.update', $order), [
                'status' => 'processing',
                'payment_status' => 'paid',
                'billing_address' => [
                    'name' => '', // blank submitted
                    'street' => '789 Emerald Avenue Suite 202', // updated
                    'region' => '',
                    'province' => '',
                    'city' => '',
                    'barangay' => '',
                    'zip_code' => '1600',
                ],
                'shipping_address' => [
                    'name' => '',
                    'street' => '789 Emerald Avenue Suite 202',
                    'region' => '',
                    'province' => '',
                    'city' => '',
                    'barangay' => '',
                    'zip_code' => '1600',
                ],
            ]);

        $response->assertRedirect();

        $order->refresh();
        $this->assertEquals('processing', $order->status);
        $this->assertEquals('paid', $order->payment_status);

        // Name and dependent fields must still be preserved, not overwritten with null/empty
        $this->assertEquals('Elena Reyes', $order->shipping_address['name']);
        $this->assertEquals('789 Emerald Avenue Suite 202', $order->shipping_address['street']);
        $this->assertEquals('National Capital Region (NCR)', $order->shipping_address['region']);
        $this->assertEquals('San Antonio', $order->shipping_address['barangay']);
        $this->assertEquals('Pasig City', $order->shipping_address['city']);
        $this->assertEquals('1600', $order->shipping_address['zip_code']);
    }

    /**
     * Test admin order show view renders successfully without blade parsing errors.
     */
    public function test_admin_order_show_view_renders_successfully(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Corazon',
            'last_name' => 'Aquino',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-SHOW-' . uniqid(),
            'status' => 'pending',
            'fulfillment_status' => 'pending',
            'requires_approval' => false,
            'subtotal' => 5000,
            'tax_amount' => 600,
            'shipping_amount' => 150,
            'shipping_cost' => 150,
            'discount_amount' => 0,
            'total_amount' => 5750,
            'currency' => 'PHP',
            'billing_address' => [
                'name' => 'Corazon Aquino',
                'street' => 'Times Street',
                'city' => 'Quezon City',
                'province' => 'N/A (NCR)',
                'region' => 'National Capital Region (NCR)',
                'zip_code' => '1100',
            ],
            'shipping_address' => [
                'name' => 'Corazon Aquino',
                'street' => 'Times Street',
                'city' => 'Quezon City',
                'province' => 'N/A (NCR)',
                'region' => 'National Capital Region (NCR)',
                'zip_code' => '1100',
            ],
            'payment_method' => 'Cash on Delivery',
            'payment_status' => 'pending',
        ]);

        $admin = $this->createTestAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->get(admin_route('orders.show', $order));

        $response->assertStatus(200);
        $this->assertStringContainsString('Times Street', $response->getContent());
        $this->assertStringContainsString('Corazon Aquino', $response->getContent());
    }

    /**
     * Test admin delivery tracking index renders successfully without syntax or pagination errors.
     */
    public function test_admin_delivery_tracking_index_renders_successfully(): void
    {
        $admin = $this->createTestAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->get(admin_route('delivery-tracking.index'));

        $response->assertStatus(200);
        $this->assertStringContainsString('Delivery Tracking', $response->getContent());
    }
}
