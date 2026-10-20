<?php
/**
 * Plugin Name: Kashida Branch Locator
 * Plugin URI:  https://kashida.ly/
 * Description: Lightweight branch and location directory with groups, CSV/XLSX import, responsive layouts, and interactive OpenStreetMap maps.
 * Version:     2.5.31
 * Author:      Mrwan elGobee - Kashida Media Services
 * Author URI:  https://kashida.ly/
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kashida-branch-locator
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'KASHIDA_BRANCH_LOCATOR_VERSION' ) ) {
    define( 'KASHIDA_BRANCH_LOCATOR_VERSION', '2.5.31' );
}
if ( ! defined( 'KASHIDA_BRANCH_LOCATOR_FILE' ) ) {
    define( 'KASHIDA_BRANCH_LOCATOR_FILE', __FILE__ );
}
if ( ! defined( 'KASHIDA_BRANCH_LOCATOR_PATH' ) ) {
    define( 'KASHIDA_BRANCH_LOCATOR_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'KASHIDA_BRANCH_LOCATOR_URL' ) ) {
    define( 'KASHIDA_BRANCH_LOCATOR_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'KASHIDA_BRANCH_LOCATOR_ASSETS_URL' ) ) {
    define( 'KASHIDA_BRANCH_LOCATOR_ASSETS_URL', KASHIDA_BRANCH_LOCATOR_URL . 'assets/' );
}
if ( ! defined( 'KASHIDA_BRANCH_LOCATOR_INCLUDES' ) ) {
    define( 'KASHIDA_BRANCH_LOCATOR_INCLUDES', KASHIDA_BRANCH_LOCATOR_PATH . 'includes/' );
}

$kashida_branch_locator_namespace = 'Kashida\\BranchLocator\\';

if ( ! class_exists( 'Kashida\\BranchLocator\\Plugin', false ) ) {
    spl_autoload_register(
        static function ( string $class ) use ( $kashida_branch_locator_namespace ): void {
            if ( strpos( $class, $kashida_branch_locator_namespace ) !== 0 ) {
                return;
            }

            $relative = substr( $class, strlen( $kashida_branch_locator_namespace ) );
            $parts    = explode( '\\', $relative );
            $map      = [
                'Admin'      => 'admin',
                'Frontend'   => 'frontend',
                'Import'     => 'import',
                'Models'     => 'models',
                'Database'   => 'database',
                'Shortcodes' => 'shortcodes',
            ];
            $folder = isset( $parts[0], $map[ $parts[0] ] ) ? $map[ $parts[0] ] . DIRECTORY_SEPARATOR : '';
            $class_name = (string) end( $parts );
            $file = KASHIDA_BRANCH_LOCATOR_INCLUDES . $folder . 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

            if ( is_readable( $file ) ) {
                require_once $file;
            }
        }
    );

    require_once KASHIDA_BRANCH_LOCATOR_INCLUDES . 'class-plugin.php';

    add_action(
        'plugins_loaded',
        static function (): void {
            // Apply schema updates on plugin upgrades; activation hooks do not run during updates.
            \Kashida\BranchLocator\Database\Installer::maybe_upgrade();
            \Kashida\BranchLocator\Plugin::instance()->init();
        }
    );

    register_activation_hook( __FILE__, [ 'Kashida\\BranchLocator\\Database\\Installer', 'install' ] );
    register_deactivation_hook( __FILE__, [ 'Kashida\\BranchLocator\\Database\\Installer', 'deactivate' ] );
}
