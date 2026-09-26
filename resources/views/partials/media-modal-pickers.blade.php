{{-- Single-path media picker for admin pages (e.g. listing-options). Excluded on admin.pages.edit which binds its own modal logic. --}}
<script>
(function () {
  function mtSyncMakeLogoPreview(tid) {
    var input = document.getElementById(tid);
    var wrap = document.querySelector('[data-mt-logo-preview-wrap="' + tid + '"]');
    var img = document.querySelector('[data-mt-logo-preview="' + tid + '"]');
    var hasPath = !!(input && String(input.value || '').trim() !== '');
    if (wrap) wrap.classList.toggle('hidden', !hasPath);
    if (img && img.tagName === 'IMG') {
      if (hasPath && input) {
        var clean = String(input.value || '').replace(/^\/+/, '');
        img.src = '/' + clean;
      } else {
        img.removeAttribute('src');
      }
    }
  }

  function mtSyncMakeLogoPickVisibilityFor(tid) {
    if (!tid || (tid.indexOf('make_logo_path_') !== 0 && tid !== 'modal_make_logo_path')) return;
    var input = document.getElementById(tid);
    var hasPath = !!(input && String(input.value || '').trim() !== '');
    document.querySelectorAll('.js-mt-media-pick[data-mt-media-target="' + tid + '"]').forEach(function (btn) {
      btn.classList.toggle('hidden', hasPath);
    });
    mtSyncMakeLogoPreview(tid);
  }

  function mtSyncMakeLogoPickVisibility() {
    document.querySelectorAll('.js-mt-media-pick[data-mt-media-target]').forEach(function (btn) {
      var tid = btn.getAttribute('data-mt-media-target');
      mtSyncMakeLogoPickVisibilityFor(tid);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    const mediaModal = document.getElementById('media-modal');
    const mediaGrid = document.getElementById('media-grid');
    const mediaSearch = document.getElementById('media-search');
    const mediaInsert = document.getElementById('media-modal-insert');
    const mediaUploadForm = document.getElementById('media-upload-form');
    const listUrlInput = document.getElementById('media-list-url');
    if (!mediaModal || !mediaGrid || !mediaSearch || !mediaInsert || !mediaUploadForm || !listUrlInput) return;

    const listUrl = listUrlInput.value;
    let mediaItems = [];
    let singleTargetInputId = null;
    let singleSelectedPath = null;

    function updateMediaModalSizing() {
      const panel = document.getElementById('media-modal-panel');
      if (!mediaModal || !panel) return;
      const shell = document.querySelector('.admin-main-shell');
      const shellRect = shell?.getBoundingClientRect();
      const hasShellRect = !!(shellRect && shellRect.width > 0 && shellRect.height > 0);
      if (hasShellRect) {
        mediaModal.style.top = Math.round(shellRect.top) + 'px';
        mediaModal.style.left = Math.round(shellRect.left) + 'px';
        mediaModal.style.width = Math.round(shellRect.width) + 'px';
        mediaModal.style.height = Math.round(shellRect.height) + 'px';
      } else {
        mediaModal.style.top = '0';
        mediaModal.style.left = '0';
        mediaModal.style.width = '100vw';
        mediaModal.style.height = '100vh';
      }
      mediaModal.style.paddingLeft = '16px';
      mediaModal.style.paddingRight = '16px';
      const usable = Math.max(480, (hasShellRect ? shellRect.width : window.innerWidth) - 32);
      panel.style.maxWidth = 'min(72rem, ' + Math.round(usable) + 'px)';
    }

    function closePickerModal() {
      singleTargetInputId = null;
      singleSelectedPath = null;
      mediaInsert.disabled = true;
      mediaModal.classList.add('hidden');
      mediaModal.classList.remove('flex');
      document.body.style.overflow = '';
    }

    function renderGrid() {
      const escapeHtml = function (value) {
        return String(value || '')
          .replace(/&/g, '&amp;')
          .replace(/"/g, '&quot;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;');
      };
      const q = (mediaSearch.value || '').toLowerCase();
      const items = mediaItems.filter(function (item) {
        return !q || (item.name || '').toLowerCase().includes(q);
      });
      mediaGrid.innerHTML = items.map(function (item) {
        const selected = singleSelectedPath === item.path;
        return '<button type="button" data-path="' + encodeURIComponent(item.path || '') + '" class="rounded-lg border p-2 text-left ' +
          (selected ? 'border-indigo-500 ring-2 ring-indigo-300 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300') +
          '"><img src="' + escapeHtml(item.url || '') + '" class="h-24 w-full rounded object-cover" alt="">' +
          '<p class="mt-2 truncate text-xs text-gray-700">' + escapeHtml(item.name || '') + '</p></button>';
      }).join('');

      mediaGrid.querySelectorAll('button[data-path]').forEach(function (button) {
        button.addEventListener('click', function () {
          const path = decodeURIComponent(button.getAttribute('data-path') || '');
          if (!path) return;
          singleSelectedPath = path;
          mediaInsert.disabled = false;
          renderGrid();
        });
      });
    }

    async function fetchItems() {
      try {
        const res = await fetch(listUrl, { credentials: 'same-origin' });
        if (!res.ok) {
          mediaItems = [];
        } else {
          const data = await res.json();
          mediaItems = Array.isArray(data.media) ? data.media : [];
        }
      } catch (e) {
        mediaItems = [];
      }
      renderGrid();
    }

    function openPickerForInput(targetInputId) {
      singleTargetInputId = targetInputId;
      singleSelectedPath = null;
      mediaInsert.disabled = true;
      mediaSearch.value = '';
      mediaModal.style.position = 'fixed';
      mediaModal.style.inset = 'auto';
      mediaModal.style.zIndex = '220';
      updateMediaModalSizing();
      mediaModal.classList.remove('hidden');
      mediaModal.classList.add('flex');
      document.body.style.overflow = 'hidden';
      fetchItems();
    }

    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.js-mt-media-pick');
      if (!btn) return;
      const id = btn.getAttribute('data-mt-media-target');
      if (!id) return;
      e.preventDefault();
      openPickerForInput(id);
    });

    if (!mediaInsert.dataset.mtSingleCapture) {
      mediaInsert.dataset.mtSingleCapture = '1';
      mediaInsert.addEventListener('click', function (e) {
        if (!singleTargetInputId || !singleSelectedPath) return;
        e.stopImmediatePropagation();
        const tid = singleTargetInputId;
        const path = singleSelectedPath;
        const input = document.getElementById(tid);
        if (input) {
          input.value = path;
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        mtSyncMakeLogoPickVisibilityFor(tid);
        closePickerModal();
      }, true);
    }

    if (!document.body.dataset.mtPickerModalUi) {
      document.body.dataset.mtPickerModalUi = '1';
      document.querySelectorAll('.js-media-modal-close').forEach(function (btn) {
        btn.addEventListener('click', function () {
          if (singleTargetInputId) closePickerModal();
        });
      });
      mediaModal.addEventListener('click', function (e) {
        if (e.target === mediaModal && singleTargetInputId) closePickerModal();
      });
      window.addEventListener('resize', function () {
        if (singleTargetInputId && !mediaModal.classList.contains('hidden')) updateMediaModalSizing();
      });
      mediaSearch.addEventListener('input', function () {
        if (!singleTargetInputId) return;
        renderGrid();
      });
    }

    if (!mediaUploadForm.dataset.mtPickerUpload) {
      mediaUploadForm.dataset.mtPickerUpload = '1';
      mediaUploadForm.addEventListener('submit', async function (ev) {
        if (!singleTargetInputId) return;
        ev.preventDefault();
        const uploadInput = document.getElementById('media-upload-input');
        const submitBtn = document.getElementById('media-upload-submit');
        const statusEl = document.getElementById('media-upload-status');
        if (!uploadInput || !uploadInput.files || !uploadInput.files.length) return;
        const formData = new FormData(mediaUploadForm);
        const token = mediaUploadForm.querySelector('input[name="_token"]')?.value || '';
        if (submitBtn) submitBtn.disabled = true;
        uploadInput.disabled = true;
        if (statusEl) {
          statusEl.classList.remove('hidden');
          const t = statusEl.querySelector('.media-upload-status-text');
          if (t) t.textContent = @json(__('Uploading…'));
        }
        try {
          const res = await fetch(mediaUploadForm.action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
          });
          if (res.ok) {
            uploadInput.value = '';
            await fetchItems();
          }
        } finally {
          if (submitBtn) submitBtn.disabled = false;
          uploadInput.disabled = false;
          if (statusEl) setTimeout(function () { statusEl.classList.add('hidden'); }, 900);
        }
      });
    }

    mtSyncMakeLogoPickVisibility();

    document.addEventListener('click', function (e) {
      var clr = e.target.closest('[data-mt-logo-clear]');
      if (!clr) return;
      e.preventDefault();
      var tid = clr.getAttribute('data-mt-logo-clear');
      if (!tid) return;
      var input = document.getElementById(tid);
      if (input) {
        input.value = '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }
      mtSyncMakeLogoPickVisibilityFor(tid);
    }, true);

    document.addEventListener('input', function (e) {
      var t = e.target;
      if (!t || !t.id) return;
      if (t.id === 'modal_make_logo_path' || t.id.indexOf('make_logo_path_') === 0) {
        mtSyncMakeLogoPickVisibilityFor(t.id);
      }
    }, true);
  });
})();
</script>
