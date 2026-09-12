/* ==========================================================================
   SV Schools - jQuery behaviour
   ========================================================================== */
(function ($) {
  "use strict";

  /* ---------------------------------------------------------------
   * 1. Highlight the current page in the nav menu
   * ------------------------------------------------------------- */
  function markActiveNav() {
    // Server-rendered pages (Laravel) already mark the active link themselves.
    if ($(".navbar .nav-link.active").length) { return; }

    var page = window.location.pathname.split("/").pop() || "index.html";
    $(".navbar .nav-link").each(function () {
      var href = $(this).attr("href");
      $(this).toggleClass("active", href === page);
    });
  }

  /* ---------------------------------------------------------------
   * 2. Animated counters (home page stats)
   * ------------------------------------------------------------- */
  function runCounters() {
    $(".counter").each(function () {
      var $el = $(this);
      if ($el.data("done")) { return; }
      $el.data("done", true);

      var target = parseInt($el.data("target"), 10) || 0;
      $({ n: 0 }).animate({ n: target }, {
        duration: 1400,
        easing: "swing",
        step: function () { $el.text(Math.floor(this.n).toLocaleString()); },
        complete: function () { $el.text(target.toLocaleString()); }
      });
    });
  }

  /* ---------------------------------------------------------------
   * 3. Student directory: search, filter, sort, add, delete
   * ------------------------------------------------------------- */
  var StudentTable = {
    $table: null,

    init: function () {
      this.$table = $("#studentTable");
      if (!this.$table.length) { return; }

      var self = this;

      // Live search + class filter + status filter
      $("#searchInput").on("keyup search", function () { self.applyFilters(); });
      $("#classFilter, #statusFilter").on("change", function () { self.applyFilters(); });

      $("#resetFilters").on("click", function () {
        $("#searchInput").val("");
        $("#classFilter, #statusFilter").val("");
        self.applyFilters();
      });

      // Column sorting
      this.$table.find("th.sortable").on("click", function () { self.sort($(this)); });

      // Delete a row (delegated - works for rows added later too)
      this.$table.on("click", ".btn-remove", function () {
        var $row = $(this).closest("tr");
        if (window.confirm("Remove " + $row.find(".s-name").text().trim() + " from the list?")) {
          $row.fadeOut(250, function () {
            $(this).remove();
            self.applyFilters();
          });
        }
      });

      // Add student form (inside the modal)
      $("#addStudentForm").on("submit", function (e) {
        e.preventDefault();
        if (!self.validateAdd($(this))) { return; }
        self.addRow({
          roll: $("#stuRoll").val().trim(),
          name: $("#stuName").val().trim(),
          cls: $("#stuClass").val(),
          guardian: $("#stuGuardian").val().trim(),
          contact: $("#stuContact").val().trim(),
          status: $("#stuStatus").val()
        });
        this.reset();
        $(this).removeClass("was-validated");
        var modalEl = document.getElementById("addStudentModal");
        bootstrap.Modal.getInstance(modalEl).hide();
        self.applyFilters();
        showToast("Student added to the directory.");
      });

      this.applyFilters();
    },

    validateAdd: function ($form) {
      var ok = $form[0].checkValidity();
      $form.addClass("was-validated");
      return ok;
    },

    addRow: function (s) {
      var initials = s.name.split(/\s+/).map(function (w) { return w.charAt(0); })
                       .join("").substring(0, 2).toUpperCase();
      var badge = s.status === "Active" ? "success" : "secondary";
      var $tr = $(
        "<tr>" +
          '<td class="s-roll">' + esc(s.roll) + "</td>" +
          '<td><span class="avatar me-2">' + esc(initials) + "</span>" +
            '<span class="s-name">' + esc(s.name) + "</span></td>" +
          '<td class="s-class">' + esc(s.cls) + "</td>" +
          "<td>" + esc(s.guardian) + "</td>" +
          "<td>" + esc(s.contact) + "</td>" +
          '<td><span class="badge text-bg-' + badge + ' s-status">' + esc(s.status) + "</span></td>" +
          '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger btn-remove">Remove</button></td>' +
        "</tr>"
      ).hide();
      this.$table.find("tbody").prepend($tr);
      $tr.fadeIn(300);
    },

    applyFilters: function () {
      var q = $.trim($("#searchInput").val()).toLowerCase();
      var cls = $("#classFilter").val();
      var status = $("#statusFilter").val();
      var shown = 0;

      this.$table.find("tbody tr").not(".no-result-row").each(function () {
        var $tr = $(this);
        var text = $tr.text().toLowerCase();
        var match = (!q || text.indexOf(q) > -1) &&
                    (!cls || $tr.find(".s-class").text().trim() === cls) &&
                    (!status || $tr.find(".s-status").text().trim() === status);
        $tr.toggle(match);
        if (match) { shown++; }
      });

      $("#resultCount").text(shown);
      $("#noResults").toggle(shown === 0);
    },

    sort: function ($th) {
      var idx = $th.index();
      var asc = !$th.hasClass("asc");
      var $tbody = this.$table.find("tbody");

      this.$table.find("th.sortable").removeClass("asc desc");
      $th.addClass(asc ? "asc" : "desc");

      var rows = $tbody.find("tr").not(".no-result-row").get();
      rows.sort(function (a, b) {
        var x = $(a).children().eq(idx).text().trim().toLowerCase();
        var y = $(b).children().eq(idx).text().trim().toLowerCase();
        var nx = parseFloat(x), ny = parseFloat(y);
        if (!isNaN(nx) && !isNaN(ny)) { return asc ? nx - ny : ny - nx; }
        return asc ? x.localeCompare(y) : y.localeCompare(x);
      });
      $.each(rows, function (i, row) { $tbody.append(row); });
    }
  };

  /* ---------------------------------------------------------------
   * 4. Admission form validation (Bootstrap styles + jQuery checks)
   * ------------------------------------------------------------- */
  function initAdmissionForm() {
    var $form = $("#admissionForm");
    if (!$form.length) { return; }

    // Extra rule: phone must be 10 digits
    $("#admPhone").on("input", function () {
      var valid = /^[0-9]{10}$/.test($(this).val().trim());
      this.setCustomValidity(valid ? "" : "Enter a 10 digit phone number.");
    });

    $form.on("submit", function (e) {
      e.preventDefault();
      e.stopPropagation();

      if (!this.checkValidity()) {
        $form.addClass("was-validated");
        $form.find(":invalid").first().trigger("focus");
        return;
      }

      var $btn = $form.find('button[type="submit"]');
      $btn.prop("disabled", true).text("Submitting...");

      // No backend here - simulate the request, then confirm.
      window.setTimeout(function () {
        $form[0].reset();
        $form.removeClass("was-validated");
        $btn.prop("disabled", false).text("Submit Application");
        $("#formSuccess").hide().removeClass("d-none").slideDown(300);
        $("html, body").animate({ scrollTop: $("#formSuccess").offset().top - 100 }, 400);
      }, 700);
    });

    // Clearing the form should also clear the red validation styling
    $form.on("reset", function () {
      $form.removeClass("was-validated");
      $("#admPhone")[0].setCustomValidity("");
      $("#formSuccess").slideUp(200);
    });

    $("#formSuccess .btn-close").on("click", function () {
      $("#formSuccess").slideUp(200);
    });
  }

  /* ---------------------------------------------------------------
   * 5. Show / hide password on the login and register forms
   * ------------------------------------------------------------- */
  function initPasswordToggle() {
    $(".toggle-password").on("click", function () {
      var $btn = $(this);
      var $input = $("#" + $btn.data("target"));
      if (!$input.length) { return; }
      var show = $input.attr("type") === "password";
      $input.attr("type", show ? "text" : "password");
      $btn.text(show ? "Hide" : "Show").attr("aria-label", show ? "Hide password" : "Show password");
    });
  }

  /* ---------------------------------------------------------------
   * 6. Back-to-top button
   * ------------------------------------------------------------- */
  function initBackToTop() {
    var $btn = $("#backToTop");
    $(window).on("scroll", function () {
      $btn.stop(true, true)[$(this).scrollTop() > 300 ? "fadeIn" : "fadeOut"](200);
    });
    $btn.on("click", function () {
      $("html, body").animate({ scrollTop: 0 }, 400);
    });
  }

  /* ---------------------------------------------------------------
   * Helpers
   * ------------------------------------------------------------- */
  function esc(str) {
    return $("<div>").text(str == null ? "" : str).html();
  }

  function showToast(message) {
    var $t = $("#appToast");
    if (!$t.length) { return; }
    $t.find(".toast-body").text(message);
    bootstrap.Toast.getOrCreateInstance($t[0]).show();
  }

  /* ---------------------------------------------------------------
   * Boot
   * ------------------------------------------------------------- */
  $(function () {
    markActiveNav();
    runCounters();
    StudentTable.init();
    initAdmissionForm();
    initPasswordToggle();
    initBackToTop();

    // Smooth scroll for in-page anchors
    $('a[href^="#"]').not('[data-bs-toggle], [href="#"]').on("click", function (e) {
      var $target = $($(this).attr("href"));
      if ($target.length) {
        e.preventDefault();
        $("html, body").animate({ scrollTop: $target.offset().top - 80 }, 400);
      }
    });
  });

})(jQuery);
