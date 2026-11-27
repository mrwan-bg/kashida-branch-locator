(function ($) {
  'use strict';
  function contrast(hex) {
    hex = String(hex || '').replace('#', '');
    if (hex.length === 3) hex = hex.replace(/(.)/g, '$1$1');
    if (!/^[0-9a-f]{6}$/i.test(hex)) return '#fff';
    var r = parseInt(hex.substr(0, 2), 16), g = parseInt(hex.substr(2, 2), 16), b = parseInt(hex.substr(4, 2), 16);
    return ((r * 299 + g * 587 + b * 114) / 1000) > 160 ? '#111' : '#fff';
  }
  function paint() {
    $('.kbl-btn-color-picker').each(function () {
      var $in = $(this), key = $in.data('key'), val = String($in.val() || '').trim(), color = /^#[0-9a-f]{3,6}$/i.test(val) ? val : $in.data('fallback');
      var $b = $('.bl-prev-btn[data-key="' + key + '"]');
      if ($b.hasClass('bl-prev-outline')) { $b.css({ color: color, borderColor: color, background: 'transparent' }); }
      else { $b.css({ background: color, borderColor: color, color: contrast(color) }); }
    });
  }
  function syncWidth(v) {
    v = Math.max(0, Math.min(2400, parseInt(v, 10) || 0));
    $('#kbl-container-width').val(v);
    $('#kbl-container-width-range').val(v);
    $('.bl-chip').each(function () { $(this).toggleClass('is-active', parseInt($(this).data('width'), 10) === v); });
  }
  $(function () {
    $('.kbl-btn-color-picker').wpColorPicker({
      defaultColor: false,
      change: function () { setTimeout(paint, 0); },
      clear: function () { setTimeout(paint, 0); }
    });
    paint();
    syncWidth($('#kbl-container-width').val());
    $('#kbl-container-width-range').on('input change', function () { syncWidth($(this).val()); });
    $('#kbl-container-width').on('input change', function () { syncWidth($(this).val()); });
    $('.bl-chip').on('click', function () { syncWidth($(this).data('width')); });
  });
})(jQuery);
