(function ($) {
    'use strict';
    function normalizeHex(v) { v = String(v || '').trim();
 if (!v)
        return '';
 if (v.charAt(0) !== '#')
        v = '#' + v;
 return /^#[0-9a-fA-F]{6}$/.test(v) ? v.toLowerCase() : '';
 }
    function syncGroupColorHex() { var c = $('#bl-g-color'), h = $('#bl-g-color-hex');
 if (c.length && h.length) {
        var v = normalizeHex(c.val());
        if (v)
            h.val(v);
    } }
    var AI = (window.KashidaBranchLocatorAdmin && KashidaBranchLocatorAdmin.i18n) || {};
    function at(k, d) { return AI[k] || d;
 }
    var modal = $('#bl-branch-modal'), groupMod = $('#bl-group-modal');
    function openModal(m) { m.show();
 $('body').addClass('bl-modal-open');
 if (m.attr('id') === 'bl-display-builder-modal') {
        var layout = m.find('input[name="bl-builder-layout"]:checked').val() || 'table';
        window.requestAnimationFrame(function () { renderBuilderPreview(m, layout); });
    }
 }
    function closeModal(m) { m.hide();
 $('body').removeClass('bl-modal-open');
 }
    function notify(msg, type) { var cls = type === 'error' ? 'notice-error' : 'notice-success';
 var n = $('<div class="notice ' + cls + ' is-dismissible"><p>' + msg + '</p></div>');
 $('.wp-header-end').after(n);
 setTimeout(function () { n.fadeOut(400, function () { n.remove();
 });
 }, 4000);
 }
    $(document).on('click', '.bl-btn-add-branch', function () { $('#bl-modal-title').text('Add Branch');
 $('#bl-branch-form')[0].reset();
 $('#bl-branch-id').val('');
 openModal(modal);
 });
    $(document).on('click', '.bl-edit-branch', function () { var d = $(this).data();
 $('#bl-modal-title').text('Edit Branch');
 $('#bl-branch-id').val(d.id);
 $('#bl-f-name').val(d.name);
 $('#bl-f-city').val(d.city);
 $('#bl-f-phone').val(d.phone);
 $('#bl-f-phone2').val(d.phone2 || '');
 $('#bl-f-website').val(d.website || '');
 $('#bl-f-address').val(d.address);
 $('#bl-f-lat').val(d.lat);
 $('#bl-f-lng').val(d.lng);
 $('#bl-f-group').val(d.group || '');
 openModal(modal);
 });
    $(document).on('click', '.bl-modal-close', function () { closeModal(modal);
 closeModal(groupMod);
 closeModal($('#bl-display-builder-modal'));
 });
    $(document).on('click', '.bl-modal-overlay', function () { closeModal(modal);
 closeModal(groupMod);
 closeModal($('#bl-display-builder-modal'));
 });
    $('#bl-branch-form').on('submit', function (e) { e.preventDefault();
 var btn = $(this).find('[type=submit]').prop('disabled', true).text(KashidaBranchLocatorAdmin.i18n.saving);
 $.post(KashidaBranchLocatorAdmin.ajax_url, { action: 'kashida_branch_locator_save_branch', nonce: KashidaBranchLocatorAdmin.nonce, id: $('#bl-branch-id').val(), name: $('#bl-f-name').val(), city: $('#bl-f-city').val(), phone: $('#bl-f-phone').val(), phone2: $('#bl-f-phone2').val(), website: $('#bl-f-website').val(), address: $('#bl-f-address').val(), latitude: $('#bl-f-lat').val(), longitude: $('#bl-f-lng').val(), group_id: $('#bl-f-group').val() }).done(function (r) { if (r.success) {
        closeModal(modal);
        notify(r.data.message);
        location.reload();
    }
    else {
        notify(r.data.message, 'error');
    } }).always(function () { btn.prop('disabled', false).text(KashidaBranchLocatorAdmin.i18n.save);
 });
 });
    $(document).on('click', '.bl-delete-branch', function () { if (!confirm($(this).hasClass('bl-delete-branch') ? (KashidaBranchLocatorAdmin.i18n.confirm_delete_branch || KashidaBranchLocatorAdmin.i18n.confirm_delete) : (KashidaBranchLocatorAdmin.i18n.confirm_delete_group || KashidaBranchLocatorAdmin.i18n.confirm_delete)))
        return;
 var btn = $(this).prop('disabled', true).text(KashidaBranchLocatorAdmin.i18n.deleting), row = $(this).closest('tr');
 $.post(KashidaBranchLocatorAdmin.ajax_url, { action: 'kashida_branch_locator_delete_branch', nonce: KashidaBranchLocatorAdmin.nonce, id: $(this).data('id') }).done(function (r) { if (r.success) {
        row.fadeOut(300, function () { row.remove();
 });
        notify(r.data.message);
    }
    else {
        notify(r.data.message, 'error');
        btn.prop('disabled', false).text(KashidaBranchLocatorAdmin.i18n.delete);
    } });
 });
    $(document).on('click', '.bl-btn-add-group', function () { $('#bl-group-modal-title').text('Add Group');
 $('#bl-group-form')[0].reset();
 $('#bl-group-id').val('');
 syncGroupColorHex();
 openModal(groupMod);
 });
    $(document).on('click', '.bl-edit-group', function () { var d = $(this).data();
 $('#bl-group-modal-title').text('Edit Group');
 $('#bl-group-id').val(d.id);
 $('#bl-g-name').val(d.name);
 $('#bl-g-color').val(d.color);
 if ($.fn.wpColorPicker && $('#bl-g-color').data('wpWpColorPicker')) { $('#bl-g-color').wpColorPicker('color', d.color); }
 $('#bl-g-color-hex').val(d.color);
 openModal(groupMod);
 });
    $('#bl-group-form').on('submit', function (e) { e.preventDefault();
 var btn = $(this).find('[type=submit]').prop('disabled', true).text(KashidaBranchLocatorAdmin.i18n.saving);
 $.post(KashidaBranchLocatorAdmin.ajax_url, { action: 'kashida_branch_locator_save_group', nonce: KashidaBranchLocatorAdmin.nonce, id: $('#bl-group-id').val(), name: $('#bl-g-name').val(), color: (normalizeHex($('#bl-g-color-hex').val()) || $('#bl-g-color').val()) }).done(function (r) { if (r.success) {
        closeModal(groupMod);
        notify(r.data.message);
        location.reload();
    }
    else {
        notify((r.data && r.data.message) || at('save_error', 'Unable to save the group.'), 'error');
    } }).fail(function (xhr) { var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message; notify(message || at('save_error', 'Unable to save the group. Please try again.'), 'error'); }).always(function () { btn.prop('disabled', false).text(KashidaBranchLocatorAdmin.i18n.save);
 });
 });
    $(document).on('click', '.bl-delete-group', function () { if (!confirm(KashidaBranchLocatorAdmin.i18n.confirm_delete))
        return;
 var row = $(this).closest('tr');
 $.post(KashidaBranchLocatorAdmin.ajax_url, { action: 'kashida_branch_locator_delete_group', nonce: KashidaBranchLocatorAdmin.nonce, id: $(this).data('id') }).done(function (r) { if (r.success) {
        row.fadeOut(300, function () { row.remove();
 });
        notify(r.data.message);
    }
    else {
        notify(r.data.message, 'error');
    } });
 });
    $(document).on('click', '.bl-copy-shortcode', function () { var btn = $(this), value = btn.attr('data-shortcode') || '';
 if (!value)
        return;
 var done = function () { var original = btn.text();
 btn.text(KashidaBranchLocatorAdmin.i18n.copied);
 setTimeout(function () { btn.text(original);
 }, 1400);
 };
 if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(value).then(done).catch(function () { fallbackCopy(value, done);
 });
    }
    else {
        fallbackCopy(value, done);
    } });
    function fallbackCopy(value, done) { var ta = document.createElement('textarea');
 ta.value = value;
 ta.setAttribute('readonly', '');
 ta.style.position = 'fixed';
 ta.style.opacity = '0';
 document.body.appendChild(ta);
 ta.select();
 try {
        document.execCommand('copy');
        done();
    }
    finally {
        document.body.removeChild(ta);
    } }
    $(document).on('click', '.bl-open-display-builder', function () { var btn = $(this), m = $('#bl-display-builder-modal'), branch = String(btn.data('branch') || '');
 if (branch) {
        m.data('selection-type', 'branch').data('selection-values', branch).data('group', '').data('group-name', btn.data('branch-name') || '').data('branch-group-slug', btn.data('branch-group-slug') || '').data('group-color', btn.data('group-color') || '').data('preview-group-name', btn.data('branch-group-name') || '');
        m.data('preview-groups', []);
        m.data('preview-items', [{id:'preview-branch',name:btn.data('branch-name')||'Central Branch',city:btn.data('branch-city')||'',address:btn.data('branch-address')||'',phone:btn.data('branch-phone')||'',phone2:btn.data('branch-phone2')||'',website:btn.data('branch-website')||'',latitude:btn.data('branch-latitude'),longitude:btn.data('branch-longitude'),group_name:btn.data('branch-group-name')||'',group_slug:btn.data('branch-group-slug')||'',group_color:btn.data('group-color')||'#1769e8'}]);
        m.find('.bl-builder-group-name').text(btn.data('branch-name') || '');
    }
    else {
        m.data('selection-type', 'group').data('selection-values', String(btn.data('group') || '')).data('group', String(btn.data('group') || '')).data('group-name', btn.data('group-name')).data('branch-group-slug', '').data('group-color', btn.data('group-color') || '').data('preview-group-name', btn.data('group-name') || '');
        m.data('preview-groups', [{slug:String(btn.data('group')||''),name:String(btn.data('group-name')||'Group'),color:String(btn.data('group-color')||'#1769e8')}]);
        m.data('preview-items', []);
        m.find('.bl-builder-group-name').text(btn.data('group-name'));
    } resetBuilder();
 updateGeneratedShortcode();
 openModal(m);
 });
    $(document).on('click', '.bl-bulk-display', function () { var toolbar = $(this).closest('.bl-bulk-toolbar'), type = toolbar.data('page-type') === 'branches' ? 'branch' : 'group', values = [];
 toolbar.closest('.wrap').find('.bl-row-select:checked').each(function () { var v = $(this).data('slug');
 if (v)
        values.push(String(v));
 });
 if (!values.length)
        return;
 var m = $('#bl-display-builder-modal'), previewItems=[], previewGroups=[];
 toolbar.closest('.wrap').find('.bl-row-select:checked').each(function(){var row=$(this),slug=String(row.data('slug')||'');if(type==='branch'){previewItems.push({id:'preview-'+slug,name:row.data('preview-name')||'',city:row.data('preview-city')||'',address:row.data('preview-address')||'',phone:row.data('preview-phone')||'',phone2:row.data('preview-phone2')||'',website:row.data('preview-website')||'',latitude:row.data('preview-latitude'),longitude:row.data('preview-longitude'),group_name:row.data('preview-group-name')||'',group_slug:row.data('preview-group-slug')||'',group_color:row.data('preview-group-color')||'#1769e8'});}else{previewGroups.push({slug:slug,name:row.data('preview-name')||slug,color:row.data('preview-color')||'#1769e8'});}});
 m.data('selection-type', type).data('selection-values', values.join(',')).data('group', '').data('group-name', '').data('branch-group-slug', '').data('group-color', '').data('preview-group-name', '').data('preview-items',previewItems).data('preview-groups',previewGroups);
 m.find('.bl-builder-group-name').text(values.length + ' ' + (type === 'branch' ? ((KashidaBranchLocatorAdmin && KashidaBranchLocatorAdmin.i18n && KashidaBranchLocatorAdmin.i18n.branches) || 'branches') : ((KashidaBranchLocatorAdmin && KashidaBranchLocatorAdmin.i18n && KashidaBranchLocatorAdmin.i18n.groups) || 'groups')));
 resetBuilder();
 updateGeneratedShortcode();
 openModal(m);
 });
    function resetBuilder() { var m = $('#bl-display-builder-modal');
 m.find('input[name="bl-builder-layout"][value="table"]').prop('checked', true);
 m.find('[data-builder-field="name"],[data-builder-field="city"],[data-builder-field="address"],[data-builder-field="phone"]').prop('checked', true);
 m.find('[data-builder-field="group"]').prop('checked', false);
 m.find('[data-builder-option="search"],[data-builder-option="pagination"],[data-builder-option="sorting"],[data-builder-option="city_filter"],[data-builder-option="group_filter"],[data-builder-option="call"],[data-builder-option="directions"]').prop('checked', true);
 m.find('[data-builder-option="per_page"]').val(15);
 m.find('[data-builder-option="height"]').val(520);
 m.find('[data-builder-option="zoom"]').val(6);
 m.find('[data-builder-option="columns"]').val(3);
 m.find('[data-builder-option="group_cards"]').prop('checked', false);
 }
    function updateGeneratedShortcode() { var m = $('#bl-display-builder-modal'), type = m.data('selection-type') || 'group', values = String(m.data('selection-values') || '').split(',').filter(Boolean), group = m.data('group') || '', layout = m.find('input[name="bl-builder-layout"]:checked').val() || 'table', a = [];
 if (type === 'branch') {
        a.push(values.length > 1 ? 'branches="' + values.join(',') + '"' : 'branch="' + (values[0] || '') + '"');
    }
    else {
        a.push(values.length > 1 ? 'groups="' + values.join(',') + '"' : 'group="' + (values[0] || group) + '"');
    } if (layout !== 'table')
        a.push('layout="' + layout + '"');
 ['name', 'city', 'address', 'phone', 'group'].forEach(function (k) { var c = m.find('[data-builder-field="' + k + '"]').prop('checked');
 if (!c)
        a.push('show_' + k + '="false"');
    else if (k === 'group')
        a.push('show_group="true"');
 });
 ['search', 'pagination', 'sorting', 'city_filter', 'group_filter'].forEach(function (k) { a.push(k + '="' + (m.find('[data-builder-option="' + k + '"]').prop('checked') ? 'true' : 'false') + '"');
 });
 ['call', 'directions'].forEach(function (k) { if (!m.find('[data-builder-option="' + k + '"]').prop('checked'))
        a.push('show_' + k + '="false"');
 });
 var per = parseInt(m.find('[data-builder-option="per_page"]').val(), 10) || 15, height = parseInt(m.find('[data-builder-option="height"]').val(), 10) || 520, zoom = parseInt(m.find('[data-builder-option="zoom"]').val(), 10) || 6, columns = parseInt(m.find('[data-builder-option="columns"]').val(), 10) || 3, groupCards = m.find('[data-builder-option="group_cards"]').prop('checked');
 if (layout === 'cards' && columns !== 3)
        a.push('columns="' + Math.max(1, Math.min(6, columns)) + '"');
 if (groupCards)
        a.push('group_cards="true"');
 if (per !== 15)
        a.push('per_page="' + Math.max(1, Math.min(100, per)) + '"');
 if (height !== 520)
        a.push('height="' + Math.max(300, Math.min(1200, height)) + '"');
 if (zoom !== 6)
        a.push('zoom="' + Math.max(1, Math.min(18, zoom)) + '"');
 m.find('#bl-generated-shortcode').text('[kashida_branch_locator ' + a.join(' ') + ']');
 renderBuilderPreview(m, layout);
 }
    function renderBuilderPreview(m, layout) {
        var frame = m.find('#bl-builder-preview').get(0);
        if (!frame) return;
        var preview = (window.KashidaBranchLocatorAdmin && KashidaBranchLocatorAdmin.preview) || {};

        function renderInsideFrame() {
            var win = frame.contentWindow;
            if (!win || !win.KBLFrontend || !win.jQuery) return;
            var checked = function (key) { return m.find('[data-builder-option="' + key + '"]').prop('checked'); };
            var fields = {};
            ['name', 'city', 'address', 'phone', 'group'].forEach(function (key) {
                fields['show_' + key] = m.find('[data-builder-field="' + key + '"]').prop('checked');
            });
            var selectionType = m.data('selection-type') || 'group';
            var selectionValues = String(m.data('selection-values') || '').split(',').filter(Boolean);
            var groupName = String(m.data('preview-group-name') || '') || at('group', 'Group');
            var groupColor = String(m.data('group-color') || '') || '#1769e8';
            var branchGroupSlug = String(m.data('branch-group-slug') || '');
            var sampleBranchName = selectionType === 'branch' ? (m.data('group-name') || at('sample_branch', 'Central Branch')) : at('sample_branch', 'Central Branch');
            var config = {
                layout: layout,
                search: checked('search'), pagination: checked('pagination'), sorting: checked('sorting'),
                city_filter: checked('city_filter'), group_filter: checked('group_filter'),
                per_page: parseInt(m.find('[data-builder-option="per_page"]').val(), 10) || 15,
                height: parseInt(m.find('[data-builder-option="height"]').val(), 10) || 520,
                zoom: parseInt(m.find('[data-builder-option="zoom"]').val(), 10) || 6,
                columns: parseInt(m.find('[data-builder-option="columns"]').val(), 10) || 3,
                group_cards: checked('group_cards'), show_call: checked('call'),
                show_directions: checked('directions'), show_website: true,
                sortKey: 'name', sortOrder: 'ASC'
            };
            Object.keys(fields).forEach(function (key) { config[key] = fields[key]; });
            var items = [
                {id:'preview-1',name:sampleBranchName,city:at('sample_city','Tripoli'),address:at('sample_address','Omar Al Mukhtar Street'),phone:'+218 21 444 0101',phone2:'',website:'https://example.com',latitude:32.8872,longitude:13.1913,group_name:groupName,group_slug:selectionType==='branch'?(branchGroupSlug||'preview-group'):(selectionValues[0]||'preview-group'),group_color:groupColor},
                {id:'preview-2',name:at('sample_branch_2','Airport Branch'),city:at('sample_city_2','Benghazi'),address:at('sample_address_2','Airport Road'),phone:'+218 61 222 0202',phone2:'',website:'https://example.org',latitude:32.1167,longitude:20.0686,group_name:selectionValues.length<=1?groupName:at('group_2','Regional Offices'),group_slug:selectionValues.length<=1?(selectionValues[0]||'preview-group'):(selectionValues[1]||'preview-group-2'),group_color:selectionValues.length<=1?groupColor:'#12b76a'},
                {id:'preview-3',name:at('sample_branch_3','Old Town Branch'),city:at('sample_city','Tripoli'),address:at('sample_address_3','Algeria Square'),phone:'+218 21 333 0303',phone2:'',website:'',latitude:32.8952,longitude:13.1801,group_name:groupName,group_slug:selectionValues[0]||'preview-group',group_color:groupColor}
            ];
            var selectedItems = m.data('preview-items') || [];
            var selectedGroups = m.data('preview-groups') || [];
            if (selectionType === 'branch' && selectedItems.length) items = selectedItems;
            if (selectionType === 'branch' && !selectedItems.length) items = items.slice(0, 1);
            if (selectionType === 'group' && selectedGroups.length) {
                items.forEach(function (item, index) {
                    var group = selectedGroups[index % selectedGroups.length];
                    item.group_name = group.name; item.group_slug = group.slug; item.group_color = group.color;
                });
            }
            var target = frame.contentDocument.getElementById('kbl-builder-render');
            if (target) win.KBLFrontend.updatePreview(target, config, items);
        }

        if (frame.dataset.kblReady === '1') {
            renderInsideFrame();
            return;
        }
        if (frame.dataset.kblLoading === '1') return;
        frame.dataset.kblLoading = '1';
        var doc = frame.contentDocument;
        doc.open();
        doc.write('<!doctype html><html dir="' + (document.documentElement.dir === 'rtl' ? 'rtl' : 'ltr') + '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>html,body{margin:0;width:100%;min-height:100%;background:#fff}body{padding:12px;box-sizing:border-box}#kbl-builder-render{width:100%;min-height:100%;box-sizing:border-box}</style></head><body><div id="kbl-builder-render" class="kbl-locator-wrap kbl-v2"></div></body></html>');
        doc.close();
        var styles = [preview.frontend_css, preview.theme_css].filter(Boolean);
        var scripts = [preview.jquery_js, preview.frontend_js].filter(Boolean);
        var pending = styles.length + scripts.length;
        function done() {
            pending--;
            if (pending <= 0) {
                frame.dataset.kblReady = '1';
                frame.dataset.kblLoading = '0';
                renderInsideFrame();
            }
        }
        styles.forEach(function (href) {
            var link = doc.createElement('link'); link.rel = 'stylesheet'; link.href = href;
            link.onload = done;
            link.onerror = function () { frame.dataset.kblLoadError = 'stylesheet'; done(); };
            doc.head.appendChild(link);
        });
        scripts.forEach(function (src) {
            var script = doc.createElement('script'); script.src = src; script.async = false;
            script.onload = done;
            script.onerror = function () { frame.dataset.kblLoadError = 'script'; done(); };
            doc.body.appendChild(script);
        });
        if (!pending) {
            frame.dataset.kblReady = '1'; frame.dataset.kblLoading = '0'; renderInsideFrame();
        }
    }
    $(document).on('change input', '#bl-display-builder-modal input', updateGeneratedShortcode);
    function selectionKey(toolbar) { return 'kbl-selected-' + String(toolbar.data('page-type') || 'branches');
 }
    function getStoredSelection(toolbar) { try {
        return JSON.parse(sessionStorage.getItem(selectionKey(toolbar)) || '[]').map(String);
    }
    catch (e) {
        return [];
    } }
    function storeSelection(toolbar, ids) { try {
        sessionStorage.setItem(selectionKey(toolbar), JSON.stringify(Array.from(new Set(ids.map(String)))));
    }
    catch (e) { } }
    function refreshBulkToolbar(toolbar) { var root = toolbar.closest('.wrap'), checks = root.find('.bl-row-select'), selected = checks.filter(':checked'), stored = getStoredSelection(toolbar), count = stored.length;
 toolbar.find('.bl-selected-count').text(count ? ((KashidaBranchLocatorAdmin.i18n.selected || '%d selected').replace('%d', count)) : '');
 toolbar.find('.bl-bulk-display,.bl-bulk-delete').prop('disabled', count === 0);
 var all = checks.length > 0 && checks.filter(function () { return stored.indexOf(String(this.value)) !== -1;
 }).length === checks.length;
 toolbar.find('.bl-select-all').prop('checked', all);
 }
    function restoreSelection(toolbar) { var root = toolbar.closest('.wrap'), stored = getStoredSelection(toolbar);
 root.find('.bl-row-select').each(function () { $(this).prop('checked', stored.indexOf(String(this.value)) !== -1);
 });
 refreshBulkToolbar(toolbar);
 }
    $(document).on('change', '.bl-row-select', function () { var toolbar = $(this).closest('.wrap').find('.bl-bulk-toolbar'), stored = getStoredSelection(toolbar), id = String(this.value);
 if (this.checked) {
        stored.push(id);
    }
    else {
        stored = stored.filter(function (x) { return x !== id;
 });
    } storeSelection(toolbar, stored);
 refreshBulkToolbar(toolbar);
 });
    $(document).on('change', '.bl-select-all', function () { var toolbar = $(this).closest('.bl-bulk-toolbar'), root = toolbar.closest('.wrap'), stored = getStoredSelection(toolbar), checked = $(this).prop('checked');
 root.find('.bl-row-select').each(function () { var id = String(this.value);
 $(this).prop('checked', checked);
 if (checked && stored.indexOf(id) === -1)
        stored.push(id);
 if (!checked)
        stored = stored.filter(function (x) { return x !== id;
 });
 });
 storeSelection(toolbar, stored);
 refreshBulkToolbar(toolbar);
 });
    $(document).on('click', '.bl-select-all-results', function () {
        var btn = $(this), toolbar = btn.closest('.bl-bulk-toolbar');
        if (toolbar.data('page-type') !== 'branches')
            return;
        var form = $('.bl-filter-form'), oldText = btn.text();
        btn.prop('disabled', true).text((KashidaBranchLocatorAdmin.i18n.selecting_results || 'Selecting…'));
        $.post(KashidaBranchLocatorAdmin.ajax_url, {
            action: 'kashida_branch_locator_select_all_branches', nonce: KashidaBranchLocatorAdmin.nonce,
            search: form.find('[name="s"]').val() || '', city: form.find('[name="city"]').val() || '', group_id: form.find('[name="group_id"]').val() || ''
        }).done(function (r) {
            if (!r.success || !r.data || !Array.isArray(r.data.ids)) {
                notify((r.data && r.data.message) || KashidaBranchLocatorAdmin.i18n.request_failed, 'error');
                return;
            }
            var stored = getStoredSelection(toolbar);
            stored = Array.from(new Set(stored.concat(r.data.ids.map(String))));
            storeSelection(toolbar, stored);
            toolbar.closest('.wrap').find('.bl-row-select').each(function () { $(this).prop('checked', stored.indexOf(String(this.value)) !== -1);
 });
            refreshBulkToolbar(toolbar);
            notify((KashidaBranchLocatorAdmin.i18n.selected_all_results || 'All matching results selected.') + ' ' + (KashidaBranchLocatorAdmin.i18n.selected || '%d selected').replace('%d', stored.length));
        }).fail(function () { notify(KashidaBranchLocatorAdmin.i18n.request_failed, 'error');
 }).always(function () { btn.prop('disabled', false).text(oldText);
 });
    });
    $(function () { $('.bl-bulk-toolbar').each(function () { restoreSelection($(this));
 });
 });
    $(document).on('click', '.bl-bulk-delete', function () { var btn = $(this), toolbar = btn.closest('.bl-bulk-toolbar'), type = toolbar.data('page-type') === 'branches' ? 'branches' : 'groups', ids = [];
 ids = getStoredSelection(toolbar);
 if (!ids.length)
        return;
 var msg = type === 'branches' ? (KashidaBranchLocatorAdmin.i18n.confirm_bulk_delete_branches || KashidaBranchLocatorAdmin.i18n.confirm_bulk_delete) : (KashidaBranchLocatorAdmin.i18n.confirm_bulk_delete_groups || KashidaBranchLocatorAdmin.i18n.confirm_bulk_delete);
 msg = msg.replace('%d', ids.length);
 if (!confirm(msg))
        return;
 btn.prop('disabled', true).text(KashidaBranchLocatorAdmin.i18n.deleting);
 $.post(KashidaBranchLocatorAdmin.ajax_url, { action: type === 'branches' ? 'kashida_branch_locator_bulk_delete_branches' : 'kashida_branch_locator_bulk_delete_groups', nonce: KashidaBranchLocatorAdmin.nonce, ids: ids }).done(function (r) { if (r.success) {
        ids.forEach(function (id) { toolbar.closest('.wrap').find('.bl-row-select[value="' + id + '"]').closest('tr').remove();
 });
        storeSelection(toolbar, []);
        notify(r.data.message);
        refreshBulkToolbar(toolbar);
    }
    else {
        notify(r.data.message, 'error');
        refreshBulkToolbar(toolbar);
    } }).fail(function () { notify(KashidaBranchLocatorAdmin.i18n.request_failed, 'error');
 refreshBulkToolbar(toolbar);
 }).always(function () { btn.text(KashidaBranchLocatorAdmin.i18n.delete_selected);
 });
 });
    $(document).on('click', '.bl-copy-generated-shortcode', function () { var value = $('#bl-generated-shortcode').text();
 if (!value)
        return;
 var btn = $(this), done = function () { var old = btn.text();
 btn.text(KashidaBranchLocatorAdmin.i18n.copied);
 setTimeout(function () { btn.text(old);
 }, 1400);
 };
 if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(value).then(done).catch(function () { fallbackCopy(value, done);
 });
    }
    else {
        fallbackCopy(value, done);
    } });
    $(document).on('input change', '#bl-g-color', syncGroupColorHex);
    $(document).on('input change', '#bl-g-color-hex', function () { var v = normalizeHex($(this).val());
 if (v) {
        var picker = $('#bl-g-color');
        picker.val(v);
        if ($.fn.wpColorPicker && picker.data('wpWpColorPicker')) picker.wpColorPicker('color', v);
    }
 });
    $(function () { if ($.fn.wpColorPicker) {
        $('.kbl-color-picker').wpColorPicker({ change: function (event, ui) { $('#bl-g-color-hex').val(normalizeHex(ui.color.toString())); } });
    } syncGroupColorHex();
 });
})(jQuery);
$(document).on('click', '.bl-help-copy', function () {
    var btn = $(this), code = btn.data('shortcode') || '';
    var done = function () { var old = btn.text();
 btn.text((window.KashidaBranchLocatorAdmin && KashidaBranchLocatorAdmin.i18n && KashidaBranchLocatorAdmin.i18n.copied) || 'Copied');
 setTimeout(function () { btn.text(old);
 }, 1400);
 };
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(code).then(done).catch(function () { btn.text('Copy failed'); });
        return;
    }
    var ta = document.createElement('textarea');
    ta.value = code;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
        document.execCommand('copy');
        done();
    }
    finally {
        document.body.removeChild(ta);
    }
});
