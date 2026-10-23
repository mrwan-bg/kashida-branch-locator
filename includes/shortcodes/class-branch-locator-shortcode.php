<?php
declare(strict_types=1);
namespace Kashida\BranchLocator\Shortcodes;

use Kashida\BranchLocator\Models\Branch;
use Kashida\BranchLocator\Models\Group;

if ( ! defined( 'ABSPATH' ) ) exit;

class Branch_Locator_Shortcode {
    public function __construct() {
        add_shortcode( 'kashida_branch_locator', [ $this, 'render' ] );
        add_shortcode( 'kashida_branch_group_map', [ $this, 'render_group_map' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
    }

    public function render_group_map( array $atts = [] ): string {
        $atts = shortcode_atts( [
            'group' => '', 'city' => '', 'zoom' => 6, 'height' => 600,
            'search' => 'false', 'pagination' => 'false', 'sorting' => 'true', 'city_filter' => 'false', 'group_filter' => 'false', 'orderby' => 'name', 'order' => 'ASC',
        ], $atts, 'kashida_branch_group_map' );
        $atts['layout'] = 'map';
        return $this->render( $atts );
    }

    public function register_assets(): void {
        wp_register_style( 'kashida-branch-locator-frontend', KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'css/frontend.css', [], KASHIDA_BRANCH_LOCATOR_VERSION );
        wp_register_script( 'kashida-branch-locator-frontend', KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'js/frontend.js', [ 'jquery' ], KASHIDA_BRANCH_LOCATOR_VERSION, true );
    }

    public function render( array $atts = [] ): string {
        $atts = shortcode_atts( [
            'group' => '', 'groups' => '', 'branch' => '', 'branches' => '', 'city' => '', 'search' => 'true', 'pagination' => 'true', 'sorting' => 'true', 'sort_control' => 'true', 'city_filter' => 'false', 'group_filter' => 'false',
            'per_page' => 15, 'layout' => 'table', 'orderby' => 'name', 'order' => 'ASC',
            'show_name' => 'true', 'show_city' => 'true', 'show_address' => 'true', 'show_phone' => 'true',
            'show_group' => 'false', 'show_call' => 'true', 'show_directions' => 'true',
            'height' => 520, 'zoom' => 6, 'columns' => 3, 'group_cards' => 'false', 'call_color' => '#12b76a', 'directions_color' => '#1769e8', 'website_color' => '#d85b00',
        ], $atts, 'kashida_branch_locator' );

        $allowed_layouts = [ 'table', 'cards', 'accordion', 'list', 'map', 'map-table' ];
        $layout = in_array( (string) $atts['layout'], $allowed_layouts, true ) ? (string) $atts['layout'] : 'table';
        $group_slug = sanitize_text_field( (string) $atts['group'] );
        $branch_slug = sanitize_text_field( (string) $atts['branch'] );
        $group_slugs = array_values( array_filter( array_map( 'sanitize_text_field', array_map( 'trim', explode( ',', (string) $atts['groups'] ) ) ) ) );
        $branch_slugs = array_values( array_filter( array_map( 'sanitize_text_field', array_map( 'trim', explode( ',', (string) $atts['branches'] ) ) ) ) );

        $single = $branch_slug !== '' ? Branch::find_by_slug( $branch_slug ) : null;

        $query = Branch::query( [
            'search' => '',
            'city' => sanitize_text_field( (string) $atts['city'] ),
            'group_slug' => $group_slug,
            'group_slugs' => $group_slugs,
            'branch_slug' => $branch_slug,
            'branch_slugs' => $branch_slugs,
            'page' => 1,
            'per_page' => 1000,
            'orderby' => sanitize_key( (string) $atts['orderby'] ),
            'order' => strtoupper( (string) $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC',
        ] );

        $items = [];
        foreach ( (array) ( $query['items'] ?? [] ) as $item ) {
            $items[] = [
                'id' => (int) $item->id,
                'name' => (string) $item->name,
                'city' => (string) $item->city,
                'address' => (string) $item->address,
                'phone' => (string) $item->phone,
                'phone2' => (string) ( $item->phone2 ?? '' ),
                'website' => (string) ( $item->website ?? '' ),
                'latitude' => (float) $item->latitude,
                'longitude' => (float) $item->longitude,
                'group_name' => (string) ( $item->group_name ?? '' ),
                'group_slug' => (string) ( $item->group_slug ?? '' ),
                'group_color' => (string) ( $item->group_color ?? '#0a6b70' ),
            ];
        }

        wp_enqueue_style( 'kashida-branch-locator-frontend' );
        wp_enqueue_script( 'kashida-branch-locator-frontend' );
        $kbl_is_arabic = 0 === strpos( determine_locale(), 'ar' ) || 0 === strpos( get_locale(), 'ar' );
        wp_localize_script( 'kashida-branch-locator-frontend', 'kbl_front_labels', [
            'search_label' => ( $kbl_is_arabic ? 'ابحث عن فرع' : __( 'Search branches', 'kashida-branch-locator' ) ),
            'filter_group' => ( $kbl_is_arabic ? 'المجموعة' : __( 'Group', 'kashida-branch-locator' ) ),
            'all_groups' => ( $kbl_is_arabic ? 'كل المجموعات' : __( 'All Groups', 'kashida-branch-locator' ) ),
            'search_branches' => ( $kbl_is_arabic ? 'اسم الفرع، المدينة، العنوان أو رقم الهاتف' : __( 'Branch name, city, address or phone number', 'kashida-branch-locator' ) ),
            'branch' => $kbl_is_arabic ? 'الفرع' : __( 'Branch', 'kashida-branch-locator' ),
            'city' => $kbl_is_arabic ? 'المدينة' : __( 'City', 'kashida-branch-locator' ),
            'address' => $kbl_is_arabic ? 'العنوان' : __( 'Address', 'kashida-branch-locator' ),
            'phone' => $kbl_is_arabic ? 'رقم الهاتف' : __( 'Phone', 'kashida-branch-locator' ),
            'phone2' => ( $kbl_is_arabic ? 'هاتف إضافي' : __( 'Secondary phone', 'kashida-branch-locator' ) ),
            'website' => ( $kbl_is_arabic ? 'الموقع الإلكتروني' : __( 'Website', 'kashida-branch-locator' ) ),
            'visit_website' => ( $kbl_is_arabic ? 'زيارة الموقع الإلكتروني' : __( 'Visit website', 'kashida-branch-locator' ) ),
            'group' => $kbl_is_arabic ? 'المجموعة' : __( 'Group', 'kashida-branch-locator' ),
            'no_results' => $kbl_is_arabic ? 'لم يتم العثور على فروع.' : __( 'No branches found.', 'kashida-branch-locator' ),
            'call' => $kbl_is_arabic ? 'اتصال' : __( 'Call', 'kashida-branch-locator' ),
            'directions' => $kbl_is_arabic ? 'الاتجاهات' : __( 'Directions', 'kashida-branch-locator' ),
            'close' => $kbl_is_arabic ? 'إغلاق' : __( 'Close', 'kashida-branch-locator' ),
            'all_cities' => $kbl_is_arabic ? 'كل المدن' : __( 'All Cities', 'kashida-branch-locator' ),
            'sort_by' => $kbl_is_arabic ? 'ترتيب حسب' : __( 'Sort by', 'kashida-branch-locator' ),
            'sort_asc' => $kbl_is_arabic ? 'تصاعدي' : __( 'Ascending', 'kashida-branch-locator' ),
            'sort_desc' => $kbl_is_arabic ? 'تنازلي' : __( 'Descending', 'kashida-branch-locator' ),
            'sort_name' => $kbl_is_arabic ? 'اسم الفرع' : __( 'Branch name', 'kashida-branch-locator' ),
            'sort_city' => $kbl_is_arabic ? 'المدينة' : __( 'City', 'kashida-branch-locator' ),
            'sort_group' => $kbl_is_arabic ? 'المجموعة' : __( 'Group', 'kashida-branch-locator' ),
            'sort_click' => $kbl_is_arabic ? 'انقر للترتيب' : __( 'Click to sort', 'kashida-branch-locator' ),
            'sort_hint' => $kbl_is_arabic ? 'يمكنك النقر على المدينة أو المجموعة لترتيب النتائج.' : __( 'You can click City or Group to sort the results.', 'kashida-branch-locator' ),
            'filter_city' => $kbl_is_arabic ? 'المدينة' : __( 'City', 'kashida-branch-locator' ),
            'zoom_in' => $kbl_is_arabic ? 'تكبير' : __( 'Zoom in', 'kashida-branch-locator' ),
            'zoom_out' => $kbl_is_arabic ? 'تصغير' : __( 'Zoom out', 'kashida-branch-locator' ),
            'no_coordinates' => $kbl_is_arabic ? 'لا تتوفر إحداثيات صحيحة لهذه الفروع.' : __( 'No valid coordinates are available for these branches.', 'kashida-branch-locator' ),
            'map_preview' => $kbl_is_arabic ? 'معاينة الخريطة' : __( 'Map preview', 'kashida-branch-locator' ),
            'clear_search' => $kbl_is_arabic ? 'مسح البحث' : __( 'Clear search', 'kashida-branch-locator' ),
            'reset_filters' => $kbl_is_arabic ? 'إعادة ضبط' : __( 'Reset', 'kashida-branch-locator' ),
            /* translators: %1$d: number of visible results; %2$d: total number of results. */
            'results_count' => $kbl_is_arabic ? 'عرض %1$d من أصل %2$d نتيجة.' : __( 'Showing %1$d of %2$d results.', 'kashida-branch-locator' ),
            'page' => $kbl_is_arabic ? 'الصفحة' : __( 'Page', 'kashida-branch-locator' ),
            'go_to_page' => $kbl_is_arabic ? 'الانتقال إلى الصفحة' : __( 'Go to page', 'kashida-branch-locator' ),
            'map_touch_hint' => $kbl_is_arabic ? 'استخدم إصبعين لتحريك الخريطة' : __( 'Use two fingers to move the map', 'kashida-branch-locator' ),
        ] );

        $cities = Branch::get_cities();
        $groups = Group::all();
        $config = [
            'layout' => $layout,
            'city' => sanitize_text_field( (string) $atts['city'] ),
            'group_slug' => $group_slug,
            'group_slugs' => $group_slugs,
            'branch_slug' => $branch_slug,
            'branch_slugs' => $branch_slugs,
            'search' => filter_var( $atts['search'], FILTER_VALIDATE_BOOLEAN ),
            'pagination' => filter_var( $atts['pagination'], FILTER_VALIDATE_BOOLEAN ),
            'display_mode' => in_array( get_option( 'kashida_branch_locator_display_mode', 'pagination' ), [ 'pagination', 'infinite_scroll' ], true ) ? get_option( 'kashida_branch_locator_display_mode', 'pagination' ) : 'pagination',
            'sorting' => filter_var( $atts['sorting'], FILTER_VALIDATE_BOOLEAN ),
            'sort_control' => filter_var( $atts['sort_control'], FILTER_VALIDATE_BOOLEAN ),
            'city_filter' => filter_var( $atts['city_filter'], FILTER_VALIDATE_BOOLEAN ),
            'group_filter' => filter_var( $atts['group_filter'], FILTER_VALIDATE_BOOLEAN ),
            'per_page' => max( 1, min( 100, (int) $atts['per_page'] ) ),
            'height' => max( 300, min( 1200, (int) $atts['height'] ) ),
            'zoom' => max( 1, min( 18, (int) $atts['zoom'] ) ),
            'show_name' => filter_var( $atts['show_name'], FILTER_VALIDATE_BOOLEAN ),
            'show_city' => filter_var( $atts['show_city'], FILTER_VALIDATE_BOOLEAN ),
            'show_address' => filter_var( $atts['show_address'], FILTER_VALIDATE_BOOLEAN ),
            'show_phone' => filter_var( $atts['show_phone'], FILTER_VALIDATE_BOOLEAN ),
            'show_group' => filter_var( $atts['show_group'], FILTER_VALIDATE_BOOLEAN ),
            'show_call' => filter_var( $atts['show_call'], FILTER_VALIDATE_BOOLEAN ),
            'show_directions' => filter_var( $atts['show_directions'], FILTER_VALIDATE_BOOLEAN ),
            'columns' => max( 1, min( 6, (int) $atts['columns'] ) ),
            'group_cards' => filter_var( $atts['group_cards'], FILTER_VALIDATE_BOOLEAN ),
            'call_color' => sanitize_hex_color( (string) $atts['call_color'] ) ?: '#12b76a',
            'directions_color' => sanitize_hex_color( (string) $atts['directions_color'] ) ?: '#1769e8',
            'website_color' => sanitize_hex_color( (string) $atts['website_color'] ) ?: '#d85b00',
            'items' => $items,
        ];

        ob_start();
        // Determine direction from the active front-end language, not the site's
        // global locale alone (which may be Arabic while this page is English).
        $kbl_language = '';
        if ( function_exists( 'pll_current_language' ) ) {
            $kbl_language = (string) pll_current_language( 'locale' );
        } elseif ( has_filter( 'wpml_current_language' ) ) {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WPML defines this public filter name.
            $kbl_language = (string) apply_filters( 'wpml_current_language', null );
        }
        if ( '' === $kbl_language ) {
            $kbl_language = (string) determine_locale();
        }
        $kbl_is_rtl = (bool) preg_match( '/^(ar|he|fa|ur)([_-]|$)/i', $kbl_language );
        echo '<div class="kbl-locator-wrap kbl-layout-' . esc_attr( $layout ) . '" dir="' . esc_attr( $kbl_is_rtl ? 'rtl' : 'ltr' ) . '" style="--kbl-call-color:' . esc_attr( $config['call_color'] ) . ';--kbl-directions-color:' . esc_attr( $config['directions_color'] ) . ';--kbl-website-color:' . esc_attr( $config['website_color'] ) . ';--kbl-card-columns:' . (int) $config['columns'] . ';" data-config="' . esc_attr( wp_json_encode( $config ) ) . '">';
        if ( $single && $layout === 'table' && $branch_slug !== '' ) {
            $branch = $single;
            include KASHIDA_BRANCH_LOCATOR_PATH . 'templates/single-branch.php';
        } else {
            include KASHIDA_BRANCH_LOCATOR_PATH . 'templates/archive-shell.php';
        }
        echo '</div>';
        return (string) ob_get_clean();
    }
}
