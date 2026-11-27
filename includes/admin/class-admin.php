<?php
declare(strict_types=1);
namespace Kashida\BranchLocator\Admin;

use Kashida\BranchLocator\Import\Importer;

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin {
    public function __construct() {
        add_action( 'admin_menu',             [ $this, 'register_menus' ] );
        add_action( 'admin_enqueue_scripts',  [ $this, 'enqueue_assets' ] );
        add_action( 'admin_init', [ $this, 'add_privacy_policy_content' ] );
        add_action( 'admin_init', [ $this, 'register_color_settings' ] );
        add_action( 'admin_init', [ $this, 'register_display_mode_setting' ] );
        add_action( 'admin_init', [ $this, 'register_appearance_settings' ] );
        add_action( 'in_admin_header', [ $this, 'render_nav' ] );
        add_action( 'wp_ajax_kashida_branch_locator_save_branch', [ $this, 'ajax_save_branch' ] );
        add_action( 'wp_ajax_kashida_branch_locator_delete_branch',[ $this, 'ajax_delete_branch' ] );
        add_action( 'wp_ajax_kashida_branch_locator_bulk_delete_branches', [ $this, 'ajax_bulk_delete_branches' ] );
        add_action( 'wp_ajax_kashida_branch_locator_select_all_branches', [ $this, 'ajax_select_all_branches' ] );
        add_action( 'wp_ajax_kashida_branch_locator_bulk_delete_groups', [ $this, 'ajax_bulk_delete_groups' ] );
        add_action( 'wp_ajax_kashida_branch_locator_save_group',  [ $this, 'ajax_save_group' ] );
        add_action( 'wp_ajax_kashida_branch_locator_delete_group',[ $this, 'ajax_delete_group' ] );
        add_action( 'wp_ajax_kashida_branch_locator_import_preview', [ $this, 'ajax_import_preview' ] );
        add_action( 'wp_ajax_kashida_branch_locator_import_run',     [ $this, 'ajax_import_run' ] );
    }

    public function register_menus(): void {
        add_menu_page(
            __( 'Kashida Branch Locator', 'kashida-branch-locator' ),
            __( 'Kashida Branch Locator', 'kashida-branch-locator' ),
            'manage_options',
            'kashida-branch-locator',
            [ $this, 'page_branches' ],
            'dashicons-location-alt',
            80
        );

        add_submenu_page(
            'kashida-branch-locator',
            __( 'Branches', 'kashida-branch-locator' ),
            __( 'Branches', 'kashida-branch-locator' ),
            'manage_options',
            'kashida-branch-locator',
            [ $this, 'page_branches' ]
        );

        add_submenu_page(
            'kashida-branch-locator',
            __( 'Groups', 'kashida-branch-locator' ),
            __( 'Groups', 'kashida-branch-locator' ),
            'manage_options',
            'kashida-branch-locator-groups',
            [ $this, 'page_groups' ]
        );

        add_submenu_page(
            'kashida-branch-locator',
            __( 'Import', 'kashida-branch-locator' ),
            __( 'Import', 'kashida-branch-locator' ),
            'manage_options',
            'kashida-branch-locator-import',
            [ $this, 'page_import' ]
        );

        add_submenu_page(
            'kashida-branch-locator',
            __( 'Settings', 'kashida-branch-locator' ),
            __( 'Settings', 'kashida-branch-locator' ),
            'manage_options',
            'kashida-branch-locator-settings',
            [ $this, 'page_settings' ]
        );

        add_submenu_page(
            'kashida-branch-locator',
            __( 'Help', 'kashida-branch-locator' ),
            __( 'Help', 'kashida-branch-locator' ),
            'manage_options',
            'kashida-branch-locator-help',
            [ $this, 'page_help' ]
        );

    }

    public function enqueue_assets( string $hook ): void {
        if ( strpos( $hook, 'kashida-branch-locator' ) === false ) {
            return;
        }

        $asset_version = static function ( string $relative_path ): string {
            $asset_path = KASHIDA_BRANCH_LOCATOR_PATH . 'assets/' . ltrim( $relative_path, '/' );
            return is_file( $asset_path ) ? (string) filemtime( $asset_path ) : KASHIDA_BRANCH_LOCATOR_VERSION;
        };

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        if ( 'toplevel_page_kashida-branch-locator' === $hook || 'kashida-branch-locator_page_kashida-branch-locator-groups' === $hook ) {
            wp_enqueue_style( 'kashida-branch-locator-frontend', KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'css/frontend.css', [], $asset_version( 'css/frontend.css' ) );
            wp_enqueue_script( 'kashida-branch-locator-frontend', KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'js/frontend.js', [ 'jquery' ], $asset_version( 'js/frontend.js' ), true );
        }
        wp_enqueue_style( 'kashida-branch-locator-admin', KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'css/admin.css', [], $asset_version( 'css/admin.css' ) );
        wp_enqueue_style( 'kashida-branch-locator-admin-v2', KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'css/admin-v2.css', [ 'kashida-branch-locator-admin' ], $asset_version( 'css/admin-v2.css' ) );
        if ( 'kashida-branch-locator_page_kashida-branch-locator-settings' === $hook ) {
            wp_enqueue_script( 'kashida-branch-locator-admin-settings', KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'js/admin-settings.js', [ 'jquery', 'wp-color-picker' ], $asset_version( 'js/admin-settings.js' ), true );
        }
        wp_enqueue_script( 'kashida-branch-locator-admin', KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'js/admin.js', [ 'jquery' ], $asset_version( 'js/admin.js' ), true );
        wp_localize_script( 'kashida-branch-locator-admin', 'KashidaBranchLocatorAdmin', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'kashida_branch_locator_admin_nonce' ),
            'preview'  => [
                'frontend_css' => KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'css/frontend.css',
                'theme_css'    => KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'css/frontend-theme.css',
                'frontend_js'  => KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'js/frontend.js',
                'jquery_js'    => includes_url( 'js/jquery/jquery.min.js' ),
            ],
            'i18n'     => [
                'confirm_delete' => __( 'Are you sure you want to delete this?', 'kashida-branch-locator' ),
                'saving'         => __( 'Saving…', 'kashida-branch-locator' ),
                'deleting'       => __( 'Deleting…', 'kashida-branch-locator' ),
                'save'           => __( 'Save', 'kashida-branch-locator' ),
                'delete'         => __( 'Delete', 'kashida-branch-locator' ),
                'copied'         => __( 'Copied', 'kashida-branch-locator' ),
                /* translators: %d: number of selected items. */
                'selected'       => __( '%d selected', 'kashida-branch-locator' ),
                'branches'       => __( 'branches', 'kashida-branch-locator' ),
                'groups'         => __( 'groups', 'kashida-branch-locator' ),
                'confirm_bulk_delete' => __( 'Are you sure you want to delete all selected items?', 'kashida-branch-locator' ),
                'delete_selected' => __( 'Delete Selected', 'kashida-branch-locator' ),
                'select_all_results' => __( 'Select all matching results', 'kashida-branch-locator' ),
                'selecting_results' => __( 'Selecting…', 'kashida-branch-locator' ),
                'selected_all_results' => __( 'All matching results selected.', 'kashida-branch-locator' ),
                /* translators: %d: number of selected branches. */
                'confirm_bulk_delete_branches' => __( 'Delete %d selected branches? This action cannot be undone.', 'kashida-branch-locator' ),
                /* translators: %d: number of selected groups. */
                'confirm_bulk_delete_groups' => __( 'Delete %d selected groups? Their branches will be unassigned. This action cannot be undone.', 'kashida-branch-locator' ),
                'confirm_delete_branch' => __( 'Delete this branch? This action cannot be undone.', 'kashida-branch-locator' ),
                'confirm_delete_group' => __( 'Delete this group? Its branches will be unassigned. This action cannot be undone.', 'kashida-branch-locator' ),
                'preview_only' => __( 'Preview only', 'kashida-branch-locator' ),
                'group' => __( 'Group', 'kashida-branch-locator' ),
                'city' => __( 'City', 'kashida-branch-locator' ),
                'address' => __( 'Address', 'kashida-branch-locator' ),
                'phone' => __( 'Phone', 'kashida-branch-locator' ),
                'call' => __( 'Call', 'kashida-branch-locator' ),
                'directions' => __( 'Directions', 'kashida-branch-locator' ),
                'map_preview' => __( 'Map preview', 'kashida-branch-locator' ),
                'sample_branch' => __( 'Sample Branch', 'kashida-branch-locator' ),
                'sample_city' => __( 'Sample City', 'kashida-branch-locator' ),
                'sample_address' => __( 'Sample address', 'kashida-branch-locator' ),
                'selected_locations' => __( 'Selected locations', 'kashida-branch-locator' ),
                'search' => __( 'Search', 'kashida-branch-locator' ),
                'sort_by' => __( 'Sort by', 'kashida-branch-locator' ),
                'results_count_preview' => __( '12 results', 'kashida-branch-locator' ),
            ],
        ] );

        if ( strpos( $hook, 'import' ) !== false ) {
            wp_enqueue_script( 'kashida-branch-locator-import', KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'js/import.js', [ 'jquery' ], KASHIDA_BRANCH_LOCATOR_VERSION, true );
            wp_localize_script( 'kashida-branch-locator-import', 'KashidaBranchLocatorImport', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'kashida_branch_locator_import_nonce' ),
                'i18n'     => [
                    'invalid_type' => __( 'Only .xlsx, .xls, and .csv files are accepted.', 'kashida-branch-locator' ),
                    /* translators: %s: uploaded file name. */
                    'selected' => __( 'Selected: %s', 'kashida-branch-locator' ),
                    'loading' => __( 'Loading…', 'kashida-branch-locator' ),
                    'preview_import' => __( 'Preview Import', 'kashida-branch-locator' ),
                    'importing' => __( 'Importing…', 'kashida-branch-locator' ),
                    'run_import' => __( 'Run Import', 'kashida-branch-locator' ),
                    'import_complete' => __( 'Import Complete', 'kashida-branch-locator' ),
                    /* translators: %d: number of inserted branches. */
                    'branches_inserted' => __( '%d branches inserted', 'kashida-branch-locator' ),
                    /* translators: %d: number of updated branches. */
                    'branches_updated' => __( '%d branches updated', 'kashida-branch-locator' ),
                    /* translators: %d: number of skipped import rows. */
                    'rows_skipped' => __( '%d rows skipped', 'kashida-branch-locator' ),
                    'errors' => __( 'Errors:', 'kashida-branch-locator' ),
                    'import_failed' => __( 'Import Failed', 'kashida-branch-locator' ),
                    'request_failed' => __( 'Request failed.', 'kashida-branch-locator' ),
                    'rows_total' => __( 'rows total', 'kashida-branch-locator' ),
                    'showing_first' => __( 'showing first', 'kashida-branch-locator' ),
                    'error' => __( 'Error', 'kashida-branch-locator' ),
                    'warning' => __( 'Warning', 'kashida-branch-locator' ),
                    'ready' => __( 'Ready', 'kashida-branch-locator' ),
                    'warnings' => __( 'Warnings:', 'kashida-branch-locator' ),
                ],
            ] );
        }
    }

    public function add_privacy_policy_content(): void {
        if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
            wp_add_privacy_policy_content( __( 'Kashida Branch Locator', 'kashida-branch-locator' ), wp_kses_post( '<p>' . __( 'Kashida Branch Locator stores branch, address, phone, coordinate, and group information entered by site administrators in the WordPress database. The plugin does not send this data to Kashida servers. When map layouts are used, visitors\' browsers request map tiles from OpenStreetMap. When a visitor chooses Directions, the browser opens Google Maps. Site owners should review the privacy policies of those services for their own sites.', 'kashida-branch-locator' ) . '</p>' ) );
        }
    }

    public function register_color_settings(): void {
        register_setting( 'kashida_branch_locator_settings', 'kashida_branch_locator_default_group_color', [
            'type' => 'string',
            'sanitize_callback' => static function ( $value ): string {
                return sanitize_hex_color( $value ) ?: '#0a6b70';
            },
            'default' => '#0a6b70',
        ] );
    }

    public function register_appearance_settings(): void {
        $hex = static function ( $value ): string {
            $clean = sanitize_hex_color( is_string( $value ) ? $value : '' );
            return is_string( $clean ) ? $clean : '';
        };
        foreach ( [ 'call', 'directions', 'details', 'website' ] as $name ) {
            register_setting( 'kashida_branch_locator_settings', 'kashida_branch_locator_btn_' . $name . '_color', [
                'type' => 'string',
                'sanitize_callback' => $hex,
                'default' => '',
            ] );
        }
        register_setting( 'kashida_branch_locator_settings', 'kashida_branch_locator_theme_colors', [
            'type' => 'string',
            'sanitize_callback' => static function ( $value ): string {
                return '0' === (string) $value ? '0' : '1';
            },
            'default' => '1',
        ] );
        register_setting( 'kashida_branch_locator_settings', 'kashida_branch_locator_container_width', [
            'type' => 'integer',
            'sanitize_callback' => static function ( $value ): int {
                $width = absint( $value );
                return 0 === $width ? 0 : max( 320, min( 2400, $width ) );
            },
            'default' => 0,
        ] );
        register_setting( 'kashida_branch_locator_settings', 'kashida_branch_locator_container_align', [
            'type' => 'string',
            'sanitize_callback' => static function ( $value ): string {
                return 'start' === $value ? 'start' : 'center';
            },
            'default' => 'center',
        ] );
    }

    public function render_nav(): void {
        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation state.
        if ( 0 !== strpos( $page, 'kashida-branch-locator' ) ) {
            $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
            $screen_pages = [
                'toplevel_page_kashida-branch-locator' => 'kashida-branch-locator',
                'kashida-branch-locator_page_kashida-branch-locator-groups' => 'kashida-branch-locator-groups',
                'kashida-branch-locator_page_kashida-branch-locator-import' => 'kashida-branch-locator-import',
                'kashida-branch-locator_page_kashida-branch-locator-settings' => 'kashida-branch-locator-settings',
                'kashida-branch-locator_page_kashida-branch-locator-help' => 'kashida-branch-locator-help',
            ];
            $page = ( $screen && isset( $screen_pages[ $screen->id ] ) ) ? $screen_pages[ $screen->id ] : '';
        }
        if ( 0 !== strpos( $page, 'kashida-branch-locator' ) ) {
            return;
        }
        $items = [
            'kashida-branch-locator'          => [ __( 'Branches', 'kashida-branch-locator' ), 'dashicons-location' ],
            'kashida-branch-locator-groups'   => [ __( 'Groups', 'kashida-branch-locator' ), 'dashicons-category' ],
            'kashida-branch-locator-import'   => [ __( 'Import', 'kashida-branch-locator' ), 'dashicons-upload' ],
            'kashida-branch-locator-settings' => [ __( 'Settings', 'kashida-branch-locator' ), 'dashicons-admin-generic' ],
            'kashida-branch-locator-help'     => [ __( 'Help', 'kashida-branch-locator' ), 'dashicons-editor-help' ],
        ];
        echo '<nav class="bl-admin-nav" aria-label="' . esc_attr__( 'Kashida Branch Locator', 'kashida-branch-locator' ) . '">';
        foreach ( $items as $slug => $item ) {
            printf(
                '<a class="bl-admin-nav-link%s" href="%s"%s><span class="dashicons %s" aria-hidden="true"></span>%s</a>',
                $slug === $page ? ' is-active' : '',
                esc_url( admin_url( 'admin.php?page=' . $slug ) ),
                $slug === $page ? ' aria-current="page"' : '',
                esc_attr( $item[1] ),
                esc_html( $item[0] )
            );
        }
        echo '</nav>';
    }

    public function register_display_mode_setting(): void {
        register_setting( 'kashida_branch_locator_settings', 'kashida_branch_locator_display_mode', [
            'type' => 'string',
            'sanitize_callback' => static function ( $value ): string {
                return in_array( $value, [ 'pagination', 'infinite_scroll' ], true ) ? $value : 'pagination';
            },
            'default' => 'pagination',
        ] );
    }

    public function page_settings(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $display_mode = get_option( 'kashida_branch_locator_display_mode', 'pagination' );
        $group_color  = get_option( 'kashida_branch_locator_default_group_color', '#0a6b70' );
        $theme_colors = '0' !== (string) get_option( 'kashida_branch_locator_theme_colors', '1' );
        $width        = absint( get_option( 'kashida_branch_locator_container_width', 0 ) );
        $align        = 'start' === get_option( 'kashida_branch_locator_container_align', 'center' ) ? 'start' : 'center';
        $buttons = [
            'directions' => [ __( 'Directions / View on map', 'kashida-branch-locator' ), __( 'Main action button. Empty follows your theme accent color.', 'kashida-branch-locator' ), '#1f4fa8' ],
            'call'       => [ __( 'Call button', 'kashida-branch-locator' ), __( 'Phone call button. Empty uses the plugin green.', 'kashida-branch-locator' ), '#147d64' ],
            'details'    => [ __( 'Details button', 'kashida-branch-locator' ), __( 'Outlined button and icon on cards and tables. Empty follows your theme accent color.', 'kashida-branch-locator' ), '#1f4fa8' ],
            'website'    => [ __( 'Website button', 'kashida-branch-locator' ), __( 'Visit website button. Empty uses the plugin copper tone.', 'kashida-branch-locator' ), '#a64f2b' ],
        ];
        ?>
        <div class="wrap bl-admin-wrap bl-settings-wrap">
            <div class="bl-admin-page-head bl-settings-page-head">
                <div class="bl-admin-brand">
                    <div class="bl-admin-brand-logo"><img src="<?php echo esc_url( KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'images/kashida-mark.svg' ); ?>" alt="" width="46" height="46"></div>
                    <div>
                        <span class="bl-admin-eyebrow"><?php esc_html_e( 'Kashida Branch Locator', 'kashida-branch-locator' ); ?></span>
                        <h1><?php esc_html_e( 'Settings', 'kashida-branch-locator' ); ?></h1>
                        <p><?php esc_html_e( 'Control colors, container width, theme integration and how branch results are displayed.', 'kashida-branch-locator' ); ?></p>
                    </div>
                </div>
            </div>
            <form method="post" action="options.php" class="bl-settings-form">
                <?php settings_fields( 'kashida_branch_locator_settings' ); ?>

                <section class="bl-settings-card bl-settings-card--buttons bl-span-2">
                    <div class="bl-settings-card-heading">
                        <span class="bl-settings-card-icon dashicons dashicons-admin-appearance" aria-hidden="true"></span>
                        <div><h2><?php esc_html_e( 'Button Colors', 'kashida-branch-locator' ); ?></h2><p><?php esc_html_e( 'Set a color for each button. Leave a color empty to keep the automatic theme-matched look. Shortcode attributes still override these values.', 'kashida-branch-locator' ); ?></p></div>
                    </div>
                    <div class="bl-btn-colors">
                        <div class="bl-btn-colors-fields">
                            <?php foreach ( $buttons as $key => $meta ) :
                                $option = 'kashida_branch_locator_btn_' . $key . '_color';
                                $value  = (string) get_option( $option, '' );
                                ?>
                                <div class="bl-btn-color-row">
                                    <div class="bl-settings-field-copy"><label for="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $meta[0] ); ?></label><p class="description"><?php echo esc_html( $meta[1] ); ?></p></div>
                                    <div class="bl-settings-field-control"><input type="text" id="<?php echo esc_attr( $option ); ?>" name="<?php echo esc_attr( $option ); ?>" value="<?php echo esc_attr( $value ); ?>" class="kbl-btn-color-picker" data-key="<?php echo esc_attr( $key ); ?>" data-fallback="<?php echo esc_attr( $meta[2] ); ?>" placeholder="<?php esc_attr_e( 'Theme', 'kashida-branch-locator' ); ?>"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="bl-btn-preview" aria-hidden="true">
                            <span class="bl-btn-preview-title"><?php esc_html_e( 'Live preview', 'kashida-branch-locator' ); ?></span>
                            <div class="bl-btn-preview-card">
                                <strong><?php esc_html_e( 'Sample Branch', 'kashida-branch-locator' ); ?></strong>
                                <span><?php esc_html_e( 'Sample address', 'kashida-branch-locator' ); ?></span>
                                <div class="bl-btn-preview-actions">
                                    <span class="bl-prev-btn" data-key="call"><?php esc_html_e( 'Call', 'kashida-branch-locator' ); ?></span>
                                    <span class="bl-prev-btn" data-key="directions"><?php esc_html_e( 'Directions', 'kashida-branch-locator' ); ?></span>
                                    <span class="bl-prev-btn bl-prev-outline" data-key="details"><?php esc_html_e( 'Details', 'kashida-branch-locator' ); ?></span>
                                    <span class="bl-prev-btn" data-key="website"><?php esc_html_e( 'Visit website', 'kashida-branch-locator' ); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bl-settings-card bl-settings-card--container">
                    <div class="bl-settings-card-heading">
                        <span class="bl-settings-card-icon dashicons dashicons-editor-expand" aria-hidden="true"></span>
                        <div><h2><?php esc_html_e( 'Display Container', 'kashida-branch-locator' ); ?></h2><p><?php esc_html_e( 'Choose how wide the locator is on your pages.', 'kashida-branch-locator' ); ?></p></div>
                    </div>
                    <div class="bl-settings-stack">
                        <div class="bl-width-control">
                            <label for="kbl-container-width"><?php esc_html_e( 'Maximum width (px)', 'kashida-branch-locator' ); ?></label>
                            <div class="bl-width-row">
                                <input type="range" id="kbl-container-width-range" min="0" max="2400" step="20" value="<?php echo esc_attr( (string) $width ); ?>">
                                <input type="number" id="kbl-container-width" name="kashida_branch_locator_container_width" min="0" max="2400" step="10" value="<?php echo esc_attr( (string) $width ); ?>">
                            </div>
                            <div class="bl-width-chips" role="group" aria-label="<?php esc_attr_e( 'Width presets', 'kashida-branch-locator' ); ?>">
                                <button type="button" class="bl-chip" data-width="0"><?php esc_html_e( 'Full width', 'kashida-branch-locator' ); ?></button>
                                <button type="button" class="bl-chip" data-width="1000">1000</button>
                                <button type="button" class="bl-chip" data-width="1200">1200</button>
                                <button type="button" class="bl-chip" data-width="1400">1400</button>
                                <button type="button" class="bl-chip" data-width="1600">1600</button>
                            </div>
                            <p class="description"><?php esc_html_e( '0 means the locator fills the full width of its content area. Per shortcode you can use width="1200" or width="90%".', 'kashida-branch-locator' ); ?></p>
                        </div>
                        <fieldset class="bl-align-options">
                            <legend><?php esc_html_e( 'Alignment', 'kashida-branch-locator' ); ?></legend>
                            <label class="bl-settings-choice"><input type="radio" name="kashida_branch_locator_container_align" value="center" <?php checked( $align, 'center' ); ?>><span><strong><?php esc_html_e( 'Centered', 'kashida-branch-locator' ); ?></strong></span></label>
                            <label class="bl-settings-choice"><input type="radio" name="kashida_branch_locator_container_align" value="start" <?php checked( $align, 'start' ); ?>><span><strong><?php esc_html_e( 'Start (left in LTR, right in RTL)', 'kashida-branch-locator' ); ?></strong></span></label>
                        </fieldset>
                    </div>
                </section>

                <section class="bl-settings-card bl-settings-card--theme">
                    <div class="bl-settings-card-heading">
                        <span class="bl-settings-card-icon dashicons dashicons-admin-customizer" aria-hidden="true"></span>
                        <div><h2><?php esc_html_e( 'Theme Integration', 'kashida-branch-locator' ); ?></h2><p><?php esc_html_e( 'Let the locator inherit your active theme style.', 'kashida-branch-locator' ); ?></p></div>
                    </div>
                    <div class="bl-settings-stack">
                        <input type="hidden" name="kashida_branch_locator_theme_colors" value="0">
                        <label class="bl-settings-choice"><input type="checkbox" name="kashida_branch_locator_theme_colors" value="1" <?php checked( $theme_colors ); ?>><span><strong><?php esc_html_e( 'Match theme colors and style', 'kashida-branch-locator' ); ?></strong><small><?php esc_html_e( 'Uses the theme accent color, button text color, corner radius, font family and font size. Turn off to use the plugin default palette.', 'kashida-branch-locator' ); ?></small></span></label>
                    </div>
                </section>

                <section class="bl-settings-card bl-settings-card--group">
                    <div class="bl-settings-card-heading">
                        <span class="bl-settings-card-icon dashicons dashicons-art" aria-hidden="true"></span>
                        <div><h2><?php esc_html_e( 'Group Appearance', 'kashida-branch-locator' ); ?></h2><p><?php esc_html_e( 'Choose the initial color used for newly created groups.', 'kashida-branch-locator' ); ?></p></div>
                    </div>
                    <div class="bl-settings-field">
                        <div class="bl-settings-field-copy"><label for="kashida_branch_locator_default_group_color"><?php esc_html_e( 'Default group color', 'kashida-branch-locator' ); ?></label><p class="description"><?php esc_html_e( 'This color is applied when a new group is created. You can customize each group later.', 'kashida-branch-locator' ); ?></p></div>
                        <div class="bl-settings-field-control"><input type="text" id="kashida_branch_locator_default_group_color" name="kashida_branch_locator_default_group_color" value="<?php echo esc_attr( $group_color ); ?>" class="kbl-color-picker" data-default-color="#0a6b70"></div>
                    </div>
                </section>

                <section class="bl-settings-card bl-settings-card--display">
                    <div class="bl-settings-card-heading">
                        <span class="bl-settings-card-icon dashicons dashicons-list-view" aria-hidden="true"></span>
                        <div><h2><?php esc_html_e( 'Branch Display', 'kashida-branch-locator' ); ?></h2><p><?php esc_html_e( 'Control how branch results load on the public-facing locator.', 'kashida-branch-locator' ); ?></p></div>
                    </div>
                    <fieldset class="bl-settings-mode-options">
                        <legend class="screen-reader-text"><?php esc_html_e( 'Branch Display Mode', 'kashida-branch-locator' ); ?></legend>
                        <label class="bl-settings-choice"><input type="radio" name="kashida_branch_locator_display_mode" value="pagination" <?php checked( $display_mode, 'pagination' ); ?>><span><strong><?php esc_html_e( 'Pagination', 'kashida-branch-locator' ); ?></strong><small><?php esc_html_e( 'Show navigation controls between result pages.', 'kashida-branch-locator' ); ?></small></span></label>
                        <label class="bl-settings-choice"><input type="radio" name="kashida_branch_locator_display_mode" value="infinite_scroll" <?php checked( $display_mode, 'infinite_scroll' ); ?>><span><strong><?php esc_html_e( 'Infinite Scroll', 'kashida-branch-locator' ); ?></strong><small><?php esc_html_e( 'Load more branches as visitors scroll down.', 'kashida-branch-locator' ); ?></small></span></label>
                    </fieldset>
                </section>

                <div class="bl-settings-actions bl-span-2"><?php submit_button( __( 'Save Changes', 'kashida-branch-locator' ), 'primary', 'submit', false ); ?><span class="description"><?php esc_html_e( 'Changes apply to the branch locator display and newly created groups.', 'kashida-branch-locator' ); ?></span></div>
            </form>
            <?php Footer::render(); ?>
        </div>
        <?php
    }

    public function page_branches(): void { $page = new Branches_Page(); $page->render(); }
    public function page_groups(): void { $page = new Groups_Page(); $page->render(); }
    public function page_import(): void { $page = new Import_Page(); $page->render(); }
    public function page_help(): void { $page = new Help_Page(); $page->render(); }

    private function verify_nonce(): void {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'kashida_branch_locator_admin_nonce' ) || ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Permission denied.', 'kashida-branch-locator' ) ], 403 );
        }
    }

    public function ajax_save_branch(): void {
        $this->verify_nonce();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verify_nonce() validates the AJAX request before reading POST data.
        $post = isset( $_POST ) && is_array( $_POST ) ? wp_unslash( $_POST ) : [];
        $id   = isset( $post['id'] ) ? absint( $post['id'] ) : 0;
        $latitude = $post['latitude'] ?? 0;
        $longitude = $post['longitude'] ?? 0;
        if ( ! is_scalar( $latitude ) || ! is_scalar( $longitude ) || ( '' !== trim( (string) $latitude ) && ! is_numeric( $latitude ) ) || ( '' !== trim( (string) $longitude ) && ! is_numeric( $longitude ) ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid coordinates.', 'kashida-branch-locator' ) ] );
        }
        $data = [
            'name'      => sanitize_text_field( $post['name'] ?? '' ),
            'city'      => sanitize_text_field( $post['city'] ?? '' ),
            'address'   => sanitize_textarea_field( $post['address'] ?? '' ),
            'phone'     => sanitize_text_field( $post['phone'] ?? '' ),
            'phone2'    => sanitize_text_field( $post['phone2'] ?? '' ),
            'website'   => esc_url_raw( $post['website'] ?? '' ),
            'latitude'  => (float) $latitude,
            'longitude' => (float) $longitude,
            'group_id'  => ! empty( $post['group_id'] ) ? absint( $post['group_id'] ) : null,
        ];
        if ( $data['latitude'] < -90 || $data['latitude'] > 90 || $data['longitude'] < -180 || $data['longitude'] > 180 ) {
            wp_send_json_error( [ 'message' => __( 'Invalid coordinates.', 'kashida-branch-locator' ) ] );
        }
        if ( $data['group_id'] && ! \Kashida\BranchLocator\Models\Group::find( $data['group_id'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid group.', 'kashida-branch-locator' ) ] );
        }
        if ( empty( $data['name'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Name is required.', 'kashida-branch-locator' ) ] );
        }
        if ( $id ) {
            if ( ! \Kashida\BranchLocator\Models\Branch::update( $id, $data ) ) {
                wp_send_json_error( [ 'message' => __( 'The branch could not be updated. Please verify that it still exists and try again.', 'kashida-branch-locator' ) ] );
            }
            wp_send_json_success( [ 'message' => __( 'Branch updated.', 'kashida-branch-locator' ), 'id' => $id ] );
        }
        $new_id = \Kashida\BranchLocator\Models\Branch::insert( $data );
        if ( ! $new_id ) {
            wp_send_json_error( [ 'message' => __( 'The branch could not be created. Please try again.', 'kashida-branch-locator' ) ] );
        }
        wp_send_json_success( [ 'message' => __( 'Branch created.', 'kashida-branch-locator' ), 'id' => $new_id ] );
    }

    public function ajax_select_all_branches(): void {
        $this->verify_nonce();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verify_nonce() validates this AJAX request.
        $post = isset( $_POST ) && is_array( $_POST ) ? wp_unslash( $_POST ) : [];
        $search = sanitize_text_field( $post['search'] ?? '' );
        $city = sanitize_text_field( $post['city'] ?? '' );
        $group_id = absint( $post['group_id'] ?? 0 );
        $ids = [];
        $page = 1;
        do {
            $result = \Kashida\BranchLocator\Models\Branch::query( [
                'search' => $search,
                'city' => $city,
                'group_id' => $group_id,
                'page' => $page,
                'per_page' => 100,
            ] );
            foreach ( (array) ( $result['items'] ?? [] ) as $branch ) {
                $ids[] = (string) absint( $branch->id );
            }
            $total = (int) ( $result['total'] ?? 0 );
            $page++;
        } while ( ( $page - 1 ) * 100 < $total );

        wp_send_json_success( [ 'ids' => array_values( array_unique( $ids ) ), 'count' => count( $ids ) ] );
    }

    public function ajax_bulk_delete_branches(): void {
        $this->verify_nonce();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verify_nonce() validates the AJAX request before reading POST data.
        $post = isset( $_POST ) && is_array( $_POST ) ? wp_unslash( $_POST ) : [];
        $ids = isset( $post['ids'] ) && is_array( $post['ids'] ) ? array_values( array_filter( array_map( 'absint', $post['ids'] ) ) ) : [];
        if ( empty( $ids ) ) {
            wp_send_json_error( [ 'message' => __( 'Please select at least one branch.', 'kashida-branch-locator' ) ] );
        }
        $deleted = 0;
        foreach ( $ids as $id ) {
            if ( \Kashida\BranchLocator\Models\Branch::delete( $id ) ) $deleted++;
        }
        wp_send_json_success( [ 'message' => sprintf(
            /* translators: %d: number of branches deleted. */
            _n( '%d branch deleted.', '%d branches deleted.', $deleted, 'kashida-branch-locator' ),
            $deleted
        ), 'deleted' => $deleted ] );
    }

    public function ajax_bulk_delete_groups(): void {
        $this->verify_nonce();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verify_nonce() validates the AJAX request before reading POST data.
        $post = isset( $_POST ) && is_array( $_POST ) ? wp_unslash( $_POST ) : [];
        $ids = isset( $post['ids'] ) && is_array( $post['ids'] ) ? array_values( array_filter( array_map( 'absint', $post['ids'] ) ) ) : [];
        if ( empty( $ids ) ) {
            wp_send_json_error( [ 'message' => __( 'Please select at least one group.', 'kashida-branch-locator' ) ] );
        }
        $deleted = 0;
        foreach ( $ids as $id ) {
            if ( \Kashida\BranchLocator\Models\Group::delete( $id ) ) $deleted++;
        }
        wp_send_json_success( [ 'message' => sprintf(
            /* translators: %d: number of groups deleted. */
            _n( '%d group deleted.', '%d groups deleted.', $deleted, 'kashida-branch-locator' ),
            $deleted
        ), 'deleted' => $deleted ] );
    }

    public function ajax_delete_branch(): void {
        $this->verify_nonce();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verify_nonce() validates the AJAX request before reading POST data.
        $post = isset( $_POST ) && is_array( $_POST ) ? wp_unslash( $_POST ) : [];
        $id = isset( $post['id'] ) ? absint( $post['id'] ) : 0;
        if ( ! $id ) {
            wp_send_json_error( [ 'message' => __( 'Invalid ID.', 'kashida-branch-locator' ) ] );
        }
        if ( ! \Kashida\BranchLocator\Models\Branch::delete( $id ) ) {
            wp_send_json_error( [ 'message' => __( 'The branch could not be deleted. It may already have been removed.', 'kashida-branch-locator' ) ] );
        }
        wp_send_json_success( [ 'message' => __( 'Branch deleted.', 'kashida-branch-locator' ) ] );
    }

    public function ajax_save_group(): void {
        $this->verify_nonce();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verify_nonce() validates the AJAX request before reading POST data.
        $post = isset( $_POST ) && is_array( $_POST ) ? wp_unslash( $_POST ) : [];
        $id   = isset( $post['id'] ) ? absint( $post['id'] ) : 0;
        $data = [
            'name'  => sanitize_text_field( $post['name'] ?? '' ),
            'slug'  => sanitize_title( $post['slug'] ?? '' ),
            'color' => sanitize_hex_color( $post['color'] ?? '#0a6b70' ) ?: '#0a6b70',
        ];
        if ( empty( $data['name'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Name is required.', 'kashida-branch-locator' ) ] );
        }
        if ( $id ) {
            $updated = \Kashida\BranchLocator\Models\Group::update( $id, $data );
            if ( ! $updated ) {
                wp_send_json_error( [ 'message' => __( 'The group could not be updated. Please verify that it still exists and try again.', 'kashida-branch-locator' ) ] );
            }
            wp_send_json_success( [ 'message' => __( 'Group updated.', 'kashida-branch-locator' ), 'id' => $id ] );
        }
        $new_id = \Kashida\BranchLocator\Models\Group::insert( $data );
        if ( ! $new_id ) {
            wp_send_json_error( [ 'message' => __( 'The group could not be created. Please try again.', 'kashida-branch-locator' ) ] );
        }
        wp_send_json_success( [ 'message' => __( 'Group created.', 'kashida-branch-locator' ), 'id' => $new_id ] );
    }

    public function ajax_delete_group(): void {
        $this->verify_nonce();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verify_nonce() validates the AJAX request before reading POST data.
        $post = isset( $_POST ) && is_array( $_POST ) ? wp_unslash( $_POST ) : [];
        $id = isset( $post['id'] ) ? absint( $post['id'] ) : 0;
        if ( ! $id ) {
            wp_send_json_error( [ 'message' => __( 'Invalid ID.', 'kashida-branch-locator' ) ] );
        }
        if ( ! \Kashida\BranchLocator\Models\Group::delete( $id ) ) {
            wp_send_json_error( [ 'message' => __( 'The group could not be deleted. It may already have been removed.', 'kashida-branch-locator' ) ] );
        }
        wp_send_json_success( [ 'message' => __( 'Group deleted.', 'kashida-branch-locator' ) ] );
    }

    public function ajax_import_preview(): void {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'kashida_branch_locator_import_nonce' ) || ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Permission denied.', 'kashida-branch-locator' ) ], 403 );
        }
        $importer = new Importer();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- The nonce is verified immediately above.
        $post = isset( $_POST ) && is_array( $_POST ) ? wp_unslash( $_POST ) : [];
        $update_existing = ! empty( sanitize_text_field( $post['update_existing'] ?? '' ) );
        $result   = $importer->preview_from_upload( 'import_file', $update_existing );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }
        wp_send_json_success( $result );
    }

    public function ajax_import_run(): void {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'kashida_branch_locator_import_nonce' ) || ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Permission denied.', 'kashida-branch-locator' ) ], 403 );
        }
        $importer = new Importer();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- The nonce is verified immediately above.
        $post = isset( $_POST ) && is_array( $_POST ) ? wp_unslash( $_POST ) : [];
        $update_existing = ! empty( sanitize_text_field( $post['update_existing'] ?? '' ) );
        $result   = $importer->run_from_upload( 'import_file', $update_existing );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }
        wp_send_json_success( $result );
    }
}
