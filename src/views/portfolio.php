<?php
declare(strict_types=1);

function render_portfolio_page(array $data, ?array $ecoUser): void
{
    $profile = $data['profile'];
    $exps = $data['experiences'];
    $certs = $data['certificates'];

    $education = array_values(array_filter($exps, fn($e) => $e['kind'] === 'EDUCATION'));
    $work = array_values(array_filter($exps, fn($e) => $e['kind'] === 'WORK'));
    $orgGroups = group_experiences_by_org(array_values(array_filter($exps, fn($e) => $e['kind'] === 'ORGANIZATION')));

    $contacts = [];
    if (!empty($profile['email'])) {
        $contacts[] = ['label' => $profile['email'], 'href' => 'mailto:' . $profile['email'], 'ext' => false];
    }
    if (!empty($profile['location'])) {
        $contacts[] = ['label' => $profile['location'], 'href' => null, 'ext' => false];
    }
    if (!empty($profile['linkedinUrl'])) {
        $contacts[] = ['label' => 'LinkedIn', 'href' => $profile['linkedinUrl'], 'ext' => true];
    }
    if (!empty($profile['githubUrl'])) {
        $contacts[] = ['label' => 'GitHub', 'href' => $profile['githubUrl'], 'ext' => true];
    }
    if (!empty($profile['resumeUrl'])) {
        $contacts[] = ['label' => 'Resume', 'href' => $profile['resumeUrl'], 'ext' => true];
    }

    render_html_shell(
        'PORTFOLIO — ' . ($profile['fullName'] ?? 'Farhan Satria Nugraha'),
        function () use ($profile, $ecoUser, $education, $work, $orgGroups, $certs, $contacts): void {
            ?>
            <div class="relative flex min-h-screen flex-col text-[#14100e]">
              <div class="shizuku-workspace-bg" aria-hidden="true"></div>
              <?php shizuku_rain_layer('behind'); ?>
              <?php render_public_header($profile, $ecoUser, '/portfolio', false); ?>

              <main class="relative z-10 mx-auto w-[92%] max-w-[820px] flex-1 pt-28 pb-24 md:pt-34 md:pb-28">
                <?php render_subpage_nav('portfolio'); ?>

                <article class="vn-frame mt-10 overflow-hidden md:mt-12">
                  <div class="vn-band-strip flex flex-wrap items-center justify-between gap-2 px-5 py-2 text-white">
                    <span class="font-mono text-[10px] font-semibold tracking-[0.2em] uppercase drop-shadow-2xs">PORTFOLIO // CURRICULUM VITAE</span>
                    <span class="font-mono text-[10px] tracking-[0.14em] uppercase drop-shadow-2xs"><?= h($profile['location'] ?? '') ?></span>
                  </div>

                  <div class="bg-white/96 px-6 py-8 font-sans text-[#1b1726] sm:px-11 sm:py-11">
                    <header>
                      <h1 class="text-[24px] font-bold tracking-[0.03em] text-[#14100e] uppercase sm:text-[28px]">
                        <?= h($profile['fullName'] ?? '') ?>
                      </h1>

                      <?php if (!empty($profile['currentRoleEn']) || !empty($profile['currentOrg'])): ?>
                        <p class="mt-1 text-[13.5px] font-medium text-[#5f47a8] sm:text-[14px]">
                          <span data-en="<?= h($profile['currentRoleEn']) ?>" data-id="<?= h($profile['currentRoleId']) ?>"><?= h($profile['currentRoleEn']) ?></span>
                          <?= !empty($profile['currentOrg']) ? ' · ' . h($profile['currentOrg']) : '' ?>
                        </p>
                      <?php endif; ?>

                      <?php if (count($contacts) > 0): ?>
                        <div class="mt-1.5 flex flex-wrap items-center text-[13px] leading-relaxed text-[#4f4663]">
                          <?php foreach ($contacts as $i => $c): ?>
                            <?php if ($i > 0): ?><span class="mx-1.5 text-[#14100e]/30 select-none">|</span><?php endif; ?>
                            <?php if ($c['href']): ?>
                              <a href="<?= h($c['href']) ?>" <?= $c['ext'] ? 'target="_blank" rel="noreferrer"' : '' ?> class="inline-flex items-center gap-0.5 transition hover:text-[#745bc7] hover:underline">
                                <?= h($c['label']) ?><?= $c['ext'] ? ' ↗' : '' ?>
                              </a>
                            <?php else: ?>
                              <span><?= h($c['label']) ?></span>
                            <?php endif; ?>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>

                      <p class="mt-4 text-[13.5px] leading-[1.68] text-[#262033] sm:text-[14px]" data-en="<?= h($profile['bioEn']) ?>" data-id="<?= h($profile['bioId']) ?>">
                        <?= h($profile['bioEn']) ?>
                      </p>
                    </header>

                    <div class="mt-7 space-y-7">
                      <?php if (count($education) > 0): ?>
                        <section id="education">
                          <h2 class="border-b border-[#14100e]/28 pb-1.5 text-[16px] font-bold tracking-[0.01em] text-[#14100e] sm:text-[17px]" data-en="Education" data-id="Pendidikan">Education</h2>
                          <div class="mt-3.5 space-y-3.5">
                            <?php foreach ($education as $edu): ?>
                              <div>
                                <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5 text-[13.5px] leading-snug sm:text-[14px]">
                                  <p class="text-[#14100e]">
                                    <span class="font-bold" data-en="<?= h($edu['roleEn']) ?>" data-id="<?= h($edu['roleId']) ?>"><?= h($edu['roleEn']) ?></span>,
                                    <span><?= h($edu['organization']) ?></span>
                                  </p>
                                  <span class="text-[13px] text-[#4f4663]" data-en="<?= h($edu['periodEn']) ?>" data-id="<?= h($edu['periodId']) ?>"><?= h($edu['periodEn']) ?></span>
                                </div>
                                <?php if ($edu['summaryEn'] !== ''): ?>
                                  <p class="mt-1 text-[13px] leading-[1.58] text-[#3a324a] sm:text-[13.5px]" data-en="<?= h($edu['summaryEn']) ?>" data-id="<?= h($edu['summaryId']) ?>"><?= h($edu['summaryEn']) ?></p>
                                <?php endif; ?>
                                <?php if (count($edu['bullets']['en']) > 0): ?>
                                  <ul class="mt-1.5 list-disc space-y-1 pl-5 text-[13px] leading-[1.58] text-[#2d263b] sm:text-[13.5px]">
                                    <?php foreach ($edu['bullets']['en'] as $bi => $bEn):
                                      $bId = $edu['bullets']['id'][$bi] ?? $bEn;
                                    ?>
                                      <li data-en="<?= h($bEn) ?>" data-id="<?= h($bId) ?>"><?= h($bEn) ?></li>
                                    <?php endforeach; ?>
                                  </ul>
                                <?php endif; ?>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </section>
                      <?php endif; ?>

                      <?php if (count($work) > 0): ?>
                        <section id="work-experience">
                          <h2 class="border-b border-[#14100e]/28 pb-1.5 text-[16px] font-bold tracking-[0.01em] text-[#14100e] sm:text-[17px]" data-en="Work Experience" data-id="Pengalaman Kerja">Work Experience</h2>
                          <div class="mt-3.5 space-y-5">
                            <?php foreach ($work as $w): ?>
                              <div>
                                <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5 text-[13.5px] sm:text-[14px]">
                                  <h3 class="font-bold text-[#14100e]" data-en="<?= h($w['roleEn']) ?>" data-id="<?= h($w['roleId']) ?>"><?= h($w['roleEn']) ?></h3>
                                  <span class="text-[13px] text-[#4f4663]">(<span data-en="<?= h($w['periodEn']) ?>" data-id="<?= h($w['periodId']) ?>"><?= h($w['periodEn']) ?></span>)</span>
                                </div>
                                <p class="mt-0.5 text-[13px] font-medium text-[#524669] sm:text-[13.5px]"><?= h($w['organization']) ?></p>
                                <?php if ($w['summaryEn'] !== ''): ?>
                                  <p class="mt-1.5 text-[13px] leading-[1.6] text-[#2d263b] sm:text-[13.5px]" data-en="<?= h($w['summaryEn']) ?>" data-id="<?= h($w['summaryId']) ?>"><?= h($w['summaryEn']) ?></p>
                                <?php endif; ?>
                                <?php if (count($w['bullets']['en']) > 0): ?>
                                  <ul class="mt-1.5 list-disc space-y-1 pl-5 text-[13px] leading-[1.6] text-[#2d263b] sm:text-[13.5px]">
                                    <?php foreach ($w['bullets']['en'] as $bi => $bEn):
                                      $bId = $w['bullets']['id'][$bi] ?? $bEn;
                                    ?>
                                      <li data-en="<?= h($bEn) ?>" data-id="<?= h($bId) ?>"><?= h($bEn) ?></li>
                                    <?php endforeach; ?>
                                  </ul>
                                <?php endif; ?>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </section>
                      <?php endif; ?>

                      <?php if (count($orgGroups) > 0): ?>
                        <section id="organizational-experience">
                          <h2 class="border-b border-[#14100e]/28 pb-1.5 text-[16px] font-bold tracking-[0.01em] text-[#14100e] sm:text-[17px]" data-en="Organizational Experience" data-id="Pengalaman Organisasi &amp; Mengajar">Organizational Experience</h2>
                          <div class="mt-3.5 space-y-5">
                            <?php foreach ($orgGroups as $group): ?>
                              <div class="space-y-3.5">
                                <?php foreach ($group['entries'] as $ei => $entry): ?>
                                  <div>
                                    <?php if ($ei === 0): ?>
                                      <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5 text-[13.5px] sm:text-[14px]">
                                        <h3 class="font-bold text-[#14100e]"><?= h($group['organization']) ?></h3>
                                        <span class="text-[13px] text-[#4f4663]">(<span data-en="<?= h($entry['periodEn']) ?>" data-id="<?= h($entry['periodId']) ?>"><?= h($entry['periodEn']) ?></span>)</span>
                                      </div>
                                      <p class="mt-0.5 text-[13px] font-medium text-[#524669] sm:text-[13.5px]" data-en="<?= h($entry['roleEn']) ?>" data-id="<?= h($entry['roleId']) ?>"><?= h($entry['roleEn']) ?></p>
                                    <?php else: ?>
                                      <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5 text-[13px] sm:text-[13.5px]">
                                        <p class="font-medium text-[#3b3150]" data-en="<?= h($entry['roleEn']) ?>" data-id="<?= h($entry['roleId']) ?>"><?= h($entry['roleEn']) ?></p>
                                        <span class="text-[13px] text-[#4f4663]">(<span data-en="<?= h($entry['periodEn']) ?>" data-id="<?= h($entry['periodId']) ?>"><?= h($entry['periodEn']) ?></span>)</span>
                                      </div>
                                    <?php endif; ?>
                                    <?php if ($entry['summaryEn'] !== ''): ?>
                                      <p class="mt-1.5 text-[13px] leading-[1.6] text-[#2d263b] sm:text-[13.5px]" data-en="<?= h($entry['summaryEn']) ?>" data-id="<?= h($entry['summaryId']) ?>"><?= h($entry['summaryEn']) ?></p>
                                    <?php endif; ?>
                                    <?php if (count($entry['bullets']['en']) > 0): ?>
                                      <ul class="mt-1.5 list-disc space-y-1 pl-5 text-[13px] leading-[1.6] text-[#2d263b] sm:text-[13.5px]">
                                        <?php foreach ($entry['bullets']['en'] as $bi => $bEn):
                                          $bId = $entry['bullets']['id'][$bi] ?? $bEn;
                                        ?>
                                          <li data-en="<?= h($bEn) ?>" data-id="<?= h($bId) ?>"><?= h($bEn) ?></li>
                                        <?php endforeach; ?>
                                      </ul>
                                    <?php endif; ?>
                                  </div>
                                <?php endforeach; ?>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </section>
                      <?php endif; ?>

                      <?php if (count($certs) > 0): ?>
                        <section id="certificates">
                          <h2 class="border-b border-[#14100e]/28 pb-1.5 text-[16px] font-bold tracking-[0.01em] text-[#14100e] sm:text-[17px]" data-en="Certificates" data-id="Sertifikat">Certificates</h2>
                          <ul class="mt-3.5 list-disc space-y-1.5 pl-5 text-[13px] leading-[1.6] text-[#262033] sm:text-[13.5px]">
                            <?php foreach ($certs as $cert): ?>
                              <li>
                                <button
                                  type="button"
                                  data-cert-json="<?= h(json_encode($cert, JSON_UNESCAPED_UNICODE)) ?>"
                                  class="inline text-left transition hover:text-[#745bc7] focus:outline-none"
                                >
                                  <span class="font-bold text-[#14100e] underline-offset-4 hover:text-[#745bc7] hover:underline" data-en="<?= h($cert['titleEn']) ?>" data-id="<?= h($cert['titleId']) ?>"><?= h($cert['titleEn']) ?></span>
                                  <span class="text-[#4f4663]"> (<?= h($cert['issuedDate']) ?>)</span>
                                  — <span><?= h($cert['issuer']) ?></span>
                                </button>
                              </li>
                            <?php endforeach; ?>
                          </ul>
                        </section>
                      <?php endif; ?>
                    </div>
                  </div>
                </article>
              </main>

              <!-- Certificate Lightbox Modal -->
              <div id="cert-modal" class="fixed inset-0 z-[80] hidden items-center justify-center bg-[#19132b]/65 p-4 backdrop-blur-sm">
                <div class="vn-frame w-full max-w-lg overflow-hidden" onclick="event.stopPropagation()">
                  <div class="vn-band-strip flex items-center justify-between px-5 py-3 text-white">
                    <span id="cert-modal-cat" class="font-mono text-[10px] font-semibold tracking-[0.18em] uppercase">CERTIFICATE</span>
                    <button type="button" id="cert-modal-close" class="border border-white/55 bg-white/15 px-2 py-0.5 font-mono text-xs text-white">✕</button>
                  </div>
                  <div class="space-y-4 bg-white p-6">
                    <img id="cert-modal-img" src="" alt="" class="hidden max-h-64 w-full border border-[#745bc7]/30 object-contain bg-[#faf7ff]" />
                    <div>
                      <h3 id="cert-modal-title" class="text-lg font-bold text-[#14100e]"></h3>
                      <p id="cert-modal-meta" class="mt-1 text-xs text-[#5d5370]"></p>
                    </div>
                    <a id="cert-modal-link" href="#" target="_blank" rel="noreferrer" class="vn-btn-band hidden px-4 py-2 text-[10px]">VERIFY CREDENTIAL ↗</a>
                  </div>
                </div>
              </div>

              <?php render_public_footer($profile); ?>
            </div>
            <?php
        }
    );
}
