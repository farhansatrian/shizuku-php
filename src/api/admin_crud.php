<?php
declare(strict_types=1);

function handle_admin_profile_put(): never
{
    require_admin_api();
    $b = read_json_body();
    $fullName = trim((string) ($b['fullName'] ?? ''));
    $brandName = trim((string) ($b['brandName'] ?? 'shizuku'));
    $bioEn = trim((string) ($b['bioEn'] ?? ''));
    $aboutStoryEn = trim((string) ($b['aboutStoryEn'] ?? ''));

    if ($fullName === '' || $brandName === '' || $bioEn === '' || $aboutStoryEn === '') {
        json_response(['error' => 'Please fill in Full Name, Brand, Bio (EN), and About Story (EN)'], 400);
    }

    $pdo = db();
    $now = now_iso();
    $stmt = $pdo->prepare(
        'UPDATE "SiteProfile" SET
            "brandName"=?, "brandKanji"=?, "fullName"=?, "headlineEn"=?, "headlineId"=?,
            "bioEn"=?, "bioId"=?, "aboutStoryEn"=?, "aboutStoryId"=?, "location"=?,
            "currentRoleEn"=?, "currentRoleId"=?, "currentOrg"=?,
            "heroImage"=?, "heroImageMobile"=?, "avatarImage"=?,
            "email"=?, "linkedinUrl"=?, "githubUrl"=?, "instagramUrl"=?, "youtubeUrl"=?, "resumeUrl"=?,
            "updatedAt"=?
         WHERE "id" = \'main\''
    );
    $stmt->execute([
        $brandName,
        trim((string) ($b['brandKanji'] ?? '雫')),
        $fullName,
        trim((string) ($b['headlineEn'] ?? substr($bioEn, 0, 200))),
        trim((string) ($b['headlineId'] ?? ($b['bioId'] ?? $bioEn))),
        $bioEn,
        trim((string) ($b['bioId'] ?? '')) ?: $bioEn,
        $aboutStoryEn,
        trim((string) ($b['aboutStoryId'] ?? '')) ?: $aboutStoryEn,
        trim((string) ($b['location'] ?? 'Jakarta, Indonesia')),
        trim((string) ($b['currentRoleEn'] ?? '')),
        trim((string) ($b['currentRoleId'] ?? '')) ?: trim((string) ($b['currentRoleEn'] ?? '')),
        trim((string) ($b['currentOrg'] ?? '')),
        trim((string) ($b['heroImage'] ?? '')),
        trim((string) ($b['heroImageMobile'] ?? '')),
        trim((string) ($b['avatarImage'] ?? '')),
        trim((string) ($b['email'] ?? '')),
        trim((string) ($b['linkedinUrl'] ?? '')),
        trim((string) ($b['githubUrl'] ?? '')),
        trim((string) ($b['instagramUrl'] ?? '')),
        trim((string) ($b['youtubeUrl'] ?? '')),
        trim((string) ($b['resumeUrl'] ?? '')),
        $now,
    ]);

    $row = $pdo->query('SELECT * FROM "SiteProfile" WHERE "id" = \'main\' LIMIT 1')->fetch();
    json_response(['ok' => true, 'profile' => serialize_profile($row)]);
}

function handle_admin_workspace_post(): never
{
    require_admin_api();
    $b = read_json_body();
    $name = trim((string) ($b['name'] ?? ''));
    $url = trim((string) ($b['url'] ?? ''));
    $descEn = trim((string) ($b['descriptionEn'] ?? ''));
    if ($name === '' || $url === '' || $descEn === '') {
        json_response(['error' => 'Name, URL, and Description (EN) are required'], 400);
    }

    $id = make_cuid('app_');
    $now = now_iso();
    $pdo = db();
    $stmt = $pdo->prepare(
        'INSERT INTO "WorkspaceApp" ("id","name","category","descriptionEn","descriptionId","url","icon","badge","isExternal","published","sortOrder","createdAt","updatedAt")
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
    );
    $stmt->execute([
        $id,
        $name,
        in_array((string) ($b['category'] ?? 'APP'), ['INTERNAL', 'APP', 'AGENT', 'BOOKMARK'], true) ? (string) $b['category'] : 'APP',
        $descEn,
        trim((string) ($b['descriptionId'] ?? '')) ?: $descEn,
        $url,
        trim((string) ($b['icon'] ?? 'globe')) ?: 'globe',
        strtoupper(trim((string) ($b['badge'] ?? ''))),
        !empty($b['isExternal']) ? 1 : 0,
        array_key_exists('published', $b) ? (!empty($b['published']) ? 1 : 0) : 1,
        (int) ($b['sortOrder'] ?? 0),
        $now,
        $now,
    ]);

    $sel = $pdo->prepare('SELECT * FROM "WorkspaceApp" WHERE "id" = ?');
    $sel->execute([$id]);
    json_response(['ok' => true, 'workspaceApp' => serialize_workspace_app($sel->fetch())], 201);
}

