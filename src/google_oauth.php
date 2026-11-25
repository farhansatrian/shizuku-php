<?php
declare(strict_types=1);

const GOOGLE_OAUTH_COOKIE_NAME = 'shizuku_google_oauth';
const GOOGLE_OAUTH_SENTINEL_PREFIX = '$oauth$google$';

function google_oauth_config(): ?array
{
    $clientId = env_str('GOOGLE_CLIENT_ID', '');
    $clientSecret = env_str('GOOGLE_CLIENT_SECRET', '');
    if ($clientId === '' || $clientSecret === '') {
        return null;
    }
    return ['clientId' => $clientId, 'clientSecret' => $clientSecret];
}

function is_google_oauth_configured(): bool
{
    return google_oauth_config() !== null;
}

function google_oauth_state_secret(): string
{
    return 'google-oauth:' . ecosystem_auth_secret();
}

function sanitize_oauth_redirect(?string $value): string
{
    if ($value === null || trim($value) === '') {
        return '/account';
    }
    $trimmed = trim($value);
    if (str_starts_with($trimmed, '/') && !str_starts_with($trimmed, '//')) {
        return $trimmed;
    }

    $parts = parse_url($trimmed);
    if (
        is_array($parts) &&
        in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true) &&
        !empty($parts['host'])
    ) {
        $origin = strtolower((string) $parts['scheme']) . '://' . $parts['host']
            . (isset($parts['port']) ? ':' . $parts['port'] : '');
        if (is_allowed_ecosystem_origin($origin)) {
            $path = (string) ($parts['path'] ?? '/');
            $query = isset($parts['query']) ? '?' . $parts['query'] : '';
            return $origin . $path . $query;
        }
    }

    return '/account';
}

function is_external_redirect_target(string $target): bool
{
    return str_starts_with($target, 'http://') || str_starts_with($target, 'https://');
}

function google_http_request(string $url, string $method = 'GET', array $headers = [], ?string $body = null): array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $resp = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['status' => $status, 'body' => is_string($resp) ? $resp : ''];
    }

    $ctx = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $body ?? '',
            'timeout' => 15,
            'ignore_errors' => true,
        ],
    ]);
    $resp = @file_get_contents($url, false, $ctx);
    $status = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $status = (int) $m[1];
    }
    return ['status' => $status, 'body' => is_string($resp) ? $resp : ''];
}

