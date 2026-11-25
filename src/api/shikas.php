<?php
declare(strict_types=1);

function sanitize_shikas_accounts(mixed $raw): array
{
    if (!is_array($raw)) {
        return [];
    }
    $clean = [];
    foreach (array_slice($raw, 0, 30) as $acc) {
        if (!is_array($acc)) {
            continue;
        }
        $id = trim((string) ($acc['id'] ?? ''));
        $name = trim((string) ($acc['name'] ?? ''));
        if ($id === '' || strlen($id) > 80 || $name === '' || strlen($name) > 60) {
            continue;
        }
        $role = ($acc['role'] ?? 'daily') === 'savings' ? 'savings' : 'daily';
        $opening = max(0, (int) round((float) ($acc['openingBalanceIdr'] ?? 0)));
        $color = trim((string) ($acc['color'] ?? '#745bc7'));
        if (strlen($color) < 3 || strlen($color) > 32) {
            $color = '#745bc7';
        }
        $entry = [
            'id' => $id,
            'name' => $name,
            'role' => $role,
            'openingBalanceIdr' => $opening,
            'color' => $color,
        ];
        if (!empty($acc['isDefault'])) {
            $entry['isDefault'] = true;
        }
        $clean[] = $entry;
    }
    return $clean;
}

function handle_shikas_store_get(): never
{
    $user = get_ecosystem_user();
    if (!$user) {
        json_response(['error' => 'UNAUTHORIZED'], 401, true);
    }

    $pdo = db();
    $txStmt = $pdo->prepare(
        'SELECT * FROM "ShikasTransaction" WHERE "userId" = ? ORDER BY "date" DESC, "createdAt" DESC'
    );
    $txStmt->execute([$user['id']]);
    $rows = $txStmt->fetchAll();

    $setStmt = $pdo->prepare('SELECT * FROM "ShikasSettings" WHERE "userId" = ? LIMIT 1');
    $setStmt->execute([$user['id']]);
    $settings = $setStmt->fetch();

    $accounts = sanitize_shikas_accounts(safe_json_decode($settings['accountsJson'] ?? '[]', []));

    $transactions = [];
    foreach ($rows as $r) {
        $type = match ((string) $r['type']) {
            'income' => 'income',
            'transfer' => 'transfer',
            default => 'expense',
        };
        $item = [
            'id' => (string) $r['id'],
            'type' => $type,
            'date' => (string) $r['date'],
            'amountIdr' => (int) $r['amountIdr'],
            'categoryId' => (string) $r['categoryId'],
            'note' => (string) $r['note'],
            'createdAt' => (string) $r['createdAt'],
        ];
        if ($r['merchant'] !== null && $r['merchant'] !== '') {
            $item['merchant'] = (string) $r['merchant'];
        }
        if ($r['accountId'] !== null && $r['accountId'] !== '') {
            $item['accountId'] = (string) $r['accountId'];
        }
        if ($r['toAccountId'] !== null && $r['toAccountId'] !== '') {
            $item['toAccountId'] = (string) $r['toAccountId'];
        }
        if ((int) ($r['feeIdr'] ?? 0) > 0) {
            $item['feeIdr'] = (int) $r['feeIdr'];
        }
        $transactions[] = $item;
    }

    $cycleStartDay = (int) ($settings['cycleStartDay'] ?? 1);
    $cycleStartDay = max(1, min(28, $cycleStartDay));

    json_response([
        'version' => 5,
        'accounts' => $accounts,
        'transactions' => $transactions,
        'settings' => [
            'locale' => (($settings['locale'] ?? 'en') === 'id') ? 'id' : 'en',
            'privacyMode' => (bool) ($settings['privacyMode'] ?? false),
            'rainEnabled' => $settings ? (bool) $settings['rainEnabled'] : true,
            'cycleStartDay' => $cycleStartDay,
            'shizukuWebsiteUrl' => '',
        ],
    ], 200, true);
}

