<?php
declare(strict_types=1);
namespace Kashida\BranchLocator\Admin;
if ( ! defined( 'ABSPATH' ) ) exit;

class Import_Page {
    public function __construct() { $this->render(); }
    private function render(): void {
        ?>
        <div class="wrap bl-admin-wrap">
            <h1><?php esc_html_e('Import Branches','kashida-branch-locator'); ?></h1>
            <p class="description"><?php esc_html_e('Upload an Excel (.xlsx) or CSV file. Columns: Name, City, Address, Phone, Lat, Lng, Group. Location (lat,lng) is also supported.','kashida-branch-locator'); ?></p>
            <div class="bl-import-guide"><strong><?php esc_html_e('Prepare your file','kashida-branch-locator'); ?></strong><span><?php esc_html_e('Use the template as a reference. It contains column instructions only and no sample locations.','kashida-branch-locator'); ?></span><a class="button" href="<?php echo esc_url( KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'samples/kashida-branch-locator-import-template.xlsx' ); ?>"><?php esc_html_e('Download Import Template','kashida-branch-locator'); ?></a></div>

            <div class="bl-import-container bl-import-container-full">
                <div class="bl-import-upload-box" id="bl-drop-zone">
                    <div class="bl-drop-icon">📂</div>
                    <p><?php esc_html_e('Drag & drop your file here, or click to browse','kashida-branch-locator'); ?></p>
                    <input type="file" id="bl-import-file" accept=".xlsx,.csv,.xls" style="display:none">
                    <button class="button button-primary" id="bl-browse-btn"><?php esc_html_e('Browse File','kashida-branch-locator'); ?></button>
                    <p class="bl-accepted"><?php esc_html_e('Accepted formats: .xlsx, .csv (legacy .xls requires PhpSpreadsheet)','kashida-branch-locator'); ?></p>
                </div>

                <div id="bl-import-options" style="display:none">
                    <label class="bl-checkbox-label">
                        <input type="checkbox" id="bl-update-existing" checked>
                        <?php esc_html_e('Update existing branches (matched by name)','kashida-branch-locator'); ?>
                    </label>
                    <button class="button button-primary" id="bl-preview-btn"><?php esc_html_e('Preview Import','kashida-branch-locator'); ?></button>
                </div>

                <div id="bl-preview-area" style="display:none">
                    <h3><?php esc_html_e('Preview','kashida-branch-locator'); ?> <span id="bl-preview-count"></span></h3>
                    <div id="bl-preview-table-wrap"></div>
                    <div class="bl-import-actions">
                        <button class="button button-primary" id="bl-run-import"><?php esc_html_e('Run Import','kashida-branch-locator'); ?></button>
                        <button class="button" id="bl-cancel-import"><?php esc_html_e('Cancel','kashida-branch-locator'); ?></button>
                    </div>
                </div>

                <div id="bl-import-result" style="display:none" class="bl-result-box"></div>
                <button type="button" class="button bl-download-report" id="bl-download-report" style="display:none"><?php esc_html_e('Download Error Report','kashida-branch-locator'); ?></button>
            </div>
            <section class="bl-import-requirements" aria-labelledby="bl-import-requirements-title">
                <h2 id="bl-import-requirements-title"><?php esc_html_e( 'File Requirements', 'kashida-branch-locator' ); ?></h2>
                <div class="bl-import-requirements-grid">
                    <div class="bl-import-requirement"><span class="dashicons dashicons-media-spreadsheet" aria-hidden="true"></span><div><h3><?php esc_html_e( 'Supported Formats', 'kashida-branch-locator' ); ?></h3><p><?php esc_html_e( 'Excel (.xlsx) or CSV (.csv).', 'kashida-branch-locator' ); ?></p><small><?php esc_html_e( 'Legacy .xls files require PhpSpreadsheet.', 'kashida-branch-locator' ); ?></small></div></div>
                    <div class="bl-import-requirement"><span class="dashicons dashicons-list-view" aria-hidden="true"></span><div><h3><?php esc_html_e( 'Supported Columns', 'kashida-branch-locator' ); ?></h3><p><?php esc_html_e( 'Name, City, Address, Phone, Lat, Lng, Group, and Location (lat,lng).', 'kashida-branch-locator' ); ?></p></div></div>
                    <div class="bl-import-requirement"><span class="dashicons dashicons-lightbulb" aria-hidden="true"></span><div><h3><?php esc_html_e( 'Tip', 'kashida-branch-locator' ); ?></h3><p><?php esc_html_e( 'Download the import template for column guidance before preparing your file.', 'kashida-branch-locator' ); ?></p></div></div>
                </div>
            </section>
        </div>
        <?php Footer::render(); ?>
        <?php
    }
}
