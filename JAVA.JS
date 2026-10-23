document.addEventListener("DOMContentLoaded", () => {
  const initThemeSwitcher = () => {
    const savedTheme = localStorage.getItem("studenthub-theme");
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
    
    if (savedTheme === "dark" || (!savedTheme && prefersDark)) {
      document.documentElement.setAttribute("data-theme", "dark");
    } else {
      document.documentElement.setAttribute("data-theme", "light");
    }

    // Toggle button setup
    const themeToggleBtn = document.querySelector("#theme-toggle");
    if (themeToggleBtn) {
      themeToggleBtn.addEventListener("click", () => {
        const currentTheme = document.documentElement.getAttribute("data-theme");
        const newTheme = currentTheme === "dark" ? "light" : "dark";
        
        document.documentElement.setAttribute("data-theme", newTheme);
        localStorage.setItem("studenthub-theme", newTheme);
        themeToggleBtn.setAttribute("aria-label", `Switch to ${currentTheme} mode`);
      });
    }
  };
  const initMobileMenu = () => {
    const navToggle = document.querySelector(".nav-toggle");
    const primaryNav = document.querySelector(".primary-nav");

    if (navToggle && primaryNav) {
      navToggle.addEventListener("click", () => {
        const isExpanded = navToggle.getAttribute("aria-expanded") === "true";
        navToggle.setAttribute("aria-expanded", !isExpanded);
        primaryNav.classList.toggle("nav-open");
      });
    }
  };

  const initFAQAccordion = () => {
    const accordionTriggers = document.querySelectorAll(".faq-trigger");

    accordionTriggers.forEach((trigger) => {
      trigger.addEventListener("click", () => {
        const contentId = trigger.getAttribute("aria-controls");
        const contentEl = document.getElementById(contentId);
        const isExpanded = trigger.getAttribute("aria-expanded") === "true";

        accordionTriggers.forEach((otherTrigger) => {
          if (otherTrigger !== trigger) {
            otherTrigger.setAttribute("aria-expanded", "false");
            const otherContent = document.getElementById(otherTrigger.getAttribute("aria-controls"));
            if (otherContent) otherContent.hidden = true;
          }
        });

        trigger.setAttribute("aria-expanded", !isExpanded);
        if (contentEl) {
          contentEl.hidden = isExpanded;
        }
      });
    });
  };

  const initModalDialog = () => {
    const modalTrigger = document.querySelector("[data-modal-open]");
    const modalCloseBtn = document.querySelector("[data-modal-close]");
    const modalOverlay = document.querySelector(".modal-overlay");

    const openModal = () => {
      if (modalOverlay) {
        modalOverlay.classList.add("is-visible");
        modalOverlay.setAttribute("aria-hidden", "false");
        modalCloseBtn?.focus();
      }
    };

    const closeModal = () => {
      if (modalOverlay) {
        modalOverlay.classList.remove("is-visible");
        modalOverlay.setAttribute("aria-hidden", "true");
        modalTrigger?.focus();
      }
    };
    modalTrigger?.addEventListener("click", openModal);
    modalCloseBtn?.addEventListener("click", closeModal);
    modalOverlay?.addEventListener("click", (e) => {
      if (e.target === modalOverlay) closeModal();
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && modalOverlay?.classList.contains("is-visible")) {
        closeModal();
      }
    });
  };
  const initNotificationBanner = () => {
    const banner = document.querySelector(".notification-banner");
    const dismissBtn = document.querySelector(".banner-dismiss");

    if (banner && dismissBtn) {
      dismissBtn.addEventListener("click", () => {
        banner.style.display = "none";
        sessionStorage.setItem("banner-dismissed", "true");
      });

      if (sessionStorage.getItem("banner-dismissed") === "true") {
        banner.style.display = "none";
      }
    }
  };
  const initContentSlider = () => {
    const slides = document.querySelectorAll(".slide");
    const prevBtn = document.querySelector(".slider-prev");
    const nextBtn = document.querySelector(".slider-next");
    let currentSlide = 0;

    if (slides.length === 0) return;

    const showSlide = (index) => {
      slides.forEach((slide, i) => {
        slide.classList.toggle("active", i === index);
        slide.setAttribute("aria-hidden", i !== index);
      });
    };

    nextBtn?.addEventListener("click", () => {
      currentSlide = (currentSlide + 1) % slides.length;
      showSlide(currentSlide);
    });

    prevBtn?.addEventListener("click", () => {
      currentSlide = (currentSlide - 1 + slides.length) % slides.length;
      showSlide(currentSlide);
    });
  };
  initThemeSwitcher();
  initMobileMenu();
  initFAQAccordion();
  initModalDialog();
  initNotificationBanner();
  initContentSlider();
});
