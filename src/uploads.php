<?php
declare(strict_types=1);

function is_safe_upload_filename(string $filename): bool
{
    if ($filename === '' || strlen($filename) > 160) {
        return false;
    }
    if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
        return false;
    }
    return (bool) preg_match('/^[a-zA-Z0-9._-]+$/', $filename);
}

function content_type_for_filename(string $filename): string
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return match ($ext) {
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        'avif' => 'image/avif',
        'svg' => 'image/svg+xml',
        default => 'application/octet-stream',
    };
}

function serve_upload_file(string $filename): never
{
    if (!is_safe_upload_filename($filename)) {
        json_response(['error' => 'Invalid filename'], 400);
    }

    $fullPath = upload_dir_path() . '/' . $filename;
    if (!is_file($fullPath) || !is_readable($fullPath)) {
        json_response(['error' => 'File not found'], 404);
    }

    $size = (int) filesize($fullPath);
    $mtime = (int) filemtime($fullPath);
    $etag = '"' . $size . '-' . $mtime . '"';
    $isImmutable = (bool) preg_match('/^shizuku-[a-z0-9]+-[a-f0-9]+\./i', $filename);
    $cacheControl = $isImmutable
        ? 'public, max-age=31536000, immutable'
        : 'public, max-age=3600, stale-while-revalidate=86400';

    if (trim((string) ($_SERVER['HTTP_IF_NONE_MATCH'] ?? '')) === $etag) {
        http_response_code(304);
        header('ETag: ' . $etag);
        header('Cache-Control: ' . $cacheControl);
        exit;
    }

    http_response_code(200);
    header('Content-Type: ' . content_type_for_filename($filename));
    header('Content-Length: ' . $size);
    header('ETag: ' . $etag);
    header('Cache-Control: ' . $cacheControl);
    readfile($fullPath);
    exit;
}

function handle_admin_upload(): never
{
    require_admin_api();

    if (empty($_FILES['file']) || !is_array($_FILES['file'])) {
        json_response(['error' => 'No file provided'], 400);
    }

    $file = $_FILES['file'];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        json_response(['error' => 'Upload failed'], 400);
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > 8 * 1024 * 1024) {
        json_response(['error' => 'Image must be smaller than 8 MB'], 400);
    }

    $tmpPath = (string) ($file['tmp_name'] ?? '');
    if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
        json_response(['error' => 'Invalid upload source'], 400);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($tmpPath);
    $extMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/avif' => 'avif',
    ];
    if (!isset($extMap[$mime])) {
        json_response(['error' => 'Unsupported image type (use JPG, PNG, WebP, GIF, or AVIF)'], 400);
    }

    $dir = upload_dir_path();
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $stamp = base_convert((string) time(), 10, 36);
    $rand = bin2hex(random_bytes(6));
    $filename = "shizuku-{$stamp}-{$rand}.{$extMap[$mime]}";
    $dest = $dir . '/' . $filename;

    if (!move_uploaded_file($tmpPath, $dest)) {
        json_response(['error' => 'Failed to save uploaded image'], 500);
    }

    json_response([
        'ok' => true,
        'url' => '/api/uploads/' . $filename,
        'filename' => $filename,
        'size' => $size,
    ], 201);
}
