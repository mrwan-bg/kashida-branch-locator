<?php
declare(strict_types=1);
namespace Kashida\BranchLocator\Import;
use Kashida\BranchLocator\Models\Branch;
use Kashida\BranchLocator\Models\Group;
if ( ! defined( 'ABSPATH' ) ) exit;

class Importer {

    const COL_NAME     = 'Name';
    const COL_CITY     = 'City';
    const COL_ADDRESS  = 'Add';
    const COL_PHONE    = 'Tel';
    const COL_LOCATION = 'Location';
    const COL_GROUP    = 'Group';

    private array $errors   = [];
    private array $warnings = [];
    private int   $inserted = 0;
    private int   $updated  = 0;
    private int   $skipped  = 0;

    public function preview_from_upload( string $field, bool $update_existing = true ): array|\WP_Error {
        $rows = $this->parse_uploaded_file( $field );
        if ( is_wp_error( $rows ) ) return $rows;
        if ( empty( $rows ) ) {
            return new \WP_Error( 'kashida_branch_locator_empty_file', __( 'No branch records were found below the header row. Add at least one branch and upload the file again.', 'kashida-branch-locator' ) );
        }
        $header_error = $this->validate_headers( $rows );
        if ( is_wp_error( $header_error ) ) return $header_error;
        return [ 'rows' => $this->preview( $rows ), 'total' => count( $rows ) ];
    }

    public function run_from_upload( string $field, bool $update_existing = true ): array|\WP_Error {
        global $wpdb;

        // Fail clearly if the plugin tables are missing instead of reporting a false successful import.
        $groups_table   = $wpdb->prefix . 'kashida_branch_locator_groups';
        $branches_table = $wpdb->prefix . 'kashida_branch_locator_branches';
        foreach ( [ $groups_table, $branches_table ] as $table ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Required schema check for the plugin's custom tables.
            $found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
            if ( $found !== $table ) {
                return new \WP_Error(
                    'kashida_branch_locator_missing_table',
                    __( 'Import stopped because the plugin database tables are missing. Deactivate and reactivate Kashida Branch Locator, then try again. If the issue continues, contact support before deleting any data.', 'kashida-branch-locator' )
                );
            }
        }

        $rows = $this->parse_uploaded_file( $field );
        if ( is_wp_error( $rows ) ) return $rows;
        if ( empty( $rows ) ) {
            return new \WP_Error(
                'kashida_branch_locator_empty_file',
                __( 'No branch records were found below the header row. Add at least one branch and upload the file again.', 'kashida-branch-locator' )
            );
        }
        $header_error = $this->validate_headers( $rows );
        if ( is_wp_error( $header_error ) ) return $header_error;
        return $this->import( $rows, $update_existing );
    }

