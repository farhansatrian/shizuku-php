<?php
declare(strict_types=1);

function render_admin_login_page(array $profile): void
{
    render_html_shell(
        'Admin Studio Login — ' . ($profile['brandName'] ?? 'shizuku'),
        function (): void {
            ?>
            <div class="relative flex min-h-screen flex-col items-center justify-center px-5 py-12 text-[#14100e]">
              <div class="shizuku-workspace-bg" aria-hidden="true"></div>

              <div class="relative z-10 w-full max-w-[440px]">
                <a href="/" class="mb-5 inline-flex items-center gap-2 font-mono text-[11px] font-semibold tracking-[0.14em] text-[#14100e]/80 uppercase transition hover:text-[#745bc7]">
                  ← Back to shizuku
                </a>

                <div class="vn-frame overflow-hidden">
                  <div class="vn-band-strip flex items-center justify-between px-5 py-2.5 text-white">
                    <span class="font-mono text-[10px] font-semibold tracking-[0.2em] uppercase drop-shadow-2xs">SHIZUKU STUDIO // 雫</span>
                    <span class="font-mono text-[10px] font-semibold tracking-[0.14em] uppercase drop-shadow-2xs">ADMIN</span>
                  </div>

                  <div class="bg-white/96 p-6 sm:p-8">
                    <div class="flex items-center gap-3">
                      <span class="flex h-11 w-11 shrink-0 items-center justify-center border border-[#745bc7]/45 bg-gradient-to-b from-[#efe9fd] to-[#fde8f6] text-[#745bc7]">
                        <?= shizuku_umbrella_svg('h-5 w-5') ?>
                      </span>
                      <div>
                        <h1 class="font-editorial text-2xl font-bold tracking-[0.12em] text-[#14100e] uppercase">Admin Studio</h1>
                        <p class="text-xs text-[#5d5370]">Manage Profile, Work, Portfolio CV, and Certificates.</p>
                      </div>
                    </div>

                    <form id="admin-login-form" class="mt-6 space-y-4">
                      <div>
                        <label for="admin-identifier" class="block font-mono text-[10px] font-semibold tracking-[0.16em] text-[#5d5370] uppercase">Username or Email</label>
                        <input id="admin-identifier" type="text" required autofocus placeholder="farhan" class="shizuku-input mt-1.5 w-full border border-[#745bc7]/45 bg-white px-3.5 py-2.5 text-sm text-[#14100e] outline-none transition placeholder:text-[#8b7fa6] focus:border-[#745bc7] focus:ring-2 focus:ring-[#745bc7]/15" />
                      </div>

                      <div>
                        <label for="admin-password" class="block font-mono text-[10px] font-semibold tracking-[0.16em] text-[#5d5370] uppercase">Password</label>
                        <div class="relative mt-1.5">
                          <input id="admin-password" type="password" required placeholder="••••••••••••" class="shizuku-input w-full border border-[#745bc7]/45 bg-white py-2.5 pr-10 pl-3.5 text-sm text-[#14100e] outline-none transition placeholder:text-[#8b7fa6] focus:border-[#745bc7] focus:ring-2 focus:ring-[#745bc7]/15" />
                          <button type="button" data-toggle-pw="admin-password" aria-label="Toggle password visibility" class="absolute top-1/2 right-2.5 -translate-y-1/2 p-1 font-mono text-xs text-[#745bc7]/70 hover:text-[#745bc7]">👁</button>
                        </div>
                      </div>

                      <div id="admin-login-error" class="hidden border border-rose-300 bg-rose-50 px-3.5 py-2.5 text-xs text-rose-700"></div>

                      <button id="admin-login-submit" type="submit" class="vn-btn-band mt-2 w-full py-3 text-[11px] disabled:opacity-60">
                        Enter Admin Studio
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <?php
        }
    );
}
