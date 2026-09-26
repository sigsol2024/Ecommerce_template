<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\Mail\OutboundMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $this->mock(OutboundMailService::class, function ($mock) {
            $mock->shouldReceive('send')->once();
        });

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHasNoErrors();
    }

    private function extractResetTokenFromEmailHtml(string $html): string
    {
        if (preg_match('~/reset-password/([^?\s"\']+)~', $html, $m)) {
            return rawurldecode($m[1]);
        }

        $this->fail('Could not parse reset token from email HTML');
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $capturedHtml = '';
        $this->mock(OutboundMailService::class, function ($mock) use (&$capturedHtml) {
            $mock->shouldReceive('send')
                ->once()
                ->andReturnUsing(function (string $toEmail, string $toName, string $subject, string $html, ?string $replyToEmail = null, ?string $replyToName = null) use (&$capturedHtml): void {
                    $capturedHtml = $html;
                });
        });

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $token = $this->extractResetTokenFromEmailHtml($capturedHtml);
        $response = $this->get('/reset-password/'.$token.'?email='.urlencode($user->email));

        $response->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $capturedHtml = '';
        $this->mock(OutboundMailService::class, function ($mock) use (&$capturedHtml) {
            $mock->shouldReceive('send')
                ->once()
                ->andReturnUsing(function (string $toEmail, string $toName, string $subject, string $html, ?string $replyToEmail = null, ?string $replyToName = null) use (&$capturedHtml): void {
                    $capturedHtml = $html;
                });
        });

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $token = $this->extractResetTokenFromEmailHtml($capturedHtml);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Str0ng!Pass99',
            'password_confirmation' => 'Str0ng!Pass99',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(password_verify('Str0ng!Pass99', $user->fresh()->password));
    }
}
