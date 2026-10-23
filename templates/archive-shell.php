<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are supplied by the shortcode renderer.
$kbl_config = $config ?? [];
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are supplied by the shortcode renderer.
$kbl_layout = $layout ?? ($kbl_config['layout'] ?? 'table');
?>
<div class="kbl-toolbar"></div>

<?php if ( $kbl_layout === 'map-table' ) : ?>
  <div class="kbl-map-table-shell">
    <div class="kbl-map-table-map"><div class="kbl-map" style="height:<?php echo (int) $kbl_config['height']; ?>px"></div></div>
    <div class="kbl-map-table-list"><div class="kbl-results"></div></div>
  </div>
<?php elseif ( $kbl_layout === 'map' ) : ?>
  <div class="kbl-map" style="height:<?php echo (int) $kbl_config['height']; ?>px"></div>
  <div class="kbl-results"></div>
<?php else : ?>
  <div class="kbl-results"></div>
<?php endif; ?>

<div class="kbl-pagination"></div>
