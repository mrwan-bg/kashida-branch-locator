(function ($) {
    'use strict';
    var I = (window.KashidaBranchLocatorImport && KashidaBranchLocatorImport.i18n) || {};
    function t(k, d) { return I[k] || d; }
    function tf(k, d, v) { return t(k, d).replace('%s', v).replace('%d', v); }
    function esc(s) { return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
    var fileInput = $('#bl-import-file'), dropZone = $('#bl-drop-zone'), options = $('#bl-import-options'), previewArea = $('#bl-preview-area'), resultArea = $('#bl-import-result'), currentFile = null, lastRows = [];
    $('#bl-browse-btn').on('click', function (e) { e.preventDefault(); fileInput.trigger('click'); });
    fileInput.on('change', function () { handleFile(this.files[0]); });
    dropZone.on('click', function (e) { if (!$(e.target).closest('button,input').length)
        fileInput.trigger('click'); });
    dropZone.on('dragover', function (e) { e.preventDefault(); dropZone.addClass('drag-over'); });
    dropZone.on('dragleave drop', function () { dropZone.removeClass('drag-over'); });
    dropZone.on('drop', function (e) { e.preventDefault(); var f = e.originalEvent.dataTransfer.files[0]; if (f)
        handleFile(f); });
    function handleFile(file) { if (!file)
        return; var ext = file.name.split('.').pop().toLowerCase(); if (['xlsx', 'xls', 'csv'].indexOf(ext) === -1) {
        alert(t('invalid_type', 'Only .xlsx, .xls, and .csv files are accepted.'));
        return;
    } currentFile = file; dropZone.find('p:first').text(tf('selected', 'Selected: %s', file.name)); options.show(); previewArea.hide(); resultArea.hide().removeClass('error'); $('#bl-download-report').hide(); }
    $('#bl-preview-btn').on('click', function () { if (!currentFile)
        return; var fd = new FormData(); fd.append('action', 'kashida_branch_locator_import_preview'); fd.append('nonce', KashidaBranchLocatorImport.nonce); fd.append('import_file', currentFile); var btn = $(this); btn.prop('disabled', true).text(t('loading', 'Loading…')); $.ajax({ url: KashidaBranchLocatorImport.ajax_url, type: 'POST', data: fd, processData: false, contentType: false }).done(function (r) { if (r.success) {
        lastRows = r.data.rows || [];
        renderPreview(lastRows, r.data.total || 0);
        previewArea.show();
    }
    else {
        alert(r.data.message || t('request_failed', 'Request failed.'));
    } }).fail(function () { alert(t('request_failed', 'Request failed.')); }).always(function () { btn.prop('disabled', false).text(t('preview_import', 'Preview Import')); }); });
    function renderPreview(rows, total) {
        $('#bl-preview-count').text('(' + total + ' ' + t('rows_total', 'rows total') + ', ' + t('showing_first', 'showing first') + ' ' + rows.length + ')');
        var table = $('<table>').addClass('bl-preview-table');
        var thead = $('<thead>');
        var headerRow = $('<tr>');
        ['Name', 'City', 'Address', 'Phone', 'Lat', 'Lng', 'Group', 'Status'].forEach(function (heading) {
            headerRow.append($('<th>').text(heading));
        });
        thead.append(headerRow);
        table.append(thead);
        var tbody = $('<tbody>');
        rows.forEach(function (row) {
            var rowClass = row._status === 'error' ? 'row-error' : (row._status === 'warning' ? 'row-warning' : '');
            var tr = $('<tr>');
            if (rowClass) tr.addClass(rowClass);
            ['name', 'city', 'address', 'phone', 'latitude', 'longitude', '_group_name'].forEach(function (key) {
                tr.append($('<td>').text(row[key] || ''));
            });
            var status = row._status === 'error' ? t('error', 'Error') : (row._status === 'warning' ? t('warning', 'Warning') : t('ready', 'Ready'));
            var statusCell = $('<td>').append($('<strong>').text(status));
            function appendMessages(messages, warning) {
                if (!messages || !messages.length) return;
                var list = $('<ul>').addClass('bl-row-messages');
                if (warning) list.addClass('warning');
                messages.forEach(function (message) {
                    list.append($('<li>').text(message));
                });
                statusCell.append(list);
            }
            appendMessages(row._errors, false);
            appendMessages(row._warnings, true);
            tr.append(statusCell);
            tbody.append(tr);
        });
        table.append(tbody);
        $('#bl-preview-table-wrap').empty().append(table);
        $('#bl-download-report').toggle(rows.some(function (row) {
            return (row._errors && row._errors.length) || (row._warnings && row._warnings.length);
        }));
    }
    $('#bl-run-import').on('click', function () { if (!currentFile)
        return; var fd = new FormData(); fd.append('action', 'kashida_branch_locator_import_run'); fd.append('nonce', KashidaBranchLocatorImport.nonce); fd.append('import_file', currentFile); fd.append('update_existing', $('#bl-update-existing').is(':checked') ? '1' : ''); var btn = $(this); btn.prop('disabled', true).text(t('importing', 'Importing…')); $.ajax({ url: KashidaBranchLocatorImport.ajax_url, type: 'POST', data: fd, processData: false, contentType: false }).done(function (r) { previewArea.hide(); if (r.success) {
        var d = r.data || {};
        var heading = $('<h3>').text('✓ ' + t('import_complete', 'Import Complete'));
        var summary = $('<ul>');
        [
            [d.inserted, 'branches_inserted', '%d branches inserted'],
            [d.updated, 'branches_updated', '%d branches updated'],
            [d.skipped, 'rows_skipped', '%d rows skipped']
        ].forEach(function (item) {
            var count = Number(item[0] || 0);
            summary.append($('<li>').append($('<strong>').text(count)).append(document.createTextNode(' ' + tf(item[1], item[2], count))));
        });
        function appendResultMessages(messages, label) {
            if (!messages || !messages.length) return;
            var item = $('<li>').append($('<strong>').text(label));
            var list = $('<ul>');
            messages.forEach(function (message) {
                list.append($('<li>').text(message));
            });
            item.append(list);
            summary.append(item);
        }
        appendResultMessages(d.errors, t('errors', 'Errors:'));
        appendResultMessages(d.warnings, t('warnings', 'Warnings:'));
        resultArea.empty().append(heading, summary).removeClass('error').show();
        $('#bl-download-report').toggle(!!((d.errors && d.errors.length) || (d.warnings && d.warnings.length)));
    }
    else {
        resultArea.empty().append(
            $('<h3>').text('✕ ' + t('import_failed', 'Import Failed')),
            $('<p>').text(r.data && r.data.message ? r.data.message : t('request_failed', 'Request failed.'))
        ).addClass('error').show();
    } }).fail(function () { resultArea.empty().append($('<h3>').text('✕ ' + t('import_failed', 'Import Failed')), $('<p>').text(t('request_failed', 'Request failed.'))).addClass('error').show(); }).always(function () { btn.prop('disabled', false).text(t('run_import', 'Run Import')); }); });
    $('#bl-cancel-import').on('click', function () { previewArea.hide(); $('#bl-download-report').hide(); });
    $('#bl-download-report').on('click', function () { var lines = [['Row', 'Status', 'Name', 'City', 'Address', 'Phone', 'Lat', 'Lng', 'Group', 'Message']]; lastRows.forEach(function (r) { var msgs = [].concat(r._errors || [], r._warnings || []); if (!msgs.length)
        return; msgs.forEach(function (m) { lines.push([r._row, r._status, r.name || '', r.city || '', r.address || '', r.phone || '', r.latitude || '', r.longitude || '', r._group_name || '', m]); }); }); var csv = lines.map(function (row) { return row.map(function (v) { v = String(v == null ? '' : v).replace(/"/g, '""'); return '"' + v + '"'; }).join(','); }).join('\r\n'); var blob = new Blob(["\ufeff" + csv], { type: 'text/csv;charset=utf-8;' }), url = URL.createObjectURL(blob), a = document.createElement('a'); a.href = url; a.download = 'kashida-branch-locator-import-report.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url); });
})(jQuery);
