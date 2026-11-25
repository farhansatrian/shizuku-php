(() => {
  const LOCALE_KEY = "shizuku_locale";
  let locale = localStorage.getItem(LOCALE_KEY) === "id" ? "id" : "en";

  function applyLocale(next) {
    locale = next === "id" ? "id" : "en";
    localStorage.setItem(LOCALE_KEY, locale);
    document.documentElement.lang = locale;

    document.querySelectorAll("[data-en][data-id]").forEach((el) => {
      el.textContent = locale === "id" ? el.dataset.id : el.dataset.en;
    });

    document.querySelectorAll("[data-locale-btn]").forEach((btn) => {
      const isMatch = btn.dataset.localeBtn === locale;
      btn.classList.toggle("opacity-100", isMatch);
      btn.classList.toggle("opacity-60", !isMatch);
    });

    // Keep Account page heading/submit synced when switching EN | ID
    const af = document.getElementById("eco-auth-form");
    if (af) {
      const isReg = (af.dataset.mode || "login") === "register";
      const hd = document.getElementById("auth-heading");
      const bm = document.getElementById("auth-band-mode");
      const sb = document.getElementById("eco-auth-submit");
      if (hd) hd.textContent = isReg ? (locale === "id" ? "BUAT AKUN" : "CREATE ACCOUNT") : (locale === "id" ? "MASUK" : "SIGN IN");
      if (bm) bm.textContent = isReg ? (locale === "id" ? "DAFTAR" : "REGISTER") : (locale === "id" ? "MASUK" : "SIGN IN");
      if (sb) sb.textContent = isReg ? (locale === "id" ? "BUAT SHIZUKU ID" : "CREATE SHIZUKU ID") : (locale === "id" ? "MASUK" : "SIGN IN");
    }
  }

  document.querySelectorAll("[data-locale-btn]").forEach((btn) => {
    btn.addEventListener("click", () => applyLocale(btn.dataset.localeBtn));
  });
  applyLocale(locale);

  // 1. TUYU Splash Preloader (plays once per browser tab session so clicking HOME stays snappy)
  const splash = document.getElementById("tuyu-splash");
  const splashInner = document.getElementById("tuyu-splash-inner");
  if (splash && splashInner) {
    const dismissSplash = () => {
      try {
        sessionStorage.setItem("shizuku_splash_seen", "1");
      } catch {
        // ignore
      }
      splash.remove();
    };
    let alreadySeen = false;
    try {
      alreadySeen = sessionStorage.getItem("shizuku_splash_seen") === "1";
    } catch {
      alreadySeen = false;
    }
    if (alreadySeen) {
      splash.remove();
    } else {
      splash.addEventListener("click", dismissSplash);
      const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      window.setTimeout(() => {
        try {
          sessionStorage.setItem("shizuku_splash_seen", "1");
        } catch {
          // ignore
        }
        splashInner.classList.remove("opacity-100");
        splashInner.classList.add("opacity-0");
        window.setTimeout(() => {
          splash.classList.remove("opacity-100");
          splash.classList.add("opacity-0", "pointer-events-none");
          window.setTimeout(dismissSplash, reduced ? 150 : 460);
        }, reduced ? 150 : 850);
      }, reduced ? 350 : 1200);
    }
  }

  // 2. Transparent-on-Hero Header + Full-Screen Drawer
  const header = document.getElementById("shizuku-header");
  const drawer = document.getElementById("shizuku-drawer");
  const toggleBtn = document.getElementById("drawer-toggle-btn");
  const iconOpen = document.getElementById("drawer-icon-open");
  const iconClose = document.getElementById("drawer-icon-close");
  const headerSocials = document.getElementById("header-socials");
  const headerEcoBtn = document.getElementById("header-eco-btn");

  let drawerOpen = false;

  function syncHeaderTheme() {
    if (!header) return;
    const transparentOnTop = header.dataset.transparentTop === "1";
    const overHero =
      transparentOnTop &&
      window.scrollY < Math.max(window.innerHeight - 90, 260);
    const isHeroWhite = overHero && !drawerOpen;

    header.classList.remove(
      "bg-transparent",
      "text-[#745bc7]",
      "bg-gradient-to-b",
      "from-[#17112a]/55",
      "via-[#17112a]/20",
      "to-transparent",
      "text-white",
      "[text-shadow:0_1px_8px_rgba(20,16,14,0.45)]",
      "bg-[#e5dcfb]/88",
      "backdrop-blur-md"
    );

    if (drawerOpen) {
      header.classList.add("bg-transparent", "text-[#745bc7]");
    } else if (isHeroWhite) {
      header.classList.add(
        "bg-gradient-to-b",
        "from-[#17112a]/55",
        "via-[#17112a]/20",
        "to-transparent",
        "text-white",
        "[text-shadow:0_1px_8px_rgba(20,16,14,0.45)]"
      );
    } else {
      header.classList.add("bg-[#e5dcfb]/88", "text-[#745bc7]", "backdrop-blur-md");
    }

    if (iconOpen) {
      iconOpen.classList.toggle("brightness-0", isHeroWhite);
      iconOpen.classList.toggle("invert", isHeroWhite);
    }

    if (headerEcoBtn) {
      const signedIn = header.dataset.ecoSignedIn === "1";
      headerEcoBtn.classList.remove(
        "border-white/85",
        "bg-white/18",
        "border-white/70",
        "bg-white/10",
        "text-white",
        "backdrop-blur-sm",
        "hover:bg-white",
        "hover:text-[#745bc7]",
        "border-[#745bc7]",
        "bg-[#745bc7]",
        "bg-white/85",
        "text-[#745bc7]",
        "shadow-[2px_2px_0_0_rgba(116,91,199,0.22)]",
        "shadow-[2px_2px_0_0_rgba(116,91,199,0.16)]",
        "hover:bg-[#5f47a8]",
        "hover:bg-[#745bc7]",
        "hover:text-white"
      );
      if (signedIn) {
        if (isHeroWhite) {
          headerEcoBtn.classList.add("border-white/85", "bg-white/18", "text-white", "backdrop-blur-sm", "hover:bg-white", "hover:text-[#745bc7]");
        } else {
          headerEcoBtn.classList.add("border-[#745bc7]", "bg-[#745bc7]", "text-white", "shadow-[2px_2px_0_0_rgba(116,91,199,0.22)]", "hover:bg-[#5f47a8]");
        }
      } else if (isHeroWhite) {
        headerEcoBtn.classList.add("border-white/70", "bg-white/10", "text-white", "backdrop-blur-sm", "hover:bg-white", "hover:text-[#745bc7]");
      } else {
        headerEcoBtn.classList.add("border-[#745bc7]", "bg-white/85", "text-[#745bc7]", "shadow-[2px_2px_0_0_rgba(116,91,199,0.16)]", "hover:bg-[#745bc7]", "hover:text-white");
      }
    }
  }

  function setDrawer(open) {
    drawerOpen = open;
    if (drawer) {
      drawer.setAttribute("aria-hidden", open ? "false" : "true");
      drawer.classList.toggle("pointer-events-auto", open);
      drawer.classList.toggle("opacity-100", open);
      drawer.classList.toggle("pointer-events-none", !open);
      drawer.classList.toggle("opacity-0", !open);
    }
    if (toggleBtn) toggleBtn.setAttribute("aria-expanded", open ? "true" : "false");
    if (iconOpen) iconOpen.classList.toggle("hidden", open);
    if (iconClose) iconClose.classList.toggle("hidden", !open);
    if (headerSocials) headerSocials.classList.toggle("lg:flex", !open);
    if (headerEcoBtn) headerEcoBtn.classList.toggle("hidden", open);
    document.body.style.overflow = open ? "hidden" : "";
    syncHeaderTheme();
  }

  if (toggleBtn) {
    toggleBtn.addEventListener("click", () => setDrawer(!drawerOpen));
  }
  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && drawerOpen) setDrawer(false);
  });
  window.addEventListener("scroll", syncHeaderTheme, { passive: true });
  window.addEventListener("resize", syncHeaderTheme, { passive: true });
  syncHeaderTheme();

  // 3. WIB Jakarta Live Clock on /workspace
  const clockTime = document.getElementById("wib-clock-time");
  const clockDate = document.getElementById("wib-clock-date");
  if (clockTime && clockDate) {
    const updateClock = () => {
      try {
        const now = new Date();
        clockTime.textContent = new Intl.DateTimeFormat(locale === "id" ? "id-ID" : "en-US", {
          timeZone: "Asia/Jakarta",
          hour: "2-digit",
          minute: "2-digit",
          hour12: false,
        }).format(now);
        clockDate.textContent = new Intl.DateTimeFormat(locale === "id" ? "id-ID" : "en-US", {
          timeZone: "Asia/Jakarta",
          weekday: "short",
          day: "numeric",
          month: "short",
          year: "numeric",
        }).format(now);
      } catch {
        // Keep server fallback
      }
    };
    updateClock();
    window.setInterval(updateClock, 30000);
  }

  // 4. Visual Novel Dialogue Typewriter on /workspace
  const vnBox = document.getElementById("vn-dialogue-box");
  const vnText = document.getElementById("vn-dialogue-text");
  if (vnBox && vnText) {
    let lines = [];
    try {
      lines = JSON.parse(vnBox.dataset.dialogues || "[]");
    } catch {
      lines = [];
    }
    let idx = 0;
    let timer = null;
    let typing = false;

    const getLine = () => {
      const item = lines[idx % Math.max(1, lines.length)];
      if (!item) return "";
      return locale === "id" ? item.id : item.en;
    };

    const startTyping = () => {
      const full = getLine();
      if (timer) window.clearInterval(timer);
      if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        vnText.textContent = full;
        typing = false;
        return;
      }
      let pos = 0;
      typing = true;
      vnText.textContent = "";
      timer = window.setInterval(() => {
        pos += 2;
        if (pos >= full.length) {
          vnText.textContent = full;
          typing = false;
          window.clearInterval(timer);
          timer = null;
        } else {
          vnText.textContent = full.slice(0, pos);
        }
      }, 18);
    };

    const advance = () => {
      if (typing) {
        if (timer) window.clearInterval(timer);
        timer = null;
        typing = false;
        vnText.textContent = getLine();
      } else {
        idx += 1;
        startTyping();
      }
    };

    vnBox.addEventListener("click", advance);
    vnBox.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        advance();
      }
    });
    startTyping();
  }

  // 5. Ecosystem Sign Out buttons
  document.querySelectorAll("[data-eco-signout]").forEach((btn) => {
    btn.addEventListener("click", async () => {
      btn.disabled = true;
      await fetch("/api/ecosystem/logout", { method: "POST", credentials: "include" });
      window.location.reload();
    });
  });

  // 6. Password Eye Toggles
  document.querySelectorAll("[data-toggle-pw]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const input = document.getElementById(btn.dataset.togglePw);
      if (!input) return;
      input.type = input.type === "password" ? "text" : "password";
    });
  });

  // 7. Account Sign In / Register Form
  const authForm = document.getElementById("eco-auth-form");
  if (authForm) {
    const errBox = document.getElementById("eco-auth-error");
    const nameWrap = document.getElementById("eco-name-wrap");
    const heading = document.getElementById("auth-heading");
    const bandMode = document.getElementById("auth-band-mode");
    const submitBtn = document.getElementById("eco-auth-submit");
    const tabBtns = document.querySelectorAll("[data-auth-tab]");

    const errorCopy = (code) => {
      const isId = locale === "id";
      switch (code) {
        case "INVALID_EMAIL":
          return isId ? "Masukkan alamat email yang valid." : "Please enter a valid email address.";
        case "SHORT_PASSWORD":
          return isId ? "Kata sandi minimal 6 karakter." : "Password must be at least 6 characters.";
        case "EMAIL_EXISTS":
          return isId ? "Email ini sudah terdaftar di SHIZUKU ID. Silakan masuk." : "This email is already registered with SHIZUKU ID. Try signing in instead.";
        case "ACCOUNT_NOT_FOUND":
          return isId ? "Akun SHIZUKU ID belum ditemukan. Daftar dulu." : "No SHIZUKU ID found with this email. Create a new account first.";
        case "INVALID_CREDENTIALS":
          return isId ? "Email atau kata sandi tidak sesuai. Jika sebelumnya daftar pakai Google, gunakan Lanjutkan dengan Google." : "Incorrect email or password. If you signed up with Google, use Continue with Google.";
        case "GOOGLE_NOT_CONFIGURED":
          return isId ? "Login Google belum dikonfigurasi. Isi GOOGLE_CLIENT_ID & GOOGLE_CLIENT_SECRET di .env." : "Google Sign-In is not configured yet. Add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET to .env.";
        case "GOOGLE_CANCELLED":
          return isId ? "Proses masuk dengan Google dibatalkan." : "Google sign-in was cancelled.";
        case "GOOGLE_EMAIL_UNVERIFIED":
          return isId ? "Email akun Google harus terverifikasi." : "Your Google account email must be verified.";
        case "GOOGLE_AUTH_FAILED":
          return isId ? "Gagal masuk dengan Google. Coba lagi." : "Could not complete Google sign-in. Please try again.";
        default:
          return isId ? "Autentikasi gagal. Periksa kembali data kamu." : "Authentication failed. Please check your details and try again.";
      }
    };

    const showErr = (code) => {
      if (!errBox) return;
      if (!code) {
        errBox.classList.add("hidden");
        errBox.textContent = "";
      } else {
        errBox.textContent = errorCopy(code);
        errBox.classList.remove("hidden");
      }
    };

    const setMode = (nextMode) => {
      authForm.dataset.mode = nextMode;
      const isReg = nextMode === "register";
      if (nameWrap) nameWrap.classList.toggle("hidden", !isReg);
      if (heading) heading.textContent = isReg ? (locale === "id" ? "BUAT AKUN" : "CREATE ACCOUNT") : (locale === "id" ? "MASUK" : "SIGN IN");
      if (bandMode) bandMode.textContent = isReg ? (locale === "id" ? "DAFTAR" : "REGISTER") : (locale === "id" ? "MASUK" : "SIGN IN");
      if (submitBtn) submitBtn.textContent = isReg ? (locale === "id" ? "BUAT SHIZUKU ID" : "CREATE SHIZUKU ID") : (locale === "id" ? "MASUK" : "SIGN IN");
      tabBtns.forEach((b) => {
        const active = b.dataset.authTab === nextMode;
        b.classList.toggle("bg-[#745bc7]", active);
        b.classList.toggle("text-white", active);
        b.classList.toggle("text-[#5d5370]", !active);
      });
      showErr("");
    };

    tabBtns.forEach((b) => b.addEventListener("click", () => setMode(b.dataset.authTab)));

    if (authForm.dataset.oauthError) {
      showErr(authForm.dataset.oauthError);
    }

    authForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      showErr("");
      const mode = authForm.dataset.mode || "login";
      const email = (document.getElementById("eco-email")?.value || "").trim();
      const password = document.getElementById("eco-password")?.value || "";
      const displayName = (document.getElementById("eco-display-name")?.value || "").trim();

      if (!email || !email.includes("@")) return showErr("INVALID_EMAIL");
      if (password.length < 6) return showErr("SHORT_PASSWORD");

      if (submitBtn) submitBtn.disabled = true;
      try {
        const endpoint = mode === "register" ? "/api/ecosystem/register" : "/api/ecosystem/login";
        const body = mode === "register"
          ? { email, password, ...(displayName ? { displayName } : {}) }
          : { email, password };
        const res = await fetch(endpoint, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "include",
          body: JSON.stringify(body),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.user) {
          showErr(data.error || "AUTH_FAILED");
          return;
        }
        const target = authForm.dataset.redirect || "/account";
        window.location.href = target;
      } catch {
        showErr("NETWORK_ERROR");
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  // 8. Portfolio Certificate Modal
  const certModal = document.getElementById("cert-modal");
  if (certModal) {
    const closeModal = () => {
      certModal.classList.add("hidden");
      certModal.classList.remove("flex");
    };
    certModal.addEventListener("click", closeModal);
    document.getElementById("cert-modal-close")?.addEventListener("click", closeModal);
    window.addEventListener("keydown", (e) => {
      if (e.key === "Escape") closeModal();
    });
    document.querySelectorAll("[data-cert-json]").forEach((btn) => {
      btn.addEventListener("click", () => {
        try {
          const c = JSON.parse(btn.dataset.certJson || "{}");
          document.getElementById("cert-modal-cat").textContent = c.category || "CERTIFICATE";
          document.getElementById("cert-modal-title").textContent = locale === "id" ? (c.titleId || c.titleEn) : c.titleEn;
          document.getElementById("cert-modal-meta").textContent = `${c.issuer} · ${c.issuedDate}${c.credentialId ? " · ID: " + c.credentialId : ""}`;
          const img = document.getElementById("cert-modal-img");
          if (c.imageUrl) {
            img.src = c.imageUrl;
            img.alt = c.titleEn || "";
            img.classList.remove("hidden");
          } else {
            img.classList.add("hidden");
          }
          const link = document.getElementById("cert-modal-link");
          if (c.credentialUrl) {
            link.href = c.credentialUrl;
            link.classList.remove("hidden");
          } else {
            link.classList.add("hidden");
          }
          certModal.classList.remove("hidden");
          certModal.classList.add("flex");
        } catch {
          // ignore
        }
      });
    });
  }

  // 9. Admin Login Form
  const adminLoginForm = document.getElementById("admin-login-form");
  if (adminLoginForm) {
    adminLoginForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const errEl = document.getElementById("admin-login-error");
      const btn = document.getElementById("admin-login-submit");
      if (errEl) errEl.classList.add("hidden");
      if (btn) btn.disabled = true;
      try {
        const res = await fetch("/api/auth/login", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            identifier: document.getElementById("admin-identifier")?.value || "",
            password: document.getElementById("admin-password")?.value || "",
          }),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.error || "Sign in failed");
        window.location.href = "/admin";
      } catch (err) {
        if (errEl) {
          errEl.textContent = err instanceof Error ? err.message : "Sign in failed";
          errEl.classList.remove("hidden");
        }
      } finally {
        if (btn) btn.disabled = false;
      }
    });
  }
})();
