(function ($) {
  'use strict';

  function pictureInput($picture) {
    return $picture.find('.psplus-image-value').first();
  }

  function setStatus($picture, message, type) {
    var $status = $picture.find('.psplus-upload-status');
    $status.text(message || '').removeClass('is-error is-success');
    if (type) {
      $status.addClass('is-' + type);
    }
  }

  function updateControls($picture) {
    var $preview = $picture.find('.psplus-preview');
    var has = $preview.length && !$preview.hasClass('is-hidden') && !!$preview.attr('src');
    var label = has ? 'Change picture' : 'Add picture';
    $picture.find('.psplus-image-placeholder').toggleClass('is-hidden', has);
    $picture.find('.psplus-image-edit').toggleClass('has-picture', has).attr('title', label);
    $picture.find('.psplus-image-edit-text').text(label);
    $picture.find('.psplus-remove-image').prop('disabled', !has);
  }

  function showPreview($picture, url) {
    var $preview = $picture.find('.psplus-preview');
    if (!url) {
      $preview.attr('src', '').addClass('is-hidden');
      updateControls($picture);
      return;
    }
    $preview.attr('src', url).removeClass('is-hidden');
    updateControls($picture);
  }

  function pendingUploads($form, change) {
    if (!$form || !$form.length) {
      return 0;
    }
    var count = Math.max(0, (parseInt($form.data('psplus-pending') || 0, 10) + change));
    $form.data('psplus-pending', count);
    $form.find('[name$="apply"],[name$="submit"]').each(function () {
      var $control = $(this);
      if (count > 0) {
        if (!$control.prop('disabled')) {
          $control.prop('disabled', true);
          $control.data('psplus-upload-disabled', 1);
        }
      } else if ($control.data('psplus-upload-disabled') === 1) {
        $control.prop('disabled', false);
        $control.removeData('psplus-upload-disabled');
      }
    });
    return count;
  }

  function absUrl($root, val) {
    if (!val) {
      return '';
    }
    if (/^(https?:)?\/\//i.test(val) || val.charAt(0) === '/') {
      return val;
    }
    var base = $root.attr('data-uploads-url') || '';
    var parts = val.replace(/^\//, '').split('/');
    for (var i = 0; i < parts.length; i++) {
      parts[i] = encodeURIComponent(parts[i]);
    }
    return base.replace(/\/$/, '') + '/' + parts.join('/');
  }

  function uploadImage($root, $picture, file, imageLink) {
    if ($picture.data('psplus-xhr')) {
      return;
    }
    var url = $root.attr('data-upload-url') || '';
    var $input = $picture.find('.psplus-upload-file');
    var $form = $root.closest('form');
    if (!url) {
      $input.val('');
      setStatus($picture, 'Picture upload is unavailable. Reload the page and try again.', 'error');
      return;
    }
    if (file && file.type && !/^image\/(jpeg|png|gif|webp)$/i.test(file.type)) {
      $input.val('');
      setStatus($picture, 'Please choose a JPG, PNG, GIF or WebP picture.', 'error');
      return;
    }
    if (file && (file.size <= 0 || file.size > 8388608)) {
      $input.val('');
      setStatus($picture, 'Pictures must be smaller than 8 MB.', 'error');
      return;
    }

    var data = new FormData();
    if (imageLink) {
      data.append('psuploadimage_url', imageLink);
    } else {
      data.append('psuploadfile', file, file.name);
    }

    var xhr = new XMLHttpRequest();
    $picture.data('psplus-xhr', xhr);
    $input.prop('disabled', true);
    $picture.find('.psplus-source-controls button').prop('disabled', true);
    $picture.find('.psplus-remove-image').prop('disabled', true);
    pendingUploads($form, 1);
    setStatus($picture, imageLink ? 'Copying picture…' : 'Uploading picture…', '');

    function finish() {
      $picture.removeData('psplus-xhr');
      $input.prop('disabled', false);
      $picture.find('.psplus-source-controls button').prop('disabled', false);
      pendingUploads($form, -1);
    }

    xhr.open('POST', url, true);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) {
        return;
      }
      var result = null;
      try {
        result = JSON.parse(xhr.responseText || '');
      } catch (e) {}
      $input.val('');
      if (xhr.status >= 200 && xhr.status < 300 && result && result.success && result.image && result.url) {
        if ($picture.data('psplus-blob')) {
          try { URL.revokeObjectURL($picture.data('psplus-blob')); } catch (e) {}
          $picture.removeData('psplus-blob');
        }
        pictureInput($picture).val(result.image);
        showPreview($picture, result.url);
        setStatus($picture, 'Picture uploaded. Apply or Submit to save the page.', 'success');
        markChanged();
      } else {
        var stored = pictureInput($picture).val();
        showPreview($picture, stored ? absUrl($root, stored) : '');
        $picture.find('.psplus-remove-image').prop('disabled', !stored);
        setStatus($picture, (result && result.error) ? result.error : 'The picture could not be uploaded. Please try again.', 'error');
      }
      finish();
    };
    xhr.onerror = function () {
      $input.val('');
      setStatus($picture, 'The picture could not be uploaded. Please try again.', 'error');
      finish();
    };
    xhr.send(data);
  }

  function markChanged() {
    if (window.jQuery) {
      window.jQuery(document).trigger('cmsms_formchange');
    }
  }

  function firstTinyEditor() {
    if (!window.tinymce) {
      return null;
    }
    if (window.tinymce.activeEditor) {
      return window.tinymce.activeEditor;
    }
    if (typeof window.tinymce.get === 'function') {
      var list = window.tinymce.get();
      if (list && list.length) {
        return list[0];
      }
    }
    if (window.tinymce.editors && window.tinymce.editors.length) {
      return window.tinymce.editors[0];
    }
    return null;
  }

  function optionFromEditor(editor, name) {
    if (!editor) {
      return undefined;
    }
    if (editor.options && typeof editor.options.get === 'function') {
      try {
        return editor.options.get(name);
      } catch (e) {}
    }
    if (editor.settings && editor.settings[name] !== undefined) {
      return editor.settings[name];
    }
    return undefined;
  }

  function copyEditorSettings(editor) {
    var settings = {};
    var src = (editor && editor.settings) ? editor.settings : {};
    Object.keys(src).forEach(function (key) {
      if (key !== 'target' && key !== 'selector' && key !== 'elements' && key !== 'id') {
        settings[key] = src[key];
      }
    });
    [
      'license_key', 'plugins', 'toolbar', 'toolbar1', 'toolbar2', 'menubar', 'height',
      'min_height', 'width', 'skin', 'content_css', 'content_style', 'language',
      'promotion', 'branding', 'statusbar', 'resize', 'browser_spellcheck',
      'entity_encoding', 'convert_urls', 'relative_urls', 'remove_script_host',
      'document_base_url', 'valid_elements', 'extended_valid_elements',
      'paste_as_text', 'file_picker_callback', 'automatic_uploads', 'setup',
      'init_instance_callback', 'forced_root_block', 'newline_behavior'
    ].forEach(function (key) {
      var value = optionFromEditor(editor, key);
      if (value !== undefined && value !== null) {
        settings[key] = value;
      }
    });
    if (!settings.license_key) {
      settings.license_key = 'gpl';
    }
    settings.promotion = false;
    return settings;
  }

  function editorInitOptions($root, textarea) {
    var saved = ($root.get(0) && $root.get(0)._psplusEditorSettings) ? $root.get(0)._psplusEditorSettings : {};
    var settings = $.extend(true, {}, saved);
    settings.selector = '#' + textarea.id;
    settings.license_key = settings.license_key || 'gpl';
    settings.promotion = false;
    delete settings.target;
    delete settings.elements;
    delete settings.id;
    return settings;
  }

  function rememberEditor($root, textarea, editor) {
    var root = $root.get(0);
    if (editor && !root._psplusEditorSettings) {
      root._psplusEditorSettings = copyEditorSettings(editor);
    }
    if (!root._psplusEditorClasses && textarea) {
      root._psplusEditorClasses = Array.prototype.filter.call(textarea.classList, function (name) {
        return name && !/^psplus_/.test(name);
      });
    }
  }

  function applyEditorClasses($root, textarea) {
    var classes = $root.get(0)._psplusEditorClasses;
    if (!textarea || !classes) {
      return;
    }
    classes.forEach(function (name) {
      if (name) {
        textarea.classList.add(name);
      }
    });
  }

  function saveEditors($scope) {
    if (window.tinymce && typeof window.tinymce.triggerSave === 'function') {
      try { window.tinymce.triggerSave(); } catch (e) {}
    }
    if (!window.tinymce) {
      return;
    }
    $scope.find('textarea.psplus-wysiwyg').each(function () {
      var ed = window.tinymce.get(this.id);
      if (ed) {
        try { ed.save(); } catch (e) {}
      }
    });
  }

  function bindWysiwyg($root, textarea) {
    if (!textarea || !window.tinymce) {
      return;
    }
    var attempts = 0;
    var timer = window.setInterval(function () {
      attempts++;
      var ed = null;
      try { ed = window.tinymce.get(textarea.id); } catch (e) {}
      if (ed) {
        window.clearInterval(timer);
        textarea._psplusInitPending = false;
        rememberEditor($root, textarea, ed);
        if (!ed._psplusBound) {
          ed._psplusBound = true;
          try {
            ed.on('change input keyup undo redo SetContent blur', function () { markChanged(); });
          } catch (e) {}
        }
      } else if (attempts >= 40) {
        window.clearInterval(timer);
        textarea._psplusInitPending = false;
      }
    }, 100);
  }

  function initEditorFromSavedSettings($root, textarea, attempt) {
    if (!$root.length || !textarea || !window.tinymce || window.tinymce.get(textarea.id)) {
      return;
    }
    if (textarea._psplusInitPending) {
      return;
    }
    attempt = attempt || 0;
    applyEditorClasses($root, textarea);
    var source = firstTinyEditor();
    if (source) {
      rememberEditor($root, source.getElement ? source.getElement() : textarea, source);
    }
    var saved = $root.get(0)._psplusEditorSettings;
    if (!saved || !saved.license_key) {
      if (attempt < 40 && !saved) {
        window.setTimeout(function () { initEditorFromSavedSettings($root, textarea, attempt + 1); }, 100);
        return;
      }
    }
    var settings = editorInitOptions($root, textarea);
    textarea._psplusInitPending = true;
    try {
      window.tinymce.init(settings);
      bindWysiwyg($root, textarea);
    } catch (e) {
      textarea._psplusInitPending = false;
    }
  }

  function initDynamicEditor($root, textarea, attempt) {
    if (!$root.length || !textarea) {
      return;
    }
    attempt = attempt || 0;
    applyEditorClasses($root, textarea);
    if (!window.tinymce) {
      if (attempt < 40) {
        window.setTimeout(function () { initDynamicEditor($root, textarea, attempt + 1); }, 100);
      }
      return;
    }
    if (window.tinymce.get(textarea.id) || textarea._psplusInitPending) {
      return;
    }
    var source = firstTinyEditor();
    if (source) {
      rememberEditor($root, source.getElement ? source.getElement() : textarea, source);
    }
    if (!$root.get(0)._psplusEditorSettings && attempt < 40) {
      window.setTimeout(function () { initDynamicEditor($root, textarea, attempt + 1); }, 100);
      return;
    }
    initEditorFromSavedSettings($root, textarea, 0);
  }

  function removeEditors($sec) {
    $sec.find('textarea.psplus-wysiwyg').each(function () {
      if (!window.tinymce) {
        return;
      }
      try {
        var ed = window.tinymce.get(this.id);
        if (ed) {
          ed.remove();
        }
      } catch (e) {}
    });
  }

  function orderedSections($root) {
    return $root.find('.psplus-sections > .psplus-section').toArray().sort(function (a, b) {
      var ao = parseInt(a.getAttribute('data-order'), 10);
      var bo = parseInt(b.getAttribute('data-order'), 10);
      if (isNaN(ao)) {
        ao = 0;
      }
      if (isNaN(bo)) {
        bo = 0;
      }
      if (ao !== bo) {
        return ao - bo;
      }
      var pos = a.compareDocumentPosition(b);
      if (pos & Node.DOCUMENT_POSITION_FOLLOWING) {
        return -1;
      }
      if (pos & Node.DOCUMENT_POSITION_PRECEDING) {
        return 1;
      }
      return 0;
    });
  }

  function applyOrder($root, items) {
    items.forEach(function (el, i) {
      el.setAttribute('data-order', String(i));
      el.style.order = String(i);
      var num = el.querySelector('.psplus-num');
      if (num) {
        num.textContent = String(i + 1);
      }
      var up = el.querySelector('.psplus-up');
      var down = el.querySelector('.psplus-down');
      if (up) {
        up.disabled = i === 0;
      }
      if (down) {
        down.disabled = i === items.length - 1;
      }
    });
    reindexNames($root, items);
  }

  function reindexNames($root, items) {
    var block = $root.attr('data-block');
    items.forEach(function (el, i) {
      $(el).find('[name]').each(function () {
        var name = this.getAttribute('name');
        if (!name) {
          return;
        }
        this.setAttribute('name', name.replace(/^[^\[]+\[items\]\[[^\]]+\]/, block + '[items][' + i + ']'));
      });
    });
  }

  function moveSection($root, item, target, after) {
    if (!item || !target || item === target) {
      return;
    }
    var items = orderedSections($root).filter(function (el) { return el !== item; });
    var targetIndex = items.indexOf(target);
    if (targetIndex < 0) {
      return;
    }
    items.splice(targetIndex + (after ? 1 : 0), 0, item);
    applyOrder($root, items);
    markChanged();
  }

  function toggleType($root, $col) {
    var type = $col.find('.psplus-type').val();
    $col.find('.psplus-image-fields').toggle(type === 'image');
    $col.find('.psplus-gallery-fields').toggle(type === 'gallery');
    $col.find('.psplus-html-fields').toggle(type === 'wysiwyg');
    if (type === 'wysiwyg') {
      $col.find('textarea.psplus-wysiwyg').each(function () {
        initDynamicEditor($root, this);
      });
    }
  }

  function updateSideLabels($section) {
    var $left = $section.find('.psplus-col[data-side="left"]').first();
    var $right = $section.find('.psplus-col[data-side="right"]').first();
    var $leftLegend = $left.children('legend').first();
    var $rightLegend = $right.children('legend').first();
    var leftLabel = $leftLegend.attr('data-default-label') || 'Left';
    var rightLabel = $rightLegend.attr('data-default-label') || 'Right';
    var $swap = $section.find('.psplus-swap input[type="checkbox"]').first();
    var swapped = $swap.length
      ? $swap.prop('checked')
      : $section.children('input[type="hidden"][name$="[swap]"]').val() === '1';

    $leftLegend.text(swapped ? rightLabel : leftLabel);
    $rightLegend.text(swapped ? leftLabel : rightLabel);
  }

  function isFileDrag(e) {
    var dt = e.originalEvent && e.originalEvent.dataTransfer;
    if (!dt) {
      return false;
    }
    return Array.prototype.indexOf.call(dt.types || [], 'Files') !== -1;
  }

  function prepareForm($root) {
    saveEditors($root);
    applyOrder($root, orderedSections($root));
  }

  function readCookie(name) {
    var prefix = name + '=';
    var cookies = document.cookie ? document.cookie.split(';') : [];
    for (var i = 0; i < cookies.length; i++) {
      var value = cookies[i].trim();
      if (value.indexOf(prefix) === 0) {
        return decodeURIComponent(value.substring(prefix.length));
      }
    }
    return '';
  }

  function setupCollapse($root) {
    var pageId = parseInt($root.attr('data-page-id') || '0', 10);
    var safe = String($root.attr('data-safe') || 'sections').replace(/[^A-Za-z0-9_-]/g, '');
    var cookieName = pageId > 0 ? 'psplusEditor_' + pageId + '_' + safe : '';
    var cookiePath = window.location.pathname.replace(/[^/]*$/, '') || '/';
    var showLabel = $root.attr('data-show-label') || 'Show Page Sections';
    var hideLabel = $root.attr('data-hide-label') || 'Hide Page Sections';
    var $button = $root.find('.psplus-toggle-all').first();

    function setCollapsed(collapsed, remember) {
      $root.toggleClass('psplus-collapsed', collapsed);
      $button.text(collapsed ? showLabel : hideLabel);
      $button.attr('aria-expanded', collapsed ? 'false' : 'true');
      if (remember && cookieName) {
        document.cookie = cookieName + '=' + encodeURIComponent(collapsed ? 'hidden' : 'shown')
          + '; Path=' + cookiePath + '; Max-Age=31536000; SameSite=Lax'
          + (window.location.protocol === 'https:' ? '; Secure' : '');
      }
    }

    setCollapsed(cookieName && readCookie(cookieName) === 'hidden', false);
    $button.on('click', function () {
      setCollapsed(!$root.hasClass('psplus-collapsed'), true);
    });
  }

  function sharedFilePickerInput($root) {
    var $host = $root.find('.psplus-shared-filepicker').first();
    var $input = $host.find('input.cms_filepicker, input[data-cmsms-fp-type], input[data-fp-type], input[type="text"]').first();
    if (!$input.length) {
      $input = $host.find('input[name]').first();
    }
    return $input;
  }

  function applyFilePickerValue($root) {
    var $picture = $root.data('psplus-filepicker-picture');
    if (!$picture || !$picture.length) {
      return;
    }
    var $input = sharedFilePickerInput($root);
    if (!$input.length) {
      return;
    }
    var value = $.trim($input.val() || '');
    if (!value || value === ($root.data('psplus-filepicker-value') || '')) {
      return;
    }
    $root.data('psplus-filepicker-value', value);
    pictureInput($picture).val(value);
    showPreview($picture, absUrl($root, value));
    setStatus($picture, 'Picture selected. Apply or Submit to save the page.', 'success');
    markChanged();
  }

  function openSharedFilePicker($root, $picture) {
    var $host = $root.find('.psplus-shared-filepicker').first();
    var $input = sharedFilePickerInput($root);
    if (!$host.length || !$input.length) {
      setStatus($picture, 'The CMSMS File Picker is unavailable.', 'error');
      return;
    }

    var current = pictureInput($picture).val() || '';
    $root.data('psplus-filepicker-picture', $picture);
    $root.data('psplus-filepicker-value', current);
    $input.val(current);

    var $trigger = $host.find('button, input[type="button"], a').first();
    if ($trigger.length) {
      $trigger.trigger('click');
    } else {
      $input.trigger('click');
    }
  }

  function bind($root) {
    if ($root.data('psplus-bound')) {
      return;
    }
    $root.data('psplus-bound', true);
    setupCollapse($root);

    var $list = $root.find('.psplus-sections');
    applyOrder($root, orderedSections($root));

    $root.find('textarea.psplus-wysiwyg').each(function () {
      bindWysiwyg($root, this);
      rememberEditor($root, this, window.tinymce ? window.tinymce.get(this.id) : firstTinyEditor());
    });
    if (!$root.get(0)._psplusEditorSettings && firstTinyEditor()) {
      rememberEditor($root, null, firstTinyEditor());
    }

    $root.on('click', '.psplus-add', function (e) {
      e.preventDefault();
      saveEditors($root);
      var uid = 'n' + Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
      var html = $root.find('.psplus-template').html() || '';
      html = html.split('_new_').join('_' + uid + '_');
      html = html.split('[items][new]').join('[items][' + uid + ']');
      var $item = $(html).filter('.psplus-section');
      if (!$item.length) {
        $item = $('<div/>').html(html).children('.psplus-section');
      }
      var next = $list.children('.psplus-section').length;
      $item.attr('draggable', 'false');
      $item.attr('data-order', String(next));
      $item.css('order', String(next));
      $list.append($item);
      applyOrder($root, orderedSections($root));
      $item.find('.psplus-col').each(function () {
        toggleType($root, $(this));
      });
      $item.find('.psplus-picture').each(function () {
        updateControls($(this));
      });
      updateSideLabels($item);
      window.setTimeout(function () {
        $item.find('textarea.psplus-wysiwyg').each(function () {
          applyEditorClasses($root, this);
          initDynamicEditor($root, this);
        });
      }, 0);
      markChanged();
    });

    $root.on('click', '.psplus-remove', function (e) {
      e.preventDefault();
      var $sec = $(this).closest('.psplus-section');
      removeEditors($sec);
      $sec.remove();
      if ($list.find('.psplus-section').length === 0) {
        $root.find('.psplus-add').trigger('click');
      } else {
        applyOrder($root, orderedSections($root));
      }
      markChanged();
    });

    $root.on('click', '.psplus-up', function (e) {
      e.preventDefault();
      var item = $(this).closest('.psplus-section').get(0);
      var items = orderedSections($root);
      var index = items.indexOf(item);
      var prev = index > 0 ? items[index - 1] : null;
      if (prev) {
        moveSection($root, item, prev, false);
      }
    });

    $root.on('click', '.psplus-down', function (e) {
      e.preventDefault();
      var item = $(this).closest('.psplus-section').get(0);
      var items = orderedSections($root);
      var index = items.indexOf(item);
      var next = index >= 0 && index < items.length - 1 ? items[index + 1] : null;
      if (next) {
        moveSection($root, item, next, true);
      }
    });

    $root.on('mousedown', '.psplus-handle', function () {
      $(this).closest('.psplus-section').attr('draggable', 'true');
    });
    $root.on('mouseup', '.psplus-handle', function () {
      var $sec = $(this).closest('.psplus-section');
      window.setTimeout(function () { $sec.attr('draggable', 'false'); }, 0);
    });

    $root.on('dragstart', '.psplus-section', function (e) {
      if ($(this).attr('draggable') !== 'true') {
        e.preventDefault();
        return;
      }
      $(this).addClass('psplus-dragging');
      e.originalEvent.dataTransfer.effectAllowed = 'move';
      try { e.originalEvent.dataTransfer.setData('text/plain', 'section'); } catch (err) {}
    });

    $root.on('dragend', '.psplus-section', function () {
      $(this).removeClass('psplus-dragging').attr('draggable', 'false');
      $root.get(0)._psplusDropTarget = null;
      $root.get(0)._psplusDropAfter = false;
      $root.find('.psplus-section').removeClass('psplus-drop-before psplus-drop-after');
      applyOrder($root, orderedSections($root));
      markChanged();
    });

    $list.on('dragover', function (e) {
      var dragging = $root.find('.psplus-dragging').get(0);
      if (!dragging || isFileDrag(e)) {
        return;
      }
      e.preventDefault();
      var target = $(e.target).closest('.psplus-section').get(0);
      if (!target || target === dragging) {
        return;
      }
      $root.find('.psplus-section').removeClass('psplus-drop-before psplus-drop-after');
      var rect = target.getBoundingClientRect();
      var after = e.originalEvent.clientY > rect.top + rect.height / 2;
      $(target).addClass(after ? 'psplus-drop-after' : 'psplus-drop-before');
      $root.get(0)._psplusDropTarget = target;
      $root.get(0)._psplusDropAfter = after;
    });

    $list.on('drop', function (e) {
      var dragging = $root.find('.psplus-dragging').get(0);
      var target = $root.get(0)._psplusDropTarget;
      if (!dragging || !target || isFileDrag(e)) {
        return;
      }
      e.preventDefault();
      moveSection($root, dragging, target, !!$root.get(0)._psplusDropAfter);
      $root.get(0)._psplusDropTarget = null;
      $root.find('.psplus-section').removeClass('psplus-drop-before psplus-drop-after');
    });

    $root.on('change', '.psplus-type', function () {
      toggleType($root, $(this).closest('.psplus-col'));
      markChanged();
    });

    $root.on('change', '.psplus-swap input[type="checkbox"]', function () {
      updateSideLabels($(this).closest('.psplus-section'));
      markChanged();
    });

    $root.on('click', '.psplus-choose-file', function (e) {
      e.preventDefault();
      $(this).closest('.psplus-picture').find('.psplus-upload-file').trigger('click');
    });

    $root.on('click', '.psplus-use-filepicker', function (e) {
      e.preventDefault();
      openSharedFilePicker($root, $(this).closest('.psplus-picture'));
    });

    $root.on('change input', '.psplus-shared-filepicker input', function () {
      applyFilePickerValue($root);
    });

    $root.on('click', '.psplus-choose-link', function (e) {
      e.preventDefault();
      var $btn = $(this);
      var $fields = $btn.closest('.psplus-source-controls').find('.psplus-link-fields');
      var open = $fields.prop('hidden');
      $fields.prop('hidden', !open);
      $btn.attr('aria-expanded', open ? 'true' : 'false');
      $btn.text(open ? 'Use image link −' : 'Use image link +');
      if (open) {
        $fields.find('.psplus-image-link').trigger('focus');
      }
    });

    $root.on('click', '.psplus-import-link', function (e) {
      e.preventDefault();
      var $picture = $(this).closest('.psplus-picture');
      var link = $.trim($picture.find('.psplus-image-link').val() || '');
      if (!/^https?:\/\//i.test(link)) {
        setStatus($picture, 'Paste a direct image link starting with https:// or http://.', 'error');
        return;
      }
      uploadImage($root, $picture, null, link);
    });

    $root.on('keydown', '.psplus-image-link', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        $(this).closest('.psplus-source-controls').find('.psplus-import-link').trigger('click');
      }
    });

    $root.on('change', '.psplus-upload-file', function () {
      var $input = $(this);
      var $picture = $input.closest('.psplus-picture');
      var file = this.files && this.files[0];
      if ($picture.data('psplus-blob')) {
        try { URL.revokeObjectURL($picture.data('psplus-blob')); } catch (err) {}
        $picture.removeData('psplus-blob');
      }
      if (file && /^image\//.test(file.type)) {
        try {
          var blob = URL.createObjectURL(file);
          $picture.data('psplus-blob', blob);
          showPreview($picture, blob);
        } catch (err) {}
      }
      if (file) {
        uploadImage($root, $picture, file, null);
      }
    });

    $root.on('click', '.psplus-remove-image', function (e) {
      e.preventDefault();
      var $picture = $(this).closest('.psplus-picture');
      if ($picture.data('psplus-blob')) {
        try { URL.revokeObjectURL($picture.data('psplus-blob')); } catch (err) {}
        $picture.removeData('psplus-blob');
      }
      pictureInput($picture).val('');
      $picture.find('.psplus-image-link').val('');
      showPreview($picture, '');
      setStatus($picture, '', '');
      markChanged();
    });

    $root.on('dragenter dragover', '.psplus-image-frame', function (e) {
      if (!isFileDrag(e)) {
        return;
      }
      e.preventDefault();
      e.stopPropagation();
      $(this).addClass('psplus-file-dragover');
    });
    $root.on('dragleave', '.psplus-image-frame', function (e) {
      if (!this.contains(e.relatedTarget)) {
        $(this).removeClass('psplus-file-dragover');
      }
    });
    $root.on('drop', '.psplus-image-frame', function (e) {
      var $frame = $(this);
      $frame.removeClass('psplus-file-dragover');
      var dt = e.originalEvent && e.originalEvent.dataTransfer;
      if (!dt || !dt.files || !dt.files.length) {
        return;
      }
      e.preventDefault();
      e.stopPropagation();
      var $picture = $frame.closest('.psplus-picture');
      if ($picture.data('psplus-xhr')) {
        setStatus($picture, 'Please wait for the current picture to finish uploading.', '');
        return;
      }
      if (dt.files.length !== 1) {
        setStatus($picture, 'Please drop one image file at a time.', 'error');
        return;
      }
      uploadImage($root, $picture, dt.files[0], null);
    });

    var $form = $root.closest('form');
    if ($form.length && !$form.data('psplus-form-bound')) {
      $form.data('psplus-form-bound', 1);
      $form.on('submit', function () {
        $('.psplus-admin').each(function () {
          prepareForm($(this));
        });
      });
      $form.on('mousedown pointerdown', 'button, input', function () {
        var type = String(this.getAttribute('type') || '').toLowerCase();
        if (this.tagName === 'BUTTON' || type === 'submit' || type === 'button' || type === 'image') {
          prepareForm($root);
        }
      });
    }

    $root.find('.psplus-col').each(function () {
      toggleType($root, $(this));
    });
    $root.find('.psplus-picture').each(function () {
      updateControls($(this));
    });
    $root.find('.psplus-section').each(function () {
      updateSideLabels($(this));
    });

    window.setInterval(function () {
      applyFilePickerValue($root);
    }, 400);
  }

  $(function () {
    $('.psplus-admin').each(function () {
      bind($(this));
    });
  });
})(jQuery);
