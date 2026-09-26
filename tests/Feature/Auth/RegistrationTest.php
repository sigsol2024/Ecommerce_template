<?php

namespace Tests\Feature\Auth;

use App\Services\Auth\EmailOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $this->mock(EmailOtpService::class, function ($mock) {
            $mock->shouldReceive('issueForRegistration')->once()->andReturn(true);
            $mock->shouldReceive('verifyRegistration')->once()->andReturn(true);
        });

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Str0ng!Pass99',
            'password_confirmation' => 'Str0ng!Pass99',
        ])->assertRedirect(route('register.verify.show', absolute: false));

        $this->assertGuest();

        $this->post('/register/verify', [
            'code' => '000000',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }
}
