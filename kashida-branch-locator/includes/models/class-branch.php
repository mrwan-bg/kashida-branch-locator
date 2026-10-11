<?php
declare(strict_types=1);
namespace Kashida\BranchLocator\Models;
if ( ! defined( 'ABSPATH' ) ) exit;

class Branch {
    private static function table(): string { global $wpdb; return $wpdb->prefix . 'kashida_branch_locator_branches'; }

    public static function find_by_slug( string $slug ): ?object {
        global $wpdb;
        $slug = sanitize_text_field( $slug );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom branch table has no equivalent WordPress API.
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT b.*, g.name AS group_name, g.slug AS group_slug, g.color AS group_color FROM {$wpdb->prefix}kashida_branch_locator_branches b LEFT JOIN {$wpdb->prefix}kashida_branch_locator_groups g ON g.id = b.group_id WHERE b.slug = %s",
            $slug
        ) );
    }

    public static function query( array $args = [] ): array {
        global $wpdb;

        $args = wp_parse_args( $args, [
            'search'       => '',
            'city'         => '',
            'group_id'     => 0,
            'group_slug'   => '',
            'group_slugs'  => [],
            'branch_slug'  => '',
            'branch_slugs' => [],
            'per_page'     => 15,
            'page'         => 1,
            'orderby'      => 'name',
            'order'        => 'ASC',
        ] );

        $search = sanitize_text_field( (string) $args['search'] );
        $like = '%' . $wpdb->esc_like( $search ) . '%';
        $city = sanitize_text_field( (string) $args['city'] );
        $group_id = absint( $args['group_id'] );
        $group_slug = sanitize_text_field( (string) $args['group_slug'] );
        $branch_slug = sanitize_text_field( (string) $args['branch_slug'] );
        $group_slugs = is_array( $args['group_slugs'] ) ? array_values( array_filter( array_map( 'sanitize_text_field', $args['group_slugs'] ) ) ) : [];
        $branch_slugs = is_array( $args['branch_slugs'] ) ? array_values( array_filter( array_map( 'sanitize_text_field', $args['branch_slugs'] ) ) ) : [];
        $group_slugs_csv = implode( ',', $group_slugs );
        $branch_slugs_csv = implode( ',', $branch_slugs );

        $page = max( 1, (int) $args['page'] );
        $per = max( 1, min( 100, (int) $args['per_page'] ) );
        $offset = ( $page - 1 ) * $per;


        $filter_params = [
            $search, $like, $like, $like, $like,
            $city, $city,
            $group_id, $group_id,
            $group_slug, $group_slug,
            $group_slugs_csv, $group_slugs_csv,
            $branch_slug, $branch_slug,
            $branch_slugs_csv, $branch_slugs_csv,
        ];

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Query targets the plugin's custom table; prepare() accepts an array of replacement values.
        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}kashida_branch_locator_branches b LEFT JOIN {$wpdb->prefix}kashida_branch_locator_groups g ON g.id = b.group_id
            WHERE (%s = '' OR b.name LIKE %s OR b.city LIKE %s OR b.address LIKE %s OR b.phone LIKE %s)
            AND (%s = '' OR b.city = %s)
            AND (%d = 0 OR b.group_id = %d)
            AND (%s = '' OR g.slug = %s)
            AND (%s = '' OR FIND_IN_SET(g.slug, %s))
            AND (%s = '' OR b.slug = %s)
            AND (%s = '' OR FIND_IN_SET(b.slug, %s))",
            $filter_params
        ) );
        $query_params = array_merge( $filter_params, [ $per, $offset ] );
        $order_desc = strtoupper( (string) $args['order'] ) === 'DESC';

        if ( $args['orderby'] === 'city' ) {
            if ( $order_desc ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Query targets the plugin's custom table; prepare() accepts an array of replacement values.
                $items = $wpdb->get_results( $wpdb->prepare(
                    "SELECT b.*, g.name AS group_name, g.slug AS group_slug, g.color AS group_color FROM {$wpdb->prefix}kashida_branch_locator_branches b LEFT JOIN {$wpdb->prefix}kashida_branch_locator_groups g ON g.id = b.group_id
                    WHERE (%s = '' OR b.name LIKE %s OR b.city LIKE %s OR b.address LIKE %s OR b.phone LIKE %s) AND (%s = '' OR b.city = %s) AND (%d = 0 OR b.group_id = %d) AND (%s = '' OR g.slug = %s) AND (%s = '' OR FIND_IN_SET(g.slug, %s)) AND (%s = '' OR b.slug = %s) AND (%s = '' OR FIND_IN_SET(b.slug, %s)) ORDER BY b.city DESC LIMIT %d OFFSET %d",
                    $query_params
                ) );
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Query targets the plugin's custom table; prepare() accepts an array of replacement values.
                $items = $wpdb->get_results( $wpdb->prepare(
                    "SELECT b.*, g.name AS group_name, g.slug AS group_slug, g.color AS group_color FROM {$wpdb->prefix}kashida_branch_locator_branches b LEFT JOIN {$wpdb->prefix}kashida_branch_locator_groups g ON g.id = b.group_id
                    WHERE (%s = '' OR b.name LIKE %s OR b.city LIKE %s OR b.address LIKE %s OR b.phone LIKE %s) AND (%s = '' OR b.city = %s) AND (%d = 0 OR b.group_id = %d) AND (%s = '' OR g.slug = %s) AND (%s = '' OR FIND_IN_SET(g.slug, %s)) AND (%s = '' OR b.slug = %s) AND (%s = '' OR FIND_IN_SET(b.slug, %s)) ORDER BY b.city ASC LIMIT %d OFFSET %d",
                    $query_params
                ) );
            }
        } elseif ( $args['orderby'] === 'created_at' ) {
            if ( $order_desc ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Query targets the plugin's custom table; prepare() accepts an array of replacement values.
                $items = $wpdb->get_results( $wpdb->prepare(
                    "SELECT b.*, g.name AS group_name, g.slug AS group_slug, g.color AS group_color FROM {$wpdb->prefix}kashida_branch_locator_branches b LEFT JOIN {$wpdb->prefix}kashida_branch_locator_groups g ON g.id = b.group_id
                    WHERE (%s = '' OR b.name LIKE %s OR b.city LIKE %s OR b.address LIKE %s OR b.phone LIKE %s) AND (%s = '' OR b.city = %s) AND (%d = 0 OR b.group_id = %d) AND (%s = '' OR g.slug = %s) AND (%s = '' OR FIND_IN_SET(g.slug, %s)) AND (%s = '' OR b.slug = %s) AND (%s = '' OR FIND_IN_SET(b.slug, %s)) ORDER BY b.created_at DESC LIMIT %d OFFSET %d",
                    $query_params
                ) );
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Query targets the plugin's custom table; prepare() accepts an array of replacement values.
                $items = $wpdb->get_results( $wpdb->prepare(
                    "SELECT b.*, g.name AS group_name, g.slug AS group_slug, g.color AS group_color FROM {$wpdb->prefix}kashida_branch_locator_branches b LEFT JOIN {$wpdb->prefix}kashida_branch_locator_groups g ON g.id = b.group_id
                    WHERE (%s = '' OR b.name LIKE %s OR b.city LIKE %s OR b.address LIKE %s OR b.phone LIKE %s) AND (%s = '' OR b.city = %s) AND (%d = 0 OR b.group_id = %d) AND (%s = '' OR g.slug = %s) AND (%s = '' OR FIND_IN_SET(g.slug, %s)) AND (%s = '' OR b.slug = %s) AND (%s = '' OR FIND_IN_SET(b.slug, %s)) ORDER BY b.created_at ASC LIMIT %d OFFSET %d",
                    $query_params
                ) );
            }
        } else {
            if ( $order_desc ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Query targets the plugin's custom table; prepare() accepts an array of replacement values.
                $items = $wpdb->get_results( $wpdb->prepare(
                    "SELECT b.*, g.name AS group_name, g.slug AS group_slug, g.color AS group_color FROM {$wpdb->prefix}kashida_branch_locator_branches b LEFT JOIN {$wpdb->prefix}kashida_branch_locator_groups g ON g.id = b.group_id
                    WHERE (%s = '' OR b.name LIKE %s OR b.city LIKE %s OR b.address LIKE %s OR b.phone LIKE %s) AND (%s = '' OR b.city = %s) AND (%d = 0 OR b.group_id = %d) AND (%s = '' OR g.slug = %s) AND (%s = '' OR FIND_IN_SET(g.slug, %s)) AND (%s = '' OR b.slug = %s) AND (%s = '' OR FIND_IN_SET(b.slug, %s)) ORDER BY b.name DESC LIMIT %d OFFSET %d",
                    $query_params
                ) );
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Query targets the plugin's custom table; prepare() accepts an array of replacement values.
                $items = $wpdb->get_results( $wpdb->prepare(
                    "SELECT b.*, g.name AS group_name, g.slug AS group_slug, g.color AS group_color FROM {$wpdb->prefix}kashida_branch_locator_branches b LEFT JOIN {$wpdb->prefix}kashida_branch_locator_groups g ON g.id = b.group_id
                    WHERE (%s = '' OR b.name LIKE %s OR b.city LIKE %s OR b.address LIKE %s OR b.phone LIKE %s) AND (%s = '' OR b.city = %s) AND (%d = 0 OR b.group_id = %d) AND (%s = '' OR g.slug = %s) AND (%s = '' OR FIND_IN_SET(g.slug, %s)) AND (%s = '' OR b.slug = %s) AND (%s = '' OR FIND_IN_SET(b.slug, %s)) ORDER BY b.name ASC LIMIT %d OFFSET %d",
                    $query_params
                ) );
            }
        }
        return [ 'items' => $items ?: [], 'total' => $total ];
    }

    public static function get_cities(): array {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom branch table has no equivalent WordPress API.
        return $wpdb->get_col( "SELECT DISTINCT city FROM {$wpdb->prefix}kashida_branch_locator_branches WHERE city != '' ORDER BY city ASC" ) ?: [];
    }

    public static function insert( array $data ): int|false {
        global $wpdb;
        $payload = [
            'name'      => sanitize_text_field( $data['name'] ?? '' ),
            'city'      => sanitize_text_field( $data['city'] ?? '' ),
            'address'   => sanitize_textarea_field( $data['address'] ?? '' ),
            'phone'     => sanitize_text_field( $data['phone'] ?? '' ),
            'latitude'  => self::normalize_latitude( $data['latitude'] ?? 0 ),
            'longitude' => self::normalize_longitude( $data['longitude'] ?? 0 ),
            'group_id'  => ! empty( $data['group_id'] ) ? (int) $data['group_id'] : null,
            'slug'      => self::generate_code( 'br' ),
        ];
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom branch table requires direct writes.
        return $wpdb->insert( self::table(), $payload ) ? (int) $wpdb->insert_id : false;
    }

    public static function update( int $id, array $data ): bool {
        global $wpdb;
        $payload = [];
        foreach ( [ 'name', 'city', 'address', 'phone' ] as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $payload[ $field ] = $field === 'address' ? sanitize_textarea_field( $data[ $field ] ) : sanitize_text_field( $data[ $field ] );
            }
        }
        if ( isset( $data['latitude'] ) ) $payload['latitude'] = self::normalize_latitude( $data['latitude'] );
        if ( isset( $data['longitude'] ) ) $payload['longitude'] = self::normalize_longitude( $data['longitude'] );
        if ( array_key_exists( 'group_id', $data ) ) $payload['group_id'] = $data['group_id'] ? absint( $data['group_id'] ) : null;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom branch table requires direct writes.
        if ( ! $payload ) return false;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom branch table requires a direct update.
        $result = $wpdb->update( self::table(), $payload, [ 'id' => $id ] );
        // WordPress returns 0 when the row exists but values are unchanged; that is not a failure.
        return false !== $result;
    }

    private static function normalize_latitude( $value ): float {
        $v = (float) $value;
        return ( $v >= -90 && $v <= 90 ) ? $v : 0.0;
    }

    private static function normalize_longitude( $value ): float {
        $v = (float) $value;
        return ( $v >= -180 && $v <= 180 ) ? $v : 0.0;
    }

    public static function delete( int $id ): bool {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom branch table requires direct writes.
        return (bool) $wpdb->delete( self::table(), [ 'id' => $id ] );
    }

    public static function exists_by_name( string $name ): ?object {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom branch table has no equivalent WordPress API.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom branch table has no equivalent WordPress API.
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kashida_branch_locator_branches WHERE name = %s", $name ) );
    }

    private static function generate_code( string $prefix ): string {
        global $wpdb;
        do {
            $code = $prefix . '_' . wp_generate_password( 8, false, false );
        } while ( /* phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom branch table has no equivalent WordPress API. */ $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}kashida_branch_locator_branches WHERE slug = %s", $code ) ) );
        return $code;
    }

    public static function normalize_existing_slugs(): void {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom branch table has no equivalent WordPress API.
        $rows = $wpdb->get_results( "SELECT id, slug FROM {$wpdb->prefix}kashida_branch_locator_branches WHERE slug IS NULL OR slug = '' OR slug NOT LIKE 'br\_%'" );
        if ( empty( $rows ) ) return;
        foreach ( $rows as $row ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom branch table requires direct writes.
            $wpdb->update( self::table(), [ 'slug' => self::generate_code( 'br' ) ], [ 'id' => (int) $row->id ] );
        }
    }
}
