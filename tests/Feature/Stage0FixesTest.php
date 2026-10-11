<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Stage0FixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogue_endpoint_works_without_room_category_error(): void
    {
        $response = $this->get('/catalogue');
        $response->assertStatus(200);
    }

    public function test_api_products_works(): void
    {
        $response = $this->getJson('/api/products');
        $response->assertStatus(200);
    }

    public function test_cart_cleanup_endpoint_executes_successfully(): void
    {
        $response = $this->postJson('/api/cart/cleanup');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cleanup completed',
            ]);
    }

    public function test_order_items_relationship_alias_works(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST-001',
            'status' => 'pending',
            'subtotal' => 1000,
            'tax_amount' => 120,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'total_amount' => 1120,
            'currency' => 'PHP',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'billing_address' => json_encode(['street' => '123 Main St', 'city' => 'Manila']),
            'shipping_address' => json_encode(['street' => '123 Main St', 'city' => 'Manila']),
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $order->items());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $order->orderItems());
    }
}
