<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Stage1ApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test Sanctum token authentication endpoint (POST /api/v1/auth/token).
     */
    public function test_auth_token_issuance_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test-bearer@eclore.test',
            'username' => 'test_bearer_user',
            'password' => Hash::make('Secret123!'),
        ]);

        $response = $this->postJson('/api/v1/auth/token', [
            'login' => 'test-bearer@eclore.test',
            'password' => 'Secret123!',
            'device_name' => 'ionic-mobile-test',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'token',
                'token_type',
                'user' => ['id', 'email', 'username'],
            ]);

        $this->assertEquals('Bearer', $response->json('token_type'));
        $this->assertNotEmpty($response->json('token'));
    }

    /**
     * Test token issuance fails with invalid credentials.
     */
    public function test_auth_token_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/token', [
            'login' => 'nonexistent@eclore.test',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
    }

    /**
     * Test GET /api/v1/auth/user requires valid Bearer token.
     */
    public function test_auth_user_endpoint_requires_token(): void
    {
        // Unauthenticated request
        $this->getJson('/api/v1/auth/user')
            ->assertStatus(401);

        // Authenticated request
        $user = User::factory()->create([
            'email' => 'authed@eclore.test',
            'password' => Hash::make('Secret123!'),
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/user');

        $response->assertStatus(200)
            ->assertJsonPath('user.email', 'authed@eclore.test');
    }

    /**
     * Test POST /api/v1/auth/logout revokes current token.
     */
    public function test_auth_logout_revokes_token(): void
    {
        $user = User::factory()->create([
            'email' => 'logout-test@eclore.test',
            'password' => Hash::make('Secret123!'),
        ]);
        $token = $user->createToken('logout-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify token deleted from database
        $this->assertCount(0, $user->fresh()->tokens);

        // Reset in-memory auth guard state for subsequent request
        $this->app['auth']->forgetGuards();

        // Token should now be rejected with 401
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/user')
            ->assertStatus(401);
    }

    /**
     * Test guest cart operations using X-Guest-Session-Id header without cookie leakage.
     */
    public function test_guest_cart_via_header_without_debug_leak(): void
    {
        $category = Category::create([
            'name' => 'Rings',
            'slug' => 'rings-' . uniqid(),
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Diamond Solitaire Ring',
            'slug' => 'diamond-solitaire-ring-' . uniqid(),
            'category_id' => $category->id,
            'description' => 'Handcrafted 18k champagne gold solitaire diamond ring.',
            'short_description' => '18k champagne gold ring.',
            'price' => 45000,
            'sku' => 'ECL-RNG-' . rand(1000, 9999),
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $guestUuid = 'guest-' . bin2hex(random_bytes(8));

        // Add to cart with guest header
        $addResponse = $this->withHeader('X-Guest-Session-Id', $guestUuid)
            ->postJson('/api/v1/cart/add', [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $addResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify response contains NO debug or cookie leakage
        $this->assertArrayNotHasKey('debug', $addResponse->json());
        $this->assertArrayNotHasKey('request_cookies', $addResponse->json());

        // Get cart with same guest header
        $getResponse = $this->withHeader('X-Guest-Session-Id', $guestUuid)
            ->getJson('/api/v1/cart');

        $getResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertArrayNotHasKey('debug', $getResponse->json());
        $this->assertGreaterThanOrEqual(1, $getResponse->json('data.total_items'));
    }

    /**
     * Test products and categories v1 endpoints.
     */
    public function test_v1_products_and_categories_endpoints(): void
    {
        $productsResponse = $this->getJson('/api/v1/products');
        $productsResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => ['current_page', 'total', 'per_page'],
            ]);

        $categoriesResponse = $this->getJson('/api/v1/categories');
        $categoriesResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    /**
     * Test CORS configuration contains Ionic and Capacitor origins.
     */
    public function test_cors_configuration_allows_ionic_and_capacitor(): void
    {
        $allowedOrigins = config('cors.allowed_origins', []);

        $this->assertContains('http://localhost:8100', $allowedOrigins);
        $this->assertContains('capacitor://localhost', $allowedOrigins);
        $this->assertContains('ionic://localhost', $allowedOrigins);
        $this->assertContains('http://10.0.2.2:8000', $allowedOrigins);
    }

    /**
     * Test POST /api/v1/auth/register creates user and returns Sanctum Bearer token.
     */
    public function test_registration_endpoint_creates_user_and_issues_token(): void
    {
        $uniqueEmail = 'new_atelier_' . uniqid() . '@eclore.test';
        $uniqueUsername = 'atelier_member_' . uniqid();

        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Claire',
            'last_name' => 'Laurent',
            'email' => $uniqueEmail,
            'username' => $uniqueUsername,
            'password' => 'Bespoke2026!',
            'password_confirmation' => 'Bespoke2026!',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => ['id', 'email', 'username'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $uniqueEmail,
            'username' => $uniqueUsername,
        ]);
    }

    /**
     * Test POST /api/v1/auth/forgot-password sends reset token to valid user email.
     */
    public function test_forgot_password_dispatches_reset_link_for_valid_user(): void
    {
        $user = User::factory()->create([
            'email' => 'reset_target_' . uniqid() . '@eclore.test',
            'username' => 'reset_user_' . uniqid(),
            'password' => Hash::make('OldSecret123!'),
        ]);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('magic_link_tokens', [
            'email' => $user->email,
            'type' => 'password_reset',
        ]);
    }

    /**
     * Test POST /api/v1/auth/forgot-password returns 404 for unknown user.
     */
    public function test_forgot_password_returns_404_for_unknown_user(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'unknown_client_' . uniqid() . '@nonexistent.com',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    /**
     * Test POST /api/v1/auth/reset-password resets password using valid token.
     */
    public function test_reset_password_with_valid_token_updates_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'reset_apply_' . uniqid() . '@eclore.test',
            'username' => 'reset_applier_' . uniqid(),
            'password' => Hash::make('InitialPwd123!'),
        ]);

        $magicLinkService = app(\App\Services\MagicLinkService::class);
        $token = $magicLinkService->generateMagicLink($user, 'password_reset');

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'password' => 'NewBespoke2026!',
            'password_confirmation' => 'NewBespoke2026!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify user can now authenticate with the new password
        $loginResponse = $this->postJson('/api/v1/auth/token', [
            'login' => $user->email,
            'password' => 'NewBespoke2026!',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
