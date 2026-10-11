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
            'height' => 520, 'zoom' => 6,
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
                'latitude' => (float) $item->latitude,
                'longitude' => (float) $item->longitude,
                'group_name' => (string) ( $item->group_name ?? '' ),
                'group_slug' => (string) ( $item->group_slug ?? '' ),
                'group_color' => (string) ( $item->group_color ?? '#0a6b70' ),
            ];
        }

        wp_enqueue_style( 'kashida-branch-locator-frontend' );
        wp_enqueue_script( 'kashida-branch-locator-frontend' );
        wp_localize_script( 'kashida-branch-locator-frontend', 'kbl_front_labels', [
            'search_label' => __( 'Search', 'kashida-branch-locator' ),
            'branch' => __( 'Branch', 'kashida-branch-locator' ),
            'city' => __( 'City', 'kashida-branch-locator' ),
            'address' => __( 'Address', 'kashida-branch-locator' ),
            'phone' => __( 'Phone', 'kashida-branch-locator' ),
            'group' => __( 'Group', 'kashida-branch-locator' ),
            'search_branches' => __( 'Search branches...', 'kashida-branch-locator' ),
            'no_results' => __( 'No branches found.', 'kashida-branch-locator' ),
            'call' => __( 'Call', 'kashida-branch-locator' ),
            'directions' => __( 'Directions', 'kashida-branch-locator' ),
            'close' => __( 'Close', 'kashida-branch-locator' ),
            'all_cities' => __( 'All Cities', 'kashida-branch-locator' ),
            'all_groups' => __( 'All Groups', 'kashida-branch-locator' ),
            'sort_by' => __( 'Sort by', 'kashida-branch-locator' ),
            'sort_asc' => __( 'Ascending', 'kashida-branch-locator' ),
            'sort_desc' => __( 'Descending', 'kashida-branch-locator' ),
            'sort_name' => __( 'Branch name', 'kashida-branch-locator' ),
            'sort_city' => __( 'City', 'kashida-branch-locator' ),
            'sort_group' => __( 'Group', 'kashida-branch-locator' ),
            'sort_click' => __( 'Click to sort', 'kashida-branch-locator' ),
            'sort_hint' => __( 'You can click City or Group to sort the results.', 'kashida-branch-locator' ),
            'filter_city' => __( 'City', 'kashida-branch-locator' ),
            'filter_group' => __( 'Group', 'kashida-branch-locator' ),
            'zoom_in' => __( 'Zoom in', 'kashida-branch-locator' ),
            'zoom_out' => __( 'Zoom out', 'kashida-branch-locator' ),
            'no_coordinates' => __( 'No valid coordinates are available for these branches.', 'kashida-branch-locator' ),
            'map_preview' => __( 'Map preview', 'kashida-branch-locator' ),
            'clear_search' => __( 'Clear search', 'kashida-branch-locator' ),
            'reset_filters' => __( 'Reset', 'kashida-branch-locator' ),
            /* translators: %1$d: number of visible results; %2$d: total number of results. */
            'results_count' => __( 'Showing %1$d of %2$d results.', 'kashida-branch-locator' ),
            'page' => __( 'Page', 'kashida-branch-locator' ),
            'go_to_page' => __( 'Go to page', 'kashida-branch-locator' ),
            'map_touch_hint' => __( 'Use two fingers to move the map', 'kashida-branch-locator' ),
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
            'items' => $items,
        ];

        ob_start();
        echo '<div class="kbl-locator-wrap kbl-layout-' . esc_attr( $layout ) . '" dir="' . esc_attr( is_rtl() ? 'rtl' : 'ltr' ) . '" data-config="' . esc_attr( wp_json_encode( $config ) ) . '">';
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