    private function parse_uploaded_file( string $field ): array|\WP_Error {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The caller verifies the upload nonce; file metadata is validated below with is_uploaded_file(), size, and wp_check_filetype_and_ext().
        if ( empty( $_FILES[ $field ] ) || ! isset( $_FILES[ $field ]['tmp_name'] ) ) {
            return new \WP_Error('kashida_branch_locator_no_file', __('Please select a file.','kashida-branch-locator'));
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The caller verifies the upload nonce; file metadata is validated below.
        $file = $_FILES[ $field ];
        if ( !empty($file['error']) ) return new \WP_Error('kashida_branch_locator_upload_error', __('The uploaded file could not be processed.','kashida-branch-locator'));
        if ( !is_uploaded_file($file['tmp_name']) ) return new \WP_Error('kashida_branch_locator_invalid_upload', __('Invalid upload.','kashida-branch-locator'));
        $max = min( 10 * 1024 * 1024, (int)wp_max_upload_size() );
        if ( !empty($file['size']) && (int)$file['size'] > $max ) return new \WP_Error('kashida_branch_locator_file_too_large', __('The file is too large. Maximum size is 10 MB.','kashida-branch-locator'));
        $name = sanitize_file_name( wp_unslash( $file['name'] ?? '' ) );
        $check = wp_check_filetype_and_ext( $file['tmp_name'], $name );
        if ( empty( $check['ext'] ) || ! in_array( strtolower( (string) $check['ext'] ), [ 'csv', 'xlsx', 'xls' ], true ) ) {
            return new \WP_Error( 'kashida_branch_locator_invalid_type', __( 'The uploaded file type could not be verified. Please use CSV or XLSX.', 'kashida-branch-locator' ) );
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ( !in_array($ext, ['csv','xlsx','xls'], true) ) return new \WP_Error('kashida_branch_locator_invalid_type', __('Only CSV and Excel files are supported.','kashida-branch-locator'));
        if ( $ext === 'xls' && !class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory') ) return new \WP_Error('kashida_branch_locator_xls_dependency', __('Legacy XLS files require PhpSpreadsheet on the server. Please use CSV or XLSX.','kashida-branch-locator'));
        try { return Parser::parse($file['tmp_name'], $ext); } catch (\Throwable $e) { return new \WP_Error('kashida_branch_locator_parse_error', $e->getMessage()); }
    }

    /**
     * Confirm that the parsed file contains a recognized branch-name column before preview/import.
     *
     * @param array $rows Parsed records keyed by spreadsheet column header.
     * @return true|\WP_Error
     */
    private function validate_headers( array $rows ): true|\WP_Error {
        if ( empty( $rows[0] ) || ! is_array( $rows[0] ) ) {
            return new \WP_Error( 'kashida_branch_locator_invalid_headers', __( 'The file does not contain a valid header row. Use the Kashida Branch Locator import template.', 'kashida-branch-locator' ) );
        }

        $normalize = static function ( string $value ): string {
            $value = preg_replace( '/^\xEF\xBB\xBF/', '', $value );
            $value = strtolower( trim( $value ) );
            return preg_replace( '/[\s_-]+/u', '', $value ) ?? $value;
        };
        $recognized = [ 'name', 'branchname', 'اسم الفرع', 'الاسم' ];
        foreach ( array_keys( $rows[0] ) as $header ) {
            if ( in_array( $normalize( (string) $header ), $recognized, true ) ) {
                return true;
            }
        }

        return new \WP_Error(
            'kashida_branch_locator_missing_name_column',
            __( 'The required Name column was not found. Keep the template headers, including Name, or use the supported Arabic name header.', 'kashida-branch-locator' )
        );
    }

    public function preview( array $rows ): array {
        $result = [];
        foreach ( $rows as $i => $row ) {
            $r = $this->map_row( $row, $i + 2 );
            $r['_row']    = $i + 2;
            $r['_status'] = ! empty($r['_errors']) ? 'error' : ( ! empty($r['_warnings']) ? 'warning' : 'ok' );
            $result[] = $r;
        }
        return $result;
    }

    public function import( array $rows, bool $update_existing = true ): array {
        // Importer instances can be reused by integrations; never leak counters or messages across runs.
        $this->errors = [];
        $this->warnings = [];
        $this->inserted = 0;
        $this->updated = 0;
        $this->skipped = 0;

        foreach ( $rows as $i => $row ) {
            $data = $this->map_row( $row, $i + 2 );
            if ( ! empty($data['_errors']) ) { $this->skipped++; continue; }
            unset( $data['_errors'], $data['_warnings'] );

            // Resolve group and report failures instead of silently assigning an invalid group ID.
            if ( ! empty( $data['_group_name'] ) ) {
                $group_name = $data['_group_name'];
                $group_id   = Group::find_or_create( $group_name );
                if ( $group_id < 1 ) {
                    global $wpdb;
                    $this->errors[] = sprintf(
                        /* translators: %1$d: row number; %2$s: group name; %3$s: database error. */
                        __( 'Row %1$d: Could not create or find group "%2$s". %3$s', 'kashida-branch-locator' ),
                        $i + 2,
                        $group_name,
                        $wpdb->last_error ? $wpdb->last_error : __( 'Please check the database tables and permissions.', 'kashida-branch-locator' )
                    );
                    $this->skipped++;
                    continue;
                }
                $data['group_id'] = $group_id;
            }
            unset( $data['_group_name'] );

            // Check duplicate. The model treats a no-change update as successful.
            $existing = Branch::exists_by_name( $data['name'] );
            if ( $existing ) {
                if ( $update_existing ) {
                    global $wpdb;
                    $wpdb->last_error = '';
                    if ( Branch::update( (int) $existing->id, $data ) ) {
                        $this->updated++;
                    } else {
                        $this->errors[] = sprintf(
                            /* translators: %1$d: row number; %2$s: branch name; %3$s: database error. */
                            __( 'Row %1$d: Could not update branch "%2$s". %3$s', 'kashida-branch-locator' ),
                            $i + 2,
                            $data['name'],
                            $wpdb->last_error ? $wpdb->last_error : __( 'The database update did not complete.', 'kashida-branch-locator' )
                        );
                        $this->skipped++;
                    }
                } else {
                    $this->skipped++;
                }
            } else {
                global $wpdb;
                $wpdb->last_error = '';
                $inserted_id = Branch::insert( $data );
                if ( $inserted_id ) {
                    $this->inserted++;
                } else {
                    $this->errors[] = sprintf(
                        /* translators: %1$d: row number; %2$s: branch name; %3$s: database error. */
                        __( 'Row %1$d: Could not save branch "%2$s". %3$s', 'kashida-branch-locator' ),
                        $i + 2,
                        $data['name'],
                        $wpdb->last_error ? $wpdb->last_error : __( 'The database did not insert the record.', 'kashida-branch-locator' )
                    );
                    $this->skipped++;
                }
            }
        }
        return $this->get_result();
    }

    public function get_result(): array {
        return [
            'inserted' => $this->inserted,
            'updated'  => $this->updated,
            'skipped'  => $this->skipped,
            'errors'   => $this->errors,
            'warnings' => $this->warnings,
        ];
    }

    private function map_row( array $row, int $line ): array {
        $errors = []; $warnings = [];

        // Accept the documented English columns as well as common aliases and Arabic headers.
        $name = trim( $this->column_value( $row, ['Name', 'name', 'Branch Name', 'اسم الفرع', 'الاسم'] ) );
        $city = trim( $this->column_value( $row, ['City', 'city', 'المدينة'] ) );
        $addr = trim( $this->column_value( $row, ['Address', 'Add', 'address', 'العنوان'] ) );
        $tel  = trim( $this->column_value( $row, ['Phone', 'Tel', 'phone', 'الهاتف', 'رقم الهاتف'] ) );
        $loc  = trim( $this->column_value( $row, ['Location', 'location', 'Coordinates', 'الإحداثيات'] ) );
        $lat_raw = trim( $this->column_value( $row, ['Lat', 'Latitude', 'latitude', 'خط العرض'] ) );
        $lng_raw = trim( $this->column_value( $row, ['Lng', 'Long', 'Longitude', 'longitude', 'خط الطول'] ) );
        $grp  = trim( $this->column_value( $row, ['Group', 'group', 'المجموعة'] ) );

        if ( empty($name) ) {
            $errors[] = sprintf(
                /* translators: %d: import row number. */
                __('Row %d: Name is required.','kashida-branch-locator'),
                $line
            );
        }

        $lat = 0.0; $lng = 0.0;

        // Prefer separate Lat/Lng columns when supplied. Otherwise accept Location as "lat,lng".
        if ( $lat_raw !== '' || $lng_raw !== '' ) {
            if ( is_numeric($lat_raw) && is_numeric($lng_raw) ) {
                $lat_v = (float)$lat_raw; $lng_v = (float)$lng_raw;
                if ( $lat_v >= -90 && $lat_v <= 90 && $lng_v >= -180 && $lng_v <= 180 ) {
                    $lat = $lat_v; $lng = $lng_v;
                } else {
                    $warnings[] = sprintf(
                        /* translators: %d: import row number. */
                        __('Row %d: Coordinates are out of range. This branch will not appear on the map until valid coordinates are provided.','kashida-branch-locator'),
                        $line
                    );
                }
            } else {
                $warnings[] = sprintf(
                    /* translators: %d: import row number. */
                    __('Row %d: Latitude and longitude must be numeric. This branch will not appear on the map until valid coordinates are provided.','kashida-branch-locator'),
                    $line
                );
            }
        } elseif ( ! empty($loc) ) {
            $parts = array_map('trim', explode(',', $loc, 2));
            if ( count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1]) ) {
                $lat_v = (float)$parts[0]; $lng_v = (float)$parts[1];
                if ( $lat_v >= -90 && $lat_v <= 90 && $lng_v >= -180 && $lng_v <= 180 ) {
                    $lat = $lat_v; $lng = $lng_v;
                } else {
                    $warnings[] = sprintf(
                        /* translators: %d: import row number. */
                        __('Row %d: Coordinates are out of range. This branch will not appear on the map until valid coordinates are provided.','kashida-branch-locator'),
                        $line
                    );
                }
            } else {
                $warnings[] = sprintf(
                    /* translators: %1$d: import row number; %2$s: invalid location value. */
                    __('Row %1$d: Invalid Location format "%2$s". Use latitude,longitude or separate Lat and Lng columns.','kashida-branch-locator'),
                    $line,
                    $loc
                );
            }
        }

        if ( $warnings ) $this->warnings = array_merge( $this->warnings, $warnings );
        if ( $errors )   $this->errors   = array_merge( $this->errors, $errors );

        return [
            'name'        => sanitize_text_field( $name ),
            'city'        => sanitize_text_field( $city ),
            'address'     => sanitize_textarea_field( $addr ),
            'phone'       => sanitize_text_field( $tel ),
            'latitude'    => $lat,
            'longitude'   => $lng,
            '_group_name' => $grp,
            '_errors'     => $errors,
            '_warnings'   => $warnings,
        ];
    }

    private function column_value( array $row, array $keys ): string {
        foreach ( $keys as $key ) {
            if ( array_key_exists( $key, $row ) && $row[ $key ] !== null ) {
                return trim( (string) $row[ $key ] );
            }
        }

        // Match headers case-insensitively and tolerate BOMs, extra spaces, and underscores.
        $normalize = static function ( string $value ): string {
            $value = preg_replace( '/^\xEF\xBB\xBF/', '', $value );
            $value = strtolower( trim( (string) $value ) );
            return preg_replace( '/[\s_-]+/u', '', $value ) ?? $value;
        };
        $wanted = array_map( $normalize, $keys );
        foreach ( $row as $header => $value ) {
            if ( $value !== null && in_array( $normalize( (string) $header ), $wanted, true ) ) {
                return trim( (string) $value );
            }
        }
        return '';
    }

}
