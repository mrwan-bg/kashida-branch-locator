<?php
declare(strict_types=1);
namespace Kashida\BranchLocator\Models;
if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The plugin uses dedicated custom tables; no equivalent WordPress data API exists.
class Group {
    private static function table(): string { global $wpdb; return $wpdb->prefix . 'kashida_branch_locator_groups'; }

    public static function all(): array {
        global $wpdb;
        return $wpdb->get_results( "SELECT g.*, COUNT(b.id) AS branch_count FROM {$wpdb->prefix}kashida_branch_locator_groups g LEFT JOIN {$wpdb->prefix}kashida_branch_locator_branches b ON b.group_id = g.id GROUP BY g.id ORDER BY g.name ASC" ) ?: [];
    }

    public static function find( int $id ): ?object {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kashida_branch_locator_groups WHERE id = %d", $id ) );
    }

    public static function find_by_name( string $name ): ?object {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kashida_branch_locator_groups WHERE name = %s", $name ) );
    }

    public static function find_by_slug( string $slug ): ?object {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kashida_branch_locator_groups WHERE slug = %s", sanitize_text_field( $slug ) ) );
    }

    public static function insert( array $data ): int|false {
        global $wpdb;
        $name  = sanitize_text_field( $data['name'] ?? '' );
        $color = sanitize_hex_color( $data['color'] ?? '' ) ?: '#0a6b70';
        $slug  = self::generate_code( 'grp' );
        return $wpdb->insert( self::table(), [ 'name' => $name, 'slug' => $slug, 'color' => $color ] ) ? (int) $wpdb->insert_id : false;
    }

    public static function update( int $id, array $data ): bool {
        global $wpdb;
        $payload = [];
        if ( isset( $data['name'] ) ) $payload['name'] = sanitize_text_field( $data['name'] );
        if ( isset( $data['color'] ) ) $payload['color'] = sanitize_hex_color( $data['color'] ) ?: '#0a6b70';
        return $payload ? (bool) $wpdb->update( self::table(), $payload, [ 'id' => $id ] ) : false;
    }

    public static function delete( int $id ): bool {
        global $wpdb;
        $wpdb->update( $wpdb->prefix . 'kashida_branch_locator_branches', [ 'group_id' => null ], [ 'group_id' => $id ] );
        return (bool) $wpdb->delete( self::table(), [ 'id' => $id ] );
    }

    public static function find_or_create( string $name ): int {
        $existing = self::find_by_name( $name );
        if ( $existing ) return (int) $existing->id;
        return (int) self::insert( [ 'name' => $name ] );
    }

    private static function generate_code( string $prefix ): string {
        global $wpdb;
        do {
            $code = $prefix . '_' . wp_generate_password( 8, false, false );
        } while ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}kashida_branch_locator_groups WHERE slug = %s", $code ) ) );
        return $code;
    }
}

// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
