<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePowerBiFeed
{
    public const TENANT_ATTRIBUTE = 'power_bi_tenant';

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant === null) {
            return $this->unauthorizedResponse();
        }

        $request->attributes->set(self::TENANT_ATTRIBUTE, $tenant);

        return $next($request);
    }

    private function resolveTenant(Request $request): ?string
    {
        $tenants = config('services.power_bi.tenants', []);

        if (! is_array($tenants)) {
            return null;
        }

        [$username, $password] = $this->basicCredentials($request);
        $token = $password ?? $request->bearerToken() ?? $request->header('X-Dharta-Token');

        if (! is_string($token) || $token === '') {
            return null;
        }

        foreach ($tenants as $tenant => $configuration) {
            if (! is_string($tenant) || ! is_array($configuration)) {
                continue;
            }

            $configuredUsername = $configuration['username'] ?? null;
            $configuredToken = $configuration['token'] ?? null;

            if (! is_string($configuredToken) || $configuredToken === '') {
                continue;
            }

            if ($username !== null && (! is_string($configuredUsername) || ! hash_equals($configuredUsername, $username))) {
                continue;
            }

            if (hash_equals($configuredToken, $token)) {
                return $tenant;
            }
        }

        return null;
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function basicCredentials(Request $request): array
    {
        $authorization = $request->header('Authorization');

        if (! is_string($authorization) || ! Str::startsWith($authorization, 'Basic ')) {
            return [null, null];
        }

        $decoded = base64_decode(Str::after($authorization, 'Basic '), true);

        if (! is_string($decoded) || ! str_contains($decoded, ':')) {
            return [null, null];
        }

        [$username, $password] = explode(':', $decoded, 2);

        return [$username, $password];
    }

    private function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 'Unauthorized',
                'message' => 'A valid Dharta Power BI credential is required.',
            ],
        ], Response::HTTP_UNAUTHORIZED, [
            'WWW-Authenticate' => 'Basic realm="Dharta Power BI", charset="UTF-8"',
        ]);
    }
}
