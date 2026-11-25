<?php
declare(strict_types=1);

function handle_admin_login(): never
{
    assert_same_origin_request();
    $body = read_json_body();
    $identifier = trim((string) ($body['identifier'] ?? ''));
    $password = (string) ($body['password'] ?? '');

    if ($identifier === '' || strlen($password) < 4) {
        json_response(['error' => 'Invalid credentials format'], 400);
    }

    $stmt = db()->prepare(
        'SELECT "id","username","email","passwordHash","displayName"
         FROM "AdminUser"
         WHERE lower("username") = lower(?) OR lower("email") = lower(?)
         LIMIT 1'
    );
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, (string) $user['passwordHash'])) {
        json_response(['error' => 'Invalid username or password'], 401);
    }

    $payload = [
        'userId' => (string) $user['id'],
        'username' => (string) $user['username'],
        'displayName' => (string) $user['displayName'],
    ];
    $token = create_admin_token($payload);
    set_admin_cookie($token);

    json_response([
        'ok' => true,
        'user' => [
            'id' => $payload['userId'],
            'username' => $payload['username'],
            'displayName' => $payload['displayName'],
        ],
    ]);
}

function handle_admin_logout(): never
{
    assert_same_origin_request();
    clear_admin_cookie();
    json_response(['ok' => true]);
}

function handle_admin_session(): never
{
    $session = get_admin_session();
    if (!$session) {
        json_response(['authenticated' => false, 'user' => null]);
    }
    json_response(['authenticated' => true, 'user' => $session]);
}
