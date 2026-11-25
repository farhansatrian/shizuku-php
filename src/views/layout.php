<?php
declare(strict_types=1);

function shizuku_umbrella_svg(string $class = 'h-6 w-6'): string
{
    return '<svg viewBox="0 0 32 32" fill="none" class="' . h($class) . '" aria-hidden="true">'
        . '<path d="M16 4V6.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>'
        . '<path d="M5 17.5C5 11.425 9.925 6.5 16 6.5C22.075 6.5 27 11.425 27 17.5C24.8 16.2 22.8 16.2 21 17.5C19.2 16.2 17.4 16.2 16 17.5C14.6 16.2 12.8 16.2 11 17.5C9.2 16.2 7.2 16.2 5 17.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" fill="currentColor" fill-opacity="0.14"/>'
        . '<path d="M16 17.5V24.5C16 26.2 14.7 27.5 13 27.5C11.7 27.5 10.6 26.6 10.3 25.4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>'
        . '<circle cx="8" cy="8.5" r="1.1" fill="currentColor" fill-opacity="0.7"/>'
        . '<circle cx="25.5" cy="9.5" r="1.3" fill="currentColor" fill-opacity="0.55"/>'
        . '</svg>';
}

function shizuku_rain_layer(string $mode = 'behind'): void
{
    $drops = [
        ['5%', '-190px', '13s'], ['10%', '-180px', '14s'], ['15%', '-170px', '11s'], ['20%', '-160px', '15s'],
        ['25%', '-150px', '13s'], ['30%', '-150px', '14s'], ['35%', '-160px', '16s'], ['40%', '-170px', '10s'],
        ['45%', '-180px', '12s'], ['50%', '-190px', '14s'], ['55%', '-190px', '13s'], ['60%', '-180px', '9s'],
        ['65%', '-170px', '17s'], ['70%', '-160px', '15s'], ['75%', '-150px', '13s'], ['80%', '-150px', '17s'],
        ['85%', '-160px', '11s'], ['90%', '-170px', '12s'], ['95%', '-180px', '14s'], ['100%', '-190px', '16s'],
    ];
    $zClass = match ($mode) {
        'behind' => 'z-0',
        'over-splash' => 'z-30 md:z-[95]',
        default => 'z-30',
    };
    echo '<div class="pointer-events-none fixed inset-0 overflow-hidden ' . $zClass . '" aria-hidden="true">';
    foreach ($drops as [$left, $top, $dur]) {
        echo '<span class="tuyu-rain-drop" style="left:' . $left . ';top:' . $top . ';animation-duration:' . $dur . '"></span>';
    }
    echo '</div>';
}

