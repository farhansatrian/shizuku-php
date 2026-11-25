<?php
declare(strict_types=1);

function render_home_page(array $data, ?array $ecoUser): void
{
    $profile = $data['profile'];
    $hasDesktopArt = trim((string) ($profile['heroImage'] ?? '')) !== '';
    $hasMobileArt = trim((string) ($profile['heroImageMobile'] ?? '')) !== '';
    $hasAnyArt = $hasDesktopArt || $hasMobileArt;

    render_html_shell(
        ($profile['brandName'] ?? 'shizuku') . ' (' . ($profile['brandKanji'] ?? '雫') . ')',
        function () use ($profile, $ecoUser, $hasDesktopArt, $hasMobileArt, $hasAnyArt): void {
            ?>
            <div class="relative min-h-screen text-[#14100e]">
              <div class="tuyu-fixed-watercolor" aria-hidden="true"></div>
              <?php shizuku_rain_layer('over-splash'); ?>

              <!-- Calm TUYU splash screen -->
              <div
                id="tuyu-splash"
                data-testid="tuyu-splash"
                role="presentation"
                class="fixed inset-0 z-[90] flex items-center justify-center transition-opacity duration-[450ms] ease-out opacity-100"
              >
                <div class="tuyu-watercolor-pan-pc absolute inset-0 hidden md:block"></div>
                <div class="tuyu-watercolor-pan-sp absolute inset-0 md:hidden"></div>
                <div id="tuyu-splash-inner" class="relative z-10 flex flex-col items-center justify-center transition-opacity duration-[800ms] ease-out opacity-100">
                  <img
                    src="/api/uploads/tuyu-umbrella.png"
                    alt="<?= h(($profile['brandName'] ?? 'shizuku') . ' loading umbrella') ?>"
                    class="w-[78px] max-w-[100px] select-none md:w-[100px]"
                  />
                </div>
              </div>

              <?php render_public_header($profile, $ecoUser, '/', true); ?>

              <main class="relative z-10">
                <!-- 1. Pure Full-Bleed Hero -->
                <section id="top" class="relative h-[100svh] w-full overflow-hidden bg-[#1a1333]">
                  <?php if ($hasAnyArt): ?>
                    <div class="absolute inset-0 z-0">
                      <?php if ($hasMobileArt): ?>
                        <img src="<?= h($profile['heroImageMobile']) ?>" alt="<?= h($profile['brandName'] . ' mobile hero') ?>" class="h-full w-full object-cover object-center md:hidden" />
                        <img src="<?= h($hasDesktopArt ? $profile['heroImage'] : $profile['heroImageMobile']) ?>" alt="<?= h($profile['brandName'] . ' desktop hero') ?>" class="hidden h-full w-full object-cover object-center md:block" />
                      <?php else: ?>
                        <img src="<?= h($profile['heroImage']) ?>" alt="<?= h($profile['brandName'] . ' hero') ?>" class="h-full w-full object-cover object-center" />
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <div class="absolute inset-0 z-0 flex flex-col items-center justify-center text-white" style="background: linear-gradient(180deg, #171229 0%, #2a1e52 55%, #745bc7 100%);">
                      <?= shizuku_umbrella_svg('h-14 w-14 text-white/90') ?>
                      <p class="mt-4 font-editorial text-5xl tracking-[0.28em] uppercase"><?= h($profile['brandName'] ?? 'shizuku') ?></p>
                    </div>
                  <?php endif; ?>
                </section>

                <!-- 2. Minimalist Gallery Showcase -->
                <section id="gallery" class="relative scroll-mt-16 pt-20 pb-24 md:pt-28 md:pb-32">
                  <div class="relative z-10 mx-auto w-[90%] max-w-[920px]">
                    <nav aria-label="Gallery navigation" class="flex items-center justify-center gap-7 sm:gap-11 md:gap-14">
                      <a href="#top" class="font-condensed text-[17px] font-normal tracking-[0.14em] text-[#14100e] uppercase transition hover:text-[#745bc7] sm:text-[19px]" data-en="HOME" data-id="BERANDA">HOME</a>
                      <a href="/workspace" class="font-condensed text-[17px] font-normal tracking-[0.14em] text-[#14100e] uppercase transition hover:text-[#745bc7] sm:text-[19px]">WORK</a>
                      <a href="/portfolio" class="font-condensed text-[17px] font-normal tracking-[0.14em] text-[#14100e] uppercase transition hover:text-[#745bc7] sm:text-[19px]">PORTFOLIO</a>
                      <a href="/about" class="font-condensed text-[17px] font-normal tracking-[0.14em] text-[#14100e] uppercase transition hover:text-[#745bc7] sm:text-[19px]" data-en="ABOUT" data-id="TENTANG">ABOUT</a>
                    </nav>

                    <div class="mx-auto mt-10 w-full max-w-[840px] md:mt-14">
                      <img
                        src="/api/uploads/IMG_20210913_0002.webp"
                        onerror="if(!this.dataset.fb){this.dataset.fb='1';this.src='/assets/IMG_20210913_0002.webp';}"
                        alt="<?= h(($profile['brandName'] ?? 'shizuku') . ' monochrome artwork') ?>"
                        class="block h-auto w-full select-none shadow-[0_18px_48px_rgba(32,20,64,0.08)]"
                      />
                    </div>
                  </div>
                </section>
              </main>

              <?php render_public_footer($profile); ?>
            </div>
            <?php
        }
    );
}
