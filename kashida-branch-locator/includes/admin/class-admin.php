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
            25
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

        wp_enqueue_style( 'kashida-branch-locator-admin', KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'css/admin.css', [], KASHIDA_BRANCH_LOCATOR_VERSION );
        wp_enqueue_script( 'kashida-branch-locator-admin', KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'js/admin.js', [ 'jquery' ], KASHIDA_BRANCH_LOCATOR_VERSION, true );
        wp_localize_script( 'kashida-branch-locator-admin', 'KashidaBranchLocatorAdmin', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'kashida_branch_locator_admin_nonce' ),
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
                'clear_selection' => __( 'Clear Selection', 'kashida-branch-locator' ),
                'select_all_results' => __( 'Select all matching results', 'kashida-branch-locator' ),
                'selecting_results' => __( 'Selecting matching branches…', 'kashida-branch-locator' ),
                'selected_all_results' => __( 'All matching branches have been selected.', 'kashida-branch-locator' ),
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

    public function page_branches(): void { new Branches_Page(); }
    public function page_groups(): void { new Groups_Page(); }
    public function page_import(): void { new Import_Page(); }
    public function page_help(): void { new Help_Page(); }

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
        $data = [
            'name'      => sanitize_text_field( $post['name'] ?? '' ),
            'city'      => sanitize_text_field( $post['city'] ?? '' ),
            'address'   => sanitize_textarea_field( $post['address'] ?? '' ),
            'phone'     => sanitize_text_field( $post['phone'] ?? '' ),
            'latitude'  => (float) ( $post['latitude'] ?? 0 ),
            'longitude' => (float) ( $post['longitude'] ?? 0 ),
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
            \Kashida\BranchLocator\Models\Branch::update( $id, $data );
            wp_send_json_success( [ 'message' => __( 'Branch updated.', 'kashida-branch-locator' ), 'id' => $id ] );
        }
        $new_id = \Kashida\BranchLocator\Models\Branch::insert( $data );
        wp_send_json_success( [ 'message' => __( 'Branch created.', 'kashida-branch-locator' ), 'id' => $new_id ] );
    }

    public function ajax_select_all_branches(): void {
        $this->verify_nonce();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verify_nonce() validates this AJAX request before reading POST data.
        $post = isset( $_POST ) && is_array( $_POST ) ? wp_unslash( $_POST ) : [];
        $search = sanitize_text_field( $post['search'] ?? '' );
        $city = sanitize_text_field( $post['city'] ?? '' );
        $group_id = absint( $post['group_id'] ?? 0 );
        $selected = [];
        $page = 1;
        $per_page = 100;
        do {
            $result = \\Kashida\\BranchLocator\\Models\\Branch::query( [
                'search' => $search,
                'city' => $city,
                'group_id' => $group_id,
                'page' => $page,
                'per_page' => $per_page,
            ] );
            foreach ( (array) ( $result['items'] ?? [] ) as $branch ) {
                $selected[] = [
                    'id' => (string) absint( $branch->id ),
                    'slug' => sanitize_text_field( (string) $branch->slug ),
                ];
            }
            $total = (int) ( $result['total'] ?? 0 );
            $page++;
        } while ( ( $page - 1 ) * $per_page < $total );

        wp_send_json_success( [
            'items' => $selected,
            'count' => count( $selected ),
        ] );
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
        \Kashida\BranchLocator\Models\Branch::delete( $id );
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
            \Kashida\BranchLocator\Models\Group::update( $id, $data );
            wp_send_json_success( [ 'message' => __( 'Group updated.', 'kashida-branch-locator' ), 'id' => $id ] );
        }
        $new_id = \Kashida\BranchLocator\Models\Group::insert( $data );
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
        \Kashida\BranchLocator\Models\Group::delete( $id );
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
