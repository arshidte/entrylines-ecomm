/**
 * FreshMart storefront interactivity — vanilla JS port of the original
 * React client components (hero slider, search suggestions, enquiry modal,
 * quick view, wishlist, recently viewed, tabs, gallery, filters, counters,
 * scroll animations, mobile drawers).
 */
(function () {
  "use strict";

  var BASE = window.FM_BASE || "";
  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function $(sel, root) { return (root || document).querySelector(sel); }
  function $all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  function formatPrice(price) {
    return "$" + Number(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  var ICONS = {
    heart: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>',
    eye: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>',
    basket: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true"><path d="m15 11-1 9"/><path d="m19 11-4-7"/><path d="M2 11h20"/><path d="m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8a2 2 0 0 0 2-1.6l1.7-7.4"/><path d="M4.5 15.5h15"/><path d="m5 11 4-7"/><path d="m9 11 1 9"/></svg>',
    leaf: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3" aria-hidden="true"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>',
  };

  function escapeHtml(s) {
    return String(s == null ? "" : s)
      .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }

  // ── Scroll animations (FadeIn / Stagger) ───────────────────
  function initAnimations() {
    var animated = $all("[data-animate]");
    if (animated.length === 0) return;

    if (reduceMotion || !("IntersectionObserver" in window)) {
      animated.forEach(function (el) { el.classList.add("fm-in"); });
      $all("[data-animate-item]").forEach(function (el) { el.classList.add("fm-in"); });
      return;
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        io.unobserve(el);
        var delay = parseFloat(el.getAttribute("data-animate-delay") || "0") * 1000;
        setTimeout(function () {
          el.classList.add("fm-in");
          if (el.getAttribute("data-animate") === "stagger") {
            $all("[data-animate-item]", el).forEach(function (item, i) {
              setTimeout(function () { item.classList.add("fm-in"); }, i * 60);
            });
          }
        }, delay);
      });
    }, { rootMargin: "-40px" });

    animated.forEach(function (el) { io.observe(el); });
  }

  // ── Animated counters ──────────────────────────────────────
  function initCounters() {
    var counters = $all("[data-counter]");
    if (counters.length === 0) return;

    function animate(el) {
      var target = parseInt(el.getAttribute("data-counter"), 10) || 0;
      var suffix = el.getAttribute("data-counter-suffix") || "";
      if (reduceMotion) {
        el.textContent = target.toLocaleString("en-US") + suffix;
        return;
      }
      var duration = 1800;
      var start = null;
      function step(ts) {
        if (start === null) start = ts;
        var t = Math.min(1, (ts - start) / duration);
        var eased = 1 - Math.pow(1 - t, 3);
        el.textContent = Math.round(target * eased).toLocaleString("en-US") + suffix;
        if (t < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    }

    if (!("IntersectionObserver" in window)) {
      counters.forEach(animate);
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          io.unobserve(entry.target);
          animate(entry.target);
        }
      });
    }, { rootMargin: "-40px" });
    counters.forEach(function (el) { io.observe(el); });
  }

  // ── Modal helpers ──────────────────────────────────────────
  function openModal(wrapper) {
    wrapper.classList.remove("hidden");
    document.body.style.overflow = "hidden";
    requestAnimationFrame(function () { wrapper.classList.add("is-open"); });
  }
  function closeModal(wrapper) {
    wrapper.classList.remove("is-open");
    document.body.style.overflow = "";
    setTimeout(function () { wrapper.classList.add("hidden"); }, 200);
  }

  // ── Mobile menu drawer ─────────────────────────────────────
  function initMobileMenu() {
    var drawer = $("[data-mobile-menu]");
    if (!drawer) return;
    var openBtn = $("[data-mobile-menu-open]");
    if (openBtn) openBtn.addEventListener("click", function () { openModal(drawer); });
    $all("[data-mobile-menu-close], [data-mobile-menu-overlay]", drawer).forEach(function (el) {
      el.addEventListener("click", function () { closeModal(drawer); });
    });
  }

  // ── Products mega menu (hover) ─────────────────────────────
  function initMegaMenu() {
    $all("[data-mega-menu]").forEach(function (menu) {
      var panel = $("[data-mega-panel]", menu);
      var trigger = $("[data-mega-trigger]", menu);
      var chevron = trigger ? trigger.querySelector("svg") : null;
      var hideTimer = null;
      function show() {
        clearTimeout(hideTimer);
        panel.classList.remove("hidden");
        requestAnimationFrame(function () { panel.classList.add("is-open"); });
        if (trigger) trigger.setAttribute("aria-expanded", "true");
        if (chevron) chevron.classList.add("rotate-180");
      }
      function hide() {
        hideTimer = setTimeout(function () {
          panel.classList.remove("is-open");
          if (trigger) trigger.setAttribute("aria-expanded", "false");
          if (chevron) chevron.classList.remove("rotate-180");
          setTimeout(function () { panel.classList.add("hidden"); }, 180);
        }, 60);
      }
      menu.addEventListener("mouseenter", show);
      menu.addEventListener("mouseleave", hide);
      $all("a", panel).forEach(function (a) { a.addEventListener("click", hide); });
    });
  }

  // ── Search suggestions ─────────────────────────────────────
  function initSearch() {
    $all("[data-search-box]").forEach(function (box) {
      var input = $("[data-search-input]", box);
      var panel = $("[data-search-panel]", box);
      var form = $("[data-search-form]", box);
      var spinner = box.querySelector(".animate-spin");
      var debounce = null;

      function close() { panel.classList.add("hidden"); panel.classList.remove("is-open"); }
      function open() {
        panel.classList.remove("hidden");
        requestAnimationFrame(function () { panel.classList.add("is-open"); });
      }

      document.addEventListener("mousedown", function (e) {
        if (!box.contains(e.target)) close();
      });

      function render(data, q) {
        var hasResults = data.products.length > 0 || data.categories.length > 0;
        var html = "";
        if (!hasResults) {
          html = '<p class="px-5 py-6 text-center text-sm text-ink-soft">No matches for “' + escapeHtml(q) + '”. Try “tomato” or “salmon”.</p>';
        } else {
          html = '<div class="max-h-105 overflow-y-auto py-2">';
          if (data.categories.length > 0) {
            html += '<div class="border-b border-black/5 px-3 pb-2">'
              + '<p class="px-2 py-1.5 text-xs font-semibold uppercase tracking-wide text-black/40">Categories</p>'
              + '<div class="flex flex-wrap gap-2 px-2 pb-1">';
            data.categories.forEach(function (c) {
              html += '<a href="' + BASE + '/category/' + encodeURIComponent(c.slug) + '" class="rounded-full bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 transition-colors hover:bg-brand-100">' + escapeHtml(c.name) + '</a>';
            });
            html += '</div></div>';
          }
          data.products.forEach(function (p) {
            html += '<a href="' + BASE + '/products/' + encodeURIComponent(p.slug) + '" class="flex items-center gap-3 px-5 py-2.5 transition-colors hover:bg-brand-50/60">'
              + '<span class="relative size-11 shrink-0 overflow-hidden rounded-lg bg-cream-dark">'
              + (p.image ? '<img src="' + escapeHtml(p.image) + '" alt="' + escapeHtml(p.name) + '" class="absolute inset-0 size-full object-cover">' : "")
              + '</span>'
              + '<span class="min-w-0 flex-1"><span class="block truncate text-sm font-medium text-ink">' + escapeHtml(p.name) + '</span>'
              + '<span class="block text-xs text-black/40">' + escapeHtml(p.category) + '</span></span>'
              + '<span class="text-sm font-semibold text-brand-700">' + formatPrice(p.price)
              + '<span class="text-xs font-normal text-black/40">/' + escapeHtml(p.unit) + '</span></span>'
              + '</a>';
          });
          html += '<button type="button" data-search-viewall class="w-full cursor-pointer border-t border-black/5 px-5 py-3 text-center text-sm font-semibold text-brand-700 transition-colors hover:bg-brand-50">View all results for “' + escapeHtml(q) + '”</button>';
          html += "</div>";
        }
        panel.innerHTML = html;
        var viewAll = $("[data-search-viewall]", panel);
        if (viewAll) viewAll.addEventListener("click", function () { form.submit(); });
        open();
      }

      input.addEventListener("input", function () {
        var value = input.value;
        clearTimeout(debounce);
        if (value.trim().length < 2) { close(); return; }
        debounce = setTimeout(function () {
          if (spinner) spinner.classList.remove("hidden");
          fetch(BASE + "/api/search?q=" + encodeURIComponent(value))
            .then(function (r) { return r.json(); })
            .then(function (data) { render(data, value); })
            .catch(function () {})
            .finally(function () { if (spinner) spinner.classList.add("hidden"); });
        }, 250);
      });

      input.addEventListener("focus", function () {
        if (input.value.trim().length >= 2 && panel.innerHTML !== "") open();
      });

      form.addEventListener("submit", function (e) {
        if (!input.value.trim()) e.preventDefault();
        close();
      });
    });
  }

  // ── Wishlist (localStorage, synced across buttons + tabs) ──
  var WISHLIST_KEY = "freshmart-wishlist";

  function readWishlist() {
    try { return JSON.parse(localStorage.getItem(WISHLIST_KEY) || "[]"); } catch (e) { return []; }
  }

  function paintWishlistButtons() {
    var list = readWishlist();
    $all("[data-wishlist-toggle]").forEach(function (btn) {
      var slug = btn.getAttribute("data-slug");
      var saved = list.indexOf(slug) !== -1;
      var heart = btn.querySelector("svg");
      btn.setAttribute("aria-pressed", saved ? "true" : "false");
      btn.setAttribute("aria-label", saved ? "Remove from wishlist" : "Add to wishlist");
      if (btn.hasAttribute("data-wishlist-detail")) {
        // Detail-page button switches between outline and danger variants.
        var danger = "bg-danger-500 text-white shadow-md shadow-danger-500/25 hover:bg-danger-600".split(" ");
        var outline = "border-2 border-brand-600 bg-transparent text-brand-700 hover:bg-brand-50".split(" ");
        if (saved) {
          outline.forEach(function (c) { btn.classList.remove(c); });
          danger.forEach(function (c) { btn.classList.add(c); });
        } else {
          danger.forEach(function (c) { btn.classList.remove(c); });
          outline.forEach(function (c) { btn.classList.add(c); });
        }
      } else {
        var savedCls = "bg-danger-500 text-white".split(" ");
        var idleCls = "bg-white/85 text-ink-soft hover:bg-white hover:text-danger-500".split(" ");
        if (saved) {
          idleCls.forEach(function (c) { btn.classList.remove(c); });
          savedCls.forEach(function (c) { btn.classList.add(c); });
        } else {
          savedCls.forEach(function (c) { btn.classList.remove(c); });
          idleCls.forEach(function (c) { btn.classList.add(c); });
        }
      }
      if (heart) heart.classList.toggle("fill-current", saved);
    });
  }

  function initWishlist() {
    document.addEventListener("click", function (e) {
      var btn = e.target.closest("[data-wishlist-toggle]");
      if (!btn) return;
      e.preventDefault();
      var slug = btn.getAttribute("data-slug");
      var list = readWishlist();
      var next = list.indexOf(slug) !== -1 ? list.filter(function (s) { return s !== slug; }) : list.concat([slug]);
      try { localStorage.setItem(WISHLIST_KEY, JSON.stringify(next)); } catch (err) {}
      paintWishlistButtons();
    });
    window.addEventListener("storage", paintWishlistButtons);
    paintWishlistButtons();
  }

  // ── Enquiry modal ──────────────────────────────────────────
  var UNITS = ["Kg", "Gram", "Piece", "Box", "Dozen", "Bundle", "Litre", "Pack"];

  function initEnquiryModal() {
    var modal = $("[data-enquiry-modal]");
    if (!modal) return;
    var form = $("[data-enquiry-form]", modal);
    var success = $("[data-enquiry-success]", modal);
    var serverError = $("[data-enquiry-server-error]", modal);
    var productNameEl = $("[data-enquiry-product-name]", modal);
    var unitSelect = $("#enq-unit", modal);

    function openEnquiry(product) {
      // Reset state
      form.reset();
      form.classList.remove("hidden");
      success.classList.add("hidden");
      success.classList.remove("flex");
      serverError.classList.add("hidden");
      $all("[data-error-for]", form).forEach(function (p) { p.classList.add("hidden"); });

      form.elements.productId.value = product.id || "";
      form.elements.productName.value = product.name;
      form.elements.quantity.value = "1";
      productNameEl.textContent = product.name;

      // Unit options: product unit first, then the standard set (de-duplicated).
      var opts = [product.unit].concat(UNITS).filter(function (u, i, arr) { return arr.indexOf(u) === i; });
      unitSelect.innerHTML = opts.map(function (u) { return '<option value="' + escapeHtml(u) + '">' + escapeHtml(u) + "</option>"; }).join("");
      unitSelect.value = product.unit;

      openModal(modal);
    }

    window.FMopenEnquiry = openEnquiry;

    $all("[data-enquiry-close], [data-enquiry-overlay]", modal).forEach(function (el) {
      el.addEventListener("click", function () { closeModal(modal); });
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && !modal.classList.contains("hidden")) closeModal(modal);
    });

    // Validation — same rules/messages as src/lib/validation.ts.
    function validate(values) {
      var errors = {};
      if (!values.customerName || values.customerName.length < 2) errors.customerName = "Please enter your full name";
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email || "")) errors.email = "Please enter a valid email address";
      if (!values.phone || values.phone.length < 6) errors.phone = "Please enter a valid phone number";
      if (!values.location || values.location.length < 2) errors.location = "Please enter your city / location";
      if (!values.deliveryAddress || values.deliveryAddress.length < 5) errors.deliveryAddress = "Please enter your delivery address";
      if (!values.quantity) errors.quantity = "Please enter a quantity";
      if (!values.preferredUnit) errors.preferredUnit = "Please choose a unit";
      return errors;
    }

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var values = {};
      $all("input, textarea, select", form).forEach(function (el) {
        if (el.name) values[el.name] = el.value.trim();
      });

      $all("[data-error-for]", form).forEach(function (p) { p.classList.add("hidden"); });
      var errors = validate(values);
      var keys = Object.keys(errors);
      if (keys.length > 0) {
        keys.forEach(function (k) {
          var p = form.querySelector('[data-error-for="' + k + '"]');
          if (p) { p.textContent = errors[k]; p.classList.remove("hidden"); }
        });
        return;
      }

      var submitBtn = $("[data-enquiry-submit]", form);
      var idle = $("[data-enquiry-submit-idle]", form);
      var busy = $("[data-enquiry-submit-busy]", form);
      submitBtn.disabled = true;
      idle.style.display = "none";
      busy.style.display = "inline-flex";
      serverError.classList.add("hidden");

      fetch(BASE + "/api/enquiry", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(values),
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.success) {
            form.classList.add("hidden");
            success.classList.remove("hidden");
            success.classList.add("flex");
          } else {
            serverError.textContent = res.message;
            serverError.classList.remove("hidden");
          }
        })
        .catch(function () {
          serverError.textContent = "Something went wrong while saving your enquiry. Please try again.";
          serverError.classList.remove("hidden");
        })
        .finally(function () {
          submitBtn.disabled = false;
          idle.style.display = "";
          busy.style.display = "none";
        });
    });
  }

  // ── Quick view modal ───────────────────────────────────────
  function badgeHtml(variant, content) {
    var variants = {
      fresh: "bg-brand-600 text-white",
      organic: "bg-brand-100 text-brand-800",
      offer: "bg-danger-500 text-white",
      new: "bg-accent-500 text-white",
      bestseller: "bg-ink text-white",
      stock: "bg-brand-50 text-brand-700",
      lowstock: "bg-accent-50 text-accent-600",
      outofstock: "bg-red-50 text-danger-600",
    };
    return '<span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ' + variants[variant] + '">' + content + "</span>";
  }

  function productBadgesHtml(p) {
    var html = "";
    if (p.isFresh) html += badgeHtml("fresh", "Fresh");
    if (p.isOrganic) html += badgeHtml("organic", ICONS.leaf + " Organic");
    if (p.onOffer && p.discountPrice) html += badgeHtml("offer", "−" + Math.round((1 - p.discountPrice / p.price) * 100) + "%");
    if (p.isNewArrival) html += badgeHtml("new", "New");
    if (p.isBestSeller) html += badgeHtml("bestseller", "Best Seller");
    return html;
  }

  function priceHtml(p) {
    return '<span class="text-lg font-extrabold text-brand-700">' + formatPrice(p.discountPrice != null ? p.discountPrice : p.price) + "</span>"
      + (p.discountPrice != null ? '<span class="text-sm text-black/35 line-through">' + formatPrice(p.price) + "</span>" : "")
      + '<span class="text-xs font-medium text-black/45">/ ' + escapeHtml(p.unit) + "</span>";
  }

  function stockBadgeHtml(status) {
    var variant = status === "IN_STOCK" ? "stock" : status === "LOW_STOCK" ? "lowstock" : "outofstock";
    var label = status === "IN_STOCK" ? "In Stock" : status === "LOW_STOCK" ? "Limited Stock" : "Out of Stock";
    return badgeHtml(variant, label);
  }

  function initQuickView() {
    var modal = $("[data-quickview-modal]");
    if (!modal) return;

    $all("[data-quickview-close], [data-quickview-overlay]", modal).forEach(function (el) {
      el.addEventListener("click", function () { closeModal(modal); });
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && !modal.classList.contains("hidden")) closeModal(modal);
    });

    document.addEventListener("click", function (e) {
      var btn = e.target.closest("[data-quick-view]");
      if (!btn) return;
      var card = btn.closest("[data-product]");
      if (!card) return;
      var p = JSON.parse(card.getAttribute("data-product"));

      $("[data-qv-image]", modal).src = p.image || "";
      $("[data-qv-image]", modal).alt = p.imageAlt || p.name;
      $("[data-qv-badges]", modal).innerHTML = productBadgesHtml(p);
      $("[data-qv-category]", modal).textContent = p.categoryName;
      $("[data-qv-name]", modal).textContent = p.name;
      $("[data-qv-price]", modal).innerHTML = priceHtml(p);
      $("[data-qv-stock]", modal).innerHTML = stockBadgeHtml(p.stockStatus);
      $("[data-qv-desc]", modal).textContent = p.shortDescription;

      var weights = $("[data-qv-weights]", modal);
      if (p.weightOptions && p.weightOptions.length > 0) {
        weights.classList.remove("hidden");
        weights.innerHTML = p.weightOptions.map(function (w) {
          return '<span class="rounded-full border border-black/10 px-3 py-1 text-xs font-medium text-ink-soft">' + escapeHtml(w) + "</span>";
        }).join("");
      } else {
        weights.classList.add("hidden");
        weights.innerHTML = "";
      }

      var outOfStock = p.stockStatus === "OUT_OF_STOCK";
      var buyBtn = $("[data-qv-buy]", modal);
      buyBtn.disabled = outOfStock;
      $("[data-qv-buy-label]", modal).textContent = outOfStock ? "Out of Stock" : "Buy Now";
      buyBtn.onclick = function () {
        closeModal(modal);
        window.FMopenEnquiry({ id: p.id, name: p.name, unit: p.unit, weightOptions: p.weightOptions });
      };
      $("[data-qv-link]", modal).href = BASE + "/products/" + encodeURIComponent(p.slug);

      openModal(modal);
    });
  }

  // ── Buy Now buttons (cards + detail page) ──────────────────
  function initBuyButtons() {
    document.addEventListener("click", function (e) {
      var buy = e.target.closest("[data-buy-now]");
      if (buy) {
        var card = buy.closest("[data-product]");
        if (!card) return;
        var p = JSON.parse(card.getAttribute("data-product"));
        window.FMopenEnquiry({ id: p.id, name: p.name, unit: p.unit, weightOptions: p.weightOptions });
        return;
      }

      var detailBuy = e.target.closest("[data-detail-buy]");
      if (detailBuy) {
        var wrap = detailBuy.closest("[data-product-actions]");
        var product = JSON.parse(wrap.getAttribute("data-product"));
        var selected = wrap.querySelector('[data-weight-option][aria-pressed="true"]');
        var name = selected ? product.name + " (" + selected.getAttribute("data-weight-option") + ")" : product.name;
        window.FMopenEnquiry({ id: product.id, name: name, unit: product.unit, weightOptions: product.weightOptions });
      }
    });

    // Weight option selection on the detail page.
    document.addEventListener("click", function (e) {
      var opt = e.target.closest("[data-weight-option]");
      if (!opt) return;
      var wrap = opt.closest("[data-product-actions]");
      var active = "border-brand-600 bg-brand-600 text-white shadow-md shadow-brand-600/25".split(" ");
      var idle = "border-black/10 bg-white text-ink-soft hover:border-brand-400 hover:text-brand-700".split(" ");
      $all("[data-weight-option]", wrap).forEach(function (b) {
        var isThis = b === opt;
        b.setAttribute("aria-pressed", isThis ? "true" : "false");
        active.forEach(function (c) { b.classList.toggle(c, isThis); });
        idle.forEach(function (c) { b.classList.toggle(c, !isThis); });
      });
    });
  }

  // ── Newsletter form ────────────────────────────────────────
  function initNewsletter() {
    $all("[data-newsletter-form]").forEach(function (form) {
      form.addEventListener("submit", function (e) {
        e.preventDefault();
        var email = form.elements.email.value;
        var btn = $("[data-newsletter-submit]", form);
        var message = $("[data-newsletter-message]", form);
        btn.disabled = true;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4 animate-spin" aria-hidden="true"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';

        fetch(BASE + "/api/newsletter", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ email: email }),
        })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            message.textContent = res.message;
            message.classList.remove("hidden", "text-red-300", "text-brand-100");
            message.classList.add(res.success ? "text-brand-100" : "text-red-300");
            if (res.success) {
              form.elements.email.disabled = true;
              btn.textContent = "Subscribe";
            } else {
              btn.disabled = false;
              btn.textContent = "Subscribe";
            }
          })
          .catch(function () {
            btn.disabled = false;
            btn.textContent = "Subscribe";
          });
      });
    });
  }

  // ── Contact form ───────────────────────────────────────────
  function initContactForm() {
    var form = $("[data-contact-form]");
    if (!form) return;
    var successEl = $("[data-contact-success]");
    var errorEl = $("[data-contact-error]");

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var values = {};
      $all("input, textarea", form).forEach(function (el) { if (el.name) values[el.name] = el.value; });

      var btn = $("[data-contact-submit]", form);
      var idle = $("[data-contact-submit-idle]", form);
      var busy = $("[data-contact-submit-busy]", form);
      btn.disabled = true;
      idle.style.display = "none";
      busy.style.display = "inline-flex";
      errorEl.classList.add("hidden");

      fetch(BASE + "/api/contact", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(values),
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.success) {
            form.classList.add("hidden");
            successEl.classList.remove("hidden");
            successEl.classList.add("flex");
          } else {
            errorEl.textContent = res.message;
            errorEl.classList.remove("hidden");
          }
        })
        .catch(function () {
          errorEl.textContent = "Please check the form.";
          errorEl.classList.remove("hidden");
        })
        .finally(function () {
          btn.disabled = false;
          idle.style.display = "";
          busy.style.display = "none";
        });
    });
  }

  // ── Hero slider ────────────────────────────────────────────
  var AUTOPLAY_MS = 6000;

  function initHeroSlider() {
    var slider = $("[data-hero-slider]");
    if (!slider) return;
    var slides = $all("[data-hero-slide]", slider);
    if (slides.length === 0) return;
    var copies = $all("[data-hero-copy]", slider);
    var dots = $all("[data-hero-dot]", slider);
    var index = 0;
    var timer = null;
    var paused = false;

    function goTo(i) {
      index = (i + slides.length) % slides.length;
      slides.forEach(function (s, j) { s.classList.toggle("is-active", j === index); });
      copies.forEach(function (c, j) {
        if (j === index) {
          c.classList.remove("hidden");
          c.classList.remove("is-active");
          void c.offsetWidth; // restart the entrance animation
          c.classList.add("is-active");
        } else {
          c.classList.add("hidden");
          c.classList.remove("is-active");
        }
      });
      dots.forEach(function (d, j) {
        d.className = j === index
          ? "h-2 cursor-pointer rounded-full transition-all duration-300 w-8 bg-white"
          : "h-2 cursor-pointer rounded-full transition-all duration-300 w-2 bg-white/40 hover:bg-white/70";
      });
    }

    function startTimer() {
      stopTimer();
      if (paused || reduceMotion || slides.length < 2) return;
      timer = setInterval(function () { goTo(index + 1); }, AUTOPLAY_MS);
    }
    function stopTimer() { if (timer) { clearInterval(timer); timer = null; } }

    slider.addEventListener("mouseenter", function () { paused = true; stopTimer(); });
    slider.addEventListener("mouseleave", function () { paused = false; startTimer(); });

    dots.forEach(function (d, i) { d.addEventListener("click", function () { goTo(i); startTimer(); }); });
    var prev = $("[data-hero-prev]", slider);
    var next = $("[data-hero-next]", slider);
    if (prev) prev.addEventListener("click", function () { goTo(index - 1); startTimer(); });
    if (next) next.addEventListener("click", function () { goTo(index + 1); startTimer(); });

    startTimer();
  }

  // ── Product tabs ───────────────────────────────────────────
  function initProductTabs() {
    var wrap = $("[data-product-tabs]");
    if (!wrap) return;
    var buttons = $all("[data-tab-btn]", wrap);

    buttons.forEach(function (btn) {
      btn.addEventListener("click", function () {
        var key = btn.getAttribute("data-tab-btn");
        buttons.forEach(function (b) {
          var isActive = b === btn;
          b.setAttribute("aria-selected", isActive ? "true" : "false");
          b.classList.toggle("text-white", isActive);
          b.classList.toggle("bg-white", !isActive);
          b.classList.toggle("text-ink-soft", !isActive);
          b.classList.toggle("shadow-sm", !isActive);
          b.classList.toggle("hover:text-brand-700", !isActive);
          var pill = $("[data-tab-pill]", b);
          if (pill) pill.classList.toggle("hidden", !isActive);
        });
        $all("[data-tab-panel]", wrap).forEach(function (panel) {
          var show = panel.getAttribute("data-tab-panel") === key;
          if (show) {
            panel.classList.remove("hidden");
            panel.classList.remove("is-in");
            void panel.offsetWidth;
            panel.classList.add("is-in");
          } else {
            panel.classList.add("hidden");
          }
        });
        paintWishlistButtons();
      });
    });
    // Animate the initial panel in.
    var first = $("[data-tab-panel]:not(.hidden)", wrap);
    if (first) first.classList.add("is-in");
  }

  // ── Product gallery ────────────────────────────────────────
  function initGallery() {
    var gallery = $("[data-gallery]");
    if (!gallery) return;
    var main = $("[data-gallery-main]", gallery);
    var images = $all("[data-gallery-image]", gallery);
    var thumbs = $all("[data-gallery-thumb]", gallery);

    thumbs.forEach(function (thumb) {
      thumb.addEventListener("click", function () {
        var i = parseInt(thumb.getAttribute("data-gallery-thumb"), 10);
        images.forEach(function (img, j) { img.classList.toggle("is-active", j === i); });
        thumbs.forEach(function (t, j) {
          if (j === i) {
            t.classList.add("ring-2", "ring-brand-600", "ring-offset-2", "ring-offset-cream");
            t.classList.remove("opacity-60", "hover:opacity-100");
          } else {
            t.classList.remove("ring-2", "ring-brand-600", "ring-offset-2", "ring-offset-cream");
            t.classList.add("opacity-60", "hover:opacity-100");
          }
        });
      });
    });

    // Hover zoom that follows the cursor.
    main.addEventListener("mousemove", function (e) {
      var rect = main.getBoundingClientRect();
      var x = ((e.clientX - rect.left) / rect.width) * 100;
      var y = ((e.clientY - rect.top) / rect.height) * 100;
      $all("[data-gallery-zoom]", main).forEach(function (img) {
        img.style.transformOrigin = x + "% " + y + "%";
      });
    });
    main.addEventListener("mouseenter", function () {
      $all("[data-gallery-zoom]", main).forEach(function (img) { img.style.transform = "scale(1.8)"; });
    });
    main.addEventListener("mouseleave", function () {
      $all("[data-gallery-zoom]", main).forEach(function (img) { img.style.transform = "scale(1)"; });
    });
  }

  // ── Recently viewed ────────────────────────────────────────
  var RECENT_KEY = "freshmart-recently-viewed";
  var RECENT_MAX = 8;

  function renderCard(p) {
    var outOfStock = p.stockStatus === "OUT_OF_STOCK";
    var detailUrl = BASE + "/products/" + encodeURIComponent(p.slug);
    return '<article data-product="' + escapeHtml(JSON.stringify(p)) + '" class="group relative flex h-full flex-col overflow-hidden rounded-3xl bg-white shadow-card transition-all duration-300 hover:-translate-y-1.5 hover:shadow-card-hover">'
      + '<div class="relative aspect-[4/3] overflow-hidden bg-cream-dark">'
      + '<a href="' + detailUrl + '" aria-label="' + escapeHtml(p.name) + '">'
      + (p.image ? '<img src="' + escapeHtml(p.image) + '" alt="' + escapeHtml(p.imageAlt || p.name) + '" loading="lazy" class="absolute inset-0 size-full object-cover transition-transform duration-500 ease-out group-hover:scale-108">' : "")
      + "</a>"
      + '<div class="pointer-events-none absolute left-3 top-3 z-10 flex flex-col items-start gap-1.5">' + productBadgesHtml(p) + "</div>"
      + '<div class="absolute right-3 top-3 z-10 flex flex-col gap-2">'
      + '<button type="button" data-wishlist-toggle data-slug="' + escapeHtml(p.slug) + '" aria-label="Add to wishlist" aria-pressed="false" class="flex size-9 cursor-pointer items-center justify-center rounded-full shadow-md backdrop-blur transition-all duration-200 bg-white/85 text-ink-soft hover:bg-white hover:text-danger-500">' + ICONS.heart + "</button>"
      + '<button type="button" data-quick-view aria-label="Quick view ' + escapeHtml(p.name) + '" class="flex size-9 cursor-pointer items-center justify-center rounded-full bg-white/85 text-ink-soft shadow-md backdrop-blur transition-all duration-200 hover:bg-white hover:text-brand-600 sm:translate-y-1 sm:opacity-0 sm:group-hover:translate-y-0 sm:group-hover:opacity-100">' + ICONS.eye + "</button>"
      + "</div></div>"
      + '<div class="flex flex-1 flex-col p-4">'
      + '<div class="flex items-start justify-between gap-2">'
      + '<p class="text-[11px] font-semibold uppercase tracking-wider text-brand-600">' + escapeHtml(p.categoryName) + "</p>"
      + stockBadgeHtml(p.stockStatus)
      + "</div>"
      + '<a href="' + detailUrl + '"><h3 class="mt-1 font-bold leading-snug text-ink transition-colors hover:text-brand-700">' + escapeHtml(p.name) + "</h3></a>"
      + '<p class="mt-1 line-clamp-2 text-xs leading-relaxed text-ink-soft">' + escapeHtml(p.shortDescription) + "</p>"
      + '<div class="mt-auto pt-3">'
      + '<p class="flex items-baseline gap-2">' + priceHtml(p) + "</p>"
      + '<div class="mt-3 flex gap-2">'
      + '<button type="button" data-buy-now class="relative inline-flex cursor-pointer items-center justify-center gap-2 overflow-hidden whitespace-nowrap rounded-full font-semibold transition-all duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:pointer-events-none disabled:opacity-50 active:scale-[0.97] [&_svg]:pointer-events-none [&_svg]:shrink-0 bg-brand-600 text-white shadow-md shadow-brand-600/25 hover:bg-brand-700 hover:shadow-lg hover:shadow-brand-600/30 h-9 px-4 text-sm [&_svg]:size-4 flex-1"' + (outOfStock ? " disabled" : "") + ">"
      + ICONS.basket + " " + (outOfStock ? "Out of Stock" : "Buy Now")
      + "</button></div></div></div></article>";
  }

  function initRecentlyViewed() {
    var section = $("[data-recently-viewed]");
    if (!section) return;
    var currentSlug = section.getAttribute("data-current-slug");
    var grid = $("[data-recently-viewed-grid]", section);

    var slugs = [];
    try { slugs = JSON.parse(localStorage.getItem(RECENT_KEY) || "[]"); } catch (e) {}

    var others = slugs.filter(function (s) { return s !== currentSlug; });
    var next = [currentSlug].concat(others).slice(0, RECENT_MAX);
    try { localStorage.setItem(RECENT_KEY, JSON.stringify(next)); } catch (e) {}

    if (others.length === 0) return;
    fetch(BASE + "/api/products/by-slugs?slugs=" + encodeURIComponent(others.slice(0, 4).join(",")))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.products || data.products.length === 0) return;
        grid.innerHTML = data.products.map(renderCard).join("");
        section.classList.remove("hidden");
        paintWishlistButtons();
      })
      .catch(function () {});
  }

  // ── Listing filters / sort / view ──────────────────────────
  function setParams(updates, clearPage) {
    var url = new URL(window.location.href);
    Object.keys(updates).forEach(function (k) {
      var v = updates[k];
      if (v === null || v === "") url.searchParams.delete(k);
      else url.searchParams.set(k, v);
    });
    if (clearPage !== false) url.searchParams.delete("page");
    window.location.href = url.toString();
  }

  function initListingControls() {
    var sort = $("[data-sort-select]");
    if (sort) {
      sort.addEventListener("change", function () { setParams({ sort: sort.value }); });
    }

    $all("[data-view-btn]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        setParams({ view: btn.getAttribute("data-view-btn") }, false);
      });
    });

    $all("[data-filter-param]").forEach(function (cb) {
      cb.addEventListener("change", function () {
        var key = cb.getAttribute("data-filter-param");
        var value = cb.getAttribute("data-filter-value");
        var url = new URL(window.location.href);
        var current = url.searchParams.get(key);
        var updates = {};
        updates[key] = current === value ? null : value;
        setParams(updates);
      });
    });

    $all("[data-price-form]").forEach(function (form) {
      form.addEventListener("submit", function (e) {
        e.preventDefault();
        setParams({
          minPrice: form.elements.minPrice.value || null,
          maxPrice: form.elements.maxPrice.value || null,
        });
      });
    });

    $all("[data-clear-filters]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        window.location.href = window.location.pathname;
      });
    });

    // Mobile filters drawer
    var drawer = $("[data-filters-drawer]");
    if (drawer) {
      var openBtn = $("[data-filters-open]");
      if (openBtn) openBtn.addEventListener("click", function () { openModal(drawer); });
      $all("[data-filters-close], [data-filters-overlay]", drawer).forEach(function (el) {
        el.addEventListener("click", function () { closeModal(drawer); });
      });
    }
  }

  // ── Boot ───────────────────────────────────────────────────
  document.addEventListener("DOMContentLoaded", function () {
    initAnimations();
    initCounters();
    initMobileMenu();
    initMegaMenu();
    initSearch();
    initWishlist();
    initEnquiryModal();
    initQuickView();
    initBuyButtons();
    initNewsletter();
    initContactForm();
    initHeroSlider();
    initProductTabs();
    initGallery();
    initRecentlyViewed();
    initListingControls();
  });
})();
