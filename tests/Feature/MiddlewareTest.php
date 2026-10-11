<?php

namespace AungKhant\HttpSecurityHeaders\Tests\Feature;

use AungKhant\HttpSecurityHeaders\Http\Middleware\SecurityHeaders;
use AungKhant\HttpSecurityHeaders\Tests\TestCase;
use Illuminate\Support\Facades\Route;

final class MiddlewareTest extends TestCase
{
    public function test_middleware_applies_headers_and_removes_requested_headers(): void
    {
        config()->set('security-headers.headers', ['x-frame-options' => 'DENY']);
        config()->set('security-headers.remove', ['X-Powered-By', 'referrer-policy']);
        Route::middleware(SecurityHeaders::class)->get('/secured', fn () => response('hello', 201, [
            'X-Powered-By' => 'PHP', 'X-Frame-Options' => 'SAMEORIGIN', 'X-Keep' => 'yes',
        ]));

        $this->get('/secured')->assertStatus(201)->assertContent('hello')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Keep', 'yes')
            ->assertHeaderMissing('X-Powered-By')
            ->assertHeaderMissing('Referrer-Policy');
    }

    public function test_disabled_middleware_preserves_response_headers(): void
    {
        config()->set('security-headers.enabled', false);
        config()->set('security-headers.remove', ['X-Powered-By']);
        Route::middleware(SecurityHeaders::class)->get('/disabled', fn () => response('hello', 202, ['X-Powered-By' => 'PHP']));
        $this->get('/disabled')->assertStatus(202)->assertContent('hello')
            ->assertHeader('X-Powered-By', 'PHP')->assertHeaderMissing('X-Content-Type-Options');
    }

    public function test_middleware_is_opt_in(): void
    {
        Route::get('/plain', fn () => 'hello');
        $this->get('/plain')->assertOk()->assertHeaderMissing('X-Content-Type-Options');
    }

    public function test_disabling_a_preset_header_preserves_an_application_header(): void
    {
        config()->set('security-headers.headers', ['X-Frame-Options' => false]);
        Route::middleware(SecurityHeaders::class)->get('/custom', fn () => response('hello', 200, ['X-Frame-Options' => 'DENY']));
        $this->get('/custom')->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_json_and_redirect_responses_are_supported(): void
    {
        Route::middleware(SecurityHeaders::class)->get('/json', fn () => response()->json(['ok' => true]));
        Route::middleware(SecurityHeaders::class)->get('/redirect', fn () => redirect('/json'));
        $this->get('/json')->assertJson(['ok' => true])->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/redirect')->assertRedirect('/json')->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
