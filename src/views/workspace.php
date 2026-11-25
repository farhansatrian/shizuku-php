<?php
declare(strict_types=1);

function render_workspace_page(array $data, ?array $ecoUser, bool $isAdmin): void
{
    $profile = $data['profile'];
    $apps = $data['workspaceApps'];
    $accents = [
        'HUB' => '#745bc7',
        'FINANCE' => '#2276a8',
        'LEDGER' => '#d749d5',
        'APP' => '#2276a8',
        'AGENT' => '#d749d5',
        'INTERNAL' => '#745bc7',
    ];

    $dialogues = $ecoUser
        ? [
            [
                'en' => 'Signed in as ' . $ecoUser['displayName'] . '. One SHIZUKU ID connects every web app in this ecosystem.',
                'id' => 'Masuk sebagai ' . $ecoUser['displayName'] . '. Satu SHIZUKU ID menghubungkan semua web app di ekosistem ini.',
            ],
            [
                'en' => 'Launch any companion app below — your session stays connected via SHIZUKU ID.',
                'id' => 'Buka aplikasi di bawah ini — akunmu tetap terhubung lewat SHIZUKU ID.',
            ],
        ]
        : [
            [
                'en' => 'One SHIZUKU ID — sign in once to use SHIKAS, SHIKOST, and upcoming companion apps.',
                'id' => 'Satu SHIZUKU ID — buat akun sekali untuk dipakai di SHIKAS, SHIKOST, dan aplikasi ekosistem lainnya.',
            ],
            [
                'en' => 'Every work listed below is part of the SHIZUKU personal web ecosystem.',
                'id' => 'Setiap aplikasi di bawah ini terhubung dalam satu ekosistem web SHIZUKU.',
            ],
        ];

    render_html_shell(
        'WORK — ' . ($profile['brandName'] ?? 'shizuku') . ' (' . ($profile['brandKanji'] ?? '雫') . ')',
        function () use ($profile, $apps, $ecoUser, $isAdmin, $accents, $dialogues): void {
            ?>
            <div class="relative flex min-h-screen flex-col text-[#14100e]">
              <div class="shizuku-workspace-bg" aria-hidden="true"></div>
              <?php shizuku_rain_layer('behind'); ?>
              <?php render_public_header($profile, $ecoUser, '/workspace', false); ?>

              <main class="relative z-10 mx-auto w-full max-w-[960px] flex-1 px-5 pt-28 pb-16 sm:px-8 md:pt-32 md:pb-20">
                <div class="mb-8 md:mb-10">
                  <?php render_subpage_nav('work'); ?>
                </div>

                <div class="space-y-6">
                  <!-- 1. Compact 2-Panel Stage: Clock + SHIZUKU ID -->
                  <section class="grid grid-cols-1 gap-5 lg:grid-cols-12">
                    <div class="vn-frame flex flex-col justify-between overflow-hidden lg:col-span-5">
                      <div class="vn-band-strip flex items-center justify-between gap-2 px-4 py-2 text-white">
                        <span class="font-mono text-[10px] font-semibold tracking-[0.2em] uppercase drop-shadow-2xs">
                          <?= h(strtoupper((string) ($profile['brandName'] ?? 'shizuku'))) ?> // <?= h($profile['brandKanji'] ?? '雫') ?>
                        </span>
                        <span class="font-mono text-[10px] font-semibold tracking-[0.14em] uppercase drop-shadow-2xs">WIB · JKT</span>
                      </div>

                      <div class="flex flex-1 flex-col items-center justify-center px-5 py-6 text-center">
                        <div class="animate-umbrella-float flex h-20 w-20 items-center justify-center border border-[#745bc7]/45 bg-gradient-to-b from-[#efe9fd] to-[#fde8f6] p-2.5 shadow-[3px_3px_0_0_rgba(116,91,199,0.14)]">
                          <img src="/api/uploads/tuyu-umbrella.png" alt="SHIZUKU umbrella" class="h-14 w-auto object-contain select-none" />
                        </div>
                        <p id="wib-clock-time" class="mt-4 font-mono text-[30px] font-bold leading-none tracking-[0.08em] text-[#14100e] tabular-nums">--:--</p>
                        <p id="wib-clock-date" class="mt-1.5 font-mono text-[10px] tracking-[0.16em] text-[#5d5370] uppercase"><?= h($profile['location'] ?? 'Jakarta, Indonesia') ?></p>
                      </div>
                    </div>

                    <div class="vn-frame flex flex-col justify-between overflow-hidden lg:col-span-7">
                      <div class="vn-band-strip flex flex-wrap items-center justify-between gap-2 px-4 py-2 text-white">
                        <span class="font-mono text-[10px] font-semibold tracking-[0.2em] uppercase drop-shadow-2xs">SHIZUKU ID // ECOSYSTEM</span>
                        <span class="font-mono text-[10px] font-semibold tracking-[0.14em] uppercase drop-shadow-2xs"
                          data-en="<?= $ecoUser ? 'ACTIVE' : 'SSO' ?>"
                          data-id="<?= $ecoUser ? 'AKTIF' : 'SSO' ?>">
                          <?= $ecoUser ? 'ACTIVE' : 'SSO' ?>
                        </span>
                      </div>

                      <div class="flex flex-1 flex-col justify-between gap-5 p-5 sm:p-6">
                        <div>
                          <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-[#745bc7]/20 pb-3">
                            <h1 class="font-editorial text-2xl font-bold tracking-[0.16em] text-[#14100e] uppercase sm:text-[26px]">
                              <?= h($ecoUser ? $ecoUser['displayName'] : 'SHIZUKU ID') ?>
                            </h1>
                            <span class="inline-flex items-center gap-1.5 font-mono text-[11px] font-medium text-[#745bc7]">
                              <span class="h-2 w-2 shrink-0" style="background-color:<?= $ecoUser ? '#2276a8' : '#745bc7' ?>"></span>
                              <span class="max-w-[200px] truncate" <?= $ecoUser ? '' : 'data-en="Single Sign-On" data-id="Akun Terpadu"' ?>>
                                <?= h($ecoUser ? $ecoUser['email'] : 'Single Sign-On') ?>
                              </span>
                            </span>
                          </div>

                          <p class="mt-3.5 text-[13px] leading-relaxed text-[#5d5370]"
                            data-en="<?= $ecoUser ? 'Your shared account is active across SHIZUKU, SHIKAS, SHIKOST, and future apps.' : 'One account for all web apps in the SHIZUKU ecosystem.' ?>"
                            data-id="<?= $ecoUser ? 'Akun terpadumu aktif untuk SHIZUKU, SHIKAS, SHIKOST, dan aplikasi berikutnya.' : 'Satu akun untuk semua web app dalam ekosistem SHIZUKU.' ?>">
                            <?= $ecoUser
                              ? 'Your shared account is active across SHIZUKU, SHIKAS, SHIKOST, and future apps.'
                              : 'One account for all web apps in the SHIZUKU ecosystem.' ?>
                          </p>
                        </div>

                        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                          <?php if ($ecoUser): ?>
                            <a href="/account" class="vn-btn-band px-4 py-2.5">
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                              <span data-en="ACCOUNT" data-id="AKUN SAYA">ACCOUNT</span>
                            </a>
                            <button type="button" data-eco-signout class="vn-btn-action px-4 py-2.5">
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                              <span data-en="SIGN OUT" data-id="KELUAR">SIGN OUT</span>
                            </button>
                          <?php else: ?>
                            <a href="/account?mode=login&redirect=/workspace" class="vn-btn-band px-4 py-2.5">
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                              <span data-en="SIGN IN" data-id="MASUK">SIGN IN</span>
                            </a>
                            <a href="/account?mode=register&redirect=/workspace" class="vn-btn-action px-4 py-2.5">
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                              <span data-en="CREATE ACCOUNT" data-id="BUAT AKUN">CREATE ACCOUNT</span>
                            </a>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </section>

                  <!-- 2. Interactive VN Dialogue Box -->
                  <div
                    id="vn-dialogue-box"
                    data-dialogues="<?= h(json_encode($dialogues, JSON_UNESCAPED_UNICODE)) ?>"
                    role="button"
                    tabindex="0"
                    class="vn-dialogue group cursor-pointer p-4 transition hover:border-[#7650f3] select-none sm:p-5"
                  >
                    <div class="flex items-start gap-4">
                      <div class="hidden shrink-0 flex-col items-center sm:flex">
                        <div class="flex h-14 w-14 items-center justify-center border border-[#745bc7] bg-gradient-to-b from-[#efe9fd] to-[#fde8f6] p-2">
                          <img src="/api/uploads/tuyu-umbrella.png" alt="SHIZUKU" class="h-10 w-auto object-contain" />
                        </div>
                      </div>
                      <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2 border-b border-[#745bc7]/20 pb-1">
                          <div class="flex items-center gap-2">
                            <span class="font-editorial text-sm font-bold tracking-[0.22em] text-[#745bc7] uppercase"><?= h($profile['brandName'] ?? 'shizuku') ?></span>
                            <span class="font-mono text-[10px] text-[#5d5370]">(<?= h($profile['brandKanji'] ?? '雫') ?>)</span>
                          </div>
                          <span class="animate-vn-next font-mono text-[11px] font-bold text-[#d749d5]">▼</span>
                        </div>
                        <p id="vn-dialogue-text" class="mt-2 min-h-[38px] font-mono text-xs leading-relaxed text-[#14100e] sm:text-[13px]"><?= h($dialogues[0]['en']) ?></p>
                      </div>
                    </div>
                  </div>

                  <!-- 3. Ecosystem Works Deck -->
                  <section class="vn-frame overflow-hidden">
                    <div class="vn-band-strip flex flex-wrap items-center justify-between gap-2 px-4 py-2 text-white">
                      <span class="font-mono text-[10px] font-semibold tracking-[0.2em] uppercase drop-shadow-2xs">ECOSYSTEM WORKS // 雫</span>
                      <div class="flex items-center gap-3 font-mono text-[10px] tracking-[0.14em] uppercase">
                        <span class="text-white/95 drop-shadow-2xs">
                          <?= str_pad((string) count($apps), 2, '0', STR_PAD_LEFT) ?>
                          <span data-en="APPS" data-id="APP">APPS</span>
                        </span>
                        <?php if ($isAdmin): ?>
                          <a href="/admin" class="border border-white/55 bg-white/15 px-2 py-0.5 text-[9.5px] font-semibold text-white transition hover:bg-white/30" data-en="EDIT" data-id="ATUR">EDIT</a>
                        <?php endif; ?>
                      </div>
                    </div>

                    <?php if (count($apps) === 0): ?>
                      <div class="bg-white/95 px-6 py-12 text-center">
                        <p class="font-mono text-xs text-[#5d5370]" data-en="No ecosystem apps published yet." data-id="Belum ada aplikasi ekosistem yang ditampilkan.">No ecosystem apps published yet.</p>
                      </div>
                    <?php else: ?>
                      <div class="bg-white/95 p-4 sm:p-5">
                        <ul class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-3">
                          <?php foreach ($apps as $idx => $app):
                            $num = str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT);
                            $badgeKey = strtoupper((string) $app['badge']);
                            $catKey = strtoupper((string) $app['category']);
                            $accent = $accents[$badgeKey] ?? $accents[$catKey] ?? '#745bc7';
                          ?>
                            <li>
                              <a
                                href="<?= h($app['url']) ?>"
                                <?= $app['isExternal'] ? 'target="_blank" rel="noreferrer"' : '' ?>
                                class="block h-full focus:outline-none"
                              >
                                <div class="vn-cmd group flex h-full flex-col justify-between p-4">
                                  <span aria-hidden="true" class="absolute top-0 bottom-0 left-0 w-[3px] opacity-0 transition group-hover:opacity-100" style="background-color:<?= h($accent) ?>"></span>
                                  <div>
                                    <div class="flex items-center justify-between gap-2">
                                      <div class="flex items-center gap-2 font-mono text-[10px]">
                                        <span class="font-bold tracking-[0.14em] text-[#745bc7]"><?= $num ?></span>
                                        <span class="h-1.5 w-1.5 shrink-0" style="background-color:<?= h($accent) ?>"></span>
                                        <?php if ($app['badge'] !== ''): ?>
                                          <span class="border px-1.5 py-[1px] text-[9px] font-bold tracking-[0.12em] uppercase" style="border-color:<?= h($accent) ?>;color:<?= h($accent) ?>"><?= h($app['badge']) ?></span>
                                        <?php endif; ?>
                                      </div>
                                      <span class="font-mono text-xs text-[#745bc7]"><?= $app['isExternal'] ? '↗' : '→' ?></span>
                                    </div>
                                    <h2 class="mt-3 flex items-center gap-1.5 font-editorial text-lg font-bold tracking-[0.12em] text-[#14100e] uppercase transition-colors group-hover:text-[#745bc7]">
                                      <span class="font-mono text-[10px] text-[#d749d5] opacity-0 transition-opacity group-hover:opacity-100">▶</span>
                                      <span><?= h($app['name']) ?></span>
                                    </h2>
                                    <p class="mt-1.5 text-xs leading-relaxed text-[#5d5370]" data-en="<?= h($app['descriptionEn']) ?>" data-id="<?= h($app['descriptionId']) ?>">
                                      <?= h($app['descriptionEn']) ?>
                                    </p>
                                  </div>
                                  <div class="mt-4 flex items-center justify-end border-t border-[#745bc7]/15 pt-2 font-mono text-[10px] font-semibold tracking-[0.14em] text-[#745bc7] uppercase"
                                    data-en="<?= $app['isExternal'] ? 'OPEN ↗' : 'ENTER →' ?>"
                                    data-id="<?= $app['isExternal'] ? 'BUKA ↗' : 'MASUK →' ?>">
                                    <?= $app['isExternal'] ? 'OPEN ↗' : 'ENTER →' ?>
                                  </div>
                                </div>
                              </a>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      </div>
                    <?php endif; ?>
                  </section>
                </div>
              </main>

              <?php render_public_footer($profile); ?>
            </div>
            <?php
        }
    );
}