function handle_admin_workspace_item(string $id, string $method): never
{
    require_admin_api();
    $pdo = db();
    $sel = $pdo->prepare('SELECT * FROM "WorkspaceApp" WHERE "id" = ?');
    $sel->execute([$id]);
    $existing = $sel->fetch();
    if (!$existing) {
        json_response(['error' => 'Not found'], 404);
    }

    if ($method === 'DELETE') {
        $pdo->prepare('DELETE FROM "WorkspaceApp" WHERE "id" = ?')->execute([$id]);
        json_response(['ok' => true]);
    }

    $b = read_json_body();
    $descEn = array_key_exists('descriptionEn', $b) ? trim((string) $b['descriptionEn']) : (string) $existing['descriptionEn'];
    $descId = array_key_exists('descriptionId', $b) ? (trim((string) $b['descriptionId']) ?: $descEn) : (string) $existing['descriptionId'];

    $upd = $pdo->prepare(
        'UPDATE "WorkspaceApp" SET "name"=?, "category"=?, "descriptionEn"=?, "descriptionId"=?, "url"=?, "icon"=?, "badge"=?, "isExternal"=?, "published"=?, "sortOrder"=?, "updatedAt"=?
         WHERE "id"=?'
    );
    $upd->execute([
        array_key_exists('name', $b) ? trim((string) $b['name']) : (string) $existing['name'],
        array_key_exists('category', $b) ? (string) $b['category'] : (string) $existing['category'],
        $descEn,
        $descId,
        array_key_exists('url', $b) ? trim((string) $b['url']) : (string) $existing['url'],
        array_key_exists('icon', $b) ? trim((string) $b['icon']) : (string) $existing['icon'],
        array_key_exists('badge', $b) ? strtoupper(trim((string) $b['badge'])) : (string) $existing['badge'],
        array_key_exists('isExternal', $b) ? (!empty($b['isExternal']) ? 1 : 0) : (int) $existing['isExternal'],
        array_key_exists('published', $b) ? (!empty($b['published']) ? 1 : 0) : (int) $existing['published'],
        array_key_exists('sortOrder', $b) ? (int) $b['sortOrder'] : (int) $existing['sortOrder'],
        now_iso(),
        $id,
    ]);

    $sel->execute([$id]);
    json_response(['ok' => true, 'workspaceApp' => serialize_workspace_app($sel->fetch())]);
}

function handle_admin_experience_post(): never
{
    require_admin_api();
    $b = read_json_body();
    $org = trim((string) ($b['organization'] ?? ''));
    $roleEn = trim((string) ($b['roleEn'] ?? ''));
    $periodEn = trim((string) ($b['periodEn'] ?? ''));
    if ($org === '' || $roleEn === '' || $periodEn === '') {
        json_response(['error' => 'Organization, Role/Degree (EN), and Period (EN) are required'], 400);
    }

    $kind = in_array((string) ($b['kind'] ?? 'WORK'), ['EDUCATION', 'WORK', 'ORGANIZATION'], true)
        ? (string) $b['kind']
        : 'WORK';
    $bullets = is_array($b['bullets'] ?? null) ? $b['bullets'] : ['en' => [], 'id' => []];
    $summaryEn = trim((string) ($b['summaryEn'] ?? ''));
    $id = make_cuid('exp_');
    $now = now_iso();
    $pdo = db();

    $stmt = $pdo->prepare(
        'INSERT INTO "Experience" ("id","kind","organization","roleEn","roleId","location","periodEn","periodId","isCurrent","summaryEn","summaryId","bulletsJson","tagsJson","published","sortOrder","createdAt","updatedAt")
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    );
    $stmt->execute([
        $id,
        $kind,
        $org,
        $roleEn,
        trim((string) ($b['roleId'] ?? '')) ?: $roleEn,
        trim((string) ($b['location'] ?? 'Indonesia')) ?: 'Indonesia',
        $periodEn,
        trim((string) ($b['periodId'] ?? '')) ?: $periodEn,
        !empty($b['isCurrent']) ? 1 : 0,
        $summaryEn,
        trim((string) ($b['summaryId'] ?? '')) ?: $summaryEn,
        json_encode([
            'en' => array_values(is_array($bullets['en'] ?? null) ? $bullets['en'] : []),
            'id' => array_values(is_array($bullets['id'] ?? null) ? $bullets['id'] : []),
        ], JSON_UNESCAPED_UNICODE),
        '[]',
        array_key_exists('published', $b) ? (!empty($b['published']) ? 1 : 0) : 1,
        (int) ($b['sortOrder'] ?? 0),
        $now,
        $now,
    ]);

    $sel = $pdo->prepare('SELECT * FROM "Experience" WHERE "id" = ?');
    $sel->execute([$id]);
    json_response(['ok' => true, 'experience' => serialize_experience($sel->fetch())], 201);
}

