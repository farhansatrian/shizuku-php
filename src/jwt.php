<?php
declare(strict_types=1);

function b64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function b64url_decode(string $data): string|false
{
    $pad = strlen($data) % 4;
    if ($pad > 0) {
        $data .= str_repeat('=', 4 - $pad);
    }
    return base64_decode(strtr($data, '-_', '+/'), true);
}

function jwt_sign(array $payload, string $secret, int $ttlSeconds): string
{
    $now = time();
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $claims = array_merge($payload, [
        'iat' => $now,
        'exp' => $now + $ttlSeconds,
    ]);

    $h64 = b64url_encode((string) json_encode($header, JSON_UNESCAPED_SLASHES));
    $p64 = b64url_encode((string) json_encode($claims, JSON_UNESCAPED_SLASHES));
    $sig = hash_hmac('sha256', "$h64.$p64", $secret, true);
    return "$h64.$p64." . b64url_encode($sig);
}

function jwt_verify(string $token, string $secret): ?array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }
    [$h64, $p64, $s64] = $parts;
    $headerRaw = b64url_decode($h64);
    $payloadRaw = b64url_decode($p64);
    $sigRaw = b64url_decode($s64);
    if ($headerRaw === false || $payloadRaw === false || $sigRaw === false) {
        return null;
    }

    $header = json_decode($headerRaw, true);
    $payload = json_decode($payloadRaw, true);
    if (!is_array($header) || !is_array($payload)) {
        return null;
    }
    if (($header['alg'] ?? '') !== 'HS256') {
        return null;
    }

    $expected = hash_hmac('sha256', "$h64.$p64", $secret, true);
    if (!hash_equals($expected, $sigRaw)) {
        return null;
    }

    $exp = $payload['exp'] ?? null;
    if (!is_int($exp) && !is_float($exp)) {
        return null;
    }
    if ((int) $exp < time()) {
        return null;
    }

    return $payload;
}
