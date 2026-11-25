<?php
declare(strict_types=1);

function render_admin_studio_page(array $data, array $adminUser): void
{
    $profile = $data['profile'];
    $bootstrap = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    render_html_shell(
        'Admin Studio — ' . ($profile['brandName'] ?? 'shizuku'),
        function () use ($profile, $adminUser, $bootstrap): void {
            ?>
            <div class="relative min-h-screen overflow-x-hidden text-[#14100e]">
              <div class="shizuku-workspace-bg" aria-hidden="true"></div>

              <header class="sticky top-0 z-40 border-b border-[#745bc7]/25 bg-[#fdf9ff]/92 backdrop-blur-md">
                <div class="mx-auto flex h-14 max-w-[960px] items-center justify-between px-4 sm:px-6">
                  <div class="flex min-w-0 items-center gap-2">
                    <?= shizuku_umbrella_svg('h-5 w-5 shrink-0 text-[#745bc7]') ?>
                    <span class="truncate font-editorial text-base font-bold tracking-[0.14em] text-[#745bc7] uppercase sm:text-lg sm:tracking-[0.2em]">
                      <?= h($profile['brandName'] ?? 'shizuku') ?>
                      <span class="hidden sm:inline">STUDIO</span>
                    </span>
                    <span class="hidden font-mono text-[10px] text-[#5d5370] sm:inline">· @<?= h($adminUser['username']) ?></span>
                  </div>

                  <div class="flex shrink-0 items-center gap-2">
                    <a href="/" target="_blank" class="vn-btn-action px-3 py-1.5 text-[10px]">
                      <span>View Site ↗</span>
                    </a>
                    <button id="admin-logout-btn" type="button" class="inline-flex items-center gap-1.5 border-[1.5px] border-rose-400 bg-white px-3 py-1.5 font-mono text-[10px] font-semibold tracking-[0.12em] text-rose-600 uppercase transition hover:bg-rose-600 hover:text-white">
                      <span>Sign Out</span>
                    </button>
                  </div>
                </div>
              </header>

              <div class="relative z-10 mx-auto max-w-[960px] px-4 py-6 sm:px-6 md:py-8">
                <!-- Exact 5-Tab Bar: 2-Row Zero-Truncation Grid on Mobile, 1-Row on Desktop -->
                <nav id="admin-tabs" aria-label="Admin sections" class="mb-6 grid grid-cols-6 border-[1.5px] border-[#745bc7] bg-[#fdf9ff]/95 shadow-[3px_3px_0_0_rgba(116,91,199,0.16)] md:flex">
                  <button type="button" data-admin-tab="profile" class="order-1 col-span-2 flex items-center justify-center gap-1.5 bg-[#745bc7] px-2 py-2.5 font-mono text-[10px] font-semibold tracking-[0.05em] text-white uppercase transition sm:text-[10.5px] md:order-none md:flex-1 md:px-3.5 md:text-[11px] md:tracking-[0.08em]">
                    <span class="whitespace-nowrap">Profile</span>
                  </button>
                  <button type="button" data-admin-tab="workspace" class="order-2 col-span-2 flex items-center justify-center gap-1.5 border-l border-[#745bc7]/30 px-2 py-2.5 font-mono text-[10px] font-semibold tracking-[0.05em] text-[#5d5370] uppercase transition hover:bg-[#efe9fd] hover:text-[#14100e] sm:text-[10.5px] md:order-none md:flex-1 md:px-3.5 md:text-[11px] md:tracking-[0.08em]">
                    <span class="whitespace-nowrap">Work</span>
                    <span data-count-badge="workspace" class="shrink-0 bg-[#e5dcfb] px-1.5 py-0.2 text-[10px] text-[#745bc7]">0</span>
                  </button>
                  <button type="button" data-admin-tab="experiences" class="order-4 col-span-3 flex items-center justify-center gap-1.5 border-t border-[#745bc7]/30 px-2 py-2.5 font-mono text-[10px] font-semibold tracking-[0.05em] text-[#5d5370] uppercase transition hover:bg-[#efe9fd] hover:text-[#14100e] sm:text-[10.5px] md:order-none md:flex-1 md:border-t-0 md:border-l md:px-3.5 md:text-[11px] md:tracking-[0.08em]">
                    <span class="whitespace-nowrap">Portfolio</span>
                    <span data-count-badge="experiences" class="shrink-0 bg-[#e5dcfb] px-1.5 py-0.2 text-[10px] text-[#745bc7]">0</span>
                  </button>
                  <button type="button" data-admin-tab="certificates" class="order-5 col-span-3 flex items-center justify-center gap-1.5 border-t border-l border-[#745bc7]/30 px-2 py-2.5 font-mono text-[10px] font-semibold tracking-[0.05em] text-[#5d5370] uppercase transition hover:bg-[#efe9fd] hover:text-[#14100e] sm:text-[10.5px] md:order-none md:flex-1 md:border-t-0 md:px-3.5 md:text-[11px] md:tracking-[0.08em]">
                    <span class="whitespace-nowrap">Certificates</span>
                    <span data-count-badge="certificates" class="shrink-0 bg-[#e5dcfb] px-1.5 py-0.2 text-[10px] text-[#745bc7]">0</span>
                  </button>
                  <button type="button" data-admin-tab="security" class="order-3 col-span-2 flex items-center justify-center gap-1.5 border-l border-[#745bc7]/30 px-2 py-2.5 font-mono text-[10px] font-semibold tracking-[0.05em] text-[#5d5370] uppercase transition hover:bg-[#efe9fd] hover:text-[#14100e] sm:text-[10.5px] md:order-none md:flex-1 md:px-3.5 md:text-[11px] md:tracking-[0.08em]">
                    <span class="whitespace-nowrap">Security</span>
                  </button>
                </nav>

                <main class="vn-frame overflow-hidden">
                  <div id="admin-root" class="bg-white/95 p-5 sm:p-7"></div>
                </main>
              </div>

              <!-- Modal Host -->
              <div id="admin-modal-host"></div>

              <!-- Floating Toast -->
              <div id="admin-toast" class="fixed right-5 bottom-5 z-[95] hidden items-center gap-2 border-[1.5px] border-[#745bc7] bg-[#fdf9ff]/95 px-4 py-2.5 font-mono text-[11px] font-medium text-[#14100e] shadow-[3px_3px_0_0_rgba(116,91,199,0.22)] backdrop-blur-md"></div>
            </div>

            <script id="admin-bootstrap" type="application/json"><?= str_replace('</', '<\/', (string) $bootstrap) ?></script>
            <?php
        },
        ['/js/admin.js?v=1']
    );
}