function handle_admin_experience_item(string $id, string $method): never
{
    require_admin_api();
    $pdo = db();
    $sel = $pdo->prepare('SELECT * FROM "Experience" WHERE "id" = ?');
    $sel->execute([$id]);
    $existing = $sel->fetch();
    if (!$existing) {
        json_response(['error' => 'Not found'], 404);
    }

    if ($method === 'DELETE') {
        $pdo->prepare('DELETE FROM "Experience" WHERE "id" = ?')->execute([$id]);
        json_response(['ok' => true]);
    }

    $b = read_json_body();
    $roleEn = array_key_exists('roleEn', $b) ? trim((string) $b['roleEn']) : (string) $existing['roleEn'];
    $periodEn = array_key_exists('periodEn', $b) ? trim((string) $b['periodEn']) : (string) $existing['periodEn'];
    $summaryEn = array_key_exists('summaryEn', $b) ? trim((string) $b['summaryEn']) : (string) $existing['summaryEn'];
    $bulletsJson = array_key_exists('bullets', $b) && is_array($b['bullets'])
        ? json_encode([
            'en' => array_values(is_array($b['bullets']['en'] ?? null) ? $b['bullets']['en'] : []),
            'id' => array_values(is_array($b['bullets']['id'] ?? null) ? $b['bullets']['id'] : []),
        ], JSON_UNESCAPED_UNICODE)
        : (string) $existing['bulletsJson'];

    $upd = $pdo->prepare(
        'UPDATE "Experience" SET "kind"=?, "organization"=?, "roleEn"=?, "roleId"=?, "location"=?, "periodEn"=?, "periodId"=?, "isCurrent"=?, "summaryEn"=?, "summaryId"=?, "bulletsJson"=?, "published"=?, "sortOrder"=?, "updatedAt"=?
         WHERE "id"=?'
    );
    $upd->execute([
        array_key_exists('kind', $b) && in_array((string) $b['kind'], ['EDUCATION', 'WORK', 'ORGANIZATION'], true) ? (string) $b['kind'] : (string) $existing['kind'],
        array_key_exists('organization', $b) ? trim((string) $b['organization']) : (string) $existing['organization'],
        $roleEn,
        array_key_exists('roleId', $b) ? (trim((string) $b['roleId']) ?: $roleEn) : (string) $existing['roleId'],
        array_key_exists('location', $b) ? trim((string) $b['location']) : (string) $existing['location'],
        $periodEn,
        array_key_exists('periodId', $b) ? (trim((string) $b['periodId']) ?: $periodEn) : (string) $existing['periodId'],
        array_key_exists('isCurrent', $b) ? (!empty($b['isCurrent']) ? 1 : 0) : (int) $existing['isCurrent'],
        $summaryEn,
        array_key_exists('summaryId', $b) ? (trim((string) $b['summaryId']) ?: $summaryEn) : (string) $existing['summaryId'],
        $bulletsJson,
        array_key_exists('published', $b) ? (!empty($b['published']) ? 1 : 0) : (int) $existing['published'],
        array_key_exists('sortOrder', $b) ? (int) $b['sortOrder'] : (int) $existing['sortOrder'],
        now_iso(),
        $id,
    ]);

    $sel->execute([$id]);
    json_response(['ok' => true, 'experience' => serialize_experience($sel->fetch())]);
}

