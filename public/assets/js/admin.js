/**
 * FreshMart admin interactivity — vanilla JS port of the admin React
 * components (product form, category/banner managers, enquiry rows,
 * delete buttons, settings form).
 */
(function () {
  "use strict";

  function $(sel, root) { return (root || document).querySelector(sel); }
  function $all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  function postJSON(url, data) {
    return fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data || {}),
    }).then(function (r) { return r.json(); });
  }

  var MAX_UPLOAD_BYTES = 2 * 1024 * 1024; // keep in sync with Admin\Uploads

  function uploadImageFile(file, type) {
    var fd = new FormData();
    fd.append("file", file);
    fd.append("type", type || "misc");
    return fetch((window.FM_BASE || "") + "/admin/upload", {
      method: "POST",
      body: fd,
    }).then(function (r) { return r.json(); });
  }

  // ── Image upload buttons (works for static forms + dynamic rows) ──
  // A file <input data-image-upload data-upload-type="…"> uploads on change and
  // writes the returned URL into the [data-upload-target] input of its
  // [data-upload-group]. Status text goes into [data-upload-status].
  function initImageUploads() {
    document.addEventListener("change", function (e) {
      var input = e.target;
      if (!input.matches || !input.matches("[data-image-upload]")) return;
      var file = input.files && input.files[0];
      if (!file) return;

      var group  = input.closest("[data-upload-group]");
      var target = group && group.querySelector("[data-upload-target]");
      var status = group && group.querySelector("[data-upload-status]");

      function say(msg, isError) {
        if (!status) return;
        status.textContent = msg;
        status.classList.remove("hidden");
        status.classList.toggle("text-danger-600", !!isError);
        status.classList.toggle("text-ink-soft", !isError);
      }

      if (file.size > MAX_UPLOAD_BYTES) {
        say("Image is larger than 2 MB. Please choose a smaller file.", true);
        input.value = "";
        return;
      }

      say("Uploading…", false);
      uploadImageFile(file, input.getAttribute("data-upload-type"))
        .then(function (res) {
          if (!res || res.success === false) {
            throw new Error((res && res.message) || "Upload failed.");
          }
          if (target) {
            target.value = res.url;
            // Notify existing listeners (thumbnail preview, product image state).
            target.dispatchEvent(new Event("input", { bubbles: true }));
          }
          say("Uploaded ✓", false);
        })
        .catch(function (err) { say(err.message || "Upload failed.", true); })
        .finally(function () { input.value = ""; });
    });
  }

  function setBusy(form, busy) {
    var btn = $("[data-form-submit], [data-editor-submit]", form);
    if (!btn) return;
    btn.disabled = busy;
    var idle = $("[data-submit-idle]", form);
    var spin = $("[data-submit-busy]", form);
    // style.display is used because these spans carry inline-flex, which
    // outranks the hidden utility in the compiled CSS.
    if (idle) idle.style.display = busy ? "none" : "";
    if (spin) spin.style.display = busy ? "inline-flex" : "none";
  }

  // ── Generic delete buttons (products list, subscribers) ────
  function initDeleteButtons() {
    document.addEventListener("click", function (e) {
      var btn = e.target.closest("button[data-delete-url]");
      if (!btn) return;
      var label = btn.getAttribute("data-confirm") || "Delete this item?";
      if (!window.confirm(label)) return;
      btn.disabled = true;
      postJSON(btn.getAttribute("data-delete-url"))
        .then(function (res) {
          if (res && res.success === false) {
            window.alert(res.message);
            btn.disabled = false;
            return;
          }
          window.location.reload();
        })
        .catch(function () { btn.disabled = false; });
    });
  }

  // ── Product form ───────────────────────────────────────────
  function initProductForm() {
    var form = $("[data-product-form]");
    if (!form) return;

    var imagesList = $("[data-images-list]", form);
    var images = JSON.parse(form.getAttribute("data-initial-images") || "[]");
    if (images.length === 0) images = [{ url: "", alt: "" }];

    function renderImages() {
      imagesList.innerHTML = images.map(function (img, i) {
        return '<div class="space-y-2 rounded-2xl border border-black/5 p-3" data-upload-group>'
          + '<div class="flex items-start gap-3">'
          + '<span class="relative size-16 shrink-0 overflow-hidden rounded-xl bg-cream-dark">'
          + (img.url ? '<img src="' + img.url.replace(/"/g, "&quot;") + '" alt="" class="absolute inset-0 size-full object-cover">' : "")
          + "</span>"
          + '<div class="flex-1 space-y-2">'
          + '<input placeholder="https://… or upload" aria-label="Image ' + (i + 1) + ' URL" data-image-url="' + i + '" data-upload-target value="' + (img.url || "").replace(/"/g, "&quot;") + '" class="fm-input">'
          + '<input placeholder="Alt text" aria-label="Image ' + (i + 1) + ' alt text" data-image-alt="' + i + '" value="' + (img.alt || "").replace(/"/g, "&quot;") + '" class="fm-input">'
          + '<div class="flex flex-wrap items-center gap-2">'
          + '<label class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-black/10 px-3 py-1.5 text-xs font-semibold text-ink-soft transition-colors hover:bg-brand-50 hover:text-brand-700"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>Upload<input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-image-upload data-upload-type="products" class="hidden"></label>'
          + '<span data-upload-status class="hidden text-xs text-ink-soft"></span>'
          + '</div>'
          + "</div>"
          + (images.length > 1
            ? '<button type="button" data-remove-image="' + i + '" aria-label="Remove image" class="relative inline-flex cursor-pointer items-center justify-center gap-2 overflow-hidden whitespace-nowrap rounded-full font-semibold transition-all duration-200 text-ink hover:bg-brand-50 hover:text-brand-700 size-9 [&_svg]:size-4"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-danger-500" aria-hidden="true"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>'
            : "")
          + "</div></div>";
      }).join("");
    }

    imagesList.addEventListener("input", function (e) {
      var urlIdx = e.target.getAttribute("data-image-url");
      var altIdx = e.target.getAttribute("data-image-alt");
      if (urlIdx !== null) {
        images[+urlIdx].url = e.target.value;
        // Update the preview thumbnail without re-rendering (keeps focus).
        var thumb = e.target.closest(".flex").previousElementSibling || e.target.closest(".flex.items-start").querySelector("span");
        var wrap = e.target.closest(".flex.items-start").querySelector("span");
        if (wrap) {
          wrap.innerHTML = e.target.value ? '<img src="' + e.target.value.replace(/"/g, "&quot;") + '" alt="" class="absolute inset-0 size-full object-cover">' : "";
        }
      }
      if (altIdx !== null) images[+altIdx].alt = e.target.value;
    });

    imagesList.addEventListener("click", function (e) {
      var btn = e.target.closest("[data-remove-image]");
      if (!btn) return;
      images.splice(+btn.getAttribute("data-remove-image"), 1);
      renderImages();
    });

    $("[data-add-image]", form).addEventListener("click", function () {
      images.push({ url: "", alt: "" });
      renderImages();
    });

    renderImages();

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var message = $("[data-form-message]", form);
      message.classList.add("hidden");

      var val = function (name) {
        var el = form.elements[name];
        return el ? el.value : "";
      };
      var checked = function (name) {
        var el = form.elements[name];
        return el ? el.checked : false;
      };

      var data = {
        id: form.getAttribute("data-product-id") || null,
        name: val("name"),
        slug: val("slug"),
        categoryId: val("categoryId"),
        description: val("description"),
        shortDescription: val("shortDescription"),
        price: parseFloat(val("price")) || 0,
        discountPrice: val("discountPrice") ? parseFloat(val("discountPrice")) : null,
        unit: val("unit"),
        weightOptions: val("weightOptions"),
        stockStatus: val("stockStatus"),
        nutrition: val("nutrition"),
        storageInstructions: val("storageInstructions"),
        origin: val("origin"),
        brand: val("brand"),
        seoTitle: val("seoTitle"),
        seoDescription: val("seoDescription"),
        metaKeywords: val("metaKeywords"),
        images: images.filter(function (i) { return i.url.trim(); }),
        isFresh: checked("isFresh"),
        isOrganic: checked("isOrganic"),
        isFeatured: checked("isFeatured"),
        isPopular: checked("isPopular"),
        isNewArrival: checked("isNewArrival"),
        isBestSeller: checked("isBestSeller"),
        isSeasonal: checked("isSeasonal"),
        onOffer: checked("onOffer"),
      };

      setBusy(form, true);
      postJSON(form.getAttribute("data-save-url"), data)
        .then(function (res) {
          message.textContent = res.message;
          message.classList.remove("hidden", "bg-brand-50", "text-brand-700", "bg-red-50", "text-danger-600");
          message.classList.add(res.success ? "bg-brand-50" : "bg-red-50", res.success ? "text-brand-700" : "text-danger-600");
          if (res.success) window.location.href = form.getAttribute("data-redirect");
        })
        .finally(function () { setBusy(form, false); });
    });
  }

  // ── Category manager ───────────────────────────────────────
  function initCategoryManager() {
    var manager = $("[data-category-manager]");
    if (!manager) return;

    var categories = JSON.parse(manager.getAttribute("data-categories") || "[]");
    var form = $("[data-category-form]", manager);
    var placeholder = $("[data-editor-placeholder]", manager);
    var title = $("[data-editor-title]", manager);
    var newBtn = $("[data-editor-new]", manager);
    var cancelBtn = $("[data-editor-cancel]", manager);
    var message = $("[data-editor-message]", manager);
    var parentSelect = form.elements.parentId;

    function showEditor(cat) {
      var isEdit = cat && cat.id;
      title.textContent = isEdit ? "Edit Category" : "New Category";
      placeholder.classList.add("hidden");
      form.classList.remove("hidden");
      newBtn.style.display = "none";
      cancelBtn.style.display = "";
      message.classList.add("hidden");

      form.elements.id.value = isEdit ? cat.id : "";
      form.elements.name.value = cat ? cat.name : "";
      form.elements.slug.value = cat ? cat.slug || "" : "";
      form.elements.description.value = cat ? cat.description || "" : "";
      form.elements.image.value = cat ? cat.image || "" : "";
      form.elements.sortOrder.value = cat ? cat.sortOrder : 0;
      form.elements.isActive.checked = cat ? !!cat.isActive : true;
      form.elements.isFeatured.checked = cat ? !!cat.isFeatured : false;

      var parents = categories.filter(function (c) { return !c.parentId && (!isEdit || c.id !== cat.id); });
      parentSelect.innerHTML = '<option value="">None (top level)</option>' + parents.map(function (p) {
        return '<option value="' + p.id + '">' + p.name.replace(/</g, "&lt;") + "</option>";
      }).join("");
      parentSelect.value = cat && cat.parentId ? String(cat.parentId) : "";
    }

    function hideEditor() {
      title.textContent = "Category Editor";
      placeholder.classList.remove("hidden");
      form.classList.add("hidden");
      newBtn.style.display = "";
      cancelBtn.style.display = "none";
    }

    newBtn.addEventListener("click", function () { showEditor(null); });
    cancelBtn.addEventListener("click", hideEditor);

    $all("[data-edit-category]", manager).forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = +btn.getAttribute("data-edit-category");
        var cat = categories.find(function (c) { return c.id === id; });
        if (cat) showEditor(cat);
      });
    });

    $all("[data-delete-category]", manager).forEach(function (btn) {
      btn.addEventListener("click", function () {
        var name = btn.getAttribute("data-name");
        if (!window.confirm('Delete category "' + name + '"?')) return;
        postJSON(manager.getAttribute("data-delete-url") + "/" + btn.getAttribute("data-delete-category"))
          .then(function (res) {
            if (res && res.success === false) window.alert(res.message);
            window.location.reload();
          });
      });
    });

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var data = {
        id: form.elements.id.value ? +form.elements.id.value : null,
        name: form.elements.name.value,
        slug: form.elements.slug.value,
        description: form.elements.description.value,
        image: form.elements.image.value,
        parentId: form.elements.parentId.value ? +form.elements.parentId.value : null,
        isActive: form.elements.isActive.checked,
        isFeatured: form.elements.isFeatured.checked,
        sortOrder: parseInt(form.elements.sortOrder.value, 10) || 0,
      };
      setBusy(form, true);
      postJSON(manager.getAttribute("data-save-url"), data)
        .then(function (res) {
          if (res.success) {
            window.location.reload();
          } else {
            message.textContent = res.message;
            message.classList.remove("hidden", "bg-brand-50", "text-brand-700", "bg-red-50", "text-danger-600");
            message.classList.add("bg-red-50", "text-danger-600");
          }
        })
        .finally(function () { setBusy(form, false); });
    });
  }

  // ── Banner manager ─────────────────────────────────────────
  function initBannerManager() {
    var manager = $("[data-banner-manager]");
    if (!manager) return;

    var banners = JSON.parse(manager.getAttribute("data-banners") || "[]");
    var form = $("[data-banner-form]", manager);
    var placeholder = $("[data-editor-placeholder]", manager);
    var title = $("[data-editor-title]", manager);
    var newBtn = $("[data-editor-new]", manager);
    var cancelBtn = $("[data-editor-cancel]", manager);
    var message = $("[data-editor-message]", manager);

    function showEditor(banner) {
      var isEdit = banner && banner.id;
      title.textContent = isEdit ? "Edit Banner" : "New Banner";
      placeholder.classList.add("hidden");
      form.classList.remove("hidden");
      newBtn.style.display = "none";
      cancelBtn.style.display = "";
      message.classList.add("hidden");

      form.elements.id.value = isEdit ? banner.id : "";
      form.elements.title.value = banner ? banner.title : "";
      form.elements.subtitle.value = banner ? banner.subtitle || "" : "";
      form.elements.image.value = banner ? banner.image : "";
      form.elements.ctaText.value = banner ? banner.ctaText : "Shop Now";
      form.elements.ctaLink.value = banner ? banner.ctaLink : "/products";
      form.elements.sortOrder.value = banner ? banner.sortOrder : 0;
      form.elements.isActive.checked = banner ? !!banner.isActive : true;
    }

    function hideEditor() {
      title.textContent = "Banner Editor";
      placeholder.classList.remove("hidden");
      form.classList.add("hidden");
      newBtn.style.display = "";
      cancelBtn.style.display = "none";
    }

    newBtn.addEventListener("click", function () { showEditor(null); });
    cancelBtn.addEventListener("click", hideEditor);

    $all("[data-edit-banner]", manager).forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = +btn.getAttribute("data-edit-banner");
        var banner = banners.find(function (b) { return b.id === id; });
        if (banner) showEditor(banner);
      });
    });

    $all("[data-delete-banner]", manager).forEach(function (btn) {
      btn.addEventListener("click", function () {
        var name = btn.getAttribute("data-name");
        if (!window.confirm('Delete banner "' + name + '"?')) return;
        postJSON(manager.getAttribute("data-delete-url") + "/" + btn.getAttribute("data-delete-banner"))
          .then(function () { window.location.reload(); });
      });
    });

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var data = {
        id: form.elements.id.value ? +form.elements.id.value : null,
        title: form.elements.title.value,
        subtitle: form.elements.subtitle.value,
        image: form.elements.image.value,
        ctaText: form.elements.ctaText.value,
        ctaLink: form.elements.ctaLink.value,
        sortOrder: parseInt(form.elements.sortOrder.value, 10) || 0,
        isActive: form.elements.isActive.checked,
      };
      setBusy(form, true);
      postJSON(manager.getAttribute("data-save-url"), data)
        .then(function (res) {
          if (res.success) {
            window.location.reload();
          } else {
            message.textContent = res.message;
            message.classList.remove("hidden");
            message.classList.add("bg-red-50", "text-danger-600");
          }
        })
        .finally(function () { setBusy(form, false); });
    });
  }

  // ── Enquiry rows ───────────────────────────────────────────
  function initEnquiries() {
    var wrap = $("[data-enquiries]");
    if (!wrap) return;

    var statusStyles = {
      NEW: "bg-accent-50 text-accent-600 border-accent-500/30",
      CONTACTED: "bg-sky-50 text-sky-700 border-sky-300",
      CLOSED: "bg-black/5 text-ink-soft border-black/10",
    };
    var allStatusClasses = Object.keys(statusStyles).reduce(function (acc, k) {
      return acc.concat(statusStyles[k].split(" "));
    }, []);

    $all("[data-enquiry-row]", wrap).forEach(function (row) {
      var id = row.getAttribute("data-id");

      var toggle = $("[data-enquiry-toggle]", row);
      var details = $("[data-enquiry-details]", row);
      toggle.addEventListener("click", function () {
        var open = details.classList.toggle("hidden") === false;
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
        $("[data-icon-closed]", toggle).classList.toggle("hidden", open);
        $("[data-icon-open]", toggle).classList.toggle("hidden", !open);
      });

      var select = $("[data-enquiry-status]", row);
      select.addEventListener("change", function () {
        select.disabled = true;
        postJSON(wrap.getAttribute("data-status-url") + "/" + id, { status: select.value })
          .then(function () {
            allStatusClasses.forEach(function (c) { select.classList.remove(c); });
            statusStyles[select.value].split(" ").forEach(function (c) { select.classList.add(c); });
          })
          .finally(function () { select.disabled = false; });
      });

      var del = $("[data-enquiry-delete]", row);
      del.addEventListener("click", function () {
        if (!window.confirm(del.getAttribute("data-confirm"))) return;
        del.disabled = true;
        postJSON(wrap.getAttribute("data-delete-url") + "/" + id)
          .then(function () { window.location.reload(); })
          .catch(function () { del.disabled = false; });
      });
    });
  }

  // ── Settings form ──────────────────────────────────────────
  function initSettingsForm() {
    var form = $("[data-settings-form]");
    if (!form) return;
    var message = $("[data-settings-message]");

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var values = {};
      $all("input, textarea", form).forEach(function (el) {
        if (el.name) values[el.name] = el.value;
      });
      message.classList.add("hidden");
      setBusy(form, true);
      postJSON(form.getAttribute("data-save-url"), values)
        .then(function (res) {
          message.textContent = res.message;
          message.classList.remove("hidden");
        })
        .finally(function () { setBusy(form, false); });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initDeleteButtons();
    initImageUploads();
    initProductForm();
    initCategoryManager();
    initBannerManager();
    initEnquiries();
    initSettingsForm();
  });
})();
