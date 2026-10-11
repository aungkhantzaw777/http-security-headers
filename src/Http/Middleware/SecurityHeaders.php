<?php

namespace AungKhant\HttpSecurityHeaders\Http\Middleware;

use AungKhant\HttpSecurityHeaders\SecurityHeadersManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function __construct(private readonly SecurityHeadersManager $manager) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->manager->enabled()) {
            return $response;
        }

        foreach ($this->manager->headers() as $name => $value) {
            $response->headers->set($name, $value);
        }

        foreach ($this->manager->headersToRemove() as $name) {
            $response->headers->remove($name);
        }

        return $response;
    }
}
