(() => {
  const bootEl = document.getElementById("admin-bootstrap");
  const root = document.getElementById("admin-root");
  const modalHost = document.getElementById("admin-modal-host");
  const toastEl = document.getElementById("admin-toast");
  if (!bootEl || !root) return;

  const state = JSON.parse(bootEl.textContent || "{}");
  let activeTab = "profile";
  let profileLang = "en";
  let toastTimer = null;

  const esc = (s) =>
    String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");

  function notify(msg, tone = "ok") {
    if (!toastEl) return;
    toastEl.textContent = (tone === "err" ? "✕ " : "✓ ") + msg;
    toastEl.classList.remove("hidden", "border-rose-500", "bg-rose-50/95", "text-rose-700", "border-[#745bc7]", "bg-[#fdf9ff]/95", "text-[#14100e]");
    toastEl.classList.add("flex");
    if (tone === "err") {
      toastEl.classList.add("border-rose-500", "bg-rose-50/95", "text-rose-700");
    } else {
      toastEl.classList.add("border-[#745bc7]", "bg-[#fdf9ff]/95", "text-[#14100e]");
    }
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toastEl.classList.add("hidden");
      toastEl.classList.remove("flex");
    }, 3200);
  }

  async function api(url, opts = {}) {
    const res = await fetch(url, {
      credentials: "include",
      ...opts,
      headers: {
        ...(opts.body instanceof FormData ? {} : { "Content-Type": "application/json" }),
        ...(opts.headers || {}),
      },
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.error || "Request failed");
    return data;
  }

  async function uploadImage(file) {
    const fd = new FormData();
    fd.append("file", file);
    const json = await api("/api/admin/upload", { method: "POST", body: fd });
    return json.url;
  }

  function updateBadges() {
    const counts = {
      workspace: (state.workspaceApps || []).length,
      experiences: (state.experiences || []).length,
      certificates: (state.certificates || []).length,
    };
    document.querySelectorAll("[data-count-badge]").forEach((el) => {
      el.textContent = String(counts[el.dataset.countBadge] ?? 0);
    });
    document.querySelectorAll("[data-admin-tab]").forEach((btn) => {
      const isAct = btn.dataset.adminTab === activeTab;
      btn.classList.toggle("bg-[#745bc7]", isAct);
      btn.classList.toggle("text-white", isAct);
      btn.classList.toggle("text-[#5d5370]", !isAct);
      const badge = btn.querySelector("[data-count-badge]");
      if (badge) {
        badge.classList.toggle("bg-white/20", isAct);
        badge.classList.toggle("text-white", isAct);
        badge.classList.toggle("bg-[#e5dcfb]", !isAct);
        badge.classList.toggle("text-[#745bc7]", !isAct);
      }
    });
  }

  function closeModal() {
    modalHost.innerHTML = "";
  }
  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modalHost.innerHTML.trim() !== "") closeModal();
  });

  // =========================================================================
  // 1. PROFILE TAB
  // =========================================================================
  function renderProfileTab() {
    const p = state.profile;
    const isId = profileLang === "id";
    root.innerHTML = `
      <form id="profile-form" class="space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#745bc7]/20 pb-4">
          <div>
            <h2 class="font-editorial text-2xl font-bold tracking-[0.12em] text-[#14100e] uppercase">Profile &amp; Artwork</h2>
            <p class="mt-0.5 text-xs text-[#5d5370]">Manage Hero art, About photo, Portfolio bio, and social links.</p>
          </div>
          <div class="flex items-center gap-2.5">
            <div class="inline-flex border border-[#745bc7]/55 bg-white font-mono text-[11px]">
              <button type="button" data-plang="en" class="px-3 py-1.5 font-semibold uppercase ${!isId ? "bg-[#745bc7] text-white" : "text-[#5d5370] hover:bg-[#f4effd]"}">EN</button>
              <button type="button" data-plang="id" class="border-l border-[#745bc7]/40 px-3 py-1.5 font-semibold uppercase ${isId ? "bg-[#745bc7] text-white" : "text-[#5d5370] hover:bg-[#f4effd]"}">ID</button>
            </div>
            <button type="submit" class="vn-btn-band px-4 py-2 text-[10px]">Save Changes</button>
          </div>
        </div>

        <section class="space-y-4">
          <p class="font-mono text-[10.5px] font-semibold tracking-[0.2em] text-[#745bc7] uppercase">01 // Identity &amp; Copy (${profileLang.toUpperCase()})</p>
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Full Name</label>
              <input name="fullName" required value="${esc(p.fullName)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" />
            </div>
            <div class="grid grid-cols-[1fr_76px] gap-2">
              <div>
                <label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Brand</label>
                <input name="brandName" required value="${esc(p.brandName)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" />
              </div>
              <div>
                <label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Kanji</label>
                <input name="brandKanji" value="${esc(p.brandKanji)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" />
              </div>
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-3">
            <div>
              <label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Current Role (${profileLang.toUpperCase()})</label>
              <input name="currentRole" value="${esc(isId ? p.currentRoleId : p.currentRoleEn)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" />
            </div>
            <div>
              <label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Organization</label>
              <input name="currentOrg" value="${esc(p.currentOrg)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" />
            </div>
            <div>
              <label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Location</label>
              <input name="location" value="${esc(p.location)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" />
            </div>
          </div>

          <div>
            <label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Portfolio Bio (${profileLang.toUpperCase()})</label>
            <textarea name="bio" rows="3" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]">${esc(isId ? p.bioId : p.bioEn)}</textarea>
          </div>

          <div>
            <label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">About Page Copy (${profileLang.toUpperCase()})</label>
            <textarea name="aboutStory" rows="4" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]">${esc(isId ? p.aboutStoryId : p.aboutStoryEn)}</textarea>
          </div>
        </section>

        <section class="space-y-4 border-t border-[#745bc7]/20 pt-6">
          <p class="font-mono text-[10.5px] font-semibold tracking-[0.2em] text-[#745bc7] uppercase">02 // Social &amp; Contact Links</p>
          <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Email</label><input name="email" type="email" value="${esc(p.email)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" /></div>
            <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">LinkedIn URL</label><input name="linkedinUrl" value="${esc(p.linkedinUrl)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" /></div>
            <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">GitHub URL</label><input name="githubUrl" value="${esc(p.githubUrl)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" /></div>
            <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Instagram URL</label><input name="instagramUrl" value="${esc(p.instagramUrl)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" /></div>
            <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">YouTube URL</label><input name="youtubeUrl" value="${esc(p.youtubeUrl)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" /></div>
            <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Resume URL (optional)</label><input name="resumeUrl" value="${esc(p.resumeUrl)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" /></div>
          </div>
        </section>

        <section class="space-y-4 border-t border-[#745bc7]/20 pt-6">
          <p class="font-mono text-[10.5px] font-semibold tracking-[0.2em] text-[#745bc7] uppercase">03 // Hero Artwork &amp; About Photo</p>
          <div class="grid gap-4 md:grid-cols-3">
            ${renderUploaderBlock("heroImage", "Hero Desktop (PC)", p.heroImage)}
            ${renderUploaderBlock("heroImageMobile", "Hero Mobile", p.heroImageMobile)}
            ${renderUploaderBlock("avatarImage", "About Square Photo", p.avatarImage)}
          </div>
        </section>
      </form>
    `;

    const form = document.getElementById("profile-form");
    const syncDraftFromDom = () => {
      const fd = new FormData(form);
      p.fullName = String(fd.get("fullName") || "");
      p.brandName = String(fd.get("brandName") || "");
      p.brandKanji = String(fd.get("brandKanji") || "");
      p.currentOrg = String(fd.get("currentOrg") || "");
      p.location = String(fd.get("location") || "");
      p.email = String(fd.get("email") || "");
      p.linkedinUrl = String(fd.get("linkedinUrl") || "");
      p.githubUrl = String(fd.get("githubUrl") || "");
      p.instagramUrl = String(fd.get("instagramUrl") || "");
      p.youtubeUrl = String(fd.get("youtubeUrl") || "");
      p.resumeUrl = String(fd.get("resumeUrl") || "");
      p.heroImage = String(fd.get("heroImage") || "");
      p.heroImageMobile = String(fd.get("heroImageMobile") || "");
      p.avatarImage = String(fd.get("avatarImage") || "");
      if (profileLang === "id") {
        p.currentRoleId = String(fd.get("currentRole") || "");
        p.bioId = String(fd.get("bio") || "");
        p.aboutStoryId = String(fd.get("aboutStory") || "");
      } else {
        p.currentRoleEn = String(fd.get("currentRole") || "");
        p.bioEn = String(fd.get("bio") || "");
        p.aboutStoryEn = String(fd.get("aboutStory") || "");
      }
    };

    root.querySelectorAll("[data-plang]").forEach((btn) => {
      btn.addEventListener("click", () => {
        syncDraftFromDom();
        profileLang = btn.dataset.plang;
        renderProfileTab();
      });
    });

    wireUploaders(root, (field, url) => {
      p[field] = url;
    });

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      syncDraftFromDom();
      try {
        const res = await api("/api/admin/profile", {
          method: "PUT",
          body: JSON.stringify(p),
        });
        state.profile = res.profile;
        notify("Profile & artwork saved live!");
        renderProfileTab();
      } catch (err) {
        notify(err.message, "err");
      }
    });
  }

  function renderUploaderBlock(name, label, val) {
    return `
      <div class="space-y-2 border border-[#745bc7]/30 bg-[#fdfbff] p-3" data-uploader="${name}">
        <p class="font-mono text-[10px] font-semibold tracking-[0.12em] text-[#5d5370] uppercase">${esc(label)}</p>
        <div class="h-28 w-full overflow-hidden border border-[#745bc7]/25 bg-white flex items-center justify-center">
          ${val ? `<img src="${esc(val)}" alt="${esc(label)}" class="h-full w-full object-cover" />` : `<span class="font-mono text-[10px] text-[#8b7fa6]">NO IMAGE</span>`}
        </div>
        <input name="${name}" value="${esc(val)}" placeholder="/api/uploads/..." class="w-full border border-[#745bc7]/35 bg-white px-2 py-1 font-mono text-[10.5px] outline-none focus:border-[#745bc7]" />
        <div class="flex items-center gap-1.5">
          <label class="vn-btn-action flex-1 cursor-pointer py-1.5 text-[9.5px]">
            <span>Upload Image</span>
            <input type="file" accept="image/*" class="hidden" data-upload-file="${name}" />
          </label>
          <button type="button" data-clear-upload="${name}" class="border border-rose-300 bg-rose-50 px-2 py-1.5 font-mono text-[9.5px] text-rose-600 hover:bg-rose-600 hover:text-white">Clear</button>
        </div>
      </div>
    `;
  }

  function wireUploaders(container, onChange) {
    container.querySelectorAll("[data-upload-file]").forEach((input) => {
      input.addEventListener("change", async () => {
        const file = input.files?.[0];
        if (!file) return;
        const field = input.dataset.uploadFile;
        try {
          notify("Uploading image...");
          const url = await uploadImage(file);
          const box = container.querySelector(`[data-uploader="${field}"]`);
          const textInput = box?.querySelector(`input[name="${field}"]`);
          if (textInput) textInput.value = url;
          const preview = box?.querySelector("div.h-28");
          if (preview) preview.innerHTML = `<img src="${esc(url)}" class="h-full w-full object-cover" />`;
          onChange(field, url);
          notify("Image uploaded! Click Save to apply.");
        } catch (err) {
          notify(err.message, "err");
        }
      });
    });
    container.querySelectorAll("[data-clear-upload]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const field = btn.dataset.clearUpload;
        const box = container.querySelector(`[data-uploader="${field}"]`);
        const textInput = box?.querySelector(`input[name="${field}"]`);
        if (textInput) textInput.value = "";
        const preview = box?.querySelector("div.h-28");
        if (preview) preview.innerHTML = `<span class="font-mono text-[10px] text-[#8b7fa6]">NO IMAGE</span>`;
        onChange(field, "");
      });
    });
  }

  // =========================================================================
  // 2. WORK TAB
  // =========================================================================
  function renderWorkTab() {
    const apps = state.workspaceApps || [];
    root.innerHTML = `
      <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#745bc7]/20 pb-4">
          <div>
            <h2 class="font-editorial text-2xl font-bold tracking-[0.12em] text-[#14100e] uppercase">Work &amp; Ecosystem Apps</h2>
            <p class="mt-0.5 text-xs text-[#5d5370]">Manage the ecosystem apps displayed on /workspace — add, edit, reorder, or remove anytime.</p>
          </div>
          <div class="flex items-center gap-2">
            <a href="/workspace" target="_blank" class="vn-btn-action px-3.5 py-2 text-[10px]">Preview /workspace ↗</a>
            <button type="button" id="add-work-btn" class="vn-btn-band px-4 py-2 text-[10px]">+ Add Work App</button>
          </div>
        </div>

        ${apps.length === 0 ? `<div class="border border-dashed border-[#745bc7]/35 bg-[#faf7ff] px-5 py-10 text-center text-xs text-[#5d5370]">No ecosystem works yet.</div>` : `
        <div class="space-y-2.5">
          ${apps.map((app, idx) => `
            <div class="flex flex-col gap-3 border border-[#745bc7]/30 bg-[#fdfbff] p-4 transition hover:border-[#745bc7] sm:flex-row sm:items-center ${app.published ? "" : "opacity-60"}">
              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="font-mono text-[10.5px] font-bold text-[#745bc7]">${String(idx + 1).padStart(2, "0")}</span>
                  <h3 class="font-editorial text-base font-bold tracking-[0.1em] text-[#14100e] uppercase">${esc(app.name)}</h3>
                  ${app.badge ? `<span class="border border-[#745bc7]/45 bg-[#efe9fd] px-1.5 py-0.5 font-mono text-[9.5px] font-semibold text-[#745bc7] uppercase">${esc(app.badge)}</span>` : ""}
                </div>
                <p class="mt-1 text-xs text-[#5d5370]">${esc(app.descriptionEn)}</p>
                <p class="mt-0.5 truncate font-mono text-[10.5px] text-[#7b6f94]">${esc(app.url)} ${app.isExternal ? "(new tab)" : "(internal)"}</p>
              </div>
              <div class="flex shrink-0 flex-nowrap items-center gap-1 sm:gap-1.5">
                <button type="button" data-work-move="${idx}:-1" ${idx === 0 ? "disabled" : ""} class="border border-[#745bc7]/35 bg-white px-2 py-1.5 font-mono text-xs disabled:opacity-30">↑</button>
                <button type="button" data-work-move="${idx}:1" ${idx === apps.length - 1 ? "disabled" : ""} class="border border-[#745bc7]/35 bg-white px-2 py-1.5 font-mono text-xs disabled:opacity-30">↓</button>
                <button type="button" data-work-vis="${app.id}" class="border px-2 py-1.5 font-mono text-xs ${app.published ? "border-emerald-300 bg-emerald-50 text-emerald-700" : "border-[#745bc7]/35 bg-white text-[#8c7eb0]"}">${app.published ? "👁" : "✕"}</button>
                <button type="button" data-work-edit="${app.id}" class="border border-[#745bc7]/45 bg-white px-2.5 py-1.5 font-mono text-[10.5px] font-semibold uppercase hover:bg-[#745bc7] hover:text-white">Edit</button>
                <button type="button" data-work-del="${app.id}" class="border border-rose-300 bg-rose-50/80 px-2 py-1.5 font-mono text-xs text-rose-600 hover:bg-rose-600 hover:text-white">🗑</button>
              </div>
            </div>
          `).join("")}
        </div>`}
      </div>
    `;

    document.getElementById("add-work-btn")?.addEventListener("click", () => openWorkModal(null));
    root.querySelectorAll("[data-work-edit]").forEach((b) =>
      b.addEventListener("click", () => openWorkModal(apps.find((a) => a.id === b.dataset.workEdit)))
    );
    root.querySelectorAll("[data-work-vis]").forEach((b) =>
      b.addEventListener("click", async () => {
        const a = apps.find((x) => x.id === b.dataset.workVis);
        if (!a) return;
        const res = await api(`/api/admin/workspace/${a.id}`, { method: "PATCH", body: JSON.stringify({ published: !a.published }) });
        state.workspaceApps = apps.map((x) => (x.id === a.id ? res.workspaceApp : x));
        notify("Visibility updated");
        render();
      })
    );
    root.querySelectorAll("[data-work-del]").forEach((b) =>
      b.addEventListener("click", async () => {
        const a = apps.find((x) => x.id === b.dataset.workDel);
        if (!a || !confirm(`Remove "${a.name}"?`)) return;
        await api(`/api/admin/workspace/${a.id}`, { method: "DELETE" });
        state.workspaceApps = apps.filter((x) => x.id !== a.id);
        notify(`Removed "${a.name}"`);
        render();
      })
    );
    root.querySelectorAll("[data-work-move]").forEach((b) =>
      b.addEventListener("click", async () => {
        const [iStr, dStr] = b.dataset.workMove.split(":");
        const i = Number(iStr), t = i + Number(dStr);
        if (t < 0 || t >= apps.length) return;
        const list = [...apps];
        [list[i], list[t]] = [list[t], list[i]];
        const updated = list.map((x, idx) => ({ ...x, sortOrder: idx + 1 }));
        state.workspaceApps = updated;
        render();
        await Promise.all([updated[i], updated[t]].map((item) =>
          api(`/api/admin/workspace/${item.id}`, { method: "PATCH", body: JSON.stringify({ sortOrder: item.sortOrder }) })
        ));
        notify("Order updated");
      })
    );
  }

  function openWorkModal(existing) {
    const a = existing || { name: "", badge: "APP", url: "https://", descriptionEn: "", descriptionId: "", isExternal: true, published: true, sortOrder: (state.workspaceApps || []).length + 1 };
    modalHost.innerHTML = `
      <div class="fixed inset-0 z-[80] flex items-center justify-center bg-[#19132b]/65 p-2.5 backdrop-blur-sm sm:p-4" id="m-backdrop">
        <form id="work-modal-form" class="vn-frame flex max-h-[92dvh] w-full max-w-xl flex-col overflow-hidden" onclick="event.stopPropagation()">
          <div class="vn-band-strip flex shrink-0 items-center justify-between px-4 py-3 text-white sm:px-5">
            <h3 class="font-mono text-xs font-semibold tracking-[0.16em] uppercase">${existing ? "Edit Work App" : "Add Work App"}</h3>
            <button type="button" id="m-close" class="border border-white/55 bg-white/15 px-2 py-0.5 font-mono text-xs text-white">✕</button>
          </div>
          <div class="min-h-0 flex-1 space-y-4 overflow-y-auto bg-[#fdfbff] p-4 sm:p-6">
            <div class="grid gap-4 sm:grid-cols-[1fr_140px]">
              <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">App Name</label><input name="name" required value="${esc(a.name)}" placeholder="e.g. SHIKAS" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" /></div>
              <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Badge</label><input name="badge" value="${esc(a.badge)}" placeholder="FINANCE" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 font-mono text-xs uppercase outline-none focus:border-[#745bc7]" /></div>
            </div>
            <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Target URL</label><input name="url" required value="${esc(a.url)}" placeholder="https://shikas.sitezet.com" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 font-mono text-xs outline-none focus:border-[#745bc7]" /></div>
            <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Description (EN)</label><input name="descriptionEn" required value="${esc(a.descriptionEn)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" /></div>
            <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Description (ID — optional)</label><input name="descriptionId" value="${esc(a.descriptionId)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" /></div>
            <div class="flex flex-wrap gap-5 pt-1 text-xs text-[#3b3150]">
              <label class="inline-flex cursor-pointer items-center gap-2"><input type="checkbox" name="isExternal" ${a.isExternal ? "checked" : ""} class="h-4 w-4 accent-[#745bc7]" /> Open in new tab</label>
              <label class="inline-flex cursor-pointer items-center gap-2"><input type="checkbox" name="published" ${a.published ? "checked" : ""} class="h-4 w-4 accent-[#745bc7]" /> Show on /workspace</label>
            </div>
          </div>
          <div class="flex shrink-0 items-center justify-end gap-2.5 border-t border-[#745bc7]/20 bg-white px-4 py-3 sm:px-5">
            <button type="button" id="m-cancel" class="vn-btn-action px-4 py-2 text-[10px]">Cancel</button>
            <button type="submit" class="vn-btn-band px-4 py-2 text-[10px]">Save App</button>
          </div>
        </form>
      </div>
    `;
    document.getElementById("m-backdrop")?.addEventListener("click", closeModal);
    document.getElementById("m-close")?.addEventListener("click", closeModal);
    document.getElementById("m-cancel")?.addEventListener("click", closeModal);
    document.getElementById("work-modal-form")?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);
      const urlVal = String(fd.get("url") || "").trim();
      const payload = {
        name: String(fd.get("name") || "").trim(),
        badge: String(fd.get("badge") || "").trim().toUpperCase(),
        url: urlVal,
        category: urlVal.startsWith("http") ? "APP" : "INTERNAL",
        icon: "globe",
        descriptionEn: String(fd.get("descriptionEn") || "").trim(),
        descriptionId: String(fd.get("descriptionId") || "").trim(),
        isExternal: fd.get("isExternal") !== null,
        published: fd.get("published") !== null,
        sortOrder: a.sortOrder,
      };
      try {
        const res = await api(existing ? `/api/admin/workspace/${existing.id}` : "/api/admin/workspace", {
          method: existing ? "PATCH" : "POST",
          body: JSON.stringify(payload),
        });
        state.workspaceApps = existing
          ? state.workspaceApps.map((x) => (x.id === existing.id ? res.workspaceApp : x))
          : [...state.workspaceApps, res.workspaceApp];
        closeModal();
        notify(existing ? "Updated work app!" : "Added work app!");
        render();
      } catch (err) {
        notify(err.message, "err");
      }
    });
  }

  // =========================================================================
  // 3. PORTFOLIO (CV) TAB — 3 Sections Matching /portfolio 1-to-1
  // =========================================================================
  const CV_META = {
    EDUCATION: { code: "01 //", title: "Education", add: "Add Education", orgL: "University / Institution", orgP: "e.g. Telkom University", roleL: "Degree & Major", roleP: "e.g. Bachelor's Degree (S1) in Informatics", perL: "Graduation Year & GPA", perP: "e.g. 2026 | GPA: 3.68 / 4.0", sumL: "Relevant Coursework (optional)", sumP: "e.g. Relevant Coursework: Machine Learning, Process Mining", bulL: "Extra Notes / Bullets (1 line per bullet)" },
    WORK: { code: "02 //", title: "Work Experience", add: "Add Work", orgL: "Company / Organization", orgP: "e.g. PT Bank Negara Indonesia (Persero) Tbk (BNI)", roleL: "Role / Position", roleP: "e.g. Full-Stack Developer Intern", perL: "Period", perP: "e.g. Jul 2023 - Jun 2024 or Present", sumL: "Short Summary (optional)", sumP: "Optional 1-2 line overview", bulL: "Key Responsibilities (1 line per bullet)" },
    ORGANIZATION: { code: "03 //", title: "Organizational Experience", add: "Add Organization", orgL: "Organization / Lab / Campus", orgP: "e.g. Chevalier LAB - Telkom University", roleL: "Role / Division / Course", roleP: "e.g. Laboratory Assistant - Android Division Mentor", perL: "Period", perP: "e.g. Sep 2022 - Oct 2023", sumL: "Short Summary (optional)", sumP: "Optional overview", bulL: "Activities / Achievements (1 line per bullet)" },
  };

  function parseBullets(raw) {
    return String(raw || "")
      .split("\n")
      .map((l) => l.replace(/^[\s•\-*·]+/, "").trim())
      .filter(Boolean);
  }

  function renderPortfolioTab() {
    const exps = state.experiences || [];
    const kinds = ["EDUCATION", "WORK", "ORGANIZATION"];
    root.innerHTML = `
      <div class="space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#745bc7]/20 pb-4">
          <div>
            <h2 class="font-editorial text-2xl font-bold tracking-[0.12em] text-[#14100e] uppercase">Portfolio (CV)</h2>
            <p class="mt-0.5 text-xs text-[#5d5370]">Add, edit, or reorder Education, Work Experience, and Organizational Experience shown on /portfolio.</p>
          </div>
          <a href="/portfolio" target="_blank" class="vn-btn-action px-3.5 py-2 text-[10px]">Preview /portfolio ↗</a>
        </div>

        <div class="space-y-8">
          ${kinds.map((kind) => {
            const m = CV_META[kind];
            const items = exps.filter((e) => e.kind === kind);
            return `
              <section class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#745bc7]/20 pb-2.5">
                  <div class="flex items-center gap-2">
                    <span class="font-mono text-[10.5px] font-semibold tracking-[0.18em] text-[#745bc7] uppercase">${m.code}</span>
                    <h3 class="text-sm font-bold tracking-[0.04em] text-[#14100e] uppercase">${m.title}</h3>
                    <span class="bg-[#efe9fd] px-1.5 py-0.5 font-mono text-[10px] font-semibold text-[#745bc7]">${items.length}</span>
                  </div>
                  <button type="button" data-add-exp="${kind}" class="vn-btn-band px-3.5 py-1.5 text-[10px]">+ ${m.add}</button>
                </div>

                ${items.length === 0 ? `<div class="border border-dashed border-[#745bc7]/35 bg-[#faf7ff] px-4 py-6 text-center text-xs text-[#5d5370]">No entries yet.</div>` : `
                <div class="space-y-2.5">
                  ${items.map((exp, idx) => `
                    <div class="flex flex-col gap-3 border border-[#745bc7]/30 bg-[#fdfbff] p-4 transition hover:border-[#745bc7] sm:flex-row sm:items-center ${exp.published ? "" : "opacity-60"}">
                      <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                          <h4 class="text-sm font-bold text-[#14100e]">${esc(kind === "ORGANIZATION" ? exp.organization : exp.roleEn)}</h4>
                          <span class="font-mono text-[11px] text-[#745bc7]">(${esc(exp.periodEn)})</span>
                        </div>
                        <p class="mt-0.5 text-xs font-medium text-[#5d5370]">${esc(kind === "ORGANIZATION" ? exp.roleEn : exp.organization)}</p>
                        ${exp.summaryEn || (exp.bullets?.en?.length > 0) ? `<p class="mt-1 truncate text-[11.5px] text-[#6d6285]">${esc(exp.summaryEn || `${exp.bullets.en.length} bullet points · ${exp.bullets.en[0]}`)}</p>` : ""}
                      </div>
                      <div class="flex shrink-0 flex-nowrap items-center gap-1 sm:gap-1.5">
                        <button type="button" data-exp-move="${kind}:${idx}:-1" ${idx === 0 ? "disabled" : ""} class="border border-[#745bc7]/35 bg-white px-2 py-1.5 font-mono text-xs disabled:opacity-30">↑</button>
                        <button type="button" data-exp-move="${kind}:${idx}:1" ${idx === items.length - 1 ? "disabled" : ""} class="border border-[#745bc7]/35 bg-white px-2 py-1.5 font-mono text-xs disabled:opacity-30">↓</button>
                        <button type="button" data-exp-vis="${exp.id}" class="border px-2 py-1.5 font-mono text-xs ${exp.published ? "border-emerald-300 bg-emerald-50 text-emerald-700" : "border-[#745bc7]/35 bg-white text-[#8c7eb0]"}">${exp.published ? "👁" : "✕"}</button>
                        <button type="button" data-exp-edit="${exp.id}" class="border border-[#745bc7]/45 bg-white px-2.5 py-1.5 font-mono text-[10.5px] font-semibold uppercase hover:bg-[#745bc7] hover:text-white">Edit</button>
                        <button type="button" data-exp-del="${exp.id}" class="border border-rose-300 bg-rose-50/80 px-2 py-1.5 font-mono text-xs text-rose-600 hover:bg-rose-600 hover:text-white">🗑</button>
                      </div>
                    </div>
                  `).join("")}
                </div>`}
              </section>
            `;
          }).join("")}
        </div>
      </div>
    `;

    root.querySelectorAll("[data-add-exp]").forEach((b) =>
      b.addEventListener("click", () => openExpModal(null, b.dataset.addExp))
    );
    root.querySelectorAll("[data-exp-edit]").forEach((b) =>
      b.addEventListener("click", () => {
        const exp = exps.find((x) => x.id === b.dataset.expEdit);
        if (exp) openExpModal(exp, exp.kind);
      })
    );
    root.querySelectorAll("[data-exp-vis]").forEach((b) =>
      b.addEventListener("click", async () => {
        const exp = exps.find((x) => x.id === b.dataset.expVis);
        if (!exp) return;
        const res = await api(`/api/admin/experiences/${exp.id}`, { method: "PATCH", body: JSON.stringify({ published: !exp.published }) });
        state.experiences = exps.map((x) => (x.id === exp.id ? res.experience : x));
        notify("Visibility updated");
        render();
      })
    );
    root.querySelectorAll("[data-exp-del]").forEach((b) =>
      b.addEventListener("click", async () => {
        const exp = exps.find((x) => x.id === b.dataset.expDel);
        if (!exp || !confirm(`Delete "${exp.roleEn}"?`)) return;
        await api(`/api/admin/experiences/${exp.id}`, { method: "DELETE" });
        state.experiences = exps.filter((x) => x.id !== exp.id);
        notify("Entry deleted");
        render();
      })
    );
    root.querySelectorAll("[data-exp-move]").forEach((b) =>
      b.addEventListener("click", async () => {
        const [kind, iStr, dStr] = b.dataset.expMove.split(":");
        const group = exps.filter((x) => x.kind === kind);
        const i = Number(iStr), t = i + Number(dStr);
        if (t < 0 || t >= group.length) return;
        const idxA = exps.findIndex((x) => x.id === group[i].id);
        const idxB = exps.findIndex((x) => x.id === group[t].id);
        const next = [...exps];
        [next[idxA], next[idxB]] = [next[idxB], next[idxA]];
        const updated = next.map((x, idx) => ({ ...x, sortOrder: idx + 1 }));
        state.experiences = updated;
        render();
        await Promise.all([updated[idxA], updated[idxB]].map((item) =>
          api(`/api/admin/experiences/${item.id}`, { method: "PATCH", body: JSON.stringify({ sortOrder: item.sortOrder }) })
        ));
        notify("Order updated");
      })
    );
  }

  function openExpModal(existing, initialKind) {
    const draft = existing
      ? {
          id: existing.id,
          kind: existing.kind,
          organization: existing.organization,
          roleEn: existing.roleEn,
          roleId: existing.roleId,
          periodEn: existing.periodEn,
          periodId: existing.periodId,
          summaryEn: existing.summaryEn,
          summaryId: existing.summaryId,
          bulletsEn: (existing.bullets?.en || []).join("\n"),
          bulletsId: (existing.bullets?.id || []).join("\n"),
          published: existing.published,
          sortOrder: existing.sortOrder,
        }
      : {
          kind: initialKind || "WORK",
          organization: "",
          roleEn: "",
          roleId: "",
          periodEn: "",
          periodId: "",
          summaryEn: "",
          summaryId: "",
          bulletsEn: "",
          bulletsId: "",
          published: true,
          sortOrder: (state.experiences || []).reduce((m, e) => Math.max(m, e.sortOrder), 0) + 1,
        };
    let mLang = "en";
    const orgSuggestions = [...new Set((state.experiences || []).filter((e) => e.kind === "ORGANIZATION").map((e) => e.organization))];

    function drawModal() {
      const m = CV_META[draft.kind] || CV_META.WORK;
      const isId = mLang === "id";
      const curBullets = isId ? draft.bulletsId : draft.bulletsEn;
      modalHost.innerHTML = `
        <div class="fixed inset-0 z-[80] flex items-center justify-center bg-[#19132b]/65 p-2.5 backdrop-blur-sm sm:p-4" id="m-backdrop">
          <form id="exp-modal-form" class="vn-frame flex max-h-[92dvh] w-full max-w-2xl flex-col overflow-hidden" onclick="event.stopPropagation()">
            <div class="vn-band-strip flex shrink-0 items-center justify-between gap-2 px-4 py-3 text-white sm:px-5">
              <h3 class="font-mono text-xs font-semibold tracking-[0.16em] uppercase">${existing ? `Edit ${m.title}` : m.add}</h3>
              <div class="flex items-center gap-2">
                <div class="inline-flex border border-white/55 bg-white/15 font-mono text-[10px]">
                  <button type="button" data-mlang="en" class="px-2.5 py-1 font-semibold ${!isId ? "bg-white text-[#745bc7]" : "text-white"}">EN</button>
                  <button type="button" data-mlang="id" class="border-l border-white/40 px-2.5 py-1 font-semibold ${isId ? "bg-white text-[#745bc7]" : "text-white"}">ID</button>
                </div>
                <button type="button" id="m-close" class="border border-white/55 bg-white/15 px-2 py-0.5 font-mono text-xs text-white">✕</button>
              </div>
            </div>

            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto bg-[#fdfbff] p-4 sm:p-6">
              <div class="grid grid-cols-3 border border-[#745bc7]/40 bg-white font-mono text-[10.5px]">
                ${["EDUCATION", "WORK", "ORGANIZATION"].map((k, idx) => `
                  <button type="button" data-mkind="${k}" class="${idx > 0 ? "border-l border-[#745bc7]/30" : ""} py-2 font-semibold uppercase ${draft.kind === k ? "bg-[#745bc7] text-white" : "text-[#5d5370] hover:bg-[#f4effd]"}">${k === "ORGANIZATION" ? "Organization" : k}</button>
                `).join("")}
              </div>

              <div>
                <label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">${m.orgL}</label>
                <input name="organization" required value="${esc(draft.organization)}" placeholder="${esc(m.orgP)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" />
                ${draft.kind === "ORGANIZATION" && orgSuggestions.length > 0 ? `
                  <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                    <span class="font-mono text-[9.5px] text-[#6d6285] uppercase">Group under:</span>
                    ${orgSuggestions.map((o) => `<button type="button" data-pick-org="${esc(o)}" class="border px-2 py-0.5 text-[11px] ${draft.organization === o ? "border-[#745bc7] bg-[#745bc7] text-white" : "border-[#745bc7]/35 bg-white text-[#5d5370]"}">${esc(o)}</button>`).join("")}
                  </div>
                ` : ""}
              </div>

              <div class="grid gap-4 sm:grid-cols-2">
                <div>
                  <label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">${m.roleL} (${mLang.toUpperCase()})</label>
                  <input name="role" ${!isId ? "required" : ""} value="${esc(isId ? draft.roleId : draft.roleEn)}" placeholder="${esc(m.roleP)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" />
                </div>
                <div>
                  <label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">${m.perL} (${mLang.toUpperCase()})</label>
                  <input name="period" ${!isId ? "required" : ""} value="${esc(isId ? draft.periodId : draft.periodEn)}" placeholder="${esc(m.perP)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" />
                </div>
              </div>

              <div>
                <label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">${m.sumL} (${mLang.toUpperCase()})</label>
                <input name="summary" value="${esc(isId ? draft.summaryId : draft.summaryEn)}" placeholder="${esc(m.sumP)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" />
              </div>

              <div>
                <div class="flex items-start justify-between gap-2">
                  <label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">${m.bulL} (${mLang.toUpperCase()})</label>
                  <span id="m-bullet-count" class="shrink-0 whitespace-nowrap font-mono text-[10px] font-semibold text-[#745bc7]">${parseBullets(curBullets).length} bullets</span>
                </div>
                <textarea name="bullets" rows="4" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2.5 text-sm leading-relaxed outline-none focus:border-[#745bc7]">${esc(curBullets)}</textarea>
              </div>

              <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-[#3b3150]">
                <input type="checkbox" name="published" ${draft.published ? "checked" : ""} class="h-4 w-4 accent-[#745bc7]" /> Show on public /portfolio page
              </label>
            </div>

            <div class="flex shrink-0 items-center justify-end gap-2.5 border-t border-[#745bc7]/20 bg-white px-4 py-3 sm:px-5">
              <button type="button" id="m-cancel" class="vn-btn-action px-4 py-2 text-[10px]">Cancel</button>
              <button type="submit" class="vn-btn-band px-4 py-2 text-[10px]">Save Entry</button>
            </div>
          </form>
        </div>
      `;

      const form = document.getElementById("exp-modal-form");
      const syncDraft = () => {
        const fd = new FormData(form);
        draft.organization = String(fd.get("organization") || "");
        draft.published = fd.get("published") !== null;
        if (mLang === "id") {
          draft.roleId = String(fd.get("role") || "");
          draft.periodId = String(fd.get("period") || "");
          draft.summaryId = String(fd.get("summary") || "");
          draft.bulletsId = String(fd.get("bullets") || "");
        } else {
          draft.roleEn = String(fd.get("role") || "");
          draft.periodEn = String(fd.get("period") || "");
          draft.summaryEn = String(fd.get("summary") || "");
          draft.bulletsEn = String(fd.get("bullets") || "");
        }
      };

      document.getElementById("m-backdrop")?.addEventListener("click", closeModal);
      document.getElementById("m-close")?.addEventListener("click", closeModal);
      document.getElementById("m-cancel")?.addEventListener("click", closeModal);
      modalHost.querySelectorAll("[data-mlang]").forEach((b) =>
        b.addEventListener("click", () => { syncDraft(); mLang = b.dataset.mlang; drawModal(); })
      );
      modalHost.querySelectorAll("[data-mkind]").forEach((b) =>
        b.addEventListener("click", () => { syncDraft(); draft.kind = b.dataset.mkind; drawModal(); })
      );
      modalHost.querySelectorAll("[data-pick-org]").forEach((b) =>
        b.addEventListener("click", () => { syncDraft(); draft.organization = b.dataset.pickOrg; drawModal(); })
      );
      form.querySelector('textarea[name="bullets"]')?.addEventListener("input", (ev) => {
        const el = document.getElementById("m-bullet-count");
        if (el) el.textContent = `${parseBullets(ev.target.value).length} bullets`;
      });

      form.addEventListener("submit", async (e) => {
        e.preventDefault();
        syncDraft();
        const bEn = parseBullets(draft.bulletsEn);
        const bId = parseBullets(draft.bulletsId);
        const payload = {
          kind: draft.kind,
          organization: draft.organization.trim(),
          roleEn: draft.roleEn.trim(),
          roleId: draft.roleId.trim() || draft.roleEn.trim(),
          periodEn: draft.periodEn.trim(),
          periodId: draft.periodId.trim() || draft.periodEn.trim(),
          summaryEn: draft.summaryEn.trim(),
          summaryId: draft.summaryId.trim() || draft.summaryEn.trim(),
          bullets: { en: bEn, id: bId.length ? bId : bEn },
          published: draft.published,
          sortOrder: draft.sortOrder,
        };
        try {
          const res = await api(existing ? `/api/admin/experiences/${existing.id}` : "/api/admin/experiences", {
            method: existing ? "PATCH" : "POST",
            body: JSON.stringify(payload),
          });
          state.experiences = existing
            ? state.experiences.map((x) => (x.id === existing.id ? res.experience : x))
            : [...state.experiences, res.experience];
          closeModal();
          notify(existing ? "Portfolio entry updated!" : "Portfolio entry added!");
          render();
        } catch (err) {
          notify(err.message, "err");
        }
      });
    }
    drawModal();
  }

  // =========================================================================
  // 4. CERTIFICATES TAB
  // =========================================================================
  function renderCertificatesTab() {
    const certs = state.certificates || [];
    root.innerHTML = `
      <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#745bc7]/20 pb-4">
          <div>
            <h2 class="font-editorial text-2xl font-bold tracking-[0.12em] text-[#14100e] uppercase">Certificates</h2>
            <p class="mt-0.5 text-xs text-[#5d5370]">Automatically appears at the bottom of /portfolio once you add a certificate.</p>
          </div>
          <button type="button" id="add-cert-btn" class="vn-btn-band px-4 py-2 text-[10px]">+ Add Certificate</button>
        </div>

        ${certs.length === 0 ? `
          <div class="border border-dashed border-[#745bc7]/35 bg-[#faf7ff] px-5 py-10 text-center">
            <p class="text-xs font-medium text-[#14100e]">No certificates yet</p>
            <p class="mt-1 text-xs text-[#5d5370]">The Certificates section stays hidden on /portfolio until you add your first credential here.</p>
          </div>
        ` : `
          <div class="space-y-2.5">
            ${certs.map((c, idx) => `
              <div class="flex flex-col gap-3 border border-[#745bc7]/30 bg-[#fdfbff] p-4 sm:flex-row sm:items-center ${c.published ? "" : "opacity-60"}">
                <div class="min-w-0 flex-1">
                  <h3 class="text-sm font-bold text-[#14100e]">${esc(c.titleEn)} <span class="font-mono text-[11px] font-normal text-[#745bc7]">(${esc(c.issuedDate)})</span></h3>
                  <p class="text-xs text-[#5d5370]">${esc(c.issuer)} · ${esc(c.category)}</p>
                </div>
                <div class="flex shrink-0 flex-nowrap items-center gap-1 sm:gap-1.5">
                  <button type="button" data-cert-move="${idx}:-1" ${idx === 0 ? "disabled" : ""} class="border border-[#745bc7]/35 bg-white px-2 py-1.5 font-mono text-xs disabled:opacity-30">↑</button>
                  <button type="button" data-cert-move="${idx}:1" ${idx === certs.length - 1 ? "disabled" : ""} class="border border-[#745bc7]/35 bg-white px-2 py-1.5 font-mono text-xs disabled:opacity-30">↓</button>
                  <button type="button" data-cert-vis="${c.id}" class="border px-2 py-1.5 font-mono text-xs ${c.published ? "border-emerald-300 bg-emerald-50 text-emerald-700" : "border-[#745bc7]/35 bg-white text-[#8c7eb0]"}">${c.published ? "👁" : "✕"}</button>
                  <button type="button" data-cert-edit="${c.id}" class="border border-[#745bc7]/45 bg-white px-2.5 py-1.5 font-mono text-[10.5px] font-semibold uppercase hover:bg-[#745bc7] hover:text-white">Edit</button>
                  <button type="button" data-cert-del="${c.id}" class="border border-rose-300 bg-rose-50/80 px-2 py-1.5 font-mono text-xs text-rose-600 hover:bg-rose-600 hover:text-white">🗑</button>
                </div>
              </div>
            `).join("")}
          </div>
        `}
      </div>
    `;

    document.getElementById("add-cert-btn")?.addEventListener("click", () => openCertModal(null));
    root.querySelectorAll("[data-cert-edit]").forEach((b) =>
      b.addEventListener("click", () => openCertModal(certs.find((c) => c.id === b.dataset.certEdit)))
    );
    root.querySelectorAll("[data-cert-move]").forEach((b) =>
      b.addEventListener("click", async () => {
        const [iStr, dStr] = b.dataset.certMove.split(":");
        const i = Number(iStr), t = i + Number(dStr);
        if (t < 0 || t >= certs.length) return;
        const list = [...certs];
        [list[i], list[t]] = [list[t], list[i]];
        const updated = list.map((x, idx) => ({ ...x, sortOrder: idx + 1 }));
        state.certificates = updated;
        render();
        await Promise.all([updated[i], updated[t]].map((item) =>
          api(`/api/admin/certificates/${item.id}`, { method: "PATCH", body: JSON.stringify({ sortOrder: item.sortOrder }) })
        ));
        notify("Order updated");
      })
    );
    root.querySelectorAll("[data-cert-vis]").forEach((b) =>
      b.addEventListener("click", async () => {
        const c = certs.find((x) => x.id === b.dataset.certVis);
        if (!c) return;
        const res = await api(`/api/admin/certificates/${c.id}`, { method: "PATCH", body: JSON.stringify({ published: !c.published }) });
        state.certificates = certs.map((x) => (x.id === c.id ? res.certificate : x));
        notify("Visibility updated");
        render();
      })
    );
    root.querySelectorAll("[data-cert-del]").forEach((b) =>
      b.addEventListener("click", async () => {
        const c = certs.find((x) => x.id === b.dataset.certDel);
        if (!c || !confirm(`Delete "${c.titleEn}"?`)) return;
        await api(`/api/admin/certificates/${c.id}`, { method: "DELETE" });
        state.certificates = certs.filter((x) => x.id !== c.id);
        notify("Certificate deleted");
        render();
      })
    );
  }

  function openCertModal(existing) {
    const c = existing || { titleEn: "", titleId: "", issuer: "", category: "SOFTWARE ENG", issuedDate: String(new Date().getFullYear()), credentialId: "", credentialUrl: "", imageUrl: "", published: true, sortOrder: (state.certificates || []).length + 1 };
    modalHost.innerHTML = `
      <div class="fixed inset-0 z-[80] flex items-center justify-center bg-[#19132b]/65 p-2.5 backdrop-blur-sm sm:p-4" id="m-backdrop">
        <form id="cert-modal-form" class="vn-frame flex max-h-[92dvh] w-full max-w-xl flex-col overflow-hidden" onclick="event.stopPropagation()">
          <div class="vn-band-strip flex shrink-0 items-center justify-between px-4 py-3 text-white sm:px-5">
            <h3 class="font-mono text-xs font-semibold tracking-[0.16em] uppercase">${existing ? "Edit Certificate" : "Add Certificate"}</h3>
            <button type="button" id="m-close" class="border border-white/55 bg-white/15 px-2 py-0.5 font-mono text-xs text-white">✕</button>
          </div>
          <div class="min-h-0 flex-1 space-y-4 overflow-y-auto bg-[#fdfbff] p-4 sm:p-6">
            <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Title (EN)</label><input name="titleEn" required value="${esc(c.titleEn)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" /></div>
            <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Title (ID — optional)</label><input name="titleId" value="${esc(c.titleId)}" placeholder="Defaults to English if empty" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" /></div>
            <div class="grid gap-4 sm:grid-cols-2">
              <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Issuer</label><input name="issuer" required value="${esc(c.issuer)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" /></div>
              <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Issued Date</label><input name="issuedDate" required value="${esc(c.issuedDate)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 font-mono text-xs outline-none focus:border-[#745bc7]" /></div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Category</label>
                <select name="category" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-xs outline-none focus:border-[#745bc7]">
                  ${["SOFTWARE ENG", "BANKING & LEADERSHIP", "AI & DATA", "CLOUD & DEVOPS"].map((cat) => `<option value="${cat}" ${c.category === cat ? "selected" : ""}>${cat}</option>`).join("")}
                </select>
              </div>
              <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Credential ID (optional)</label><input name="credentialId" value="${esc(c.credentialId)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 font-mono text-xs outline-none focus:border-[#745bc7]" /></div>
            </div>
            <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Verification URL (optional)</label><input name="credentialUrl" value="${esc(c.credentialUrl)}" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-xs outline-none focus:border-[#745bc7]" /></div>
            ${renderUploaderBlock("imageUrl", "Certificate Image (optional)", c.imageUrl)}
            <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-[#3b3150]"><input type="checkbox" name="published" ${c.published ? "checked" : ""} class="h-4 w-4 accent-[#745bc7]" /> Show on /portfolio</label>
          </div>
          <div class="flex shrink-0 items-center justify-end gap-2.5 border-t border-[#745bc7]/20 bg-white px-4 py-3 sm:px-5">
            <button type="button" id="m-cancel" class="vn-btn-action px-4 py-2 text-[10px]">Cancel</button>
            <button type="submit" class="vn-btn-band px-4 py-2 text-[10px]">Save Certificate</button>
          </div>
        </form>
      </div>
    `;
    wireUploaders(modalHost, (f, u) => { c[f] = u; });
    document.getElementById("m-backdrop")?.addEventListener("click", closeModal);
    document.getElementById("m-close")?.addEventListener("click", closeModal);
    document.getElementById("m-cancel")?.addEventListener("click", closeModal);
    document.getElementById("cert-modal-form")?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);
      const payload = {
        titleEn: String(fd.get("titleEn") || "").trim(),
        titleId: String(fd.get("titleId") || "").trim(),
        issuer: String(fd.get("issuer") || "").trim(),
        category: String(fd.get("category") || "SOFTWARE ENG").trim(),
        issuedDate: String(fd.get("issuedDate") || "").trim(),
        credentialId: String(fd.get("credentialId") || "").trim(),
        credentialUrl: String(fd.get("credentialUrl") || "").trim(),
        imageUrl: String(fd.get("imageUrl") || "").trim(),
        published: fd.get("published") !== null,
        sortOrder: c.sortOrder,
      };
      try {
        const res = await api(existing ? `/api/admin/certificates/${existing.id}` : "/api/admin/certificates", {
          method: existing ? "PATCH" : "POST",
          body: JSON.stringify(payload),
        });
        state.certificates = existing
          ? state.certificates.map((x) => (x.id === existing.id ? res.certificate : x))
          : [...state.certificates, res.certificate];
        closeModal();
        notify(existing ? "Certificate updated!" : "Certificate added!");
        render();
      } catch (err) {
        notify(err.message, "err");
      }
    });
  }

  // =========================================================================
  // 5. SECURITY TAB
  // =========================================================================
  function renderSecurityTab() {
    root.innerHTML = `
      <div class="space-y-6">
        <div class="border-b border-[#745bc7]/20 pb-4">
          <h2 class="font-editorial text-2xl font-bold tracking-[0.12em] text-[#14100e] uppercase">Admin Security</h2>
          <p class="mt-0.5 text-xs text-[#5d5370]">Update your Admin Studio login password. Separate from public SHIZUKU ID accounts.</p>
        </div>

        <form id="pw-form" class="max-w-lg space-y-4 border border-[#745bc7]/30 bg-[#fdfbff] p-5">
          <p class="font-mono text-[10.5px] font-semibold tracking-[0.16em] text-[#745bc7] uppercase">Change Admin Password</p>
          <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">Current Password</label><input name="currentPassword" type="password" required class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" /></div>
            <div><label class="block font-mono text-[10px] tracking-[0.14em] text-[#5d5370] uppercase">New Password (min 8)</label><input name="newPassword" type="password" required minlength="8" class="mt-1 w-full border border-[#745bc7]/40 bg-white px-3 py-2 text-sm outline-none focus:border-[#745bc7]" /></div>
          </div>
          <button type="submit" class="vn-btn-band px-4 py-2 text-[10px]">Update Password</button>
        </form>
      </div>
    `;
    document.getElementById("pw-form")?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);
      try {
        await api("/api/admin/password", {
          method: "POST",
          body: JSON.stringify({
            currentPassword: String(fd.get("currentPassword") || ""),
            newPassword: String(fd.get("newPassword") || ""),
          }),
        });
        e.target.reset();
        notify("Admin password updated securely!");
      } catch (err) {
        notify(err.message, "err");
      }
    });
  }

  function render() {
    updateBadges();
    if (activeTab === "profile") renderProfileTab();
    else if (activeTab === "workspace") renderWorkTab();
    else if (activeTab === "experiences") renderPortfolioTab();
    else if (activeTab === "certificates") renderCertificatesTab();
    else if (activeTab === "security") renderSecurityTab();
  }

  document.querySelectorAll("[data-admin-tab]").forEach((btn) => {
    btn.addEventListener("click", () => {
      activeTab = btn.dataset.adminTab;
      render();
    });
  });

  document.getElementById("admin-logout-btn")?.addEventListener("click", async () => {
    await api("/api/auth/logout", { method: "POST" });
    window.location.href = "/admin/login";
  });

  render();
})();
