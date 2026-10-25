<?php
declare(strict_types=1);

namespace Kashida\BranchLocator\Database;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Installer {
    const DB_VERSION_KEY = 'kashida_branch_locator_db_version';
    const DB_VERSION     = '1.4';

    public static function maybe_upgrade(): void {
        global $wpdb;
        $installed_version = (string) get_option( self::DB_VERSION_KEY, '' );
        $groups_table      = $wpdb->prefix . 'kashida_branch_locator_groups';
        $branches_table    = $wpdb->prefix . 'kashida_branch_locator_branches';
        $tables_missing    = ! self::table_exists( $groups_table ) || ! self::table_exists( $branches_table );

        // Schema version alone is not sufficient: recover if an interrupted installation
        // left the option saved but one or both plugin tables missing.
        if ( version_compare( $installed_version, self::DB_VERSION, '<' ) || $tables_missing ) {
            self::install();
        }
    }

    public static function install(): void {
        global $wpdb;

        $legacy_groups   = $wpdb->prefix . 'bl_groups';
        $legacy_branches = $wpdb->prefix . 'bl_branches';
        $groups_table    = $wpdb->prefix . 'kashida_branch_locator_groups';
        $branches_table  = $wpdb->prefix . 'kashida_branch_locator_branches';

        // Rename legacy tables when the new prefixed tables do not exist.
        self::rename_legacy_table( $legacy_groups, $groups_table );
        self::rename_legacy_table( $legacy_branches, $branches_table );

        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "CREATE TABLE {$groups_table} (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name       VARCHAR(255)    NOT NULL,
            slug       VARCHAR(255)    NOT NULL,
            color      VARCHAR(20)     NOT NULL DEFAULT '#0a6b70',
            created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) {$charset};" );

        dbDelta( "CREATE TABLE {$branches_table} (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name       VARCHAR(255)    NOT NULL,
            city       VARCHAR(255)    NOT NULL DEFAULT '',
            address    TEXT            NOT NULL,
            phone      VARCHAR(60)     NOT NULL DEFAULT '',
            phone2     VARCHAR(60)     NOT NULL DEFAULT '',
            website    TEXT            NOT NULL,
            latitude   DECIMAL(10,7)   NOT NULL DEFAULT 0.0000000,
            longitude  DECIMAL(10,7)   NOT NULL DEFAULT 0.0000000,
            group_id   BIGINT UNSIGNED          DEFAULT NULL,
            slug       VARCHAR(255)    NOT NULL DEFAULT '',
            created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY group_id (group_id),
            KEY city (city)
        ) {$charset};" );

        // Do not mark the schema as upgraded unless both tables and their required
        // columns exist. This prevents a failed dbDelta() from being recorded as success.
        $groups_required_columns = [ 'id', 'name', 'slug', 'color', 'created_at' ];
        $branches_required_columns = [
            'id', 'name', 'city', 'address', 'phone', 'phone2', 'website',
            'latitude', 'longitude', 'group_id', 'slug', 'created_at'
        ];

        if (
            ! self::table_has_columns( $groups_table, $groups_required_columns ) ||
            ! self::table_has_columns( $branches_table, $branches_required_columns )
        ) {
            return;
        }

        // If a prior interrupted upgrade left both table sets, copy legacy data only
        // when the corresponding new table is empty. Keep legacy tables as a safety net.
        self::copy_legacy_table_if_new_is_empty(
            $legacy_groups,
            $groups_table,
            [ 'id', 'name', 'slug', 'color', 'created_at' ]
        );
        self::copy_legacy_table_if_new_is_empty(
            $legacy_branches,
            $branches_table,
            [ 'id', 'name', 'city', 'address', 'phone', 'phone2', 'website', 'latitude', 'longitude', 'group_id', 'slug', 'created_at' ]
        );

        update_option( self::DB_VERSION_KEY, self::DB_VERSION );
        delete_option( 'bl_db_version' );
    }

    private static function table_exists( string $table ): bool {
        global $wpdb;

        // Schema discovery targets this plugin's custom tables and is not cacheable.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Required to safely detect custom table existence.
        $found = $wpdb->get_var(
            $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) )
        );

        return $found === $table;
    }

    /**
     * Confirm that a custom table exists and contains every required column.
     *
     * @param string   $table   Table name derived from the active WordPress prefix.
     * @param string[] $columns Required column names.
     */
    private static function table_has_columns( string $table, array $columns ): bool {
        global $wpdb;

        if ( ! self::table_exists( $table ) ) {
            return false;
        }

        // The table name is constructed internally from the active WordPress prefix and a fixed plugin suffix.
        // Escape the identifier and keep the query restricted to this plugin's own tables.
        $safe_table = '`' . str_replace( '`', '``', $table ) . '`';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Required schema inspection after dbDelta(); this internally constructed and escaped table identifier cannot be passed as a value placeholder.
        $existing_columns = $wpdb->get_col( "SHOW COLUMNS FROM {$safe_table}", 0 );

        if ( ! is_array( $existing_columns ) || empty( $existing_columns ) ) {
            return false;
        }

        return empty( array_diff( $columns, $existing_columns ) );
    }

    private static function rename_legacy_table( string $legacy, string $target ): void {
        global $wpdb;

        if ( self::table_exists( $legacy ) && ! self::table_exists( $target ) ) {
            // Table names are derived exclusively from the trusted WordPress prefix and constants.
            // Identifiers are generated from the trusted WordPress prefix and fixed suffixes.
            $wpdb->query( "RENAME TABLE `{$legacy}` TO `{$target}`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Dynamic identifiers cannot be passed as SQL value placeholders.
        }
    }

    private static function copy_legacy_table_if_new_is_empty( string $legacy, string $target, array $columns ): void {
        global $wpdb;

        if ( ! self::table_exists( $legacy ) || ! self::table_exists( $target ) ) {
            return;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Trusted custom table identifier; query is used only during schema migration.
        $target_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$target}`" );
        if ( 0 !== $target_count ) {
            return;
        }

        // Table and column names are SQL identifiers, not values, so they cannot use
        // ordinary %s placeholders. Validate every identifier before quoting it.
        $quoted_target = self::quote_identifier( $target );
        $quoted_legacy = self::quote_identifier( $legacy );
        $quoted_columns = array_map( [ self::class, 'quote_identifier' ], $columns );

        if ( '' === $quoted_target || '' === $quoted_legacy || in_array( '', $quoted_columns, true ) ) {
            return;
        }

        $column_list = implode( ', ', $quoted_columns );
        $query       = sprintf(
            'INSERT IGNORE INTO %s (%s) SELECT %s FROM %s',
            $quoted_target,
            $column_list,
            $column_list,
            $quoted_legacy
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Identifiers are strictly validated against an allowlist-compatible pattern and quoted; SQL value placeholders cannot represent identifiers.
        $wpdb->query( $query );
    }

    /**
     * Validate and quote a SQL identifier used by this plugin's schema migration.
     *
     * @param string $identifier Identifier derived from the WordPress prefix or a fixed column allowlist.
     * @return string Quoted identifier, or an empty string when invalid.
     */
    private static function quote_identifier( string $identifier ): string {
        if ( ! preg_match( '/\A[a-zA-Z0-9_]+\z/', $identifier ) ) {
            return '';
        }

        return '`' . $identifier . '`';
    }

    public static function deactivate(): void {}
}
