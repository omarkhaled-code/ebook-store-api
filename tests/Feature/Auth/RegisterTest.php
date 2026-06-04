<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Omar Khaled',
            'email'                 => 'omar@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'name', 'email'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'omar@test.com',
            'role'  => 'user',
        ]);
    }

    #[Test]
    public function user_cannot_register_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'omar@test.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Omar Khaled',
            'email'                 => 'omar@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function user_cannot_register_with_short_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Omar Khaled',
            'email'                 => 'omar@test.com',
            'password'              => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    #[Test]
    public function registered_user_has_user_role(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Omar Khaled',
            'email'                 => 'omar@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'omar@test.com')->first();
        $this->assertEquals('user', $user->role);
    }
}