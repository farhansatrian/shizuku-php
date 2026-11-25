<?php
declare(strict_types=1);

function render_about_page(array $data, ?array $ecoUser): void
{
    $profile = $data['profile'];
    $enParas = array_values(array_filter(array_map('trim', preg_split('/\n+/', (string) ($profile['aboutStoryEn'] ?? '')) ?: [])));
    $idParas = array_values(array_filter(array_map('trim', preg_split('/\n+/', (string) ($profile['aboutStoryId'] ?? '')) ?: [])));

    render_html_shell(
        'ABOUT — ' . ($profile['brandName'] ?? 'shizuku') . ' (' . ($profile['brandKanji'] ?? '雫') . ')',
        function () use ($profile, $ecoUser, $enParas, $idParas): void {
            ?>
            <div class="relative flex min-h-screen flex-col text-[#14100e]">
              <div class="shizuku-workspace-bg" aria-hidden="true"></div>
              <?php shizuku_rain_layer('behind'); ?>
              <?php render_public_header($profile, $ecoUser, '/about', false); ?>

              <main class="relative z-10 mx-auto flex w-[90%] max-w-[760px] flex-1 flex-col pt-28 pb-20 md:pt-34 md:pb-24">
                <?php render_subpage_nav('about'); ?>

                <section class="flex flex-1 flex-col items-center justify-center py-10 text-center md:py-14">
                  <div class="h-[220px] w-[220px] overflow-hidden border border-[#14100e]/16 bg-white/85 shadow-[0_18px_44px_-30px_rgba(20,16,14,0.45)] sm:h-[260px] sm:w-[260px]">
                    <?php if (trim((string) ($profile['avatarImage'] ?? '')) !== ''): ?>
                      <img src="<?= h($profile['avatarImage']) ?>" alt="<?= h($profile['brandName'] ?? 'shizuku') ?>" class="h-full w-full object-cover" />
                    <?php else: ?>
                      <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-b from-[#faf6ff] to-[#fdebf6] p-6">
                        <img src="/api/uploads/tuyu-umbrella.png" alt="SHIZUKU umbrella" class="h-24 w-auto object-contain select-none" />
                        <p class="mt-4 font-editorial text-xl tracking-[0.28em] text-[#745bc7] uppercase">
                          <?= h($profile['brandName'] ?? 'shizuku') ?> (<?= h($profile['brandKanji'] ?? '雫') ?>)
                        </p>
                      </div>
                    <?php endif; ?>
                  </div>

                  <div class="font-condensed mt-8 max-w-[520px] space-y-5 px-2 text-[18px] leading-[1.72] tracking-[0.025em] text-[#14100e] sm:mt-9 sm:text-[20px]">
                    <?php foreach ($enParas as $i => $paraEn):
                      $paraId = $idParas[$i] ?? $paraEn;
                    ?>
                      <p class="<?= $i === 0 ? 'font-medium' : 'font-normal text-[#14100e]/88' ?>" data-en="<?= h($paraEn) ?>" data-id="<?= h($paraId) ?>">
                        <?= h($paraEn) ?>
                      </p>
                    <?php endforeach; ?>
                  </div>
                </section>
              </main>

              <?php render_public_footer($profile); ?>
            </div>
            <?php
        }
    );
}