function handle_shikas_store_put(): never
{
    $user = get_ecosystem_user();
    if (!$user) {
        json_response(['error' => 'UNAUTHORIZED'], 401, true);
    }

    $body = read_json_body();
    if (!isset($body['transactions']) || !is_array($body['transactions']) || count($body['transactions']) > 5000) {
        json_response(['error' => 'INVALID_PAYLOAD'], 400, true);
    }

    $validTxs = [];
    foreach ($body['transactions'] as $tx) {
        if (!is_array($tx)) {
            json_response(['error' => 'INVALID_PAYLOAD'], 400, true);
        }
        $id = trim((string) ($tx['id'] ?? ''));
        $type = (string) ($tx['type'] ?? '');
        $date = (string) ($tx['date'] ?? '');
        $amount = (int) round((float) ($tx['amountIdr'] ?? 0));
        $categoryId = trim((string) ($tx['categoryId'] ?? ''));
        $note = (string) ($tx['note'] ?? '');
        $createdAt = trim((string) ($tx['createdAt'] ?? ''));

        if (
            $id === '' || strlen($id) > 80 ||
            !in_array($type, ['income', 'expense', 'transfer'], true) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
            $amount <= 0 ||
            $categoryId === '' || strlen($categoryId) > 60 ||
            mb_strlen($note) > 240 ||
            $createdAt === '' || strlen($createdAt) > 64
        ) {
            json_response(['error' => 'INVALID_PAYLOAD'], 400, true);
        }

        $merchant = isset($tx['merchant']) && trim((string) $tx['merchant']) !== ''
            ? mb_substr(trim((string) $tx['merchant']), 0, 120)
            : null;
        $accountId = isset($tx['accountId']) && trim((string) $tx['accountId']) !== ''
            ? substr(trim((string) $tx['accountId']), 0, 80)
            : null;
        $toAccountId = isset($tx['toAccountId']) && trim((string) $tx['toAccountId']) !== ''
            ? substr(trim((string) $tx['toAccountId']), 0, 80)
            : null;
        $feeIdr = max(0, (int) round((float) ($tx['feeIdr'] ?? 0)));

        $validTxs[] = [
            $id,
            $user['id'],
            $type,
            $date,
            $amount,
            $categoryId,
            $note,
            $merchant,
            $accountId,
            $toAccountId,
            $feeIdr,
            $createdAt,
        ];
    }

    $hasAccounts = array_key_exists('accounts', $body);
    $accounts = $hasAccounts ? sanitize_shikas_accounts($body['accounts']) : null;
    $hasSettings = isset($body['settings']) && is_array($body['settings']);
    $settings = $hasSettings ? $body['settings'] : null;

    $pdo = db();
    try {
        $pdo->beginTransaction();

        $del = $pdo->prepare('DELETE FROM "ShikasTransaction" WHERE "userId" = ?');
        $del->execute([$user['id']]);

        if (count($validTxs) > 0) {
            $ins = $pdo->prepare(
                'INSERT INTO "ShikasTransaction"
                 ("id","userId","type","date","amountIdr","categoryId","note","merchant","accountId","toAccountId","feeIdr","createdAt")
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
            );
            foreach ($validTxs as $row) {
                $ins->execute($row);
            }
        }

        if ($settings !== null || $accounts !== null) {
            $existingStmt = $pdo->prepare('SELECT * FROM "ShikasSettings" WHERE "userId" = ? LIMIT 1');
            $existingStmt->execute([$user['id']]);
            $existing = $existingStmt->fetch();

            $locale = $settings !== null
                ? (($settings['locale'] ?? 'en') === 'id' ? 'id' : 'en')
                : (($existing['locale'] ?? 'en') === 'id' ? 'id' : 'en');
            $privacyMode = $settings !== null
                ? (!empty($settings['privacyMode']) ? 1 : 0)
                : (!empty($existing['privacyMode']) ? 1 : 0);
            $rainEnabled = $settings !== null
                ? (array_key_exists('rainEnabled', $settings) ? (!empty($settings['rainEnabled']) ? 1 : 0) : 1)
                : ($existing ? (!empty($existing['rainEnabled']) ? 1 : 0) : 1);
            $cycleStartDay = $settings !== null && isset($settings['cycleStartDay'])
                ? max(1, min(28, (int) $settings['cycleStartDay']))
                : max(1, min(28, (int) ($existing['cycleStartDay'] ?? 1)));
            $accountsJson = $accounts !== null
                ? json_encode($accounts, JSON_UNESCAPED_UNICODE)
                : (string) ($existing['accountsJson'] ?? '[]');
            $now = now_iso();

            if ($existing) {
                $upd = $pdo->prepare(
                    'UPDATE "ShikasSettings"
                     SET "locale" = ?, "privacyMode" = ?, "rainEnabled" = ?, "cycleStartDay" = ?, "accountsJson" = ?, "updatedAt" = ?
                     WHERE "userId" = ?'
                );
                $upd->execute([$locale, $privacyMode, $rainEnabled, $cycleStartDay, $accountsJson, $now, $user['id']]);
            } else {
                $insSet = $pdo->prepare(
                    'INSERT INTO "ShikasSettings" ("userId","locale","privacyMode","rainEnabled","cycleStartDay","accountsJson","updatedAt")
                     VALUES (?,?,?,?,?,?,?)'
                );
                $insSet->execute([$user['id'], $locale, $privacyMode, $rainEnabled, $cycleStartDay, $accountsJson, $now]);
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        json_response(['error' => 'SAVE_FAILED'], 500, true);
    }

    json_response([
        'ok' => true,
        'count' => count($validTxs),
        'accountsCount' => $accounts !== null ? count($accounts) : 0,
    ], 200, true);
}
