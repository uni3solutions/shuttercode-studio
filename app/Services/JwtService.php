<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class JwtService
{
    /**
     * Secret key derived from application key.
     */
    protected string $secretKey;

    /**
     * Default Token Time-To-Live in seconds (24 hours).
     */
    protected int $defaultTtl = 86400;

    public function __construct()
    {
        $appKey = config('app.key');

        if (Str::startsWith($appKey, 'base64:')) {
            $this->secretKey = base64_decode(substr($appKey, 7));
        } else {
            $this->secretKey = (string) $appKey;
        }
    }

    /**
     * Generate an RFC 7519 compliant JSON Web Token (JWT).
     */
    public function generateToken(User $user, ?int $ttl = null): string
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $now = time();

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $payload = [
            'iss' => config('app.name', 'ShutterCode Studio'),
            'sub' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $ttl,
            'jti' => Str::random(16),
        ];

        $encodedHeader = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));

        $signature = hash_hmac('sha256', "{$encodedHeader}.{$encodedPayload}", $this->secretKey, true);
        $encodedSignature = $this->base64UrlEncode($signature);

        return "{$encodedHeader}.{$encodedPayload}.{$encodedSignature}";
    }

    /**
     * Decode and validate a JWT string. Returns the payload array or null if invalid.
     */
    public function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        // Verify signature
        $expectedSignature = hash_hmac('sha256', "{$encodedHeader}.{$encodedPayload}", $this->secretKey, true);
        $expectedEncodedSignature = $this->base64UrlEncode($expectedSignature);

        if (! hash_equals($expectedEncodedSignature, $encodedSignature)) {
            return null;
        }

        // Decode payload
        $payloadJson = $this->base64UrlDecode($encodedPayload);
        if (! $payloadJson) {
            return null;
        }

        $payload = json_decode($payloadJson, true);
        if (! is_array($payload)) {
            return null;
        }

        $now = time();

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < $now) {
            return null;
        }

        // Check not-before
        if (isset($payload['nbf']) && $payload['nbf'] > $now) {
            return null;
        }

        return $payload;
    }

    /**
     * Retrieve the User corresponding to the token.
     */
    public function getUserFromToken(string $token): ?User
    {
        $payload = $this->validateToken($token);

        if (! $payload || empty($payload['sub'])) {
            return null;
        }

        return User::find($payload['sub']);
    }

    /**
     * Refresh a valid token to extend its lifetime.
     */
    public function refreshToken(string $token, ?int $ttl = null): ?string
    {
        $user = $this->getUserFromToken($token);

        if (! $user) {
            return null;
        }

        return $this->generateToken($user, $ttl);
    }

    /**
     * Base64URL encoding (RFC 4648).
     */
    public function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64URL decoding (RFC 4648).
     */
    public function base64UrlDecode(string $data): ?string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }

        $decoded = base64_decode(strtr($data, '-_', '+/'), true);

        return $decoded !== false ? $decoded : null;
    }
}