function shizuku_social_links(array $profile): array
{
    $list = [];
    if (!empty($profile['instagramUrl'])) {
        $list[] = [
            'label' => 'Instagram',
            'href' => $profile['instagramUrl'],
            'mail' => false,
            'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" class="h-[24px] w-[24px] md:h-[27px] md:w-[27px]" aria-hidden="true"><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm0 2A3.75 3.75 0 0 0 4 7.75v8.5A3.75 3.75 0 0 0 7.75 20h8.5A3.75 3.75 0 0 0 20 16.25v-8.5A3.75 3.75 0 0 0 16.25 4h-8.5Zm4.25 3a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm5.35-2.6a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5Z"/></svg>',
        ];
    }
    if (!empty($profile['youtubeUrl'])) {
        $list[] = [
            'label' => 'YouTube',
            'href' => $profile['youtubeUrl'],
            'mail' => false,
            'svg' => '<svg viewBox="0 0 28 20" fill="currentColor" class="h-[22px] w-[30px] md:h-[24px] md:w-[33px]" aria-hidden="true"><path d="M27.4 3.1A3.5 3.5 0 0 0 24.9.6C22.7 0 14 0 14 0S5.3 0 3.1.6A3.5 3.5 0 0 0 .6 3.1C0 5.3 0 10 0 10s0 4.7.6 6.9a3.5 3.5 0 0 0 2.5 2.5C5.3 20 14 20 14 20s8.7 0 10.9-.6a3.5 3.5 0 0 0 2.5-2.5C28 14.7 28 10 28 10s0-4.7-.6-6.9ZM11.2 14.3V5.7L18.5 10l-7.3 4.3Z"/></svg>',
        ];
    }
    if (!empty($profile['linkedinUrl'])) {
        $list[] = [
            'label' => 'LinkedIn',
            'href' => $profile['linkedinUrl'],
            'mail' => false,
            'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" class="h-[24px] w-[24px] md:h-[27px] md:w-[27px]" aria-hidden="true"><path d="M19.5 3h-15A1.5 1.5 0 0 0 3 4.5v15A1.5 1.5 0 0 0 4.5 21h15a1.5 1.5 0 0 0 1.5-1.5v-15A1.5 1.5 0 0 0 19.5 3ZM8.6 18.2H6V10h2.6v8.2ZM7.3 8.9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Zm10.9 9.3h-2.6v-4c0-.96-.02-2.2-1.34-2.2-1.35 0-1.55 1.05-1.55 2.13v4.07h-2.6V10h2.5v1.12h.03c.35-.66 1.2-1.35 2.47-1.35 2.64 0 3.13 1.74 3.13 4v4.43Z"/></svg>',
        ];
    }
    if (!empty($profile['githubUrl'])) {
        $list[] = [
            'label' => 'GitHub',
            'href' => $profile['githubUrl'],
            'mail' => false,
            'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" class="h-[24px] w-[24px] md:h-[27px] md:w-[27px]" aria-hidden="true"><path d="M12 .5A11.5 11.5 0 0 0 .5 12c0 5.08 3.29 9.39 7.86 10.91.58.11.79-.25.79-.55 0-.27-.01-1.18-.02-2.14-3.2.7-3.88-1.36-3.88-1.36-.52-1.33-1.28-1.69-1.28-1.69-1.05-.72.08-.7.08-.7 1.16.08 1.77 1.19 1.77 1.19 1.03 1.77 2.7 1.26 3.36.96.1-.75.4-1.26.73-1.55-2.55-.29-5.24-1.28-5.24-5.68 0-1.26.45-2.28 1.19-3.09-.12-.29-.52-1.46.11-3.05 0 0 .97-.31 3.17 1.18a11 11 0 0 1 5.77 0c2.2-1.49 3.17-1.18 3.17-1.18.63 1.59.23 2.76.11 3.05.74.81 1.19 1.83 1.19 3.09 0 4.41-2.69 5.39-5.25 5.67.41.36.78 1.06.78 2.13 0 1.54-.01 2.78-.01 3.16 0 .31.21.67.8.55A11.5 11.5 0 0 0 23.5 12 11.5 11.5 0 0 0 12 .5Z"/></svg>',
        ];
    }
    if (!empty($profile['email'])) {
        $list[] = [
            'label' => 'Email',
            'href' => 'mailto:' . $profile['email'],
            'mail' => true,
            'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" class="h-[24px] w-[24px] md:h-[27px] md:w-[27px]" aria-hidden="true"><path d="M3.75 4.5h16.5A2.25 2.25 0 0 1 22.5 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H3.75a2.25 2.25 0 0 1-2.25-2.25V6.75A2.25 2.25 0 0 1 3.75 4.5Zm.32 2 7.93 5.56L19.93 6.5H4.07Zm16.43 10.75V8.53l-7.93 5.56a1 1 0 0 1-1.14 0L3.5 8.53v8.72c0 .14.11.25.25.25h16.5c.14 0 .25-.11.25-.25Z"/></svg>',
        ];
    }
    return $list;
}

