<?php
declare(strict_types=1);

define('SHIZUKU_ROOT', dirname(__DIR__));

function shizuku_load_env(?string $path = null): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $file = $path ?? (SHIZUKU_ROOT . '/.env');
    if (!is_file($file) || !is_readable($file)) {
        return;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }
        $eqPos = strpos($trimmed, '=');
        if ($eqPos === false) {
            continue;
        }
        $key = trim(substr($trimmed, 0, $eqPos));
        $val = trim(substr($trimmed, $eqPos + 1));

        if ($key === '') {
            continue;
        }

        if (
            (str_starts_with($val, '"') && str_ends_with($val, '"') && strlen($val) >= 2) ||
            (str_starts_with($val, "'") && str_ends_with($val, "'") && strlen($val) >= 2)
        ) {
            $val = substr($val, 1, -1);
        }

        if (getenv($key) === false) {
            putenv("$key=$val");
            $_ENV[$key] = $val;
        }
    }
}

function env_str(string $key, string $default = ''): string
{
    shizuku_load_env();
    $val = $_ENV[$key] ?? getenv($key);
    if ($val === false || $val === null) {
        return $default;
    }
    return trim((string) $val);
}

function env_bool(string $key, bool $default = false): bool
{
    $raw = strtolower(env_str($key, $default ? 'true' : 'false'));
    return in_array($raw, ['1', 'true', 'yes', 'on'], true);
}

function is_production(): bool
{
    return strtolower(env_str('APP_ENV', 'production')) === 'production';
}

function resolve_path_from_root(string $path): string
{
    if (str_starts_with($path, '/')) {
        return $path;
    }
    return SHIZUKU_ROOT . '/' . ltrim($path, './');
}

function db_file_path(): string
{
    $rel = env_str('DATABASE_PATH', 'data/shizuku.db');
    return resolve_path_from_root($rel);
}

function upload_dir_path(): string
{
    $rel = env_str('UPLOAD_DIR', 'data/uploads');
    return rtrim(resolve_path_from_root($rel), '/');
}

function resolve_public_base_url(): string
{
    $configured = env_str('PUBLIC_BASE_URL', '');
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    $forwardedHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '';
    if ($forwardedHost !== '') {
        $host = trim(explode(',', $forwardedHost)[0]);
        $forwardedProto = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'https')[0]));
        $proto = ($forwardedProto === 'http' || $forwardedProto === 'https') ? $forwardedProto : 'https';
        return $proto . '://' . $host;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $proto = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8088';
    return $proto . '://' . $host;
}
