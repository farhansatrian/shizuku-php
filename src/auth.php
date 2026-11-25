<?php
declare(strict_types=1);

const ADMIN_COOKIE_NAME = 'shizuku_admin_session';
const ECOSYSTEM_COOKIE_NAME = 'shizuku_eco_session';

function admin_auth_secret(): string
{
    return env_str('AUTH_SECRET', 'shizuku-fallback-dev-secret-please-set-auth-secret-in-env');
}

function ecosystem_auth_secret(): string
{
    $secret = env_str('ECOSYSTEM_AUTH_SECRET', '');
    return $secret !== '' ? $secret : admin_auth_secret();
}

function create_admin_token(array $payload): string
{
    return jwt_sign([
        'userId' => (string) $payload['userId'],
        'username' => (string) $payload['username'],
        'displayName' => (string) $payload['displayName'],
    ], admin_auth_secret(), 60 * 60 * 24 * 7);
}

function get_admin_session(): ?array
{
    $token = $_COOKIE[ADMIN_COOKIE_NAME] ?? null;
    if (!is_string($token) || $token === '') {
        return null;
    }
    $claims = jwt_verify($token, admin_auth_secret());
    if (!$claims) {
        return null;
    }
    if (
        !isset($claims['userId'], $claims['username'], $claims['displayName']) ||
        !is_string($claims['userId']) ||
        !is_string($claims['username']) ||
        !is_string($claims['displayName'])
    ) {
        return null;
    }
    return [
        'userId' => $claims['userId'],
        'username' => $claims['username'],
        'displayName' => $claims['displayName'],
    ];
}

function set_admin_cookie(string $token): void
{
    set_cookie_safe(ADMIN_COOKIE_NAME, $token, 60 * 60 * 24 * 7, '/');
}

function clear_admin_cookie(): void
{
    set_cookie_safe(ADMIN_COOKIE_NAME, '', 0, '/');
}

function assert_same_origin_request(): void
{
    $method = request_method();
    if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        return;
    }
    $origin = trim((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));
    if ($origin === '') {
        return;
    }
    $expectedHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $parsedHost = strtolower((string) (parse_url($origin, PHP_URL_HOST) ?? ''));
    $parsedPort = parse_url($origin, PHP_URL_PORT);
    $originHostPort = $parsedHost . ($parsedPort ? ':' . $parsedPort : '');

    $publicBase = resolve_public_base_url();
    $publicOrigin = strtolower((string) parse_url($publicBase, PHP_URL_SCHEME) . '://' . (string) parse_url($publicBase, PHP_URL_HOST) . (parse_url($publicBase, PHP_URL_PORT) ? ':' . parse_url($publicBase, PHP_URL_PORT) : ''));

    if (
        $originHostPort !== $expectedHost &&
        strtolower($origin) !== $publicOrigin
    ) {
        json_response(['error' => 'Forbidden origin'], 403);
    }
}

function require_admin_api(): array
{
    assert_same_origin_request();
    $session = get_admin_session();
    if (!$session) {
        json_response(['error' => 'Unauthorized'], 401);
    }
    return $session;
}

function create_ecosystem_token(array $user): string
{
    return jwt_sign([
        'userId' => (string) $user['id'],
        'email' => (string) $user['email'],
        'displayName' => (string) $user['displayName'],
    ], ecosystem_auth_secret(), 60 * 60 * 24 * 30);
}

function set_ecosystem_cookie(string $token): void
{
    $domain = env_str('ECOSYSTEM_COOKIE_DOMAIN', '');
    set_cookie_safe(ECOSYSTEM_COOKIE_NAME, $token, 60 * 60 * 24 * 30, '/', $domain !== '' ? $domain : null);
}

function clear_ecosystem_cookie(): void
{
    $domain = env_str('ECOSYSTEM_COOKIE_DOMAIN', '');
    set_cookie_safe(ECOSYSTEM_COOKIE_NAME, '', 0, '/', $domain !== '' ? $domain : null);
}

function format_iso_date(string $raw): string
{
    if (str_contains($raw, 'T')) {
        return $raw;
    }
    $ts = strtotime($raw);
    return $ts !== false ? gmdate('Y-m-d\TH:i:s.000\Z', $ts) : $raw;
}

function get_ecosystem_user(): ?array
{
    $token = get_bearer_token() ?? ($_COOKIE[ECOSYSTEM_COOKIE_NAME] ?? null);
    if (!is_string($token) || $token === '') {
        return null;
    }

    $claims = jwt_verify($token, ecosystem_auth_secret());
    if (!$claims || empty($claims['userId']) || !is_string($claims['userId'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT "id", "email", "displayName", "createdAt" FROM "EcosystemUser" WHERE "id" = ? LIMIT 1');
    $stmt->execute([$claims['userId']]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }

    return [
        'id' => (string) $row['id'],
        'email' => (string) $row['email'],
        'displayName' => (string) $row['displayName'],
        'createdAt' => format_iso_date((string) $row['createdAt']),
    ];
}
