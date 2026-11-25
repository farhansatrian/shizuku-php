<?php
declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function now_iso(): string
{
    return gmdate('Y-m-d\TH:i:s.000\Z');
}

function make_cuid(string $prefix = 'c'): string
{
    return $prefix . base_convert((string) (int) (microtime(true) * 1000), 10, 36) . bin2hex(random_bytes(8));
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function request_path(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        return '/';
    }
    $path = '/' . trim($path, '/');
    return $path === '' ? '/' : $path;
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function get_authorization_header(): string
{
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return (string) $_SERVER['HTTP_AUTHORIZATION'];
    }
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return (string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (is_array($headers)) {
            foreach ($headers as $k => $v) {
                if (strtolower((string) $k) === 'authorization') {
                    return (string) $v;
                }
            }
        }
    }
    return '';
}

function get_bearer_token(): ?string
{
    $auth = trim(get_authorization_header());
    if ($auth !== '' && stripos($auth, 'bearer ') === 0) {
        $token = trim(substr($auth, 7));
        return $token !== '' ? $token : null;
    }
    return null;
}

function is_allowed_ecosystem_origin(string $origin): bool
{
    $devDefaults = [
        'http://127.0.0.1:5173',
        'http://localhost:5173',
        'http://127.0.0.1:3000',
        'http://localhost:3000',
        'http://127.0.0.1:3001',
        'http://localhost:3001',
        'http://127.0.0.1:8088',
        'http://localhost:8088',
    ];

    $configured = array_values(array_filter(array_map(
        'trim',
        explode(',', env_str('ECOSYSTEM_ALLOWED_ORIGINS', ''))
    )));

    $allowed = is_production() ? $configured : array_merge($devDefaults, $configured);
    return in_array('*', $allowed, true) || in_array($origin, $allowed, true);
}

function apply_ecosystem_cors(): void
{
    $origin = trim((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));
    if ($origin !== '' && is_allowed_ecosystem_origin($origin)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Vary: Origin');
    }
}

function send_cors_preflight(): never
{
    apply_ecosystem_cors();
    http_response_code(204);
    exit;
}

function json_response(array $data, int $status = 200, bool $withCors = false): never
{
    if ($withCors) {
        apply_ecosystem_cors();
    }
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function redirect_to(string $url, int $status = 302): never
{
    http_response_code($status);
    header('Location: ' . $url);
    exit;
}

function set_cookie_safe(
    string $name,
    string $value,
    int $maxAgeSeconds,
    string $path = '/',
    ?string $domain = null
): void {
    $opts = [
        'expires' => $maxAgeSeconds > 0 ? time() + $maxAgeSeconds : time() - 3600,
        'path' => $path,
        'secure' => env_bool('COOKIE_SECURE', false),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    if ($domain !== null && $domain !== '') {
        $opts['domain'] = $domain;
    }
    setcookie($name, $value, $opts);
}

function safe_json_decode(?string $raw, mixed $fallback): mixed
{
    if ($raw === null || $raw === '') {
        return $fallback;
    }
    $decoded = json_decode($raw, true);
    return json_last_error() === JSON_ERROR_NONE ? $decoded : $fallback;
}
