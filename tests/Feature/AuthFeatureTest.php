<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'auth_test_user@cargotrace.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);
    }

    public function test_user_can_login_with_valid_credentials_via_web(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'auth_test_user@cargotrace.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($this->user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_user_cannot_login_with_invalid_credentials_via_web(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'auth_test_user@cargotrace.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_root_route_redirects_guest_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    public function test_root_route_redirects_authenticated_user_to_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/');
        $response->assertRedirect(route('dashboard'));
    }

    public function test_unauthenticated_user_accessing_protected_web_routes_is_redirected_to_login(): void
    {
        $protectedRoutes = [
            route('dashboard'),
            route('shipments.index'),
            route('notifications.index'),
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect(route('login'));
        }
    }

    public function test_user_can_login_with_valid_credentials_via_api(): void
    {
        $response = $this->postJson(route('api.login'), [
            'email' => 'auth_test_user@cargotrace.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ])
            ->assertJson([
                'message' => 'Login successful.',
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_user_cannot_login_with_invalid_credentials_via_api(): void
    {
        $response = $this->postJson(route('api.login'), [
            'email' => 'auth_test_user@cargotrace.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid login credentials.',
            ]);
    }

    public function test_unauthenticated_user_accessing_protected_api_routes_returns_401(): void
    {
        $protectedEndpoints = [
            route('api.user'),
            route('api.shipments.index'),
            route('api.carriers.index'),
            route('api.locations.index'),
        ];

        foreach ($protectedEndpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.',
                ]);
        }
    }

    public function test_authenticated_user_can_logout_via_web(): void
    {
        $response = $this->actingAs($this->user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_authenticated_user_can_logout_via_api(): void
    {
        $token = $this->user->createToken('auth-test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.logout'));

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out.',
            ]);

        $this->assertCount(0, $this->user->fresh()->tokens);
    }
}
