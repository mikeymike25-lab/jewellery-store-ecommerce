<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\GuestSession;
use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class Stage4CartWishlistMigrationTest extends TestCase
{
    private function createTestProduct(string $name = 'Test Ring', float $price = 45000): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'test-rings'],
            ['name' => 'Test Rings', 'description' => 'Test category for rings']
        );

        return Product::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . uniqid(),
            'category_id' => $category->id,
            'description' => 'Exquisite handcrafted ring',
            'short_description' => 'Handcrafted ring',
            'price' => $price,
            'sku' => 'TEST-SKU-' . rand(1000, 9999),
            'stock_quantity' => 15,
            'is_active' => true,
        ]);
    }

    public function test_guest_cart_addition_and_subtotal_calculation(): void
    {
        $guestSessionId = 'guest-test-' . Str::random(12);
        $product = $this->createTestProduct('Solitaire Diamond Band', 50000);

        // Add item to cart as guest
        $response = $this->withHeader('X-Guest-Session-Id', $guestSessionId)
            ->postJson('/api/v1/cart/add', [
                'product_id' => $product->id,
                'quantity' => 2,
                'ring_size' => '7',
                'metal_type' => '18K Yellow Gold',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify cart items in database
        $this->assertDatabaseHas('cart_items', [
            'session_id' => $guestSessionId,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Fetch cart as guest
        $fetchRes = $this->withHeader('X-Guest-Session-Id', $guestSessionId)
            ->getJson('/api/v1/cart');

        $fetchRes->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.total_items', 1);

        $expectedSubtotal = (float) $product->price * 2;
        $actualSubtotal = (float) $fetchRes->json('data.subtotal');
        $this->assertEqualsWithDelta($expectedSubtotal, $actualSubtotal, 0.01);

        // Update quantity to 3
        $updateRes = $this->withHeader('X-Guest-Session-Id', $guestSessionId)
            ->putJson('/api/v1/cart/update', [
                'product_id' => $product->id,
                'quantity' => 3,
            ]);

        $updateRes->assertStatus(200);
        $this->assertDatabaseHas('cart_items', [
            'session_id' => $guestSessionId,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_guest_wishlist_toggle_and_retrieval(): void
    {
        $guestSessionId = 'guest-wish-' . Str::random(12);
        $product = $this->createTestProduct('Emerald Eternity Ring', 75000);

        // Toggle add to wishlist
        $toggleRes = $this->withHeader('X-Guest-Session-Id', $guestSessionId)
            ->postJson('/api/v1/wishlist/toggle', [
                'product_id' => $product->id,
            ]);

        $toggleRes->assertStatus(200)
            ->assertJson(['success' => true, 'was_added' => true]);

        // Check in wishlist
        $checkRes = $this->withHeader('X-Guest-Session-Id', $guestSessionId)
            ->getJson("/api/v1/wishlist/check/{$product->id}");

        $checkRes->assertStatus(200)
            ->assertJson(['in_wishlist' => true]);

        // Get wishlist index
        $indexRes = $this->withHeader('X-Guest-Session-Id', $guestSessionId)
            ->getJson('/api/v1/wishlist');

        $indexRes->assertStatus(200);
        $this->assertNotEmpty($indexRes->json());
    }

    public function test_post_login_cart_and_wishlist_migration(): void
    {
        $guestSessionId = 'guest-mig-' . Str::random(12);
        $user = User::factory()->create([
            'email' => 'migrator-' . Str::random(6) . '@eclore.test',
            'username' => 'migrator_' . Str::random(6),
            'password' => Hash::make('Secret123!'),
        ]);

        $p1 = $this->createTestProduct('Cart Migrated Ring', 35000);
        $p2 = $this->createTestProduct('Wishlist Migrated Pendant', 62000);

        // Add p1 to guest cart
        $this->withHeader('X-Guest-Session-Id', $guestSessionId)
            ->postJson('/api/v1/cart/add', [
                'product_id' => $p1->id,
                'quantity' => 1,
            ]);

        // Add p2 to guest wishlist
        $this->withHeader('X-Guest-Session-Id', $guestSessionId)
            ->postJson('/api/v1/wishlist/add', [
                'product_id' => $p2->id,
            ]);

        // Generate Sanctum token for user
        $token = $user->createToken('test-migration-token')->plainTextToken;

        // Perform cart migration
        $cartMigRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->withHeader('X-Guest-Session-Id', $guestSessionId)
            ->postJson('/api/v1/cart/migrate', [
                'guest_session_id' => $guestSessionId,
            ]);

        $cartMigRes->assertStatus(200)
            ->assertJson(['success' => true]);

        // Perform wishlist migration
        $wishMigRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->withHeader('X-Guest-Session-Id', $guestSessionId)
            ->postJson('/api/v1/wishlist/migrate', [
                'guest_session_id' => $guestSessionId,
            ]);

        $wishMigRes->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify user now owns cart item
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $p1->id,
        ]);

        // Verify user now owns wishlist item
        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $p2->id,
        ]);

        // Verify guest cart items were removed / transferred
        $this->assertDatabaseMissing('cart_items', [
            'session_id' => $guestSessionId,
        ]);
    }

    public function test_store_selected_cart_items(): void
    {
        $response = $this->postJson('/api/v1/cart/selected-items', [
            'selectedItems' => [1, 2, 3],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
