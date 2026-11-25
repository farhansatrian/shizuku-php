<?php
declare(strict_types=1);

function render_account_page(array $data, ?array $ecoUser): void
{
    $profile = $data['profile'];
    $apps = $data['workspaceApps'];
    $googleEnabled = is_google_oauth_configured();
    $mode = (($_GET['mode'] ?? '') === 'register') ? 'register' : 'login';
    $redirectParam = isset($_GET['redirect']) ? (string) $_GET['redirect'] : '';
    $safeRedirect = (str_starts_with($redirectParam, '/') && !str_starts_with($redirectParam, '//')) ? $redirectParam : '';
    $googleHref = $safeRedirect !== '' ? '/api/ecosystem/google?redirect=' . rawurlencode($safeRedirect) : '/api/ecosystem/google';
    $oauthError = (string) ($_GET['oauthError'] ?? '');

    $shikasApp = null;
    foreach ($apps as $a) {
        if (stripos((string) $a['name'], 'shikas') !== false || stripos((string) $a['url'], 'shikas') !== false) {
            $shikasApp = $a;
            break;
        }
    }

    $shikasTxCount = null;
    if ($ecoUser) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM "ShikasTransaction" WHERE "userId" = ?');
        $stmt->execute([$ecoUser['id']]);
        $shikasTxCount = (int) $stmt->fetchColumn();
    }

    render_html_shell(
        'SHIZUKU ID — ' . ($profile['brandName'] ?? 'shizuku') . ' (' . ($profile['brandKanji'] ?? '雫') . ')',
        function () use ($profile, $ecoUser, $googleEnabled, $mode, $safeRedirect, $googleHref, $oauthError, $shikasApp, $shikasTxCount): void {
            ?>
            <div class="relative flex min-h-screen flex-col text-[#14100e]">
              <div class="shizuku-workspace-bg" aria-hidden="true"></div>
              <?php shizuku_rain_layer('behind'); ?>
              <?php render_public_header($profile, $ecoUser, '/account', false); ?>

              <main class="relative z-10 mx-auto flex w-[92%] max-w-[760px] flex-1 flex-col pt-28 pb-24 md:pt-34 md:pb-28">
                <?php render_subpage_nav(''); ?>

                <?php if ($ecoUser):
                  $initial = strtoupper(mb_substr(trim((string) $ecoUser['displayName']) ?: (string) $ecoUser['email'], 0, 1));
                  $ts = strtotime((string) $ecoUser['createdAt']);
                  $sinceEn = $ts ? date('F j, Y', $ts) : substr((string) $ecoUser['createdAt'], 0, 10);
                ?>
                  <section class="vn-frame mx-auto mt-10 w-full max-w-[600px] overflow-hidden md:mt-12">
                    <div class="vn-band-strip flex flex-wrap items-center justify-between gap-2 px-5 py-2.5 text-white">
                      <span class="font-mono text-[10px] font-semibold tracking-[0.2em] uppercase drop-shadow-2xs">
                        <?= h(strtoupper((string) ($profile['brandName'] ?? 'shizuku'))) ?> ID // <?= h($profile['brandKanji'] ?? '雫') ?>
                      </span>
                      <span class="inline-flex items-center gap-1.5 font-mono text-[10px] font-semibold tracking-[0.14em] uppercase drop-shadow-2xs">
                        <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                        <span data-en="SIGNED IN" data-id="AKTIF">SIGNED IN</span>
                      </span>
                    </div>

                    <div class="bg-white/96 p-6 sm:p-8">
                      <div class="flex flex-col gap-4 border-b border-[#745bc7]/20 pb-6 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3.5">
                          <div class="flex h-12 w-12 shrink-0 items-center justify-center border-[1.5px] border-[#745bc7] bg-gradient-to-b from-[#efe9fd] to-[#fde8f6] font-editorial text-xl font-bold text-[#745bc7]">
                            <?= h($initial) ?>
                          </div>
                          <div class="min-w-0">
                            <h1 class="truncate font-editorial text-xl font-bold tracking-[0.08em] text-[#14100e] uppercase sm:text-2xl">
                              <?= h($ecoUser['displayName']) ?>
                            </h1>
                            <p class="truncate font-mono text-xs text-[#5d5370]"><?= h($ecoUser['email']) ?></p>
                            <p class="mt-0.5 font-mono text-[10px] tracking-[0.1em] text-[#7b6f94] uppercase">Member since <?= h($sinceEn) ?></p>
                          </div>
                        </div>

                        <button type="button" data-eco-signout class="inline-flex shrink-0 items-center justify-center gap-1.5 border-[1.5px] border-rose-400 bg-white px-3.5 py-2 font-mono text-[10.5px] font-semibold tracking-[0.12em] text-rose-600 uppercase transition hover:bg-rose-600 hover:text-white">
                          <span data-en="SIGN OUT" data-id="KELUAR">SIGN OUT</span>
                        </button>
                      </div>

                      <div class="mt-6">
                        <p class="font-mono text-[10.5px] font-semibold tracking-[0.18em] text-[#745bc7] uppercase" data-en="CONNECTED ECOSYSTEM" data-id="EKOSISTEM TERHUBUNG">CONNECTED ECOSYSTEM</p>
                        <div class="mt-3 space-y-3">
                          <div class="vn-cmd flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-center">
                            <div class="min-w-0">
                              <div class="flex flex-wrap items-center gap-2 font-mono text-[10px]">
                                <span class="font-bold text-[#745bc7]">01</span>
                                <span class="border border-[#2276a8] px-1.5 py-0.5 text-[9.5px] font-bold text-[#2276a8] uppercase">SHIKAS</span>
                                <span class="text-[#5d5370]" data-en="<?= h($shikasTxCount . ' entries synced') ?>" data-id="<?= h($shikasTxCount . ' transaksi tersinkron') ?>"><?= h($shikasTxCount . ' entries synced') ?></span>
                              </div>
                              <h2 class="mt-1.5 text-sm font-bold text-[#14100e]" data-en="SHIKAS — Personal Cashflow" data-id="SHIKAS — Keuangan Pribadi">SHIKAS — Personal Cashflow</h2>
                              <p class="mt-0.5 text-xs text-[#5d5370]" data-en="Sign in to SHIKAS with this same SHIZUKU ID to sync privately." data-id="Masuk ke SHIKAS dengan SHIZUKU ID ini untuk sinkronisasi privat.">Sign in to SHIKAS with this same SHIZUKU ID to sync privately.</p>
                            </div>
                            <?php if ($shikasApp): ?>
                              <a href="<?= h($shikasApp['url']) ?>" <?= $shikasApp['isExternal'] ? 'target="_blank" rel="noreferrer"' : '' ?> class="vn-btn-band shrink-0 px-3.5 py-2 text-[10px]">
                                <span data-en="OPEN ↗" data-id="BUKA ↗">OPEN ↗</span>
                              </a>
                            <?php endif; ?>
                          </div>

                          <a href="/workspace" class="vn-cmd group flex items-center justify-between gap-4 p-4">
                            <div class="min-w-0">
                              <div class="flex items-center gap-2 font-mono text-[10px]">
                                <span class="font-bold text-[#745bc7]">02</span>
                                <span class="border border-[#745bc7] px-1.5 py-0.5 text-[9.5px] font-bold text-[#745bc7] uppercase">WORK</span>
                              </div>
                              <h2 class="mt-1.5 text-sm font-bold text-[#14100e] transition-colors group-hover:text-[#745bc7]" data-en="SHIZUKU Ecosystem Works" data-id="Daftar Aplikasi Ekosistem">SHIZUKU Ecosystem Works</h2>
                              <p class="mt-0.5 text-xs text-[#5d5370]" data-en="Browse SHIZUKU, SHIKAS, SHIKOST, and upcoming companion apps." data-id="Lihat SHIZUKU, SHIKAS, SHIKOST, dan aplikasi ekosistem berikutnya.">Browse SHIZUKU, SHIKAS, SHIKOST, and upcoming companion apps.</p>
                            </div>
                            <span class="font-mono text-sm text-[#745bc7]">→</span>
                          </a>
                        </div>
                      </div>
                    </div>
                  </section>
                <?php else: ?>
                  <section class="vn-frame mx-auto mt-10 w-full max-w-[460px] overflow-hidden md:mt-12">
                    <div class="vn-band-strip flex items-center justify-between gap-2 px-5 py-2.5 text-white">
                      <span class="font-mono text-[10px] font-semibold tracking-[0.2em] uppercase drop-shadow-2xs">
                        <?= h(strtoupper((string) ($profile['brandName'] ?? 'shizuku'))) ?> ID // <?= h($profile['brandKanji'] ?? '雫') ?>
                      </span>
                      <span id="auth-band-mode" class="font-mono text-[10px] font-semibold tracking-[0.14em] uppercase drop-shadow-2xs">
                        <?= $mode === 'register' ? 'REGISTER' : 'SIGN IN' ?>
                      </span>
                    </div>

                    <div class="bg-white/96 p-6 sm:p-8">
                      <div class="flex flex-col items-center text-center">
                        <div class="flex h-12 w-12 items-center justify-center border border-[#745bc7]/45 bg-gradient-to-b from-[#efe9fd] to-[#fde8f6] text-[#745bc7] shadow-[2px_2px_0_0_rgba(116,91,199,0.14)]">
                          <?= shizuku_umbrella_svg('h-6 w-6') ?>
                        </div>
                        <h1 id="auth-heading" class="mt-4 font-editorial text-2xl font-bold tracking-[0.18em] text-[#14100e] uppercase sm:text-[26px]">
                          <?= $mode === 'register' ? 'CREATE ACCOUNT' : 'SIGN IN' ?>
                        </h1>
                        <p class="mt-1.5 text-xs leading-relaxed text-[#5d5370]" data-en="One SHIZUKU ID across SHIZUKU, SHIKAS, SHIKOST, and future apps." data-id="Satu SHIZUKU ID untuk SHIZUKU, SHIKAS, SHIKOST, dan aplikasi berikutnya.">
                          One SHIZUKU ID across SHIZUKU, SHIKAS, SHIKOST, and future apps.
                        </p>
                      </div>

                      <div role="tablist" aria-label="Authentication mode" class="mt-6 grid grid-cols-2 border-[1.5px] border-[#745bc7] bg-[#fdfbff] font-mono text-[11px]">
                        <button type="button" data-auth-tab="login" class="py-2.5 font-semibold tracking-[0.14em] uppercase transition <?= $mode === 'login' ? 'bg-[#745bc7] text-white' : 'text-[#5d5370] hover:bg-[#efe9fd] hover:text-[#14100e]' ?>" data-en="SIGN IN" data-id="MASUK">SIGN IN</button>
                        <button type="button" data-auth-tab="register" class="border-l border-[#745bc7] py-2.5 font-semibold tracking-[0.14em] uppercase transition <?= $mode === 'register' ? 'bg-[#745bc7] text-white' : 'text-[#5d5370] hover:bg-[#efe9fd] hover:text-[#14100e]' ?>" data-en="REGISTER" data-id="DAFTAR">REGISTER</button>
                      </div>

                      <div class="mt-5">
                        <a
                          href="<?= h($googleHref) ?>"
                          data-google-configured="<?= $googleEnabled ? 'true' : 'false' ?>"
                          class="flex w-full items-center justify-center gap-2.5 border-[1.5px] border-[#745bc7]/55 bg-white px-4 py-2.5 font-sans text-[13.5px] font-semibold text-[#14100e] shadow-[2px_2px_0_0_rgba(116,91,199,0.14)] transition hover:border-[#745bc7] hover:bg-[#f7f2ff] active:translate-x-[1px] active:translate-y-[1px]"
                        >
                          <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0" aria-hidden="true">
                            <path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.2-2.27H12v4.51h6.47a5.54 5.54 0 0 1-2.4 3.63v3h3.88c2.27-2.09 3.54-5.17 3.54-8.87Z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.07 7.94-2.86l-3.88-3c-1.08.72-2.45 1.15-4.06 1.15-3.12 0-5.77-2.11-6.72-4.95H1.28v3.1A12 12 0 0 0 12 24Z"/>
                            <path fill="#FBBC05" d="M5.28 14.34a7.2 7.2 0 0 1 0-4.68v-3.1H1.28a12 12 0 0 0 0 10.88l4-3.1Z"/>
                            <path fill="#EA4335" d="M12 4.71c1.76 0 3.34.61 4.59 1.8l3.44-3.44C17.95 1.18 15.24 0 12 0A12 12 0 0 0 1.28 6.56l4 3.1C6.23 6.82 8.88 4.71 12 4.71Z"/>
                          </svg>
                          <span data-en="Continue with Google" data-id="Lanjutkan dengan Google">Continue with Google</span>
                        </a>
                      </div>

                      <div class="my-5 flex items-center gap-3">
                        <div class="h-px flex-1 bg-[#745bc7]/22"></div>
                        <span class="font-mono text-[10px] tracking-[0.18em] text-[#7b6f94] uppercase" data-en="or with email" data-id="atau pakai email">or with email</span>
                        <div class="h-px flex-1 bg-[#745bc7]/22"></div>
                      </div>

                      <form
                        id="eco-auth-form"
                        data-mode="<?= h($mode) ?>"
                        data-redirect="<?= h($safeRedirect) ?>"
                        data-oauth-error="<?= h($oauthError) ?>"
                        class="space-y-4"
                        novalidate
                      >
                        <div id="eco-name-wrap" class="<?= $mode === 'register' ? '' : 'hidden' ?>">
                          <label for="eco-display-name" class="block font-mono text-[10px] font-semibold tracking-[0.16em] text-[#5d5370] uppercase" data-en="Display Name (optional)" data-id="Nama Tampilan (opsional)">Display Name (optional)</label>
                          <input id="eco-display-name" type="text" maxlength="60" placeholder="e.g. Farhan" class="shizuku-input mt-1.5 w-full border border-[#745bc7]/45 bg-white px-3.5 py-2.5 text-sm text-[#14100e] outline-none transition placeholder:text-[#8b7fa6] focus:border-[#745bc7] focus:ring-2 focus:ring-[#745bc7]/15" />
                        </div>

                        <div>
                          <label for="eco-email" class="block font-mono text-[10px] font-semibold tracking-[0.16em] text-[#5d5370] uppercase">Email</label>
                          <input id="eco-email" type="email" required autocomplete="email" placeholder="you@example.com" class="shizuku-input mt-1.5 w-full border border-[#745bc7]/45 bg-white px-3.5 py-2.5 text-sm text-[#14100e] outline-none transition placeholder:text-[#8b7fa6] focus:border-[#745bc7] focus:ring-2 focus:ring-[#745bc7]/15" />
                        </div>

                        <div>
                          <label for="eco-password" class="block font-mono text-[10px] font-semibold tracking-[0.16em] text-[#5d5370] uppercase" data-en="Password" data-id="Kata Sandi">Password</label>
                          <div class="relative mt-1.5">
                            <input id="eco-password" type="password" required minlength="6" placeholder="Minimum 6 characters" class="shizuku-input w-full border border-[#745bc7]/45 bg-white py-2.5 pr-10 pl-3.5 text-sm text-[#14100e] outline-none transition placeholder:text-[#8b7fa6] focus:border-[#745bc7] focus:ring-2 focus:ring-[#745bc7]/15" />
                            <button type="button" data-toggle-pw="eco-password" aria-label="Show password" class="absolute top-1/2 right-2.5 -translate-y-1/2 p-1 font-mono text-xs text-[#745bc7]/80 hover:text-[#745bc7]">👁</button>
                          </div>
                        </div>

                        <p id="eco-auth-error" role="alert" class="hidden border border-rose-300 bg-rose-50 px-3.5 py-2.5 text-xs leading-relaxed text-rose-700"></p>

                        <button id="eco-auth-submit" type="submit" class="vn-btn-band mt-2 w-full py-3 text-[11px] disabled:opacity-60">
                          <?= $mode === 'register' ? 'CREATE SHIZUKU ID' : 'SIGN IN' ?>
                        </button>
                      </form>

                      <div class="mt-6 flex items-center justify-center gap-2 border-t border-[#745bc7]/18 pt-4 text-center font-mono text-[10px] leading-relaxed text-[#6a5e82]">
                        <span data-en="Isolated per-user ecosystem session · Separate from Admin Studio" data-id="Sesi ekosistem terisolasi per pengguna · Terpisah dari Admin Studio">Isolated per-user ecosystem session · Separate from Admin Studio</span>
                      </div>
                    </div>
                  </section>
                <?php endif; ?>
              </main>

              <?php render_public_footer($profile); ?>
            </div>
            <?php
        }
    );
}
