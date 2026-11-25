<?php
declare(strict_types=1);

function handle_ecosystem_register(): never
{
    $body = read_json_body();
    $email = strtolower(trim((string) ($body['email'] ?? '')));
    $password = (string) ($body['password'] ?? '');
    $displayName = trim((string) ($body['displayName'] ?? ''));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['error' => 'INVALID_EMAIL'], 400, true);
    }
    if (strlen($password) < 6 || strlen($password) > 128) {
        json_response(['error' => 'SHORT_PASSWORD'], 400, true);
    }

    $pdo = db();
    $check = $pdo->prepare('SELECT "id" FROM "EcosystemUser" WHERE "email" = ? LIMIT 1');
    $check->execute([$email]);
    if ($check->fetch()) {
        json_response(['error' => 'EMAIL_EXISTS'], 409, true);
    }

    $fallbackName = explode('@', $email)[0] ?: 'SHIZUKU User';
    $finalName = mb_substr($displayName !== '' ? $displayName : $fallbackName, 0, 60);
    $id = make_cuid('eco_');
    $now = now_iso();
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 11]);

    $ins = $pdo->prepare(
        'INSERT INTO "EcosystemUser" ("id", "email", "displayName", "passwordHash", "createdAt", "updatedAt")
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $ins->execute([$id, $email, $finalName, $hash, $now, $now]);

    $user = [
        'id' => $id,
        'email' => $email,
        'displayName' => $finalName,
        'createdAt' => $now,
    ];
    $token = create_ecosystem_token($user);
    set_ecosystem_cookie($token);

    json_response(['user' => $user, 'token' => $token], 201, true);
}

function handle_ecosystem_login(): never
{
    $body = read_json_body();
    $email = strtolower(trim((string) ($body['email'] ?? '')));
    $password = (string) ($body['password'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        json_response(['error' => 'INVALID_CREDENTIALS'], 400, true);
    }

    $stmt = db()->prepare('SELECT "id", "email", "displayName", "passwordHash", "createdAt" FROM "EcosystemUser" WHERE "email" = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if (!$row) {
        json_response(['error' => 'ACCOUNT_NOT_FOUND'], 401, true);
    }

    $hash = (string) $row['passwordHash'];
    if (str_starts_with($hash, '$oauth$') || !password_verify($password, $hash)) {
        json_response(['error' => 'INVALID_CREDENTIALS'], 401, true);
    }

    $user = [
        'id' => (string) $row['id'],
        'email' => (string) $row['email'],
        'displayName' => (string) $row['displayName'],
        'createdAt' => format_iso_date((string) $row['createdAt']),
    ];
    $token = create_ecosystem_token($user);
    set_ecosystem_cookie($token);

    json_response(['user' => $user, 'token' => $token], 200, true);
}

function handle_ecosystem_logout(): never
{
    clear_ecosystem_cookie();
    json_response(['ok' => true], 200, true);
}

function handle_ecosystem_me(): never
{
    $user = get_ecosystem_user();
    json_response(['user' => $user], 200, true);
}
