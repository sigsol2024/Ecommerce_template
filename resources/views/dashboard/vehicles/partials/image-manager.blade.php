@php
  $supportsExistingGalleryDelete = $supportsExistingGalleryDelete ?? false;
@endphp

{{-- Render at end of <body> (see layouts.admin @stack('body-end')) so fixed overlays are not clipped by .admin-content-scroll --}}
@push('body-end')
<div id="image-preview-modal" class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/80 p-4">
  <button type="button" id="image-preview-close" class="absolute right-4 top-4 rounded-md bg-white/10 px-3 py-2 text-sm font-semibold text-white hover:bg-white/20">Close</button>
  <img id="image-preview-modal-image" src="" alt="" class="max-h-[90vh] max-w-[95vw] rounded-lg object-contain" />
</div>

<script>
  (function () {
    const supportsExistingGalleryDelete = @json((bool) $supportsExistingGalleryDelete);
    const mainInput = document.getElementById('main_image');
    const galleryInput = document.getElementById('images');
    const mainPreview = document.getElementById('main-image-preview');
    const galleryPreview = document.getElementById('gallery-preview');
    const mainClearBtn = document.getElementById('main-image-clear');
    const galleryClearBtn = document.getElementById('gallery-clear-all');
    const modal = document.getElementById('image-preview-modal');
    const modalImage = document.getElementById('image-preview-modal-image');
    const modalClose = document.getElementById('image-preview-close');
    const mainPathInput = document.getElementById('main_image_path');
    const galleryPathsHolder = document.getElementById('gallery-paths-holder');
    let galleryFiles = [];
    let galleryPathItems = [];

    if (!mainPreview || !galleryPreview || !mainClearBtn || !galleryClearBtn || !modal || !modalImage || !modalClose || !galleryPathsHolder) {
      return;
    }

    function openModal(url) {
      modalImage.src = url;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      modalImage.src = '';
      document.body.style.overflow = '';
    }

    function thumb(url, label, onRemove) {
      const wrap = document.createElement('div');
      wrap.className = 'relative overflow-hidden rounded-md border border-gray-200 bg-white';
      wrap.innerHTML =
        '<img src="' + url + '" alt="' + label + '" class="h-28 w-full cursor-zoom-in object-cover">' +
        '<button type="button" class="absolute right-2 top-2 rounded bg-white/90 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-white">Remove</button>';
      wrap.querySelector('img').addEventListener('click', () => openModal(url));
      wrap.querySelector('button').addEventListener('click', onRemove);
      return wrap;
    }

    function toPublicUrl(path) {
      const value = String(path || '').trim();
      if (!value) return '';
      if (/^https?:\/\//i.test(value)) return value;
      return '/' + value.replace(/^\/+/, '');
    }

    function syncGalleryInput() {
      if (!galleryInput) return;
      const transfer = new DataTransfer();
      galleryFiles.forEach((file) => transfer.items.add(file));
      galleryInput.files = transfer.files;
    }

    function renderGalleryPathInputs() {
      galleryPathsHolder.innerHTML = '';
      galleryPathItems.forEach((path, index) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'gallery_image_paths[' + index + ']';
        input.value = path;
        galleryPathsHolder.appendChild(input);
      });
    }

    function renderMain() {
      const file = mainInput && mainInput.files && mainInput.files[0];
      if (!file) {
        const path = (mainPathInput?.value || '').trim();
        if (!path) {
          mainPreview.classList.add('hidden');
          mainPreview.innerHTML = '';
          mainClearBtn.disabled = true;
          return;
        }
        mainPreview.classList.remove('hidden');
        mainPreview.innerHTML = '';
        mainPreview.appendChild(thumb(toPublicUrl(path), 'Main image', () => {
          if (mainPathInput) {
            mainPathInput.value = '';
          }
          renderMain();
        }));
        mainClearBtn.disabled = false;
        return;
      }
      const url = URL.createObjectURL(file);
      if (mainPathInput) {
        mainPathInput.value = '';
      }
      mainPreview.classList.remove('hidden');
      mainPreview.innerHTML = '';
        mainPreview.appendChild(thumb(url, 'Main image', () => {
          if (mainInput) mainInput.value = '';
          renderMain();
        }));
      mainClearBtn.disabled = false;
    }

    function renderGallery() {
      const pathUrls = galleryPathItems.map((path) => toPublicUrl(path));
      if (galleryFiles.length === 0 && pathUrls.length === 0) {
        galleryPreview.classList.add('hidden');
        galleryPreview.innerHTML = '';
        galleryClearBtn.disabled = true;
        return;
      }

      galleryPreview.classList.remove('hidden');
      galleryPreview.innerHTML = '';
      pathUrls.forEach((url, index) => {
        galleryPreview.appendChild(thumb(url, 'Gallery image', () => {
          galleryPathItems.splice(index, 1);
          renderGalleryPathInputs();
          renderGallery();
        }));
      });
      galleryFiles.forEach((file, index) => {
        const url = URL.createObjectURL(file);
        galleryPreview.appendChild(thumb(url, 'Gallery image', () => {
          galleryFiles.splice(index, 1);
          syncGalleryInput();
          renderGallery();
        }));
      });
      galleryClearBtn.disabled = false;
    }

    function markExistingImageForRemoval(button) {
      const imageId = Number(button.getAttribute('data-image-id') || '0');
      const card = button.closest('[data-image-card]');
      if (!card || !Number.isFinite(imageId) || imageId <= 0) {
        return;
      }
      const form = button.closest('form');
      if (!form) {
        return;
      }
      const hiddenName = 'remove_image_ids[]';
      const already = form.querySelector('input[name="' + hiddenName + '"][value="' + imageId + '"]');
      if (!already) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = hiddenName;
        input.value = String(imageId);
        form.appendChild(input);
      }
      card.remove();
      relabelExistingGallery();
    }

    function relabelExistingGallery() {
      const cards = Array.from(document.querySelectorAll('[data-image-card]'));
      cards.forEach((card, index) => {
        const role = card.querySelector('[data-image-role]');
        if (!role) {
          return;
        }
        const isFirst = index === 0;
        role.textContent = isFirst ? 'Featured image' : 'Gallery image';
        role.classList.toggle('text-indigo-600', isFirst);
        role.classList.toggle('text-gray-500', !isFirst);
      });
      updateClearButtonsState();
    }

    function getExistingImageCards() {
      return Array.from(document.querySelectorAll('[data-image-card]'));
    }

    function updateClearButtonsState() {
      const existingCards = getExistingImageCards();
      const hasExisting = existingCards.length > 0;
      const hasExistingMany = existingCards.length > 1;
      const hasPendingMainSelection = !!(mainInput && mainInput.files && mainInput.files[0]) || !!((mainPathInput?.value || '').trim());
      const hasPendingGallerySelection = galleryFiles.length > 0 || galleryPathItems.length > 0;

      mainClearBtn.disabled = !(hasPendingMainSelection || hasExisting);
      galleryClearBtn.disabled = !(hasPendingGallerySelection || hasExistingMany);
      galleryClearBtn.classList.toggle('hidden', !hasPendingGallerySelection && !hasExistingMany);
    }

    if (mainInput) mainInput.addEventListener('change', renderMain);
    mainClearBtn.addEventListener('click', () => {
      if (mainClearBtn.disabled) {
        return;
      }
      const existingCards = getExistingImageCards();
      if ((!mainInput || !mainInput.files || !mainInput.files[0]) && !((mainPathInput?.value || '').trim()) && existingCards.length > 0) {
        const featuredClearBtn = existingCards[0]?.querySelector('[data-clear-existing-image]');
        if (featuredClearBtn) {
          markExistingImageForRemoval(featuredClearBtn);
        }
        updateClearButtonsState();
        return;
      }
      if (mainInput) mainInput.value = '';
      if (mainPathInput) {
        mainPathInput.value = '';
      }
      renderMain();
      updateClearButtonsState();
    });

    if (galleryInput) {
      galleryInput.addEventListener('change', () => {
        galleryFiles = Array.from(galleryInput.files || []);
        galleryPathItems = [];
        renderGalleryPathInputs();
        renderGallery();
      });
    }

    galleryClearBtn.addEventListener('click', () => {
      if (galleryClearBtn.disabled) {
        return;
      }
      const hasPendingGallerySelection = galleryFiles.length > 0 || galleryPathItems.length > 0;
      if (!hasPendingGallerySelection) {
        const existingCards = getExistingImageCards();
        if (existingCards.length > 1) {
          existingCards.forEach((card) => {
            const btn = card.querySelector('[data-clear-existing-image]');
            if (btn) {
              markExistingImageForRemoval(btn);
            }
          });
        }
        updateClearButtonsState();
        return;
      }
      galleryFiles = [];
      galleryPathItems = [];
      syncGalleryInput();
      renderGalleryPathInputs();
      renderGallery();
      updateClearButtonsState();
    });

    document.querySelectorAll('[data-preview-image]').forEach((img) => {
      img.addEventListener('click', () => openModal(img.getAttribute('src')));
    });

    if (supportsExistingGalleryDelete) {
      document.querySelectorAll('[data-clear-existing-image]').forEach((button) => {
        button.addEventListener('click', () => markExistingImageForRemoval(button));
      });
    }

    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
      if (event.target === modal) {
        closeModal();
      }
    });

    renderMain();
    renderGallery();
    updateClearButtonsState();

    const mediaModal = document.getElementById('media-modal');
    const mediaGrid = document.getElementById('media-grid');
    const mediaSearch = document.getElementById('media-search');
    const mediaInsert = document.getElementById('media-modal-insert');
    const mediaUploadForm = document.getElementById('media-upload-form');
    const mediaUploadSubmit = document.getElementById('media-upload-submit');
    const mediaUploadStatus = document.getElementById('media-upload-status');
    const mediaListUrlEl = document.getElementById('media-list-url');
    const mediaListUrl = (mediaListUrlEl && mediaListUrlEl.value) ? mediaListUrlEl.value : @json(route('dashboard.api.media'));
    let mediaItems = [];
    let mediaTarget = null;
    let mediaSelected = [];
    let shiftAnchor = null;

    if (!mediaModal || !mediaGrid || !mediaSearch || !mediaInsert || !mediaUploadForm) {
      return;
    }

    function openMediaPicker(target) {
      mediaTarget = target;
      mediaSelected = [];
      shiftAnchor = null;
      mediaInsert.disabled = true;
      mediaInsert.textContent = 'Use selected image';
      mediaModal.style.position = 'fixed';
      mediaModal.style.inset = 'auto';
      mediaModal.style.zIndex = '220';
      updateMediaModalSizing();
      mediaModal.classList.remove('hidden');
      mediaModal.classList.add('flex');
      document.body.style.overflow = 'hidden';
      fetchMediaItems();
    }

    function closeMediaPicker() {
      mediaModal.classList.add('hidden');
      mediaModal.classList.remove('flex');
      mediaTarget = null;
      mediaSelected = [];
      document.body.style.overflow = '';
    }

    function updateMediaModalSizing() {
      const panel = document.getElementById('media-modal-panel');
      if (!mediaModal || !panel) return;
      const shell = document.querySelector('.admin-main-shell');
      const shellRect = shell?.getBoundingClientRect();
      const hasShellRect = !!(shellRect && shellRect.width > 0 && shellRect.height > 0);

      if (hasShellRect) {
        mediaModal.style.top = `${Math.round(shellRect.top)}px`;
        mediaModal.style.left = `${Math.round(shellRect.left)}px`;
        mediaModal.style.width = `${Math.round(shellRect.width)}px`;
        mediaModal.style.height = `${Math.round(shellRect.height)}px`;
      } else {
        mediaModal.style.top = '0';
        mediaModal.style.left = '0';
        mediaModal.style.width = '100vw';
        mediaModal.style.height = '100vh';
      }

      mediaModal.style.paddingLeft = '16px';
      mediaModal.style.paddingRight = '16px';
      const usable = Math.max(480, (hasShellRect ? shellRect.width : window.innerWidth) - 32);
      panel.style.maxWidth = `min(72rem, ${Math.round(usable)}px)`;
    }

    async function fetchMediaItems() {
      try {
        const response = await fetch(mediaListUrl, { credentials: 'same-origin' });
        if (!response.ok) {
          mediaItems = [];
          renderMediaGrid();
          return;
        }
        const data = await response.json();
        mediaItems = Array.isArray(data.media) ? data.media : [];
      } catch (e) {
        mediaItems = [];
      }
      renderMediaGrid();
    }

    function renderMediaGrid() {
      const escapeHtml = (value) => String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
      const q = (mediaSearch.value || '').toLowerCase();
      const items = mediaItems.filter((item) => !q || (item.name || '').toLowerCase().includes(q));
      mediaGrid.innerHTML = items.map((item, index) => {
        const selected = mediaSelected.includes(item.path);
        return '<button type="button" data-path="' + encodeURIComponent(item.path || '') + '" data-index="' + index + '" class="rounded-lg border p-2 text-left ' +
          (selected ? 'border-indigo-500 ring-2 ring-indigo-300 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300') +
          '"><img src="' + escapeHtml(item.url || '') + '" class="h-24 w-full rounded object-cover" alt="">' +
          '<p class="mt-2 truncate text-xs text-gray-700">' + escapeHtml(item.name || '') + '</p></button>';
      }).join('');

      mediaGrid.querySelectorAll('button[data-path]').forEach((button) => {
        button.addEventListener('click', (event) => {
          const path = decodeURIComponent(button.getAttribute('data-path') || '');
          const idx = Number(button.getAttribute('data-index'));
          if (!path) return;

          if (mediaTarget === 'gallery' && event.shiftKey && shiftAnchor !== null) {
            const [start, end] = [Math.min(shiftAnchor, idx), Math.max(shiftAnchor, idx)];
            const range = items.slice(start, end + 1).map((item) => item.path);
            mediaSelected = Array.from(new Set([...(mediaSelected || []), ...range]));
          } else if (mediaTarget === 'gallery' && (event.ctrlKey || event.metaKey)) {
            if (mediaSelected.includes(path)) {
              mediaSelected = mediaSelected.filter((value) => value !== path);
            } else {
              mediaSelected.push(path);
            }
            shiftAnchor = idx;
          } else if (mediaTarget === 'gallery') {
            if (mediaSelected.includes(path)) {
              mediaSelected = mediaSelected.filter((value) => value !== path);
            } else {
              mediaSelected = [...mediaSelected, path];
            }
            shiftAnchor = idx;
          } else {
            mediaSelected = [path];
            shiftAnchor = idx;
          }

          mediaInsert.disabled = mediaSelected.length === 0;
          mediaInsert.textContent = mediaSelected.length > 1 ? ('Use ' + mediaSelected.length + ' selected images') : 'Use selected image';
          renderMediaGrid();
        });
      });
    }

    mediaInsert.addEventListener('click', () => {
      if (mediaSelected.length === 0) return;

      if (mediaTarget === 'main') {
        if (mainInput) mainInput.value = '';
        if (mainPathInput) {
          mainPathInput.value = mediaSelected[0];
        }
        renderMain();
      } else if (mediaTarget === 'gallery') {
        galleryFiles = [];
        syncGalleryInput();
        galleryPathItems = mediaSelected.slice();
        renderGalleryPathInputs();
        renderGallery();
      }

      closeMediaPicker();
    });

    mediaSearch.addEventListener('input', renderMediaGrid);
    document.querySelectorAll('.js-media-modal-close').forEach((btn) => {
      btn.addEventListener('click', closeMediaPicker);
    });
    mediaModal.addEventListener('click', (event) => {
      if (event.target === mediaModal) closeMediaPicker();
    });
    window.addEventListener('resize', () => {
      if (!mediaModal.classList.contains('hidden')) updateMediaModalSizing();
    });

    document.getElementById('main-image-library')?.addEventListener('click', () => openMediaPicker('main'));
    document.getElementById('gallery-library')?.addEventListener('click', () => openMediaPicker('gallery'));

    mediaUploadForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const uploadInput = document.getElementById('media-upload-input');
      if (!uploadInput || !uploadInput.files || uploadInput.files.length === 0) {
        alert('Please choose one or more images first.');
        return;
      }
      const formData = new FormData(mediaUploadForm);
      const token = mediaUploadForm.querySelector('input[name="_token"]')?.value || '';
      if (mediaUploadSubmit) mediaUploadSubmit.disabled = true;
      if (uploadInput) uploadInput.disabled = true;
      mediaUploadForm.setAttribute('aria-busy', 'true');
      if (mediaUploadStatus) {
        mediaUploadStatus.classList.remove('hidden');
        const t = mediaUploadStatus.querySelector('.media-upload-status-text');
        if (t) t.textContent = 'Uploading...';
      }

      try {
        const response = await fetch(mediaUploadForm.action, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
          body: formData,
        });
        if (!response.ok) {
          if (mediaUploadStatus) {
            const t = mediaUploadStatus.querySelector('.media-upload-status-text');
            if (t) t.textContent = 'Upload failed';
          }
          alert('Upload failed. Please try again.');
          return;
        }

        if (mediaUploadStatus) {
          const t = mediaUploadStatus.querySelector('.media-upload-status-text');
          if (t) t.textContent = 'Upload complete';
        }
        mediaUploadForm.reset();
        await fetchMediaItems();
      } finally {
        if (mediaUploadSubmit) mediaUploadSubmit.disabled = false;
        if (uploadInput) uploadInput.disabled = false;
        mediaUploadForm.removeAttribute('aria-busy');
        if (mediaUploadStatus) {
          setTimeout(() => mediaUploadStatus.classList.add('hidden'), 900);
        }
      }
    });
  })();
</script>
@endpush