function handle_google_oauth_start(): never
{
    $baseUrl = resolve_public_base_url();
    $config = google_oauth_config();
    $redirectTarget = sanitize_oauth_redirect($_GET['redirect'] ?? null);

    if (!$config) {
        google_oauth_error_redirect($baseUrl, 'GOOGLE_NOT_CONFIGURED', is_external_redirect_target($redirectTarget) ? $redirectTarget : null);
    }

    $state = b64url_encode(random_bytes(24));
    $codeVerifier = b64url_encode(random_bytes(32));
    $codeChallenge = b64url_encode(hash('sha256', $codeVerifier, true));

    $stateToken = jwt_sign([
        'state' => $state,
        'codeVerifier' => $codeVerifier,
        'redirect' => $redirectTarget,
    ], google_oauth_state_secret(), 60 * 10);

    set_cookie_safe(GOOGLE_OAUTH_COOKIE_NAME, $stateToken, 60 * 10, '/api/ecosystem/google');

    $callbackUri = $baseUrl . '/api/ecosystem/google/callback';
    $query = http_build_query([
        'client_id' => $config['clientId'],
        'redirect_uri' => $callbackUri,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'state' => $state,
        'code_challenge' => $codeChallenge,
        'code_challenge_method' => 'S256',
        'prompt' => 'select_account',
    ]);

    redirect_to('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
}

function google_oauth_error_redirect(string $baseUrl, string $code, ?string $externalTarget = null): never
{
    set_cookie_safe(GOOGLE_OAUTH_COOKIE_NAME, '', 0, '/api/ecosystem/google');
    if ($externalTarget !== null && is_external_redirect_target($externalTarget)) {
        $sep = str_contains($externalTarget, '?') ? '&' : '?';
        redirect_to($externalTarget . $sep . 'oauthError=' . rawurlencode($code));
    }
    redirect_to($baseUrl . '/account?oauthError=' . rawurlencode($code));
}

function handle_google_oauth_callback(): never
{
    $baseUrl = resolve_public_base_url();
    $config = google_oauth_config();
    $cookieToken = (string) ($_COOKIE[GOOGLE_OAUTH_COOKIE_NAME] ?? '');
    $stored = $cookieToken !== '' ? jwt_verify($cookieToken, google_oauth_state_secret()) : null;
    $externalReturn = ($stored && is_string($stored['redirect'] ?? null) && is_external_redirect_target($stored['redirect']))
        ? sanitize_oauth_redirect($stored['redirect'])
        : null;

    if (!$config) {
        google_oauth_error_redirect($baseUrl, 'GOOGLE_NOT_CONFIGURED', $externalReturn);
    }

    if (!empty($_GET['error'])) {
        $err = (string) $_GET['error'];
        google_oauth_error_redirect($baseUrl, $err === 'access_denied' ? 'GOOGLE_CANCELLED' : 'GOOGLE_AUTH_FAILED', $externalReturn);
    }

    $code = (string) ($_GET['code'] ?? '');
    $returnedState = (string) ($_GET['state'] ?? '');

    if ($code === '' || $returnedState === '' || $cookieToken === '') {
        google_oauth_error_redirect($baseUrl, 'GOOGLE_AUTH_FAILED', $externalReturn);
    }

    if (
        !$stored ||
        !is_string($stored['state'] ?? null) ||
        !is_string($stored['codeVerifier'] ?? null) ||
        !hash_equals($stored['state'], $returnedState)
    ) {
        google_oauth_error_redirect($baseUrl, 'GOOGLE_AUTH_FAILED', $externalReturn);
    }

    $callbackUri = $baseUrl . '/api/ecosystem/google/callback';
    $tokenResp = google_http_request(
        'https://oauth2.googleapis.com/token',
        'POST',
        ['Content-Type: application/x-www-form-urlencoded'],
        http_build_query([
            'code' => $code,
            'client_id' => $config['clientId'],
            'client_secret' => $config['clientSecret'],
            'redirect_uri' => $callbackUri,
            'grant_type' => 'authorization_code',
            'code_verifier' => $stored['codeVerifier'],
        ])
    );

    if ($tokenResp['status'] < 200 || $tokenResp['status'] >= 300) {
        google_oauth_error_redirect($baseUrl, 'GOOGLE_AUTH_FAILED', $externalReturn);
    }

    $tokenData = json_decode($tokenResp['body'], true);
    $accessToken = is_array($tokenData) ? (string) ($tokenData['access_token'] ?? '') : '';
    if ($accessToken === '') {
        google_oauth_error_redirect($baseUrl, 'GOOGLE_AUTH_FAILED', $externalReturn);
    }

    $userInfoResp = google_http_request(
        'https://openidconnect.googleapis.com/v1/userinfo',
        'GET',
        ['Authorization: Bearer ' . $accessToken]
    );
    if ($userInfoResp['status'] < 200 || $userInfoResp['status'] >= 300) {
        google_oauth_error_redirect($baseUrl, 'GOOGLE_AUTH_FAILED', $externalReturn);
    }

    $profile = json_decode($userInfoResp['body'], true);
    if (!is_array($profile) || empty($profile['email']) || ($profile['email_verified'] ?? false) !== true) {
        google_oauth_error_redirect($baseUrl, 'GOOGLE_EMAIL_UNVERIFIED', $externalReturn);
    }

    $email = strtolower(trim((string) $profile['email']));
    $fallbackName = explode('@', $email)[0] ?: 'SHIZUKU User';
    $displayName = mb_substr(trim((string) ($profile['name'] ?? $fallbackName)), 0, 60);
    if ($displayName === '') {
        $displayName = $fallbackName;
    }

    $pdo = db();
    $stmt = $pdo->prepare('SELECT "id", "email", "displayName", "createdAt" FROM "EcosystemUser" WHERE "email" = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if (!$row) {
        $now = now_iso();
        $id = make_cuid('eco_');
        $sentinel = GOOGLE_OAUTH_SENTINEL_PREFIX . bin2hex(random_bytes(24));
        $ins = $pdo->prepare(
            'INSERT INTO "EcosystemUser" ("id", "email", "displayName", "passwordHash", "createdAt", "updatedAt")
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $ins->execute([$id, $email, $displayName, $sentinel, $now, $now]);
        $row = [
            'id' => $id,
            'email' => $email,
            'displayName' => $displayName,
            'createdAt' => $now,
        ];
    }

    $sessionUser = [
        'id' => (string) $row['id'],
        'email' => (string) $row['email'],
        'displayName' => (string) $row['displayName'],
        'createdAt' => format_iso_date((string) $row['createdAt']),
    ];

    $sessionToken = create_ecosystem_token($sessionUser);
    set_ecosystem_cookie($sessionToken);
    set_cookie_safe(GOOGLE_OAUTH_COOKIE_NAME, '', 0, '/api/ecosystem/google');

    $safeTarget = sanitize_oauth_redirect((string) ($stored['redirect'] ?? '/account'));
    if (is_external_redirect_target($safeTarget)) {
        $cleanTarget = preg_replace('/#.*$/', '', $safeTarget) ?? $safeTarget;
        redirect_to($cleanTarget . '#shizuku_token=' . rawurlencode($sessionToken));
    }

    redirect_to($baseUrl . $safeTarget);
}
