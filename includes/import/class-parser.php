<?php
declare(strict_types=1);
namespace Kashida\BranchLocator\Import;
if ( ! defined( 'ABSPATH' ) ) exit;

class Parser {

    public static function parse( string $file_path, string $ext ): array {
        $ext = strtolower( $ext );
        if ( $ext === 'csv' ) return self::parse_csv( $file_path );
        if ( $ext === 'xlsx' ) return self::parse_xlsx( $file_path );
        if ( $ext === 'xls' ) {
            if ( class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory') ) return self::parse_with_phpspreadsheet($file_path);
            throw new \RuntimeException( esc_html__( 'Legacy XLS requires PhpSpreadsheet. Please use XLSX or CSV.', 'kashida-branch-locator' ) );
        }
        throw new \RuntimeException( esc_html__( 'Unsupported file type.', 'kashida-branch-locator' ) );
    }

    /** Read an uploaded file through the WordPress filesystem abstraction. */
    private static function get_file_contents( string $path ): string {
        global $wp_filesystem;

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        if ( ! $wp_filesystem && ! WP_Filesystem() ) {
            throw new \RuntimeException( esc_html__( 'WordPress could not initialize its filesystem API to read the uploaded file.', 'kashida-branch-locator' ) );
        }

        if ( ! $wp_filesystem || ! $wp_filesystem->exists( $path ) ) {
            throw new \RuntimeException( esc_html__( 'The uploaded file could not be found by the WordPress filesystem API.', 'kashida-branch-locator' ) );
        }

        $contents = $wp_filesystem->get_contents( $path );
        if ( false === $contents ) {
            throw new \RuntimeException( esc_html__( 'WordPress could not read the uploaded file.', 'kashida-branch-locator' ) );
        }

        return (string) $contents;
    }

    private static function parse_csv( string $path ): array {
        $contents = self::get_file_contents( $path );
        if ( '' === $contents ) {
            return [];
        }
        $contents = preg_replace( '/^\xEF\xBB\xBF/', '', $contents );

        // Detect the most likely delimiter from the first physical line.
        $first_line_end = strcspn( $contents, "\r\n" );
        $sample = substr( $contents, 0, $first_line_end );
        $delimiter = ',';
        $highest_count = -1;
        foreach ( [ ',', ';', "\t" ] as $candidate ) {
            $count = count( str_getcsv( $sample, $candidate, '"', '\\' ) );
            if ( $count > $highest_count ) {
                $highest_count = $count;
                $delimiter = $candidate;
            }
        }

        $records = self::decode_csv_records( $contents, $delimiter );
        if ( empty( $records ) ) {
            throw new \RuntimeException( esc_html__( 'The CSV file must contain column headers in the first row.', 'kashida-branch-locator' ) );
        }

        $header = array_map( static function ( $value ): string {
            return trim( (string) $value );
        }, array_shift( $records ) );

        if ( ! array_filter( $header, 'strlen' ) ) {
            throw new \RuntimeException( esc_html__( 'The CSV file must contain column headers in the first row.', 'kashida-branch-locator' ) );
        }

        $header_count = count( $header );
        $rows = [];
        foreach ( $records as $row ) {
            $has_value = false;
            foreach ( $row as $value ) {
                if ( trim( (string) $value ) !== '' ) {
                    $has_value = true;
                    break;
                }
            }
            if ( ! $has_value ) {
                continue;
            }

            if ( count( $row ) < $header_count ) {
                $row = array_pad( $row, $header_count, '' );
            } elseif ( count( $row ) > $header_count ) {
                $row = array_slice( $row, 0, $header_count );
            }

            $assoc = array_combine( $header, $row );
            if ( false !== $assoc ) {
                $rows[] = $assoc;
            }
        }

        return $rows;
    }

    /** Parse CSV text while supporting quoted delimiters and multiline fields. */
    private static function decode_csv_records( string $contents, string $delimiter ): array {
        $records = [];
        $record = [];
        $field = '';
        $in_quotes = false;
        $length = strlen( $contents );

        for ( $i = 0; $i < $length; $i++ ) {
            $char = $contents[ $i ];

            if ( '"' === $char ) {
                if ( $in_quotes && $i + 1 < $length && '"' === $contents[ $i + 1 ] ) {
                    $field .= '"';
                    $i++;
                } else {
                    $in_quotes = ! $in_quotes;
                }
                continue;
            }

            if ( ! $in_quotes && $delimiter === $char ) {
                $record[] = $field;
                $field = '';
                continue;
            }

            if ( ! $in_quotes && ( "\n" === $char || "\r" === $char ) ) {
                if ( "\r" === $char && $i + 1 < $length && "\n" === $contents[ $i + 1 ] ) {
                    $i++;
                }
                $record[] = $field;
                $field = '';
                if ( count( $record ) > 1 || ( isset( $record[0] ) && '' !== $record[0] ) ) {
                    $records[] = $record;
                }
                $record = [];
                continue;
            }

            $field .= $char;
        }

        if ( '' !== $field || ! empty( $record ) ) {
            $record[] = $field;
            $records[] = $record;
        }

        return $records;
    }

    private static function parse_xlsx( string $path ): array {
        // Use PhpSpreadsheet if available, otherwise fallback to ZIP/XML reader
        if ( class_exists( '\PhpOffice\PhpSpreadsheet\IOFactory' ) ) {
            return self::parse_with_phpspreadsheet( $path );
        }
        return self::parse_xlsx_native( $path );
    }

    private static function parse_with_phpspreadsheet( string $path ): array {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load( $path );
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray( null, true, true, false );
        if ( empty($rows) ) return [];
        $header = array_map( static function ( $value ): string {
            $value = preg_replace( '/^\xEF\xBB\xBF/', '', (string) $value );
            return trim( (string) $value );
        }, array_shift( $rows ) );
        $header_count = count( $header );
        if ( 0 === $header_count || count( array_filter( $header, 'strlen' ) ) === 0 ) {
            throw new \RuntimeException( esc_html__( 'The first worksheet must contain column headers in the first row.', 'kashida-branch-locator' ) );
        }
        $result = [];
        foreach ( $rows as $row ) {
            if ( count( $row ) < $header_count ) {
                $row = array_pad( $row, $header_count, '' );
            } elseif ( count( $row ) > $header_count ) {
                $row = array_slice( $row, 0, $header_count );
            }
            $has_value = false;
            foreach ( $row as $value ) {
                if ( trim( (string) $value ) !== '' ) { $has_value = true; break; }
            }
            if ( ! $has_value ) { continue; }
            $assoc = array_combine( $header, $row );
            if ( false !== $assoc ) { $result[] = $assoc; }
        }
        return $result;
    }

    /**
     * Native minimal XLSX reader (no dependencies).
     * Reads the first sheet of a standard XLSX file.
     */
    private static function parse_xlsx_native( string $path ): array {
        if ( ! class_exists( \ZipArchive::class ) || ! class_exists( \DOMDocument::class ) ) {
            throw new \RuntimeException( esc_html__( 'This server is missing the PHP ZIP or DOM extension required to read XLSX files. Enable these extensions or import a CSV file instead.', 'kashida-branch-locator' ) );
        }
        $zip = new \ZipArchive();
        if ( $zip->open($path) !== true ) {
            throw new \RuntimeException( esc_html__( 'Cannot open XLSX file.', 'kashida-branch-locator' ) );
        }

        // Shared strings are optional. Some XLSX writers use inline strings instead.
        $strings = [];
        $ss_xml = $zip->getFromName('xl/sharedStrings.xml');
        if ( $ss_xml ) {
            $ss_dom = new \DOMDocument();
            if ( ! @$ss_dom->loadXML( $ss_xml, LIBXML_NONET | LIBXML_COMPACT ) ) {
                $zip->close();
                throw new \RuntimeException( esc_html__( 'Cannot read shared strings.', 'kashida-branch-locator' ) );
            }
            foreach ( $ss_dom->getElementsByTagName('si') as $si ) {
                $parts = [];
                foreach ( $si->getElementsByTagName('t') as $t ) $parts[] = $t->nodeValue;
                $strings[] = implode('', $parts);
            }
        }

        // Resolve the first worksheet through workbook relationships when possible.
        $sheet_name = 'xl/worksheets/sheet1.xml';
        $workbook_xml = $zip->getFromName('xl/workbook.xml');
        $rels_xml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ( $workbook_xml && $rels_xml ) {
            $wb = new \DOMDocument();
            $rels = new \DOMDocument();
            if ( @$wb->loadXML($workbook_xml, LIBXML_NONET | LIBXML_COMPACT) && @$rels->loadXML($rels_xml, LIBXML_NONET | LIBXML_COMPACT) ) {
                $first_sheet = $wb->getElementsByTagName('sheet')->item(0);
                if ( $first_sheet ) {
                    $rid = $first_sheet->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');
                    if ( $rid ) {
                        foreach ( $rels->getElementsByTagName('Relationship') as $rel ) {
                            if ( $rel->getAttribute('Id') === $rid ) {
                                $target = ltrim(str_replace('\\', '/', $rel->getAttribute('Target')), '/');
                                $sheet_name = 0 === strpos( $target, 'xl/' ) ? $target : 'xl/' . $target;
                                break;
                            }
                        }
                    }
                }
            }
        }

        $sheet_xml = $zip->getFromName($sheet_name);
        $zip->close();
        if ( ! $sheet_xml ) {
            throw new \RuntimeException( esc_html__( 'Cannot read sheet data.', 'kashida-branch-locator' ) );
        }

        $dom = new \DOMDocument();
        if ( ! @$dom->loadXML( $sheet_xml, LIBXML_NONET | LIBXML_COMPACT ) ) {
            throw new \RuntimeException( esc_html__( 'Cannot parse worksheet data.', 'kashida-branch-locator' ) );
        }

        $rows_data = [];
        foreach ( $dom->getElementsByTagName('row') as $row_node ) {
            $cells = [];
            $max_index = -1;
            foreach ( $row_node->getElementsByTagName('c') as $cell ) {
                $ref = $cell->getAttribute('r');
                if ( ! preg_match('/([A-Z]+)\\d+$/i', $ref, $m) ) continue;
                $letters = strtoupper($m[1]);
                $index = 0;
                for ( $i = 0, $len = strlen($letters); $i < $len; $i++ ) {
                    $index = $index * 26 + (ord($letters[$i]) - 64);
                }
                $index--;
                $type = $cell->getAttribute('t');
                $value = '';

                if ( $type === 'inlineStr' ) {
                    $parts = [];
                    foreach ( $cell->getElementsByTagName('t') as $t ) $parts[] = $t->nodeValue;
                    $value = implode('', $parts);
                } else {
                    $v = $cell->getElementsByTagName('v')->item(0);
                    $raw = $v ? $v->nodeValue : '';
                    if ( $type === 's' ) $value = $strings[(int)$raw] ?? '';
                    elseif ( $type === 'b' ) $value = ((string)$raw === '1') ? '1' : '0';
                    else $value = $raw;
                }
                $cells[$index] = $value;
                $max_index = max($max_index, $index);
            }
            $row = [];
            for ( $i = 0; $i <= $max_index; $i++ ) $row[] = $cells[$i] ?? '';
            $rows_data[] = $row;
        }

        if ( empty($rows_data) ) return [];
        $header = array_map( static fn($v) => trim((string)$v), array_shift($rows_data) );
        $header_count = count($header);
        $result = [];
        foreach ( $rows_data as $row ) {
            if ( count($row) < $header_count ) $row = array_pad($row, $header_count, '');
            if ( count($row) > $header_count ) $row = array_slice($row, 0, $header_count);
            $assoc = array_combine($header, $row);
            if ( $assoc === false ) continue;
            // Ignore completely empty spreadsheet rows.
            $has_value = false;
            foreach ( $assoc as $value ) {
                if ( trim((string)$value) !== '' ) { $has_value = true; break; }
            }
            if ( $has_value ) $result[] = $assoc;
        }
        return $result;
    }

}
