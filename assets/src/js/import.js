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
        var thead = $('<thead>').appendTo(table);
        var headerRow = $('<tr>').appendTo(thead);
        ['Name', 'City', 'Address', 'Phone', 'Lat', 'Lng', 'Group', 'Status'].forEach(function (heading) {
            $('<th>').text(heading).appendTo(headerRow);
        });
        var tbody = $('<tbody>').appendTo(table);
        rows.forEach(function (row) {
            var tr = $('<tr>').appendTo(tbody);
            if (row._status === 'error') tr.addClass('row-error');
            else if (row._status === 'warning') tr.addClass('row-warning');
            ['name', 'city', 'address', 'phone', 'latitude', 'longitude', '_group_name'].forEach(function (key) {
                $('<td>').text(row[key] || '').appendTo(tr);
            });
            var statusCell = $('<td>').appendTo(tr);
            var status = row._status === 'error' ? t('error', 'Error') : (row._status === 'warning' ? t('warning', 'Warning') : t('ready', 'Ready'));
            $('<strong>').text(status).appendTo(statusCell);
            if (row._errors && row._errors.length) {
                var errorList = $('<ul>').addClass('bl-row-messages').appendTo(statusCell);
                row._errors.forEach(function (message) { $('<li>').text(message).appendTo(errorList); });
            }
            if (row._warnings && row._warnings.length) {
                var warningList = $('<ul>').addClass('bl-row-messages warning').appendTo(statusCell);
                row._warnings.forEach(function (message) { $('<li>').text(message).appendTo(warningList); });
            }
        });
        $('#bl-preview-table-wrap').empty().append(table);
        $('#bl-download-report').toggle(rows.some(function (row) { return (row._errors && row._errors.length) || (row._warnings && row._warnings.length); }));
    }
    $('#bl-run-import').on('click', function () { if (!currentFile)
        return; var fd = new FormData(); fd.append('action', 'kashida_branch_locator_import_run'); fd.append('nonce', KashidaBranchLocatorImport.nonce); fd.append('import_file', currentFile); fd.append('update_existing', $('#bl-update-existing').is(':checked') ? '1' : ''); var btn = $(this); btn.prop('disabled', true).text(t('importing', 'Importing…')); $.ajax({ url: KashidaBranchLocatorImport.ajax_url, type: 'POST', data: fd, processData: false, contentType: false }).done(function (r) { previewArea.hide(); if (r.success) {
        var d = r.data || {};
        var html = '<h3>✓ ' + t('import_complete', 'Import Complete') + '</h3><ul><li><strong>' + Number(d.inserted || 0) + '</strong> ' + tf('branches_inserted', '%d branches inserted', Number(d.inserted || 0)) + '</li><li><strong>' + Number(d.updated || 0) + '</strong> ' + tf('branches_updated', '%d branches updated', Number(d.updated || 0)) + '</li><li><strong>' + Number(d.skipped || 0) + '</strong> ' + tf('rows_skipped', '%d rows skipped', Number(d.skipped || 0)) + '</li>';
        if (d.errors && d.errors.length) {
            html += '<li><strong>' + t('errors', 'Errors:') + '</strong><ul>';
            d.errors.forEach(function (e) { html += '<li>' + esc(e) + '</li>'; });
            html += '</ul></li>';
        }
        if (d.warnings && d.warnings.length) {
            html += '<li><strong>' + t('warnings', 'Warnings:') + '</strong><ul>';
            d.warnings.forEach(function (e) { html += '<li>' + esc(e) + '</li>'; });
            html += '</ul></li>';
        }
        html += '</ul>';
        var resultHeading = $('<h3>').text('✓ ' + t('import_complete', 'Import Complete'));
        var resultList = $('<ul>');
        $('<li>').append($('<strong>').text(Number(d.inserted || 0))).append(document.createTextNode(' ' + tf('branches_inserted', '%d branches inserted', Number(d.inserted || 0)))).appendTo(resultList);
        $('<li>').append($('<strong>').text(Number(d.updated || 0))).append(document.createTextNode(' ' + tf('branches_updated', '%d branches updated', Number(d.updated || 0)))).appendTo(resultList);
        $('<li>').append($('<strong>').text(Number(d.skipped || 0))).append(document.createTextNode(' ' + tf('rows_skipped', '%d rows skipped', Number(d.skipped || 0)))).appendTo(resultList);
        if (d.errors && d.errors.length) {
            var errorsItem = $('<li>').append($('<strong>').text(t('errors', 'Errors:')));
            var errorsList = $('<ul>').appendTo(errorsItem);
            d.errors.forEach(function (message) { $('<li>').text(message).appendTo(errorsList); });
            resultList.append(errorsItem);
        }
        if (d.warnings && d.warnings.length) {
            var warningsItem = $('<li>').append($('<strong>').text(t('warnings', 'Warnings:')));
            var warningsList = $('<ul>').appendTo(warningsItem);
            d.warnings.forEach(function (message) { $('<li>').text(message).appendTo(warningsList); });
            resultList.append(warningsItem);
        }
        resultArea.empty().append(resultHeading, resultList).removeClass('error').show();
        $('#bl-download-report').toggle(!!((d.errors && d.errors.length) || (d.warnings && d.warnings.length)));
    }
    else {
        resultArea.empty().append($('<h3>').text('✕ ' + t('import_failed', 'Import Failed')), $('<p>').text(r.data && r.data.message ? r.data.message : t('request_failed', 'Request failed.'))).addClass('error').show();
    } }).fail(function () { resultArea.empty().append($('<h3>').text('✕ ' + t('import_failed', 'Import Failed')), $('<p>').text(t('request_failed', 'Request failed.'))).addClass('error').show(); }).always(function () { btn.prop('disabled', false).text(t('run_import', 'Run Import')); }); });
    $('#bl-cancel-import').on('click', function () { previewArea.hide(); $('#bl-download-report').hide(); });
    $('#bl-download-report').on('click', function () { var lines = [['Row', 'Status', 'Name', 'City', 'Address', 'Phone', 'Lat', 'Lng', 'Group', 'Message']]; lastRows.forEach(function (r) { var msgs = [].concat(r._errors || [], r._warnings || []); if (!msgs.length)
        return; msgs.forEach(function (m) { lines.push([r._row, r._status, r.name || '', r.city || '', r.address || '', r.phone || '', r.latitude || '', r.longitude || '', r._group_name || '', m]); }); }); var csv = lines.map(function (row) { return row.map(function (v) { v = String(v == null ? '' : v).replace(/"/g, '""'); return '"' + v + '"'; }).join(','); }).join('\r\n'); var blob = new Blob(["\ufeff" + csv], { type: 'text/csv;charset=utf-8;' }), url = URL.createObjectURL(blob), a = document.createElement('a'); a.href = url; a.download = 'kashida-branch-locator-import-report.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url); });
})(jQuery);