function render_public_header(array $profile, ?array $ecoUser, string $currentPath, bool $transparentOnTop = false): void
{
    $socials = shizuku_social_links($profile);
    $shortName = '';
    $initial = '';
    if ($ecoUser) {
        $parts = preg_split('/\s+/', trim((string) $ecoUser['displayName']));
        $shortName = ($parts && $parts[0] !== '') ? $parts[0] : (explode('@', (string) $ecoUser['email'])[0] ?: 'ID');
        $initial = strtoupper(mb_substr($shortName, 0, 1));
    }

    $navItems = [
        ['href' => '/', 'en' => 'HOME', 'id' => 'BERANDA'],
        ['href' => '/workspace', 'en' => 'WORK', 'id' => 'WORK'],
        ['href' => '/portfolio', 'en' => 'PORTFOLIO', 'id' => 'PORTFOLIO'],
        ['href' => '/about', 'en' => 'ABOUT', 'id' => 'TENTANG'],
        ['href' => '/admin', 'en' => 'ADMIN STUDIO', 'id' => 'ADMIN STUDIO', 'lock' => true],
    ];
    ?>
    <header
      id="shizuku-header"
      data-transparent-top="<?= $transparentOnTop ? '1' : '0' ?>"
      data-eco-signed-in="<?= $ecoUser ? '1' : '0' ?>"
      class="fixed inset-x-0 top-0 z-50 transition-all duration-300 <?= $transparentOnTop
        ? 'bg-gradient-to-b from-[#17112a]/55 via-[#17112a]/20 to-transparent text-white [text-shadow:0_1px_8px_rgba(20,16,14,0.45)]'
        : 'bg-[#e5dcfb]/88 text-[#745bc7] backdrop-blur-md' ?>"
    >
      <div class="mx-auto flex h-[64px] w-full items-center justify-between px-4 sm:h-[72px] sm:px-[5%] md:h-[92px] md:px-[3%]">
        <a
          href="/"
          class="group flex shrink-0 items-center gap-1.5 py-2 transition-opacity hover:opacity-75 focus:outline-none sm:gap-2.5 md:gap-3"
        >
          <span class="font-editorial text-[16px] font-medium tracking-[0.22em] uppercase sm:text-[24px] sm:tracking-[0.3em] md:text-[31px] md:tracking-[0.36em]">
            <?= h($profile['brandName'] ?? 'shizuku') ?>
          </span>
          <?= shizuku_umbrella_svg('h-5 w-5 shrink-0 sm:h-7 sm:w-7 md:h-8 md:w-8') ?>
        </a>

        <div class="flex shrink-0 items-center gap-2 sm:gap-4 md:gap-6">
          <div id="header-socials" aria-label="Social links" class="hidden items-center gap-5 lg:flex lg:gap-6">
            <?php foreach ($socials as $sns): ?>
              <a
                href="<?= h($sns['href']) ?>"
                <?= $sns['mail'] ? '' : 'target="_blank" rel="noreferrer"' ?>
                aria-label="<?= h($sns['label']) ?>"
                title="<?= h($sns['label']) ?>"
                class="inline-flex items-center justify-center transition-opacity duration-200 hover:opacity-70"
              ><?= $sns['svg'] ?></a>
            <?php endforeach; ?>
          </div>

          <a
            id="header-eco-btn"
            href="/account"
            title="<?= $ecoUser ? h('Signed in as ' . $ecoUser['displayName'] . ' (' . $ecoUser['email'] . ')') : 'Sign in with SHIZUKU ID' ?>"
            aria-label="<?= $ecoUser ? h('Signed in as ' . $ecoUser['displayName']) : 'SHIZUKU ID' ?>"
            class="inline-flex shrink-0 items-center gap-1.5 border-[1.5px] px-2 py-1 font-mono text-[10px] font-semibold tracking-[0.08em] uppercase transition sm:px-3 sm:py-1.5 sm:text-[10.5px] sm:tracking-[0.12em] <?= $ecoUser
              ? ($transparentOnTop
                ? 'border-white/85 bg-white/18 text-white backdrop-blur-sm hover:bg-white hover:text-[#745bc7]'
                : 'border-[#745bc7] bg-[#745bc7] text-white shadow-[2px_2px_0_0_rgba(116,91,199,0.22)] hover:bg-[#5f47a8]')
              : ($transparentOnTop
                ? 'border-white/70 bg-white/10 text-white backdrop-blur-sm hover:bg-white hover:text-[#745bc7]'
                : 'border-[#745bc7] bg-white/85 text-[#745bc7] shadow-[2px_2px_0_0_rgba(116,91,199,0.16)] hover:bg-[#745bc7] hover:text-white') ?>"
          >
            <?php if ($ecoUser): ?>
              <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-400 ring-2 ring-emerald-400/35" aria-hidden="true"></span>
              <span class="hidden max-w-[96px] truncate sm:inline"><?= h($shortName) ?></span>
              <span class="sm:hidden"><?= h($initial) ?></span>
            <?php else: ?>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3 w-3 shrink-0 sm:h-3.5 sm:w-3.5" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <span class="hidden sm:inline">SHIZUKU ID</span>
              <span class="sm:hidden">ID</span>
            <?php endif; ?>
          </a>

          <div
            class="flex shrink-0 items-center gap-1 font-editorial text-[14px] font-bold tracking-[0.08em] sm:gap-1.5 sm:text-[19px] sm:tracking-[0.14em] md:gap-2 md:text-[23px]"
            role="group"
            aria-label="Language switcher"
          >
            <button type="button" data-locale-btn="en" class="px-0.5 py-1 transition-opacity opacity-100">EN</button>
            <span class="font-normal opacity-75">|</span>
            <button type="button" data-locale-btn="id" class="px-0.5 py-1 transition-opacity opacity-60 hover:opacity-90">ID</button>
          </div>

          <button
            id="drawer-toggle-btn"
            type="button"
            aria-label="Open menu"
            aria-expanded="false"
            class="flex h-9 w-9 shrink-0 items-center justify-center transition-opacity hover:opacity-75 sm:h-11 sm:w-11 md:h-12 md:w-12"
          >
            <img
              id="drawer-icon-open"
              src="/api/uploads/tuyu-menu-open.png"
              alt=""
              class="h-[20px] w-auto object-contain transition-all duration-300 sm:h-[24px] md:h-[34px] <?= $transparentOnTop ? 'brightness-0 invert' : '' ?>"
            />
            <img
              id="drawer-icon-close"
              src="/api/uploads/tuyu-menu-close.png"
              alt=""
              class="hidden h-7 w-7 object-contain sm:h-8 sm:w-8 md:h-10 md:w-10"
            />
          </button>
        </div>
      </div>
    </header>

    <!-- Full-screen opaque watercolor drawer -->
    <div
      id="shizuku-drawer"
      aria-hidden="true"
      class="pointer-events-none fixed inset-0 z-40 overflow-hidden bg-[#f9e4f5] opacity-0 transition-opacity duration-300 ease-out"
    >
      <div class="tuyu-watercolor-pan-pc absolute inset-0 hidden md:block"></div>
      <div class="tuyu-watercolor-pan-sp absolute inset-0 md:hidden"></div>

      <div class="pointer-events-none absolute top-0 left-0 overflow-hidden pt-24 pl-2 select-none sm:pl-4 md:pt-28 md:pl-6" aria-hidden="true">
        <span class="block font-editorial text-[5.2rem] font-normal leading-none tracking-[0.26em] text-white/80 uppercase sm:text-[7rem] md:text-[8.5rem]" style="writing-mode: vertical-rl;">
          <?= h($profile['brandName'] ?? 'shizuku') ?>
        </span>
      </div>

      <div class="relative z-10 flex h-full w-full flex-col justify-end px-[5%] pb-10 md:px-[3%] md:pb-12">
        <nav aria-label="Full navigation" class="mb-10 flex flex-col items-end gap-2.5 md:mb-14 md:gap-3">
          <?php foreach ($navItems as $item):
            $active = $item['href'] === '/' ? ($currentPath === '/') : ($currentPath === $item['href'] || str_starts_with($currentPath, $item['href'] . '/'));
          ?>
            <a
              href="<?= h($item['href']) ?>"
              class="inline-flex items-center gap-2.5 py-1 font-editorial text-[19px] tracking-[0.34em] uppercase transition-all duration-300 hover:text-[#d749d5] hover:opacity-75 sm:text-[23px] md:text-[26px] md:tracking-[0.42em] <?= $active ? 'font-bold text-[#d749d5]' : 'font-normal text-[#745bc7]' ?>"
            >
              <?php if (!empty($item['lock'])): ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <?php endif; ?>
              <span data-en="<?= h($item['en']) ?>" data-id="<?= h($item['id']) ?>"><?= h($item['en']) ?></span>
            </a>
          <?php endforeach; ?>
        </nav>

        <div class="flex items-center justify-end gap-5 text-[#745bc7] md:gap-7">
          <?php foreach ($socials as $sns): ?>
            <a
              href="<?= h($sns['href']) ?>"
              <?= $sns['mail'] ? '' : 'target="_blank" rel="noreferrer"' ?>
              aria-label="<?= h($sns['label']) ?>"
              title="<?= h($sns['label']) ?>"
              class="inline-flex items-center justify-center transition hover:text-[#d749d5] hover:opacity-75"
            ><?= $sns['svg'] ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php
}

