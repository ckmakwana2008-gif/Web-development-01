/* =========================================================
   StudentHub — main.js
   Practical 4: DOM manipulation, event handling, UI interactivity
   ---------------------------------------------------------
   Sections in this file:
     1. Theme switcher (light/dark) — with localStorage persistence
     2. Hamburger menu (mobile nav toggle)
     3. Collapsible FAQ (accordion)
     4. Notification banner (dismissible, remembers dismissal)
     5. Content slider (events teaser, auto + manual controls)
     6. Modal popup (event details)
     7. UI preference store (Advanced extension helper)

   Every section only runs if its markup exists on the current
   page (checked with `if (element) { ... }`), so this single
   file can be safely linked from every page without errors.
   ========================================================= */

document.addEventListener("DOMContentLoaded", () => {

  /* =======================================================
     1. THEME SWITCHER — light/dark, persisted in localStorage
     ======================================================= */
  (function initThemeSwitcher() {
    const STORAGE_KEY = "studenthub-theme";
    const toggleBtn = document.querySelector("[data-theme-toggle]");
    const root = document.documentElement;

    // Restore saved theme on page load (Q3 + Advanced extension)
    const savedTheme = localStorage.getItem(STORAGE_KEY);
    if (savedTheme === "dark") {
      root.setAttribute("data-theme", "dark");
    }
    updateToggleLabel();

    if (toggleBtn) {
      toggleBtn.addEventListener("click", () => {
        const isDark = root.getAttribute("data-theme") === "dark";
        if (isDark) {
          root.removeAttribute("data-theme");
          localStorage.setItem(STORAGE_KEY, "light");
        } else {
          root.setAttribute("data-theme", "dark");
          localStorage.setItem(STORAGE_KEY, "dark");
        }
        updateToggleLabel();
      });
    }

    function updateToggleLabel() {
      if (!toggleBtn) return;
      const isDark = root.getAttribute("data-theme") === "dark";
      toggleBtn.setAttribute("aria-pressed", String(isDark));
      toggleBtn.textContent = isDark ? "☀️ Light mode" : "🌙 Dark mode";
    }
  })();

  /* =======================================================
     2. HAMBURGER MENU — toggles the primary nav on small screens
     ======================================================= */
  (function initHamburgerMenu() {
    const toggleBtn = document.querySelector("[data-nav-toggle]");
    const nav = document.querySelector("[data-primary-nav]");
    if (!toggleBtn || !nav) return;

    toggleBtn.addEventListener("click", () => {
      const isOpen = nav.classList.toggle("is-open");
      toggleBtn.setAttribute("aria-expanded", String(isOpen));
      toggleBtn.textContent = isOpen ? "✕ Close menu" : "☰ Menu";
    });

    // Close the menu automatically when a nav link is clicked
    // (better usability on mobile — Q4).
    nav.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", () => {
        nav.classList.remove("is-open");
        toggleBtn.setAttribute("aria-expanded", "false");
        toggleBtn.textContent = "☰ Menu";
      });
    });

    // Close the menu with Escape for keyboard users (accessibility, Q4)
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && nav.classList.contains("is-open")) {
        nav.classList.remove("is-open");
        toggleBtn.setAttribute("aria-expanded", "false");
        toggleBtn.textContent = "☰ Menu";
        toggleBtn.focus();
      }
    });
  })();

  /* =======================================================
     3. COLLAPSIBLE FAQ (accordion)
     Each question is a <button> that toggles its answer's
     visibility and updates aria-expanded for screen readers.
     ======================================================= */
  (function initFaqAccordion() {
    const items = document.querySelectorAll("[data-faq-item]");
    if (!items.length) return;

    items.forEach((item) => {
      const trigger = item.querySelector("[data-faq-trigger]");
      const panel = item.querySelector("[data-faq-panel]");
      if (!trigger || !panel) return;

      trigger.addEventListener("click", () => {
        const isOpen = item.classList.toggle("is-open");
        trigger.setAttribute("aria-expanded", String(isOpen));

        if (isOpen) {
          // Set max-height to the content's real height so the
          // CSS transition can animate open/closed smoothly.
          panel.style.maxHeight = panel.scrollHeight + "px";
        } else {
          panel.style.maxHeight = null;
        }
      });
    });
  })();

  /* =======================================================
     4. NOTIFICATION BANNER — dismissible, remembers dismissal
     ======================================================= */
  (function initNotificationBanner() {
    const STORAGE_KEY = "studenthub-banner-dismissed";
    const banner = document.querySelector("[data-banner]");
    if (!banner) return;

    const dismissBtn = banner.querySelector("[data-banner-dismiss]");
    const bannerId = banner.getAttribute("data-banner");

    // If this specific banner was already dismissed, hide it immediately.
    const dismissed = JSON.parse(localStorage.getItem(STORAGE_KEY) || "[]");
    if (dismissed.includes(bannerId)) {
      banner.remove();
      return;
    }

    if (dismissBtn) {
      dismissBtn.addEventListener("click", () => {
        banner.classList.add("is-dismissing");
        // Wait for the CSS transition to finish before removing
        // it from the DOM (Intermediate extension: transitions).
        banner.addEventListener("transitionend", () => banner.remove(), { once: true });

        dismissed.push(bannerId);
        localStorage.setItem(STORAGE_KEY, JSON.stringify(dismissed));
      });
    }
  })();

  /* =======================================================
     5. CONTENT SLIDER — events teaser, manual + auto-advance
     ======================================================= */
  (function initSlider() {
    const slider = document.querySelector("[data-slider]");
    if (!slider) return;

    const track = slider.querySelector("[data-slider-track]");
    const slides = Array.from(slider.querySelectorAll("[data-slide]"));
    const prevBtn = slider.querySelector("[data-slider-prev]");
    const nextBtn = slider.querySelector("[data-slider-next]");
    const dotsWrap = slider.querySelector("[data-slider-dots]");
    if (!track || slides.length === 0) return;

    let current = 0;
    let autoTimer = null;

    // Build one dot button per slide for direct navigation.
    const dots = slides.map((_, i) => {
      const dot = document.createElement("button");
      dot.type = "button";
      dot.className = "slider-dot";
      dot.setAttribute("aria-label", `Go to slide ${i + 1}`);
      dot.addEventListener("click", () => goTo(i));
      dotsWrap?.appendChild(dot);
      return dot;
    });

    function render() {
      track.style.transform = `translateX(-${current * 100}%)`;
      dots.forEach((dot, i) => dot.classList.toggle("is-active", i === current));
      slides.forEach((slide, i) => slide.setAttribute("aria-hidden", String(i !== current)));
    }

    function goTo(index) {
      current = (index + slides.length) % slides.length;
      render();
      restartAutoplay();
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function restartAutoplay() {
      clearInterval(autoTimer);
      autoTimer = setInterval(next, 6000);
    }

    nextBtn?.addEventListener("click", next);
    prevBtn?.addEventListener("click", prev);

    // Pause autoplay while the user's mouse/focus is on the slider
    // (usability — doesn't yank content away while reading, Q4).
    slider.addEventListener("mouseenter", () => clearInterval(autoTimer));
    slider.addEventListener("mouseleave", restartAutoplay);
    slider.addEventListener("focusin", () => clearInterval(autoTimer));
    slider.addEventListener("focusout", restartAutoplay);

    render();
    restartAutoplay();
  })();

  /* =======================================================
     6. MODAL POPUP — event details, opened from event cards
     ======================================================= */
  (function initModal() {
    const modal = document.querySelector("[data-modal]");
    const openTriggers = document.querySelectorAll("[data-modal-open]");
    if (!modal || openTriggers.length === 0) return;

    const closeBtn = modal.querySelector("[data-modal-close]");
    const titleEl = modal.querySelector("[data-modal-title]");
    const bodyEl = modal.querySelector("[data-modal-body]");
    let lastFocusedElement = null;

    function openModal(trigger) {
      lastFocusedElement = document.activeElement;

      // Read the event's details straight from data-* attributes
      // on the trigger button (DOM selection + modification, Q1).
      titleEl.textContent = trigger.getAttribute("data-event-title") || "";
      bodyEl.textContent = trigger.getAttribute("data-event-details") || "";

      modal.classList.add("is-open");
      modal.setAttribute("aria-hidden", "false");
      closeBtn.focus();
      document.body.classList.add("no-scroll");
    }

    function closeModal() {
      modal.classList.remove("is-open");
      modal.setAttribute("aria-hidden", "true");
      document.body.classList.remove("no-scroll");
      lastFocusedElement?.focus();
    }

    openTriggers.forEach((trigger) => {
      trigger.addEventListener("click", () => openModal(trigger));
    });

    closeBtn?.addEventListener("click", closeModal);

    // Click on the dimmed backdrop (outside the dialog box) to close.
    modal.addEventListener("click", (e) => {
      if (e.target === modal) closeModal();
    });

    // Escape key closes the modal (accessibility, Q4).
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && modal.classList.contains("is-open")) {
        closeModal();
      }
    });

    // Basic focus trap: keep Tab cycling inside the modal while open.
    modal.addEventListener("keydown", (e) => {
      if (e.key !== "Tab" || !modal.classList.contains("is-open")) return;
      const focusable = modal.querySelectorAll("button, a[href]");
      const first = focusable[0];
      const last = focusable[focusable.length - 1];

      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    });
  })();

  /* =======================================================
     7. UI PREFERENCE STORE (Advanced extension)
     Stores a small set of UI preferences as one JSON object
     in localStorage and restores them on every page load,
     in addition to the dedicated theme key above.
     ======================================================= */
  (function initPreferenceStore() {
    const STORAGE_KEY = "studenthub-ui-prefs";

    function getPrefs() {
      try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
      } catch {
        return {};
      }
    }

    function savePrefs(partial) {
      const current = getPrefs();
      const updated = { ...current, ...partial };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(updated));
    }

    // Example preference tracked here: reduced-motion override.
    // (Kept intentionally small/generic so it's easy to extend
    // with more preferences in a later practical.)
    const prefs = getPrefs();
    if (prefs.reduceMotion) {
      document.documentElement.setAttribute("data-reduce-motion", "true");
    }

    // Expose a tiny API on window so other scripts/pages could
    // read or update preferences without duplicating this logic.
    window.studentHubPrefs = { getPrefs, savePrefs };
  })();

});