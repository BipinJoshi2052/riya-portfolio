(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var isSmallViewport = window.matchMedia('(max-width: 980px)').matches;

  /* ---------- Nav scroll state + mascot visibility ---------- */
  var nav = document.getElementById('rp-nav');
  var mascot = document.getElementById('rp-mascot');
  function onScroll() {
    var past = window.scrollY > 80;
    if (mascot) mascot.classList.toggle('is-visible', past);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ---------- Mascot eye tracking + blink ---------- */
  if (mascot && !isSmallViewport) {
    var mascotHead = mascot.querySelector('.rp-mascot-head');
    var mascotEyes = mascot.querySelector('.rp-mascot-eyes');
    var eyeL = mascot.querySelector('.rp-eye-l');
    var eyeR = mascot.querySelector('.rp-eye-r');
    var pupilL = mascot.querySelector('.rp-pupil-l');
    var pupilR = mascot.querySelector('.rp-pupil-r');
    var lastPointer = null;
    var rafId = null;

    function applyLook(x, y) {
      if (mascotHead) {
        mascotHead.style.transform = 'rotate(' + (x * 9).toFixed(2) + 'deg) translate(' + (x * 3).toFixed(2) + 'px, ' + (y * 2.5).toFixed(2) + 'px)';
      }
      if (mascotEyes) {
        mascotEyes.style.transform = 'translate(' + (x * 2.4).toFixed(2) + 'px, ' + (y * 2).toFixed(2) + 'px)';
      }
      if (pupilL) pupilL.setAttribute('cx', (37 + x * 3.4).toFixed(2));
      if (pupilR) pupilR.setAttribute('cx', (63 + x * 3.4).toFixed(2));
      if (pupilL) pupilL.setAttribute('cy', (52 + y * 3).toFixed(2));
      if (pupilR) pupilR.setAttribute('cy', (52 + y * 3).toFixed(2));
    }

    window.addEventListener('mousemove', function (e) {
      lastPointer = { x: e.clientX, y: e.clientY };
      if (rafId) return;
      rafId = requestAnimationFrame(function () {
        rafId = null;
        if (!lastPointer) return;
        var r = mascot.getBoundingClientRect();
        var cx = r.left + r.width / 2;
        var cy = r.top + r.height / 2;
        var nx = Math.max(-1, Math.min(1, (lastPointer.x - cx) / 420));
        var ny = Math.max(-1, Math.min(1, (lastPointer.y - cy) / 320));
        applyLook(nx, ny);
      });
    }, { passive: true });

    if (!reduceMotion) {
      setInterval(function () {
        if (eyeL) eyeL.setAttribute('ry', '1.2');
        if (eyeR) eyeR.setAttribute('ry', '1.2');
        setTimeout(function () {
          if (eyeL) eyeL.setAttribute('ry', '8.5');
          if (eyeR) eyeR.setAttribute('ry', '8.5');
        }, 130);
      }, 4200);
    }
  }

  /* ---------- Magnetic CTA button ---------- */
  var cta = document.getElementById('rp-cta');
  if (cta && !isSmallViewport) {
    cta.addEventListener('mousemove', function (e) {
      var r = cta.getBoundingClientRect();
      var x = (e.clientX - r.left - r.width / 2) * 0.35;
      var y = (e.clientY - r.top - r.height / 2) * 0.35;
      cta.style.transform = 'translate(' + x + 'px, ' + y + 'px)';
    });
    cta.addEventListener('mouseleave', function () {
      cta.style.transform = 'translate(0, 0)';
    });
  }

  /* ---------- Ambient cursor-chasing firefly (fixed overlay canvas) ---------- */
  var fireflyCanvas = document.getElementById('rp-firefly');
  if (fireflyCanvas && !reduceMotion && !isSmallViewport) {
    var fctx = fireflyCanvas.getContext('2d');
    var fw = 0, fh = 0;
    var fly = { x: window.innerWidth / 2, y: window.innerHeight / 2, vx: 0, vy: 0 };
    var trail = [];
    var t = 0;
    var fPointer = null;

    function fResize() {
      var dpr = Math.min(window.devicePixelRatio || 1, 2);
      fw = window.innerWidth; fh = window.innerHeight;
      fireflyCanvas.width = fw * dpr;
      fireflyCanvas.height = fh * dpr;
      fctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }
    fResize();
    window.addEventListener('resize', fResize);
    window.addEventListener('mousemove', function (e) { fPointer = { x: e.clientX, y: e.clientY }; }, { passive: true });

    function fFrame() {
      t += 0.045;
      var tx = (fPointer ? fPointer.x : fw / 2) + Math.cos(t * 1.7) * 26 + Math.sin(t * 0.9) * 12;
      var ty = (fPointer ? fPointer.y : fh / 2) + Math.sin(t * 1.3) * 22 + Math.cos(t * 0.7) * 10;
      fly.vx += (tx - fly.x) * 0.012;
      fly.vy += (ty - fly.y) * 0.012;
      fly.vx *= 0.9; fly.vy *= 0.9;
      fly.x += fly.vx; fly.y += fly.vy;

      trail.push({ x: fly.x, y: fly.y });
      if (trail.length > 22) trail.shift();

      fctx.clearRect(0, 0, fw, fh);
      trail.forEach(function (pt, i) {
        var k = i / trail.length;
        fctx.fillStyle = 'rgba(204,255,0,' + (0.1 * k * k) + ')';
        fctx.beginPath();
        fctx.arc(pt.x, pt.y, 1 + 3.5 * k, 0, Math.PI * 2);
        fctx.fill();
      });

      var pulse = 0.55 + 0.45 * Math.pow((Math.sin(t * 2.6) + 1) / 2, 1.6);
      var halo = fctx.createRadialGradient(fly.x, fly.y, 0, fly.x, fly.y, 34);
      halo.addColorStop(0, 'rgba(204,255,0,' + (0.42 * pulse) + ')');
      halo.addColorStop(0.45, 'rgba(204,255,0,' + (0.12 * pulse) + ')');
      halo.addColorStop(1, 'rgba(204,255,0,0)');
      fctx.fillStyle = halo;
      fctx.beginPath();
      fctx.arc(fly.x, fly.y, 34, 0, Math.PI * 2);
      fctx.fill();

      fctx.fillStyle = 'rgba(240,255,190,' + Math.min(1, 0.95 * pulse) + ')';
      fctx.beginPath();
      fctx.arc(fly.x, fly.y, 2.6, 0, Math.PI * 2);
      fctx.fill();

      requestAnimationFrame(fFrame);
    }
    fFrame();

    // Hide while the contact form is focused, matching the original design.
    var contactCard = document.querySelector('.rp-contact-form-card');
    var heroFliesCanvas = document.getElementById('rp-hero-flies');
    if (contactCard) {
      contactCard.addEventListener('focusin', function () {
        fireflyCanvas.style.opacity = '0';
        if (heroFliesCanvas) heroFliesCanvas.style.opacity = '0';
      });
      contactCard.addEventListener('focusout', function () {
        fireflyCanvas.style.opacity = '1';
        if (heroFliesCanvas) heroFliesCanvas.style.opacity = '1';
      });
    }
  }

  /* ---------- Ambient hero firefly field ---------- */
  var heroFlies = document.getElementById('rp-hero-flies');
  if (heroFlies && !reduceMotion && !isSmallViewport) {
    var hctx = heroFlies.getContext('2d');
    var hw = 0, hh = 0, flies = [];

    function hResize() {
      var dpr = Math.min(window.devicePixelRatio || 1, 2);
      var r = heroFlies.getBoundingClientRect();
      hw = r.width; hh = r.height;
      heroFlies.width = hw * dpr;
      heroFlies.height = hh * dpr;
      hctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      var count = Math.max(16, Math.min(42, Math.round(hw / 38)));
      flies = Array.from({ length: count }, function () {
        return {
          x: Math.random() * hw, y: Math.random() * hh,
          r: 0.9 + Math.random() * 1.8, sp: 0.1 + Math.random() * 0.3,
          dir: Math.random() * Math.PI * 2, turn: (Math.random() - 0.5) * 0.03,
          phase: Math.random() * Math.PI * 2, rate: 0.008 + Math.random() * 0.02
        };
      });
    }
    hResize();
    window.addEventListener('resize', hResize);

    function hFrame() {
      hctx.clearRect(0, 0, hw, hh);
      flies.forEach(function (f) {
        f.dir += f.turn + (Math.random() - 0.5) * 0.06;
        f.x += Math.cos(f.dir) * f.sp;
        f.y += Math.sin(f.dir) * f.sp;
        if (f.x < -20) f.x = hw + 20;
        if (f.x > hw + 20) f.x = -20;
        if (f.y < -20) f.y = hh + 20;
        if (f.y > hh + 20) f.y = -20;
        f.phase += f.rate;
        var glow = 0.15 + 0.85 * Math.pow((Math.sin(f.phase) + 1) / 2, 2.2);
        var halo = hctx.createRadialGradient(f.x, f.y, 0, f.x, f.y, f.r * 9);
        halo.addColorStop(0, 'rgba(204,255,0,' + (0.45 * glow) + ')');
        halo.addColorStop(1, 'rgba(204,255,0,0)');
        hctx.fillStyle = halo;
        hctx.beginPath();
        hctx.arc(f.x, f.y, f.r * 9, 0, Math.PI * 2);
        hctx.fill();
        hctx.fillStyle = 'rgba(233,255,150,' + Math.min(1, 0.8 * glow) + ')';
        hctx.beginPath();
        hctx.arc(f.x, f.y, f.r, 0, Math.PI * 2);
        hctx.fill();
      });
      requestAnimationFrame(hFrame);
    }
    hFrame();
  }

  /* ---------- Scroll reveal ---------- */
  var revealEls = document.querySelectorAll('[data-reveal], [data-reveal-x]');
  if ('IntersectionObserver' in window && revealEls.length) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(function (el) { observer.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('is-visible'); });
  }

  /* ---------- Timeline dots/years + progress bar ---------- */
  var tlRows = document.querySelectorAll('.rp-tl-row');
  var tlProgress = document.querySelector('.rp-tl-progress');
  if (tlRows.length) {
    var tlSeen = new Set();
    var tlObserver = 'IntersectionObserver' in window ? new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var row = entry.target;
        tlSeen.add(row);
        var dot = row.querySelector('.rp-tl-dot');
        var year = row.querySelector('.rp-tl-year');
        if (dot) dot.classList.add('is-visible');
        if (year) year.classList.add('is-visible');
        if (tlProgress) tlProgress.style.height = Math.round((tlSeen.size / tlRows.length) * 100) + '%';
        tlObserver.unobserve(row);
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }) : null;
    if (tlObserver) {
      tlRows.forEach(function (row) { tlObserver.observe(row); });
    } else {
      tlRows.forEach(function (row) {
        row.querySelectorAll('.rp-tl-dot, .rp-tl-year').forEach(function (el) { el.classList.add('is-visible'); });
      });
      if (tlProgress) tlProgress.style.height = '100%';
    }
  }

  /* ---------- Copy email to clipboard ---------- */
  var copyBtn = document.getElementById('rp-copy-email');
  if (copyBtn) {
    var copyLabel = copyBtn.querySelector('.rp-copy-label');
    copyBtn.addEventListener('click', function () {
      var email = copyBtn.getAttribute('data-email') || '';
      if (navigator.clipboard) navigator.clipboard.writeText(email).catch(function () {});
      if (copyLabel) {
        var original = copyLabel.getAttribute('data-original') || copyLabel.textContent;
        copyLabel.setAttribute('data-original', original);
        copyLabel.textContent = 'Copied ✓';
        clearTimeout(copyBtn._copyTimer);
        copyBtn._copyTimer = setTimeout(function () { copyLabel.textContent = original; }, 1800);
      }
    });
  }

  /* ---------- Project modal ---------- */
  var projects = window.__PROJECTS__ || [];
  var modalOverlay = document.getElementById('rp-modal-overlay');
  var modalBody = document.getElementById('rp-modal-body');

  function renderModal(p) {
    var logoHtml = p.logoSrc
      ? '<img src="' + p.logoSrc + '" alt="">'
      : '<div class="rp-modal-initials">' + (p.logoInitials || '') + '</div>';
    var tagsHtml = (p.tags || []).map(function (t) { return '<span class="rp-tag">' + t + '</span>'; }).join('');
    var metricsHtml = (p.metrics || []).map(function (m) {
      return '<div><div class="value">' + m.value + '</div><div class="label">' + m.label + '</div></div>';
    }).join('');

    modalBody.innerHTML =
      '<div class="rp-modal-close-row"><button type="button" class="rp-modal-close" id="rp-modal-close" aria-label="Close">' +
      '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f5f5f7" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>' +
      '</button></div>' +
      '<div class="rp-modal-logo">' + logoHtml + '</div>' +
      '<div class="rp-tags">' + tagsHtml + '</div>' +
      '<h3>' + p.title + '</h3>' +
      '<div class="meta">' + p.client + ' — ' + p.year + '</div>' +
      '<p class="desc">' + p.description + '</p>' +
      '<div class="rp-modal-metrics">' + metricsHtml + '</div>';

    document.getElementById('rp-modal-close').addEventListener('click', closeModal);
  }

  function openModal(idx) {
    var p = projects[idx];
    if (!p) return;
    renderModal(p);
    modalOverlay.classList.add('is-open');
  }
  function closeModal() {
    modalOverlay.classList.remove('is-open');
  }
  if (modalOverlay) {
    document.querySelectorAll('[data-project-index]').forEach(function (card) {
      card.addEventListener('click', function () {
        openModal(parseInt(card.getAttribute('data-project-index'), 10));
      });
    });
    modalOverlay.addEventListener('click', function (e) {
      if (e.target === modalOverlay) closeModal();
    });
    document.querySelector('.rp-modal').addEventListener('click', function (e) { e.stopPropagation(); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeModal();
    });
  }

  /* ---------- Contact form (real submit via fetch, idle/sending/sent/error) ---------- */
  var form = document.getElementById('rp-contact-form');
  var formCard = document.querySelector('.rp-contact-form-card');
  if (form && formCard) {
    var idlePanel = form;
    var sendingPanel = document.getElementById('rp-form-sending');
    var sentPanel = document.getElementById('rp-form-sent');

    function showPanel(panel) {
      [idlePanel, sendingPanel, sentPanel].forEach(function (p) {
        if (p) p.style.display = 'none';
      });
      if (panel) panel.style.display = '';
    }

    function showSent(success, message) {
      sentPanel.classList.toggle('is-error', !success);
      sentPanel.querySelector('.headline').textContent = success ? 'Message received' : 'Something went wrong';
      sentPanel.querySelector('p').textContent = message;
      showPanel(sentPanel);
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      showPanel(sendingPanel);

      fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'fetch' }
      })
        .then(function (res) { return res.json().catch(function () { return { success: false }; }); })
        .then(function (data) {
          if (data && data.success) {
            form.reset();
            showSent(true, data.message || 'Thanks for reaching out — your message has landed. Expect a reply within a couple of working days.');
          } else {
            showSent(false, (data && data.message) || 'Sorry, something went wrong. Please try again shortly.');
          }
        })
        .catch(function () {
          showSent(false, 'Sorry, something went wrong. Please check your connection and try again.');
        });
    });

    var againBtn = document.getElementById('rp-form-again');
    if (againBtn) {
      againBtn.addEventListener('click', function () { showPanel(idlePanel); });
    }
  }
})();
