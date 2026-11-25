<?php
declare(strict_types=1);

require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/http.php';
require_once __DIR__ . '/src/jwt.php';
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/google_oauth.php';
require_once __DIR__ . '/src/uploads.php';
require_once __DIR__ . '/src/portfolio.php';
require_once __DIR__ . '/src/api/ecosystem.php';
require_once __DIR__ . '/src/api/shikas.php';
require_once __DIR__ . '/src/api/admin_auth.php';
require_once __DIR__ . '/src/api/admin_crud.php';
require_once __DIR__ . '/src/views/layout.php';
require_once __DIR__ . '/src/views/home.php';
require_once __DIR__ . '/src/views/workspace.php';
require_once __DIR__ . '/src/views/portfolio.php';
require_once __DIR__ . '/src/views/about.php';
require_once __DIR__ . '/src/views/account.php';
require_once __DIR__ . '/src/views/admin_login.php';
require_once __DIR__ . '/src/views/admin_studio.php';

shizuku_load_env();

$method = request_method();
$path = request_path();

// 0. Serve /public static assets when running via PHP built-in server (`php -S`)
if (PHP_SAPI === 'cli-server') {
    $staticCandidate = SHIZUKU_ROOT . '/public' . $path;
    if ($path !== '/' && is_file($staticCandidate)) {
        $ext = strtolower(pathinfo($staticCandidate, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            default => 'application/octet-stream',
        };
        header('Content-Type: ' . $mime);
        readfile($staticCandidate);
        exit;
    }
}

// 1. CORS Preflight for Ecosystem & SHIKAS endpoints
if ($method === 'OPTIONS' && (str_starts_with($path, '/api/ecosystem/') || $path === '/api/shikas/store')) {
    send_cors_preflight();
}

// 2. Healthcheck
if ($path === '/api/health' && $method === 'GET') {
    try {
        db()->query('SELECT 1');
        json_response(['status' => 'ok', 'app' => 'shizuku-php', 'timestamp' => now_iso()]);
    } catch (Throwable) {
        json_response(['status' => 'error', 'app' => 'shizuku-php'], 500);
    }
}

// 3. Uploads file streaming (/api/uploads/{filename})
if (preg_match('#^/api/uploads/([^/]+)$#', $path, $m) && $method === 'GET') {
    serve_upload_file(rawurldecode($m[1]));
}

// 4. Ecosystem Auth API (100% AGENTS.md Contract)
if ($path === '/api/ecosystem/register' && $method === 'POST') {
    handle_ecosystem_register();
}
if ($path === '/api/ecosystem/login' && $method === 'POST') {
    handle_ecosystem_login();
}
if ($path === '/api/ecosystem/logout' && $method === 'POST') {
    handle_ecosystem_logout();
}
if ($path === '/api/ecosystem/me' && $method === 'GET') {
    handle_ecosystem_me();
}
if ($path === '/api/ecosystem/google' && $method === 'GET') {
    handle_google_oauth_start();
}
if ($path === '/api/ecosystem/google/callback' && $method === 'GET') {
    handle_google_oauth_callback();
}

// 5. SHIKAS v5 Cloud Store API (100% AGENTS.md Contract)
if ($path === '/api/shikas/store') {
    if ($method === 'GET') {
        handle_shikas_store_get();
    }
    if ($method === 'PUT') {
        handle_shikas_store_put();
    }
}

// 6. Admin Auth API
if ($path === '/api/auth/login' && $method === 'POST') {
    handle_admin_login();
}
if ($path === '/api/auth/logout' && $method === 'POST') {
    handle_admin_logout();
}
if ($path === '/api/auth/session' && $method === 'GET') {
    handle_admin_session();
}

// 7. Admin CRUD & Upload API
if ($path === '/api/admin/upload' && $method === 'POST') {
    handle_admin_upload();
}
if ($path === '/api/admin/profile' && $method === 'PUT') {
    handle_admin_profile_put();
}
if ($path === '/api/admin/password' && $method === 'POST') {
    handle_admin_password_post();
}
if ($path === '/api/admin/workspace' && $method === 'POST') {
    handle_admin_workspace_post();
}
if (preg_match('#^/api/admin/workspace/([^/]+)$#', $path, $m) && in_array($method, ['PATCH', 'DELETE'], true)) {
    handle_admin_workspace_item(rawurldecode($m[1]), $method);
}
if ($path === '/api/admin/experiences' && $method === 'POST') {
    handle_admin_experience_post();
}
if (preg_match('#^/api/admin/experiences/([^/]+)$#', $path, $m) && in_array($method, ['PATCH', 'DELETE'], true)) {
    handle_admin_experience_item(rawurldecode($m[1]), $method);
}
if ($path === '/api/admin/certificates' && $method === 'POST') {
    handle_admin_certificate_post();
}
if (preg_match('#^/api/admin/certificates/([^/]+)$#', $path, $m) && in_array($method, ['PATCH', 'DELETE'], true)) {
    handle_admin_certificate_item(rawurldecode($m[1]), $method);
}

// 8. Public & Admin HTML Pages
$ecoUser = get_ecosystem_user();

if ($path === '/') {
    render_home_page(get_portfolio_data(true), $ecoUser);
    exit;
}
if ($path === '/workspace') {
    render_workspace_page(get_portfolio_data(true), $ecoUser, get_admin_session() !== null);
    exit;
}
if ($path === '/portfolio') {
    render_portfolio_page(get_portfolio_data(true), $ecoUser);
    exit;
}
if ($path === '/projects' || str_starts_with($path, '/projects/')) {
    redirect_to('/portfolio', 301);
}
if ($path === '/about') {
    render_about_page(get_portfolio_data(true), $ecoUser);
    exit;
}
if ($path === '/account') {
    render_account_page(get_portfolio_data(true), $ecoUser);
    exit;
}
if ($path === '/login') {
    $q = !empty($_GET['redirect']) ? '&redirect=' . rawurlencode((string) $_GET['redirect']) : '';
    redirect_to('/account?mode=login' . $q);
}
if ($path === '/register') {
    $q = !empty($_GET['redirect']) ? '&redirect=' . rawurlencode((string) $_GET['redirect']) : '';
    redirect_to('/account?mode=register' . $q);
}
if ($path === '/admin/login') {
    if (get_admin_session()) {
        redirect_to('/admin');
    }
    $data = get_portfolio_data(true);
    render_admin_login_page($data['profile']);
    exit;
}
if ($path === '/admin') {
    $admin = get_admin_session();
    if (!$admin) {
        redirect_to('/admin/login');
    }
    render_admin_studio_page(get_portfolio_data(false), $admin);
    exit;
}

http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
echo '<!doctype html><html><body style="font-family:monospace;padding:40px;text-align:center;color:#745bc7"><h1>404 — shizuku (雫)</h1><p><a href="/" style="color:#745bc7">Back to Home</a></p></body></html>';
