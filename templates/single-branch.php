<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are scoped to this include.
$kbl_icons = [
    'pin'   => '<path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.6"/>',
    'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
    'city'  => '<path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M9 10h.01M15 10h.01M9 13h.01M15 13h.01"/>',
    'globe' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/>',
];
$kbl_icon = static function ( string $name ) use ( $kbl_icons ): string {
    return '<svg class="kbl2-ico" viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $kbl_icons[ $name ] . '</svg>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static markup.
};
$kbl_split = static function ( $value ): array {
    $parts = preg_split( '/\s+[-–—]\s+|\s*[,;]\s*/u', (string) $value, -1, PREG_SPLIT_NO_EMPTY );
    return is_array( $parts ) ? array_map( 'trim', $parts ) : [];
};
$kbl_phones  = $kbl_split( $branch->phone ?? '' );
$kbl_phones2 = $kbl_split( $branch->phone2 ?? '' );
$kbl_color   = ! empty( $branch->group_color ) ? $branch->group_color : '#315bea';
$kbl_has_geo = ( (float) ( $branch->latitude ?? 0 ) ) || ( (float) ( $branch->longitude ?? 0 ) );
$kbl_map_url = $kbl_has_geo ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( (float) $branch->latitude . ',' . (float) $branch->longitude ) : '';
$kbl_site    = ! empty( $branch->website ) ? esc_url( $branch->website ) : '';

$kbl_render_phones = static function ( array $numbers ): void {
    echo '<span class="kbl2-phone" dir="ltr">';
    foreach ( $numbers as $i => $number ) {
        if ( $i ) {
            echo '<span class="kbl2-sep"> / </span>';
        }
        echo '<a href="' . esc_url( 'tel:' . preg_replace( '/[^+0-9]/', '', $number ) ) . '">' . esc_html( $number ) . '</a>';
    }
    echo '</span>';
};
?>
<article class="kbl2-single" style="--kbl2-c:<?php echo esc_attr( $kbl_color ); ?>">
    <header class="kbl2-single-head">
        <h3 class="kbl2-single-title"><?php echo esc_html( $branch->name ); ?></h3>
        <?php if ( ! empty( $branch->group_name ) ) : ?>
            <span class="kbl2-badge" style="--kbl2-c:<?php echo esc_attr( $kbl_color ); ?>"><?php echo esc_html( $branch->group_name ); ?></span>
        <?php endif; ?>
    </header>

    <ul class="kbl2-single-list">
        <li>
            <?php echo $kbl_icon( 'city' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <div><span class="kbl2-single-label"><?php esc_html_e( 'City', 'kashida-branch-locator' ); ?></span><strong><?php echo esc_html( $branch->city ?: '—' ); ?></strong></div>
        </li>
        <li>
            <?php echo $kbl_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <div><span class="kbl2-single-label"><?php esc_html_e( 'Address', 'kashida-branch-locator' ); ?></span><strong><?php echo esc_html( $branch->address ?: '—' ); ?></strong></div>
        </li>
        <li>
            <?php echo $kbl_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <div><span class="kbl2-single-label"><?php esc_html_e( 'Phone', 'kashida-branch-locator' ); ?></span><strong><?php if ( $kbl_phones ) { $kbl_render_phones( $kbl_phones ); } else { echo '—'; } ?></strong></div>
        </li>
        <?php if ( $kbl_phones2 ) : ?>
        <li>
            <?php echo $kbl_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <div><span class="kbl2-single-label"><?php esc_html_e( 'Secondary phone', 'kashida-branch-locator' ); ?></span><strong><?php $kbl_render_phones( $kbl_phones2 ); ?></strong></div>
        </li>
        <?php endif; ?>
    </ul>

    <?php if ( $kbl_phones || $kbl_map_url || $kbl_site ) : ?>
    <footer class="kbl2-single-actions">
        <?php if ( $kbl_phones ) : ?>
            <a class="kbl2-btn kbl2-btn-call" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^+0-9]/', '', $kbl_phones[0] ) ); ?>"><?php echo $kbl_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Call', 'kashida-branch-locator' ); ?></span></a>
        <?php endif; ?>
        <?php if ( $kbl_map_url ) : ?>
            <a class="kbl2-btn kbl2-btn-primary" target="_blank" rel="noopener" href="<?php echo esc_url( $kbl_map_url ); ?>"><?php echo $kbl_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Directions', 'kashida-branch-locator' ); ?></span></a>
        <?php endif; ?>
        <?php if ( $kbl_site ) : ?>
            <a class="kbl2-btn kbl2-btn-web" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( $kbl_site ); ?>"><?php echo $kbl_icon( 'globe' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Visit website', 'kashida-branch-locator' ); ?></span></a>
        <?php endif; ?>
    </footer>
    <?php endif; ?>
</article>
