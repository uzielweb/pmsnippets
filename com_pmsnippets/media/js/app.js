/**
 * Ponto Mega Snippets - Interactive Library, Live AJAX Search, Copy, Tabs & Useful Vote
 * Zero hardcoded user-facing strings (uses Joomla.Text._())
 */
(function () {
  const t = (key) => (window.Joomla && window.Joomla.Text ? window.Joomla.Text._(key) : key);

  async function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text);
      return true;
    }
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', '');
    ta.className = 'visually-hidden';
    document.body.appendChild(ta);
    ta.select();
    const ok = document.execCommand('copy');
    document.body.removeChild(ta);
    return ok;
  }

  async function trackCopy(copyUrl, token, snippetId) {
    if (!copyUrl || !token || !snippetId) {
      return;
    }
    try {
      const formData = new FormData();
      formData.append('id', String(snippetId));
      formData.append(token, '1');
      const res = await fetch(copyUrl, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      if (!res.ok) {
        return;
      }
      const payload = await res.json();
      if (payload && payload.data && typeof payload.data.copies_count !== 'undefined') {
        document.querySelectorAll(`[data-pms-copy-count="${snippetId}"]`).forEach((el) => {
          el.textContent = String(payload.data.copies_count);
        });
      }
    } catch (err) {
      console.error(err);
    }
  }

  function showCopiedState(button) {
    const label = button.querySelector('[data-pms-copy-label]');
    button.classList.add('is-copied');
    if (label) {
      const prev = label.textContent;
      label.textContent = t('COM_PMSNIPPETS_COPIED');
      window.setTimeout(() => {
        button.classList.remove('is-copied');
        label.textContent = prev || t('COM_PMSNIPPETS_COPY_CODE');
      }, 1800);
    }
  }

  function bindCopyHandlers(root, copyUrl, token) {
    // Card-level copy buttons
    root.querySelectorAll('[data-pms-copy-btn]').forEach((btn) => {
      if (btn.dataset.pmsBound === '1') {
        return;
      }
      btn.dataset.pmsBound = '1';
      btn.addEventListener('click', async () => {
        const card = btn.closest('.pms-card, .pms-home-console__panel, .pms-embed-card');
        const rawEl = card ? card.querySelector('[data-pms-raw-code]') : null;
        if (!rawEl) {
          return;
        }
        try {
          await copyToClipboard(rawEl.value);
          showCopiedState(btn);
          const id = parseInt(btn.getAttribute('data-snippet-id') || '0', 10);
          trackCopy(copyUrl, token, id);
        } catch (err) {
          console.error(err);
        }
      });
    });

    // Workbench active-tab copy buttons
    root.querySelectorAll('[data-pms-copy-active]').forEach((btn) => {
      if (btn.dataset.pmsBound === '1') {
        return;
      }
      btn.dataset.pmsBound = '1';
      btn.addEventListener('click', async () => {
        const wb = btn.closest('[data-pms-workbench]');
        const panel = wb ? wb.querySelector('.pms-workbench__panel.is-active') : null;
        const rawEl = panel ? panel.querySelector('[data-pms-raw-code]') : null;
        if (!rawEl) {
          return;
        }
        try {
          await copyToClipboard(rawEl.value);
          showCopiedState(btn);
          const id = parseInt(btn.getAttribute('data-snippet-id') || '0', 10);
          trackCopy(copyUrl, token, id);
        } catch (err) {
          console.error(err);
        }
      });
    });
  }

  function initWorkbench(scope) {
    scope.querySelectorAll('[data-pms-workbench]').forEach((wb) => {
      const tabs = wb.querySelectorAll('[data-pms-tab]');
      const panels = wb.querySelectorAll('[data-pms-panel]');

      tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
          const target = tab.getAttribute('data-pms-tab');
          tabs.forEach((el) => {
            const active = el.getAttribute('data-pms-tab') === target;
            el.classList.toggle('is-active', active);
            el.setAttribute('aria-selected', active ? 'true' : 'false');
          });
          panels.forEach((panel) => {
            panel.classList.toggle('is-active', panel.getAttribute('data-pms-panel') === target);
          });
        });
      });

      const downloadBtn = wb.querySelector('[data-pms-download-btn]');
      if (downloadBtn) {
        downloadBtn.addEventListener('click', () => {
          const panel = wb.querySelector('.pms-workbench__panel.is-active');
          const rawEl = panel ? panel.querySelector('[data-pms-raw-code]') : null;
          if (!rawEl) {
            return;
          }
          const ext = panel.getAttribute('data-file-ext') || 'txt';
          const base = downloadBtn.getAttribute('data-filename') || 'snippet';
          const blob = new Blob([rawEl.value], { type: 'text/plain;charset=utf-8' });
          const url = URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = `${base}.${ext}`;
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          URL.revokeObjectURL(url);
        });
      }
    });
  }

  function initHomeConsole() {
    document.querySelectorAll('[data-pms-home-module]').forEach((mod) => {
      const copyUrl = mod.getAttribute('data-copy-url') || '';
      const token = mod.getAttribute('data-csrf-token') || '';
      const tabs = mod.querySelectorAll('[data-pms-home-tab]');
      const panels = mod.querySelectorAll('[data-pms-home-panel]');

      tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
          const target = tab.getAttribute('data-pms-home-tab');
          tabs.forEach((el) => {
            const active = el.getAttribute('data-pms-home-tab') === target;
            el.classList.toggle('is-active', active);
            el.setAttribute('aria-selected', active ? 'true' : 'false');
          });
          panels.forEach((panel) => {
            panel.classList.toggle('is-active', panel.getAttribute('data-pms-home-panel') === target);
          });
        });
      });

      bindCopyHandlers(mod, copyUrl, token);
    });
  }

  function initLibrary() {
    const lib = document.querySelector('[data-pms-library]');
    if (!lib) {
      return;
    }

    const ajaxUrl = lib.getAttribute('data-ajax-url') || '';
    const copyUrl = lib.getAttribute('data-copy-url') || '';
    const token = lib.getAttribute('data-csrf-token') || '';
    const form = lib.querySelector('[data-pms-filter-form]');
    const searchInput = lib.querySelector('[data-pms-search]');
    const filterSelects = lib.querySelectorAll('[data-pms-filter]');
    const quickCats = lib.querySelectorAll('[data-pms-quick-cat]');
    const resetBtn = lib.querySelector('[data-pms-reset]');
    const resultsEl = lib.querySelector('[data-pms-results]');
    const summaryEl = lib.querySelector('[data-pms-summary]');
    const paginationEl = lib.querySelector('[data-pms-pagination]');

    bindCopyHandlers(lib, copyUrl, token);

    let debounceTimer = null;
    let activeAbort = null;

    const hasFilters = () => {
      if (!form) {
        return false;
      }
      const fd = new FormData(form);
      return Boolean(
        (fd.get('q') || '').toString().trim() ||
        Number(fd.get('catid') || 0) > 0 ||
        (fd.get('version') || '').toString().trim() ||
        (fd.get('code_lang') || '').toString().trim() ||
        (fd.get('context') || '').toString().trim() ||
        (fd.get('difficulty') || '').toString().trim()
      );
    };

    const syncQuickCats = () => {
      const catSelect = form ? form.querySelector('select[name="catid"]') : null;
      const currentCat = catSelect ? catSelect.value : '0';
      quickCats.forEach((btn) => {
        btn.classList.toggle('is-active', btn.getAttribute('data-pms-quick-cat') === currentCat);
      });
    };

    const runFilter = async () => {
      if (!form || !ajaxUrl || !resultsEl) {
        return;
      }

      if (activeAbort) {
        activeAbort.abort();
      }
      activeAbort = new AbortController();

      const params = new URLSearchParams();
      const fd = new FormData(form);
      fd.forEach((val, key) => {
        const str = val.toString().trim();
        if (str !== '' && !(key === 'catid' && str === '0')) {
          params.set(key, str);
        }
      });

      const sep = ajaxUrl.includes('?') ? '&' : '?';
      const fetchUrl = params.toString() ? `${ajaxUrl}${sep}${params.toString()}` : ajaxUrl;

      // Keep URL shareable without reloading
      const cleanUrl = new URL(window.location.href);
      cleanUrl.search = params.toString();
      window.history.replaceState({}, '', cleanUrl.toString());

      if (resetBtn) {
        resetBtn.classList.toggle('d-none', !hasFilters());
      }
      syncQuickCats();

      resultsEl.classList.add('is-loading');

      try {
        const res = await fetch(fetchUrl, {
          signal: activeAbort.signal,
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) {
          return;
        }
        const payload = await res.json();
        if (payload && payload.data) {
          resultsEl.innerHTML = payload.data.html || '';
          if (summaryEl && payload.data.summary) {
            summaryEl.textContent = payload.data.summary;
          }
          if (paginationEl) {
            paginationEl.innerHTML = payload.data.pagination || '';
          }
          if (window.PMSnippetsHighlighter) {
            window.PMSnippetsHighlighter.highlightContainer(resultsEl);
          }
          bindCopyHandlers(resultsEl, copyUrl, token);
        }
      } catch (err) {
        if (err && err.name !== 'AbortError') {
          console.error(err);
        }
      } finally {
        resultsEl.classList.remove('is-loading');
      }
    };

    if (form) {
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        runFilter();
      });
    }

    if (searchInput) {
      searchInput.addEventListener('input', () => {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(runFilter, 260);
      });
    }

    filterSelects.forEach((sel) => {
      sel.addEventListener('change', runFilter);
    });

    quickCats.forEach((btn) => {
      btn.addEventListener('click', () => {
        const catSelect = form ? form.querySelector('select[name="catid"]') : null;
        if (catSelect) {
          catSelect.value = btn.getAttribute('data-pms-quick-cat') || '0';
          runFilter();
        }
      });
    });

    if (resetBtn && form) {
      resetBtn.addEventListener('click', () => {
        if (searchInput) {
          searchInput.value = '';
        }
        filterSelects.forEach((sel) => {
          sel.value = sel.name === 'catid' ? '0' : (sel.name === 'sort' ? 'featured' : '');
        });
        runFilter();
      });
    }
  }

  function initDetail() {
    const detail = document.querySelector('[data-pms-detail]');
    if (!detail) {
      return;
    }

    const copyUrl = detail.getAttribute('data-copy-url') || '';
    const usefulUrl = detail.getAttribute('data-useful-url') || '';
    const token = detail.getAttribute('data-csrf-token') || '';

    initWorkbench(detail);
    bindCopyHandlers(detail, copyUrl, token);

    const usefulBtn = detail.querySelector('[data-pms-useful-btn]');
    if (usefulBtn && usefulUrl) {
      usefulBtn.addEventListener('click', async () => {
        const id = parseInt(usefulBtn.getAttribute('data-snippet-id') || '0', 10);
        const fd = new FormData();
        fd.append('id', String(id));
        fd.append(token, '1');
        try {
          const res = await fetch(usefulUrl, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
          });
          if (!res.ok) {
            return;
          }
          const payload = await res.json();
          if (payload && payload.data) {
            const countEl = usefulBtn.querySelector('[data-pms-useful-count]');
            const labelEl = usefulBtn.querySelector('[data-pms-useful-label]');
            if (countEl) {
              countEl.textContent = String(payload.data.useful_count);
            }
            if (labelEl) {
              labelEl.textContent = payload.data.already
                ? t('COM_PMSNIPPETS_USEFUL_ALREADY')
                : t('COM_PMSNIPPETS_USEFUL_THANKS');
            }
            usefulBtn.setAttribute('disabled', 'disabled');
          }
        } catch (err) {
          console.error(err);
        }
      });
    }
  }

  function initEmbeds() {
    document.querySelectorAll('.pms-embed-card').forEach((embed) => {
      const copyUrl = embed.getAttribute('data-copy-url') || '';
      const token = embed.getAttribute('data-csrf-token') || '';
      initWorkbench(embed);
      bindCopyHandlers(embed, copyUrl, token);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initLibrary();
    initDetail();
    initHomeConsole();
    initEmbeds();
  });
})();