function render_subpage_nav(string $active): void
{
    $tabs = [
        'home' => ['href' => '/', 'en' => 'HOME', 'id' => 'BERANDA'],
        'work' => ['href' => '/workspace', 'en' => 'WORK', 'id' => 'WORK'],
        'portfolio' => ['href' => '/portfolio', 'en' => 'PORTFOLIO', 'id' => 'PORTFOLIO'],
        'about' => ['href' => '/about', 'en' => 'ABOUT', 'id' => 'TENTANG'],
    ];
    echo '<nav aria-label="Section navigation" class="font-condensed flex items-center justify-center gap-7 sm:gap-11 md:gap-14">';
    foreach ($tabs as $key => $tab) {
        if ($key === $active) {
            echo '<span class="border-b-[1.5px] border-[#14100e] pb-1 text-[17px] font-medium tracking-[0.14em] text-[#14100e] uppercase sm:text-[19px]" data-en="' . h($tab['en']) . '" data-id="' . h($tab['id']) . '">' . h($tab['en']) . '</span>';
        } else {
            echo '<a href="' . h($tab['href']) . '" class="pb-1 text-[17px] font-normal tracking-[0.14em] text-[#14100e] uppercase transition hover:text-[#745bc7] sm:text-[19px]" data-en="' . h($tab['en']) . '" data-id="' . h($tab['id']) . '">' . h($tab['en']) . '</a>';
        }
    }
    echo '</nav>';
}

