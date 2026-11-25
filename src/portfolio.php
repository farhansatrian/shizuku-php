<?php
declare(strict_types=1);

function serialize_profile(array $row): array
{
    return [
        'id' => (string) $row['id'],
        'brandName' => (string) $row['brandName'],
        'brandKanji' => (string) $row['brandKanji'],
        'fullName' => (string) $row['fullName'],
        'headlineEn' => (string) $row['headlineEn'],
        'headlineId' => (string) $row['headlineId'],
        'roles' => safe_json_decode((string) $row['rolesJson'], ['en' => [], 'id' => []]),
        'bioEn' => (string) $row['bioEn'],
        'bioId' => (string) $row['bioId'],
        'aboutStoryEn' => (string) $row['aboutStoryEn'],
        'aboutStoryId' => (string) $row['aboutStoryId'],
        'location' => (string) $row['location'],
        'currentRoleEn' => (string) $row['currentRoleEn'],
        'currentRoleId' => (string) $row['currentRoleId'],
        'currentOrg' => (string) $row['currentOrg'],
        'availabilityEn' => (string) $row['availabilityEn'],
        'availabilityId' => (string) $row['availabilityId'],
        'currentlyEn' => (string) $row['currentlyEn'],
        'currentlyId' => (string) $row['currentlyId'],
        'heroImage' => (string) $row['heroImage'],
        'heroImageMobile' => (string) $row['heroImageMobile'],
        'avatarImage' => (string) $row['avatarImage'],
        'email' => (string) $row['email'],
        'linkedinUrl' => (string) $row['linkedinUrl'],
        'githubUrl' => (string) $row['githubUrl'],
        'instagramUrl' => (string) $row['instagramUrl'],
        'youtubeUrl' => (string) $row['youtubeUrl'],
        'resumeUrl' => (string) $row['resumeUrl'],
        'stats' => safe_json_decode((string) $row['statsJson'], []),
        'pillars' => safe_json_decode((string) $row['pillarsJson'], []),
        'updatedAt' => format_iso_date((string) $row['updatedAt']),
    ];
}

function serialize_workspace_app(array $row): array
{
    return [
        'id' => (string) $row['id'],
        'name' => (string) $row['name'],
        'category' => (string) $row['category'],
        'descriptionEn' => (string) $row['descriptionEn'],
        'descriptionId' => (string) $row['descriptionId'],
        'url' => (string) $row['url'],
        'icon' => (string) $row['icon'],
        'badge' => (string) $row['badge'],
        'isExternal' => (bool) $row['isExternal'],
        'published' => (bool) $row['published'],
        'sortOrder' => (int) $row['sortOrder'],
        'createdAt' => format_iso_date((string) $row['createdAt']),
        'updatedAt' => format_iso_date((string) $row['updatedAt']),
    ];
}

function serialize_experience(array $row): array
{
    $bullets = safe_json_decode((string) $row['bulletsJson'], ['en' => [], 'id' => []]);
    if (!is_array($bullets)) {
        $bullets = ['en' => [], 'id' => []];
    }
    return [
        'id' => (string) $row['id'],
        'kind' => (string) $row['kind'],
        'organization' => (string) $row['organization'],
        'roleEn' => (string) $row['roleEn'],
        'roleId' => (string) $row['roleId'],
        'location' => (string) $row['location'],
        'periodEn' => (string) $row['periodEn'],
        'periodId' => (string) $row['periodId'],
        'isCurrent' => (bool) $row['isCurrent'],
        'summaryEn' => (string) $row['summaryEn'],
        'summaryId' => (string) $row['summaryId'],
        'bullets' => [
            'en' => array_values(is_array($bullets['en'] ?? null) ? $bullets['en'] : []),
            'id' => array_values(is_array($bullets['id'] ?? null) ? $bullets['id'] : []),
        ],
        'tags' => array_values(is_array(safe_json_decode((string) $row['tagsJson'], [])) ? safe_json_decode((string) $row['tagsJson'], []) : []),
        'published' => (bool) $row['published'],
        'sortOrder' => (int) $row['sortOrder'],
        'createdAt' => format_iso_date((string) $row['createdAt']),
        'updatedAt' => format_iso_date((string) $row['updatedAt']),
    ];
}

function serialize_certificate(array $row): array
{
    return [
        'id' => (string) $row['id'],
        'titleEn' => (string) $row['titleEn'],
        'titleId' => (string) $row['titleId'],
        'issuer' => (string) $row['issuer'],
        'category' => (string) $row['category'],
        'issuedDate' => (string) $row['issuedDate'],
        'credentialId' => (string) $row['credentialId'],
        'credentialUrl' => (string) $row['credentialUrl'],
        'imageUrl' => (string) $row['imageUrl'],
        'featured' => (bool) $row['featured'],
        'published' => (bool) $row['published'],
        'sortOrder' => (int) $row['sortOrder'],
        'createdAt' => format_iso_date((string) $row['createdAt']),
        'updatedAt' => format_iso_date((string) $row['updatedAt']),
    ];
}

function get_portfolio_data(bool $onlyPublished = true): array
{
    $pdo = db();

    $profileRow = $pdo->query('SELECT * FROM "SiteProfile" WHERE "id" = \'main\' LIMIT 1')->fetch();
    $wherePub = $onlyPublished ? 'WHERE "published" = 1' : '';

    $apps = $pdo->query("SELECT * FROM \"WorkspaceApp\" $wherePub ORDER BY \"sortOrder\" ASC, \"createdAt\" ASC")->fetchAll();
    $exps = $pdo->query("SELECT * FROM \"Experience\" $wherePub ORDER BY \"sortOrder\" ASC, \"createdAt\" DESC")->fetchAll();
    $certs = $pdo->query("SELECT * FROM \"Certificate\" $wherePub ORDER BY \"sortOrder\" ASC, \"createdAt\" DESC")->fetchAll();

    return [
        'profile' => $profileRow ? serialize_profile($profileRow) : [],
        'workspaceApps' => array_map('serialize_workspace_app', $apps),
        'experiences' => array_map('serialize_experience', $exps),
        'certificates' => array_map('serialize_certificate', $certs),
    ];
}

function group_experiences_by_org(array $items): array
{
    $groups = [];
    foreach ($items as $item) {
        $lastIdx = count($groups) - 1;
        if ($lastIdx >= 0 && $groups[$lastIdx]['organization'] === $item['organization']) {
            $groups[$lastIdx]['entries'][] = $item;
        } else {
            $groups[] = [
                'organization' => $item['organization'],
                'entries' => [$item],
            ];
        }
    }
    return $groups;
}
