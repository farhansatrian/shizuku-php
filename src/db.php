<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $path = db_file_path();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5,
    ]);

    $pdo->exec('PRAGMA busy_timeout = 5000;');
    $pdo->exec('PRAGMA foreign_keys = ON;');

    // Use WAL when supported; gracefully fall back to DELETE on shared-hosting NFS mounts
    try {
        $pdo->exec('PRAGMA journal_mode = WAL;');
    } catch (Throwable) {
        try {
            $pdo->exec('PRAGMA journal_mode = DELETE;');
        } catch (Throwable) {
        }
    }

    shizuku_ensure_schema($pdo);
    shizuku_ensure_seed($pdo);

    return $pdo;
}

function sqlite_table_columns(PDO $pdo, string $table): array
{
    $stmt = $pdo->query("PRAGMA table_info(\"$table\")");
    $cols = [];
    foreach ($stmt->fetchAll() as $row) {
        $cols[] = (string) $row['name'];
    }
    return $cols;
}

function shizuku_ensure_schema(PDO $pdo): void
{
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS "AdminUser" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "username" TEXT NOT NULL UNIQUE,
    "email" TEXT NOT NULL UNIQUE,
    "passwordHash" TEXT NOT NULL,
    "displayName" TEXT NOT NULL,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "SiteProfile" (
    "id" TEXT NOT NULL PRIMARY KEY DEFAULT 'main',
    "brandName" TEXT NOT NULL DEFAULT 'shizuku',
    "brandKanji" TEXT NOT NULL DEFAULT '雫',
    "fullName" TEXT NOT NULL,
    "headlineEn" TEXT NOT NULL DEFAULT '',
    "headlineId" TEXT NOT NULL DEFAULT '',
    "rolesJson" TEXT NOT NULL DEFAULT '{"en":[],"id":[]}',
    "bioEn" TEXT NOT NULL DEFAULT '',
    "bioId" TEXT NOT NULL DEFAULT '',
    "aboutStoryEn" TEXT NOT NULL DEFAULT '',
    "aboutStoryId" TEXT NOT NULL DEFAULT '',
    "location" TEXT NOT NULL DEFAULT 'Jakarta, Indonesia',
    "currentRoleEn" TEXT NOT NULL DEFAULT '',
    "currentRoleId" TEXT NOT NULL DEFAULT '',
    "currentOrg" TEXT NOT NULL DEFAULT '',
    "availabilityEn" TEXT NOT NULL DEFAULT '',
    "availabilityId" TEXT NOT NULL DEFAULT '',
    "currentlyEn" TEXT NOT NULL DEFAULT '',
    "currentlyId" TEXT NOT NULL DEFAULT '',
    "heroImage" TEXT NOT NULL DEFAULT '',
    "heroImageMobile" TEXT NOT NULL DEFAULT '',
    "avatarImage" TEXT NOT NULL DEFAULT '',
    "email" TEXT NOT NULL DEFAULT '',
    "linkedinUrl" TEXT NOT NULL DEFAULT '',
    "githubUrl" TEXT NOT NULL DEFAULT '',
    "instagramUrl" TEXT NOT NULL DEFAULT '',
    "youtubeUrl" TEXT NOT NULL DEFAULT '',
    "resumeUrl" TEXT NOT NULL DEFAULT '',
    "statsJson" TEXT NOT NULL DEFAULT '[]',
    "pillarsJson" TEXT NOT NULL DEFAULT '[]',
    "updatedAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "WorkspaceApp" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "name" TEXT NOT NULL,
    "category" TEXT NOT NULL DEFAULT 'APP',
    "descriptionEn" TEXT NOT NULL,
    "descriptionId" TEXT NOT NULL,
    "url" TEXT NOT NULL,
    "icon" TEXT NOT NULL DEFAULT 'sparkles',
    "badge" TEXT NOT NULL DEFAULT '',
    "isExternal" BOOLEAN NOT NULL DEFAULT 0,
    "published" BOOLEAN NOT NULL DEFAULT 1,
    "sortOrder" INTEGER NOT NULL DEFAULT 0,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "Experience" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "kind" TEXT NOT NULL DEFAULT 'WORK',
    "organization" TEXT NOT NULL,
    "roleEn" TEXT NOT NULL,
    "roleId" TEXT NOT NULL,
    "location" TEXT NOT NULL DEFAULT 'Jakarta, ID',
    "periodEn" TEXT NOT NULL,
    "periodId" TEXT NOT NULL,
    "isCurrent" BOOLEAN NOT NULL DEFAULT 0,
    "summaryEn" TEXT NOT NULL DEFAULT '',
    "summaryId" TEXT NOT NULL DEFAULT '',
    "bulletsJson" TEXT NOT NULL DEFAULT '{"en":[],"id":[]}',
    "tagsJson" TEXT NOT NULL DEFAULT '[]',
    "published" BOOLEAN NOT NULL DEFAULT 1,
    "sortOrder" INTEGER NOT NULL DEFAULT 0,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "Certificate" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "titleEn" TEXT NOT NULL,
    "titleId" TEXT NOT NULL,
    "issuer" TEXT NOT NULL,
    "category" TEXT NOT NULL DEFAULT 'SOFTWARE ENG',
    "issuedDate" TEXT NOT NULL,
    "credentialId" TEXT NOT NULL DEFAULT '',
    "credentialUrl" TEXT NOT NULL DEFAULT '',
    "imageUrl" TEXT NOT NULL DEFAULT '',
    "featured" BOOLEAN NOT NULL DEFAULT 0,
    "published" BOOLEAN NOT NULL DEFAULT 1,
    "sortOrder" INTEGER NOT NULL DEFAULT 0,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "EcosystemUser" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "email" TEXT NOT NULL UNIQUE,
    "displayName" TEXT NOT NULL,
    "passwordHash" TEXT NOT NULL,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "ShikasTransaction" (
    "id" TEXT NOT NULL,
    "userId" TEXT NOT NULL,
    "type" TEXT NOT NULL,
    "date" TEXT NOT NULL,
    "amountIdr" INTEGER NOT NULL,
    "categoryId" TEXT NOT NULL,
    "note" TEXT NOT NULL,
    "merchant" TEXT,
    "accountId" TEXT,
    "toAccountId" TEXT,
    "feeIdr" INTEGER NOT NULL DEFAULT 0,
    "reimburseJson" TEXT,
    "createdAt" TEXT NOT NULL,
    PRIMARY KEY ("userId", "id"),
    CONSTRAINT "ShikasTransaction_userId_fkey" FOREIGN KEY ("userId") REFERENCES "EcosystemUser" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS "ShikasSettings" (
    "userId" TEXT NOT NULL PRIMARY KEY,
    "locale" TEXT NOT NULL DEFAULT 'en',
    "privacyMode" BOOLEAN NOT NULL DEFAULT 0,
    "rainEnabled" BOOLEAN NOT NULL DEFAULT 1,
    "cycleStartDay" INTEGER NOT NULL DEFAULT 1,
    "accountsJson" TEXT NOT NULL DEFAULT '[]',
    "updatedAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "ShikasSettings_userId_fkey" FOREIGN KEY ("userId") REFERENCES "EcosystemUser" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX IF NOT EXISTS "WorkspaceApp_published_sortOrder_idx" ON "WorkspaceApp"("published", "sortOrder");
CREATE INDEX IF NOT EXISTS "Experience_published_kind_sortOrder_idx" ON "Experience"("published", "kind", "sortOrder");
CREATE INDEX IF NOT EXISTS "Certificate_published_featured_sortOrder_idx" ON "Certificate"("published", "featured", "sortOrder");
CREATE INDEX IF NOT EXISTS "ShikasTransaction_userId_date_idx" ON "ShikasTransaction"("userId", "date");
SQL);

    // Safe non-destructive v5 column upgrades for existing SQLite files
    $txCols = sqlite_table_columns($pdo, 'ShikasTransaction');
    if (!in_array('accountId', $txCols, true)) {
        $pdo->exec('ALTER TABLE "ShikasTransaction" ADD COLUMN "accountId" TEXT;');
    }
    if (!in_array('toAccountId', $txCols, true)) {
        $pdo->exec('ALTER TABLE "ShikasTransaction" ADD COLUMN "toAccountId" TEXT;');
    }
    if (!in_array('feeIdr', $txCols, true)) {
        $pdo->exec('ALTER TABLE "ShikasTransaction" ADD COLUMN "feeIdr" INTEGER NOT NULL DEFAULT 0;');
    }
    if (!in_array('reimburseJson', $txCols, true)) {
        $pdo->exec('ALTER TABLE "ShikasTransaction" ADD COLUMN "reimburseJson" TEXT;');
    }

    $setCols = sqlite_table_columns($pdo, 'ShikasSettings');
    if (!in_array('cycleStartDay', $setCols, true)) {
        $pdo->exec('ALTER TABLE "ShikasSettings" ADD COLUMN "cycleStartDay" INTEGER NOT NULL DEFAULT 1;');
    }
    if (!in_array('accountsJson', $setCols, true)) {
        $pdo->exec('ALTER TABLE "ShikasSettings" ADD COLUMN "accountsJson" TEXT NOT NULL DEFAULT \'[]\';');
    }
}

function shizuku_ensure_seed(PDO $pdo): void
{
    $now = now_iso();

    // 1. AdminUser
    $adminCount = (int) $pdo->query('SELECT COUNT(*) FROM "AdminUser"')->fetchColumn();
    if ($adminCount === 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO "AdminUser" ("id", "username", "email", "passwordHash", "displayName", "createdAt", "updatedAt")
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            make_cuid('adm_'),
            env_str('ADMIN_USERNAME', 'farhan'),
            env_str('ADMIN_EMAIL', 'farhansatria.07@gmail.com'),
            password_hash(env_str('ADMIN_PASSWORD', 'shizuku-admin-2026'), PASSWORD_BCRYPT, ['cost' => 11]),
            'Farhan Satria Nugraha',
            $now,
            $now,
        ]);
    }

    // 2. SiteProfile
    $profileCount = (int) $pdo->query('SELECT COUNT(*) FROM "SiteProfile" WHERE "id" = \'main\'')->fetchColumn();
    if ($profileCount === 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO "SiteProfile" (
                "id","brandName","brandKanji","fullName","headlineEn","headlineId","rolesJson",
                "bioEn","bioId","aboutStoryEn","aboutStoryId","location","currentRoleEn","currentRoleId",
                "currentOrg","availabilityEn","availabilityId","currentlyEn","currentlyId",
                "heroImage","heroImageMobile","avatarImage","email","linkedinUrl","githubUrl",
                "instagramUrl","youtubeUrl","resumeUrl","statsJson","pillarsJson","updatedAt"
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            'main',
            'shizuku',
            '雫',
            'Farhan Satria Nugraha',
            'Software Engineer, ODP IT at Bank Negara Indonesia (BNI), and creator of the shizuku web ecosystem.',
            'Software Engineer, ODP IT di Bank Negara Indonesia (BNI), dan pembuat ekosistem web shizuku.',
            json_encode(['en' => ['Software Engineer', 'ODP IT · BNI'], 'id' => ['Software Engineer', 'ODP IT · BNI']]),
            'Software engineer with a background in Informatics and Application Software Engineering, focused on backend and full-stack development. Experienced across web, mobile, and data-driven projects, continuously learning and building clean, reliable, and sustainable software solutions.',
            'Software engineer dengan latar belakang Informatika dan Rekayasa Perangkat Lunak Aplikasi yang berfokus pada pengembangan backend dan full-stack. Berpengalaman membangun aplikasi web, mobile, serta proyek berbasis data yang bersih, andal, dan berkelanjutan.',
            "shizuku (雫) is a personal web hub and digital ecosystem.\n\nBuilt around calm minimalism, shizuku brings together personal works, portfolio records, and a shared SHIZUKU ID across companion web apps like SHIKAS and SHIKOST.",
            "shizuku (雫) adalah ruang web personal dan ekosistem digital.\n\nDibangun dengan gaya minimalis yang tenang, shizuku menyatukan karya ekosistem, catatan portofolio, dan satu SHIZUKU ID untuk aplikasi seperti SHIKAS dan SHIKOST.",
            'Jakarta, Indonesia',
            'Officer Development Program (ODP) — IT',
            'Officer Development Program (ODP) — IT',
            'PT Bank Negara Indonesia (Persero) Tbk (BNI)',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'farhansatria.07@gmail.com',
            'https://www.linkedin.com/in/farhansatrian/',
            'https://github.com/farhansatrian',
            'https://www.instagram.com/farhansatria_n/',
            'https://www.youtube.com/@ahansn',
            '',
            '[]',
            '[]',
            $now,
        ]);
    }

    // 3. WorkspaceApp
    $appCount = (int) $pdo->query('SELECT COUNT(*) FROM "WorkspaceApp"')->fetchColumn();
    if ($appCount === 0) {
        $apps = [
            ['SHIZUKU', 'INTERNAL', 'Profile, portfolio, and shared ecosystem ID hub.', 'Profil, portofolio, dan pusat akun tunggal ekosistem.', '/', 'globe', 'HUB', 0, 1, 1],
            ['SHIKAS', 'APP', 'Personal cashflow tracker synced with SHIZUKU ID.', 'Pencatat arus kas pribadi terhubung dengan SHIZUKU ID.', 'https://shikas.sitezet.com', 'wallet', 'FINANCE', 1, 1, 2],
            ['SHIKOST', 'APP', 'Shared boarding-house ledger & dues (formerly KostKas).', 'Kas bersama & iuran kost (sebelumnya KostKas).', 'https://github.com/farhansatrian/kostkas', 'folder', 'LEDGER', 1, 1, 3],
        ];
        $stmt = $pdo->prepare(
            'INSERT INTO "WorkspaceApp" ("id","name","category","descriptionEn","descriptionId","url","icon","badge","isExternal","published","sortOrder","createdAt","updatedAt")
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        foreach ($apps as $a) {
            $stmt->execute([make_cuid('app_'), ...$a, $now, $now]);
        }
    }

    // 4. Experience
    $expCount = (int) $pdo->query('SELECT COUNT(*) FROM "Experience"')->fetchColumn();
    if ($expCount === 0) {
        $exps = [
            ['EDUCATION', 'Telkom University', 'Diploma (D3) in Application Software Engineering', 'Diploma (D3) Rekayasa Perangkat Lunak Aplikasi', 'Bandung, Indonesia', '2024 | GPA: 3.89 / 4.0', '2024 | IPK: 3.89 / 4.0', 0, 'Relevant Coursework: Algorithms, Data Structures, Databases, Web Programming, Mobile Programming, OOP', 'Mata Kuliah Relevan: Algoritma, Struktur Data, Basis Data, Pemrograman Web, Pemrograman Mobile, OOP', ['en' => [], 'id' => []], 1],
            ['EDUCATION', 'Telkom University', "Bachelor's Degree (S1) in Informatics", 'Sarjana (S1) Informatika', 'Bandung, Indonesia', '2026 | GPA: 3.68 / 4.0', '2026 | IPK: 3.68 / 4.0', 0, 'Relevant Coursework: Machine Learning, Process Mining', 'Mata Kuliah Relevan: Machine Learning, Process Mining', ['en' => [], 'id' => []], 2],
            ['WORK', 'PT Bank Negara Indonesia (Persero) Tbk (BNI)', 'Officer Development Program (ODP) — IT', 'Officer Development Program (ODP) — IT', 'Jakarta, Indonesia', 'Present', 'Saat ini', 1, "Banking technology track in BNI's Officer Development Program (IT), focusing on software engineering and enterprise systems.", 'Jalur teknologi informasi pada Officer Development Program (ODP IT) BNI, berfokus pada rekayasa perangkat lunak dan sistem enterprise.', ['en' => [], 'id' => []], 3],
            ['WORK', 'PT. Tabel Data Informatika', 'Full-Stack Developer Intern', 'Full-Stack Developer Intern', 'Bandung, Indonesia', 'Jul 2023 - Jun 2024', 'Jul 2023 - Jun 2024', 0, '', '', ['en' => ['Developed the Hasjrat application using Java Spring Boot for the backend and Angular, Bootstrap, and TypeScript for the frontend.', 'Developed and integrated APIs with the user interface, connecting backend services to the frontend.', 'Managed PostgreSQL databases and implemented CRUD operations for data management.', 'Participated in application deployment using a microservices architecture.'], 'id' => ['Mengembangkan aplikasi Hasjrat menggunakan Java Spring Boot untuk backend serta Angular, Bootstrap, dan TypeScript untuk frontend.', 'Mengembangkan dan mengintegrasikan API dengan antarmuka pengguna untuk menghubungkan layanan backend ke frontend.', 'Mengelola basis data PostgreSQL dan mengimplementasikan operasi CRUD untuk manajemen data.', 'Berpartisipasi dalam deployment aplikasi menggunakan arsitektur microservices.']], 4],
            ['ORGANIZATION', 'Chevalier LAB - Telkom University', 'Android Division - Participant', 'Divisi Android - Peserta', 'Bandung, Indonesia', 'Sep 2021 - Aug 2022', 'Sep 2021 - Agu 2022', 0, '', '', ['en' => ['Contributed to the development of the Noyu App Android application using Kotlin.', 'Participated in study groups and discussions on Android fundamentals.', 'Participated in laboratory events, including seminars and webinars.'], 'id' => ['Berkontribusi dalam pengembangan aplikasi Android Noyu App menggunakan Kotlin.', 'Berpartisipasi dalam study group dan diskusi fundamental Android.', 'Berpartisipasi dalam kegiatan laboratorium termasuk seminar dan webinar.']], 5],
            ['ORGANIZATION', 'Chevalier LAB - Telkom University', 'Laboratory Assistant - Android Division Mentor', 'Asisten Laboratorium - Mentor Divisi Android', 'Bandung, Indonesia', 'Sep 2022 - Oct 2023', 'Sep 2022 - Okt 2023', 0, '', '', ['en' => ['Coordinated Android Division study group activities.', 'Created the syllabus and mentored participants in Android application development.', 'Supported and took responsibility for various laboratory events.'], 'id' => ['Mengoordinasikan kegiatan study group Divisi Android.', 'Menyusun silabus dan membimbing peserta dalam pengembangan aplikasi Android.', 'Mendukung dan bertanggung jawab atas berbagai acara laboratorium.']], 6],
            ['ORGANIZATION', 'Teaching Assistant - STMIK AMIK Bandung', 'Object-Oriented Programming (Java)', 'Pemrograman Berorientasi Objek (Java)', 'Bandung, Indonesia', 'Feb 2024 - Jul 2024', 'Feb 2024 - Jul 2024', 0, '', '', ['en' => ['Led and guided students in completing their OOP final projects.', 'Taught OOP concepts: Encapsulation, Abstraction, Inheritance, and Polymorphism.'], 'id' => ['Membimbing mahasiswa dalam menyelesaikan proyek akhir OOP.', 'Mengajarkan konsep OOP: Encapsulation, Abstraction, Inheritance, dan Polymorphism.']], 7],
            ['ORGANIZATION', 'Teaching Assistant - STMIK AMIK Bandung', 'Algorithms and Programming (Java)', 'Algoritma dan Pemrograman (Java)', 'Bandung, Indonesia', 'Jul 2024 - Jan 2025', 'Jul 2024 - Jan 2025', 0, '', '', ['en' => ['Facilitated discussions and practical sessions on Java fundamentals.', 'Taught Java fundamentals: Control Flow, Loops, Methods, and Arrays.'], 'id' => ['Memfasilitasi diskusi dan sesi praktikum fundamental Java.', 'Mengajarkan dasar Java: Control Flow, Loops, Methods, dan Arrays.']], 8],
        ];
        $stmt = $pdo->prepare(
            'INSERT INTO "Experience" ("id","kind","organization","roleEn","roleId","location","periodEn","periodId","isCurrent","summaryEn","summaryId","bulletsJson","tagsJson","published","sortOrder","createdAt","updatedAt")
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, ?,?)'
        );
        foreach ($exps as $e) {
            $stmt->execute([
                make_cuid('exp_'),
                $e[0], $e[1], $e[2], $e[3], $e[4], $e[5], $e[6], $e[7], $e[8], $e[9],
                json_encode($e[10], JSON_UNESCAPED_UNICODE),
                '[]',
                1,
                $e[11],
                $now,
                $now,
            ]);
        }
    }
}