function render_public_footer(array $profile): void
{
    $year = date('Y');
    $brand = strtoupper((string) ($profile['brandName'] ?? 'shizuku'));
    $kanji = (string) ($profile['brandKanji'] ?? '雫');
    ?>
    <footer class="tuyu-footer-band relative z-10 px-6 py-10 text-center text-white md:py-16">
      <div class="mx-auto flex max-w-[1000px] flex-col items-center space-y-2">
        <p class="font-mono text-[10px] tracking-[3px] text-white/95 md:text-[11px] md:tracking-[4px]">
          ©<?= h($year) ?> <?= h($brand) ?> (<?= h($kanji) ?>)
        </p>
        <p class="font-mono text-[9px] tracking-[2px] text-white/80 md:text-[10px]">
          <span data-en="Special thanks &amp; inspiration:" data-id="Special thanks &amp; inspirasi:">Special thanks &amp; inspiration:</span>
          <a href="https://tuyu.moe/en/" target="_blank" rel="noreferrer" class="underline decoration-white/45 underline-offset-4 transition hover:text-white hover:decoration-white">TUYU</a>
          &amp;
          <a href="https://www.omo-cat.com/" target="_blank" rel="noreferrer" class="underline decoration-white/45 underline-offset-4 transition hover:text-white hover:decoration-white">OMOCAT</a>
        </p>
      </div>
    </footer>
    <?php
}

function render_html_shell(string $title, callable $bodyFn, array $extraScripts = []): void
{
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!doctype html>
<html lang="en" data-scroll-behavior="smooth">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= h($title) ?></title>
  <meta name="description" content="shizuku (雫) — Personal web hub, portfolio, and shared SHIZUKU ID ecosystem." />
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23745bc7'/%3E%3Ctext x='16' y='22' text-anchor='middle' font-size='18' fill='white' font-family='serif'%3E%E9%9B%AB%3C/text%3E%3C/svg%3E" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600&family=Cormorant+Garamond:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Noto+Sans+JP:wght@300;400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/css/shizuku.css?v=1" />
</head>
<body class="min-h-screen antialiased selection:bg-[#7650f3]/20 selection:text-[#1e172b]">
  <?php $bodyFn(); ?>
  <script src="/js/shizuku.js?v=1" defer></script>
  <?php foreach ($extraScripts as $src): ?>
    <script src="<?= h($src) ?>" defer></script>
  <?php endforeach; ?>
</body>
</html>
    <?php
}
