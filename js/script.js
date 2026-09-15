

/* =========================================================
   1. CATEGORY COVERFLOW
   ========================================================= */

const cards = [...document.querySelectorAll(".category-card")];
const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");
const count = document.getElementById("categoryCount");
const coverflow = document.getElementById("coverflow");

let active = 0;
let startX = null;

/* Updates the position of every category card. */
function renderCoverflow() {
  if (!cards.length) {
    return;
  }

  const total = cards.length;

  cards.forEach((card, index) => {
    let offset = index - active;

    /* Choose the shortest direction around the carousel. */
    if (offset > total / 2) {
      offset -= total;
    }

    if (offset < -total / 2) {
      offset += total;
    }

    const distance = Math.abs(offset);

    let x = offset * 270;
    let rotate = offset * -27;
    let scale = 1 - Math.min(distance * 0.09, 0.27);
    let opacity = distance > 2 ? 0 : 1 - distance * 0.15;
    const zIndex = 20 - distance;

    /* Use smaller spacing on mobile screens. */
    if (window.innerWidth < 720) {
      x = offset * 205;
      rotate = offset * -22;
      scale = 1 - Math.min(distance * 0.1, 0.3);
      opacity = distance > 2 ? 0 : 1 - distance * 0.2;
    }

    card.style.transform = `translateX(${x}px) rotateY(${rotate}deg) scale(${scale})`;
    card.style.opacity = opacity;
    card.style.zIndex = zIndex;
    card.style.filter = distance === 0 ? "none" : "brightness(.55)";
    card.setAttribute("aria-current", distance === 0 ? "true" : "false");
  });

  count.textContent = `${String(active + 1).padStart(2, "0")} / ${String(total).padStart(2, "0")}`;
}

/*
 * Moves the carousel to a specific card.
 * Modulo keeps the carousel circular.
 */
function goTo(index) {
  if (!cards.length) {
    return;
  }

  active = (index + cards.length) % cards.length;
  renderCoverflow();
}

/* Previous and next buttons. */
prevBtn?.addEventListener("click", () => {
  goTo(active - 1);
});

nextBtn?.addEventListener("click", () => {
  goTo(active + 1);
});

/* Clicking a card either selects it or moves it to the center. */
cards.forEach((card, index) => {
  card.addEventListener("click", () => {
    if (index === active) {
      showToast(`${card.dataset.category} selected`);
      return;
    }

    goTo(index);
  });
});

/* Allow the left and right arrow keys to control the carousel. */
document.addEventListener("keydown", (event) => {
  if (event.key === "ArrowLeft") {
    goTo(active - 1);
  }

  if (event.key === "ArrowRight") {
    goTo(active + 1);
  }
});

/* =========================================================
   2. TOUCH / MOUSE SWIPE
   ========================================================= */

if (coverflow) {
  coverflow.addEventListener("pointerdown", (event) => {
    startX = event.clientX;
    coverflow.setPointerCapture?.(event.pointerId);
  });

  coverflow.addEventListener("pointerup", (event) => {
    if (startX === null) {
      return;
    }

    const distance = event.clientX - startX;

    /* Only treat movements larger than 45px as a swipe. */
    if (Math.abs(distance) > 45) {
      goTo(active + (distance < 0 ? 1 : -1));
    }

    startX = null;
  });
}

/* =========================================================
   3. MOBILE NAVIGATION
   ========================================================= */

const menuToggle = document.querySelector(".menu-toggle");
const nav = document.querySelector(".main-nav");

menuToggle?.addEventListener("click", () => {
  const isOpen = nav.classList.toggle("open");

  menuToggle.setAttribute("aria-expanded", String(isOpen));
  menuToggle.setAttribute(
    "aria-label",
    isOpen ? "Close menu" : "Open menu"
  );
});

/* Close the mobile menu after selecting a navigation link. */
document.querySelectorAll(".main-nav a").forEach((link) => {
  link.addEventListener("click", () => {
    nav?.classList.remove("open");
    menuToggle?.setAttribute("aria-expanded", "false");
    menuToggle?.setAttribute("aria-label", "Open menu");
  });
});

/* =========================================================
   4. CATEGORY DROPDOWN
   ========================================================= */

const dropdown = document.querySelector(".nav-dropdown");
const dropdownToggle = document.querySelector(".dropdown-toggle");

/*
 * The CSS handles hover/focus behavior. This click handler
 * also gives the dropdown predictable behavior on mobile.
 */
dropdownToggle?.addEventListener("click", (event) => {
  event.stopPropagation();

  const isOpen = dropdown.classList.toggle("open");
  dropdownToggle.setAttribute("aria-expanded", String(isOpen));
});

/* Close the dropdown when clicking elsewhere on the page. */
document.addEventListener("click", () => {
  dropdown?.classList.remove("open");
  dropdownToggle?.setAttribute("aria-expanded", "false");
});

/* =========================================================
   5. SEARCH
   ========================================================= */

const search = document.querySelector(".search");
const searchInput = document.getElementById("searchInput");

search?.addEventListener("submit", (event) => {
  const value = searchInput?.value.trim() || "";

  if (!value) {
    event.preventDefault();
    showToast("Enter a product to search");
    searchInput?.focus();
  }
});

/* =========================================================
   6. FAVORITES AND CART
   ========================================================= */

const favoritesBtn = document.getElementById("favoritesBtn");
const cartBtn = document.getElementById("cartBtn");


/* =========================================================
   7. TOAST MESSAGE
   ========================================================= */

const toast = document.getElementById("toast");

/* Displays a temporary message at the bottom of the page. */
function showToast(message) {
  if (!toast) {
    return;
  }

  toast.textContent = message;
  toast.classList.add("show");

  clearTimeout(showToast.timer);

  showToast.timer = setTimeout(() => {
    toast.classList.remove("show");
  }, 1800);
}

/* =========================================================
   8. SCROLL REVEAL ANIMATION
   ========================================================= */

const revealElements = document.querySelectorAll(".reveal");

function revealOnScroll() {
  const visibleDistance = 150;

  revealElements.forEach((element) => {
    const elementTop = element.getBoundingClientRect().top;

    if (elementTop < window.innerHeight - visibleDistance) {
      element.classList.add("active");
    }
  });
}

window.addEventListener("scroll", revealOnScroll);

/* Run once when the page first loads. */
revealOnScroll();

/* =========================================================
   9. SHRINKING HEADER
   ========================================================= */

const header = document.querySelector(".site-header");

function updateHeader() {
  if (!header) {
    return;
  }

  if (window.scrollY > 50) {
    header.classList.add("scrolled");
  } else {
    header.classList.remove("scrolled");
  }
}

window.addEventListener("scroll", updateHeader);
updateHeader();

/* =========================================================
   10. INITIALIZE THE PAGE
   ========================================================= */

renderCoverflow();
window.addEventListener("resize", renderCoverflow);