function handle_admin_certificate_post(): never
{
    require_admin_api();
    $b = read_json_body();
    $titleEn = trim((string) ($b['titleEn'] ?? ''));
    $issuer = trim((string) ($b['issuer'] ?? ''));
    $issuedDate = trim((string) ($b['issuedDate'] ?? ''));
    if ($titleEn === '' || $issuer === '' || $issuedDate === '') {
        json_response(['error' => 'Title, Issuer, and Issued Date are required'], 400);
    }

    $id = make_cuid('crt_');
    $now = now_iso();
    $pdo = db();
    $stmt = $pdo->prepare(
        'INSERT INTO "Certificate" ("id","titleEn","titleId","issuer","category","issuedDate","credentialId","credentialUrl","imageUrl","featured","published","sortOrder","createdAt","updatedAt")
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    );
    $stmt->execute([
        $id,
        $titleEn,
        trim((string) ($b['titleId'] ?? '')) ?: $titleEn,
        $issuer,
        trim((string) ($b['category'] ?? 'SOFTWARE ENG')) ?: 'SOFTWARE ENG',
        $issuedDate,
        trim((string) ($b['credentialId'] ?? '')),
        trim((string) ($b['credentialUrl'] ?? '')),
        trim((string) ($b['imageUrl'] ?? '')),
        !empty($b['featured']) ? 1 : 0,
        array_key_exists('published', $b) ? (!empty($b['published']) ? 1 : 0) : 1,
        (int) ($b['sortOrder'] ?? 0),
        $now,
        $now,
    ]);

    $sel = $pdo->prepare('SELECT * FROM "Certificate" WHERE "id" = ?');
    $sel->execute([$id]);
    json_response(['ok' => true, 'certificate' => serialize_certificate($sel->fetch())], 201);
}

function handle_admin_certificate_item(string $id, string $method): never
{
    require_admin_api();
    $pdo = db();
    $sel = $pdo->prepare('SELECT * FROM "Certificate" WHERE "id" = ?');
    $sel->execute([$id]);
    $existing = $sel->fetch();
    if (!$existing) {
        json_response(['error' => 'Not found'], 404);
    }

    if ($method === 'DELETE') {
        $pdo->prepare('DELETE FROM "Certificate" WHERE "id" = ?')->execute([$id]);
        json_response(['ok' => true]);
    }

    $b = read_json_body();
    $titleEn = array_key_exists('titleEn', $b) ? trim((string) $b['titleEn']) : (string) $existing['titleEn'];
    $upd = $pdo->prepare(
        'UPDATE "Certificate" SET "titleEn"=?, "titleId"=?, "issuer"=?, "category"=?, "issuedDate"=?, "credentialId"=?, "credentialUrl"=?, "imageUrl"=?, "featured"=?, "published"=?, "sortOrder"=?, "updatedAt"=?
         WHERE "id"=?'
    );
    $upd->execute([
        $titleEn,
        array_key_exists('titleId', $b) ? (trim((string) $b['titleId']) ?: $titleEn) : (string) $existing['titleId'],
        array_key_exists('issuer', $b) ? trim((string) $b['issuer']) : (string) $existing['issuer'],
        array_key_exists('category', $b) ? trim((string) $b['category']) : (string) $existing['category'],
        array_key_exists('issuedDate', $b) ? trim((string) $b['issuedDate']) : (string) $existing['issuedDate'],
        array_key_exists('credentialId', $b) ? trim((string) $b['credentialId']) : (string) $existing['credentialId'],
        array_key_exists('credentialUrl', $b) ? trim((string) $b['credentialUrl']) : (string) $existing['credentialUrl'],
        array_key_exists('imageUrl', $b) ? trim((string) $b['imageUrl']) : (string) $existing['imageUrl'],
        array_key_exists('featured', $b) ? (!empty($b['featured']) ? 1 : 0) : (int) $existing['featured'],
        array_key_exists('published', $b) ? (!empty($b['published']) ? 1 : 0) : (int) $existing['published'],
        array_key_exists('sortOrder', $b) ? (int) $b['sortOrder'] : (int) $existing['sortOrder'],
        now_iso(),
        $id,
    ]);

    $sel->execute([$id]);
    json_response(['ok' => true, 'certificate' => serialize_certificate($sel->fetch())]);
}

function handle_admin_password_post(): never
{
    $session = require_admin_api();
    $b = read_json_body();
    $current = (string) ($b['currentPassword'] ?? '');
    $next = (string) ($b['newPassword'] ?? '');

    if (strlen($next) < 8) {
        json_response(['error' => 'New password must be at least 8 characters'], 400);
    }

    $pdo = db();
    $stmt = $pdo->prepare('SELECT "id","passwordHash" FROM "AdminUser" WHERE "id" = ? LIMIT 1');
    $stmt->execute([$session['userId']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current, (string) $user['passwordHash'])) {
        json_response(['error' => 'Current password is incorrect'], 401);
    }

    $hash = password_hash($next, PASSWORD_BCRYPT, ['cost' => 11]);
    $pdo->prepare('UPDATE "AdminUser" SET "passwordHash" = ?, "updatedAt" = ? WHERE "id" = ?')
        ->execute([$hash, now_iso(), $user['id']]);

    json_response(['ok' => true]);
}
