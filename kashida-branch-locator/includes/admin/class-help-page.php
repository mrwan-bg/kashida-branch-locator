<?php
declare(strict_types=1);
namespace Kashida\BranchLocator\Admin;
if ( ! defined( 'ABSPATH' ) ) exit;

class Help_Page {
    public function __construct() { $this->render(); }

    private function render(): void {
        $layouts = [
            ['icon'=>'▦','title'=>__('Table','kashida-branch-locator'),'desc'=>__('A clean tabular view for structured branch data.','kashida-branch-locator')],
            ['icon'=>'▤','title'=>__('Cards','kashida-branch-locator'),'desc'=>__('Modern responsive cards with optional actions.','kashida-branch-locator')],
            ['icon'=>'☷','title'=>__('List','kashida-branch-locator'),'desc'=>__('A compact list for simple directories.','kashida-branch-locator')],
            ['icon'=>'⊞','title'=>__('Accordion','kashida-branch-locator'),'desc'=>__('Expandable branch details that save space.','kashida-branch-locator')],
            ['icon'=>'⌖','title'=>__('Map','kashida-branch-locator'),'desc'=>__('Interactive map with clickable branch markers.','kashida-branch-locator')],
            ['icon'=>'⌖','title'=>__('Map + Table','kashida-branch-locator'),'desc'=>__('Interactive map linked to the branch table.','kashida-branch-locator')],
        ];
        $shortcodes = [
            ['label'=>__('All branches','kashida-branch-locator'),'code'=>'[kashida_branch_locator]'],
            ['label'=>__('Specific group','kashida-branch-locator'),'code'=>'[kashida_branch_locator group="grp_xxxxxxxx"]'],
            ['label'=>__('Map + table','kashida-branch-locator'),'code'=>'[kashida_branch_locator group="grp_xxxxxxxx" layout="map-table" search="true" pagination="true"]'],
            ['label'=>__('Specific city','kashida-branch-locator'),'code'=>'[kashida_branch_locator city="Tripoli" layout="cards"]'],
            ['label'=>__('Specific branch','kashida-branch-locator'),'code'=>'[kashida_branch_locator branch="br_xxxxxxxx"]'],
        ];
        ?>
        <div class="wrap bl-admin-wrap bl-help-wrap">
            <div class="bl-help-hero">
                <div class="bl-help-hero-main">
                    <div class="bl-help-brand-mark"><img src="<?php echo esc_url( KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'images/kashida-mark.svg' ); ?>" width="54" height="54" alt="Kashida Media Services"></div>
                    <div>
                        <span class="bl-help-eyebrow">Kashida Media Services</span>
                        <h1><?php esc_html_e('Kashida Branch Locator','kashida-branch-locator'); ?></h1>
                        <p><?php esc_html_e('A simple way to manage branches, groups, maps, and responsive location directories.','kashida-branch-locator'); ?></p>
                    </div>
                </div>
                <a class="button button-primary" href="<?php echo esc_url( admin_url('admin.php?page=kashida-branch-locator-groups') ); ?>"><?php esc_html_e('Open Groups','kashida-branch-locator'); ?></a>
            </div>

            <div class="bl-help-grid bl-help-grid-3">
                <div class="bl-help-card"><span class="bl-help-card-icon">01</span><h2><?php esc_html_e('Add your data','kashida-branch-locator'); ?></h2><p><?php esc_html_e('Create groups and branches manually, or import them from XLSX or CSV.','kashida-branch-locator'); ?></p></div>
                <div class="bl-help-card"><span class="bl-help-card-icon">02</span><h2><?php esc_html_e('Build the display','kashida-branch-locator'); ?></h2><p><?php esc_html_e('Open Display from a group, choose the layout and fields, then generate the shortcode.','kashida-branch-locator'); ?></p></div>
                <div class="bl-help-card"><span class="bl-help-card-icon">03</span><h2><?php esc_html_e('Paste and publish','kashida-branch-locator'); ?></h2><p><?php esc_html_e('Paste the generated shortcode into any WordPress page, post, widget, or builder shortcode element.','kashida-branch-locator'); ?></p></div>
            </div>

            <section class="bl-help-section bl-help-section-builder">
                <div class="bl-help-section-head"><div><span class="bl-help-eyebrow"><?php esc_html_e('Recommended workflow','kashida-branch-locator'); ?></span><h2><?php esc_html_e('Display Builder','kashida-branch-locator'); ?></h2></div><span class="bl-help-pill"><?php esc_html_e('No shortcode knowledge required','kashida-branch-locator'); ?></span></div>
                <p><?php esc_html_e('The recommended method is to open Groups → Display. Select a layout, choose the information and actions you want, adjust map settings when needed, then copy the generated shortcode.','kashida-branch-locator'); ?></p>
                <div class="bl-help-steps"><div><b>1</b><span><?php esc_html_e('Choose layout','kashida-branch-locator'); ?></span></div><div><b>2</b><span><?php esc_html_e('Choose fields','kashida-branch-locator'); ?></span></div><div><b>3</b><span><?php esc_html_e('Set options','kashida-branch-locator'); ?></span></div><div><b>4</b><span><?php esc_html_e('Copy shortcode','kashida-branch-locator'); ?></span></div></div>
            </section>

            <section class="bl-help-section bl-help-section-builder">
                <div class="bl-help-section-head"><div><span class="bl-help-eyebrow"><?php esc_html_e('Bulk tools','kashida-branch-locator'); ?></span><h2><?php esc_html_e('Select and combine','kashida-branch-locator'); ?></h2></div><span class="bl-help-pill"><?php esc_html_e('New in v2.0.4','kashida-branch-locator'); ?></span></div>
                <p><?php esc_html_e('Select multiple branches or groups from their tables. You can delete the selected items at once, or open Display Builder to generate one shortcode that combines all selected items.','kashida-branch-locator'); ?></p>
                <div class="bl-help-steps"><div><b>1</b><span><?php esc_html_e('Select items','kashida-branch-locator'); ?></span></div><div><b>2</b><span><?php esc_html_e('Create display code','kashida-branch-locator'); ?></span></div><div><b>3</b><span><?php esc_html_e('Choose layout and fields','kashida-branch-locator'); ?></span></div><div><b>4</b><span><?php esc_html_e('Copy and publish','kashida-branch-locator'); ?></span></div></div>
                <div class="bl-help-code-list" style="margin-top:14px"><div class="bl-help-code-row"><div><strong><?php esc_html_e('Multiple groups','kashida-branch-locator'); ?></strong><code>[kashida_branch_locator groups="grp_xxxxxxxx,grp_yyyyyyyy" layout="cards"]</code></div></div><div class="bl-help-code-row"><div><strong><?php esc_html_e('Multiple branches','kashida-branch-locator'); ?></strong><code>[kashida_branch_locator branches="br_xxxxxxxx,br_yyyyyyyy" layout="map-table"]</code></div></div></div>
            </section>

            <section class="bl-help-section">
                <div class="bl-help-section-head"><div><span class="bl-help-eyebrow"><?php esc_html_e('Visitor controls','kashida-branch-locator'); ?></span><h2><?php esc_html_e('Search, filter, and sort','kashida-branch-locator'); ?></h2></div></div>
                <p><?php esc_html_e('Use Display Builder to enable live search, city and group filters, and sorting. In table layouts, visitors sort directly by clicking sortable column headers; there is no separate sorting box. The search button opens the search field without leaving the table header. Result counts, reset, and pagination update immediately without reloading the page.','kashida-branch-locator'); ?></p>
            </section>

            <section class="bl-help-section">
                <div class="bl-help-section-head"><div><span class="bl-help-eyebrow"><?php esc_html_e('Map interaction','kashida-branch-locator'); ?></span><h2><?php esc_html_e('Table and map stay connected','kashida-branch-locator'); ?></h2></div></div>
                <p><?php esc_html_e('In Map + Table, clicking a branch row centers the map on that branch and opens its marker. Clicking a map marker selects and highlights the matching row; when pagination is enabled, the table moves to the page containing that branch. Search and filters update the visible markers at the same time.','kashida-branch-locator'); ?></p>
                <div class="bl-help-steps"><div><b>1</b><span><?php esc_html_e('Filter or search','kashida-branch-locator'); ?></span></div><div><b>2</b><span><?php esc_html_e('Select a row or marker','kashida-branch-locator'); ?></span></div><div><b>3</b><span><?php esc_html_e('Inspect the location','kashida-branch-locator'); ?></span></div></div>
            </section>

            <section class="bl-help-section">
                <div class="bl-help-section-head"><div><span class="bl-help-eyebrow"><?php esc_html_e('Layouts','kashida-branch-locator'); ?></span><h2><?php esc_html_e('Choose the right presentation','kashida-branch-locator'); ?></h2></div></div>
                <p><?php esc_html_e('Table is best for comparing many fields at once. Cards give each branch a more spacious visual block. List is a compact horizontal directory for larger branch sets. Accordion saves space by revealing details on demand. Map focuses on geography, while Map + Table combines the map with the data table.','kashida-branch-locator'); ?></p>
                <div class="bl-help-grid bl-help-grid-3">
                    <?php foreach($layouts as $layout): ?>
                        <div class="bl-layout-card"><span class="bl-layout-icon"><?php echo esc_html($layout['icon']); ?></span><div><h3><?php echo esc_html($layout['title']); ?></h3><p><?php echo esc_html($layout['desc']); ?></p></div></div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="bl-help-section">
                <div class="bl-help-section-head"><div><span class="bl-help-eyebrow"><?php esc_html_e('Shortcodes','kashida-branch-locator'); ?></span><h2><?php esc_html_e('Ready-to-use examples','kashida-branch-locator'); ?></h2></div></div>
                <div class="bl-help-code-list">
                    <?php foreach($shortcodes as $item): ?>
                        <div class="bl-help-code-row"><div><strong><?php echo esc_html($item['label']); ?></strong><code><?php echo esc_html($item['code']); ?></code></div><button type="button" class="button bl-help-copy" data-shortcode="<?php echo esc_attr($item['code']); ?>"><?php esc_html_e('Copy','kashida-branch-locator'); ?></button></div>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="bl-help-grid bl-help-grid-2">
                <section class="bl-help-section bl-help-compact"><h2><?php esc_html_e('Mobile map controls','kashida-branch-locator'); ?></h2><p><?php esc_html_e('On desktop, drag the map with the mouse. On touch screens, one finger is reserved for normal page scrolling; use two fingers to move the map. Zoom controls remain available on the map.','kashida-branch-locator'); ?></p></section>
                <section class="bl-help-section bl-help-compact"><h2><?php esc_html_e('Performance','kashida-branch-locator'); ?></h2><p><?php esc_html_e('For large branch directories, use pagination and only enable the fields, filters, and map features you need. Map layouts use valid coordinates only, while search, sorting, and filtering run on the loaded branch data without a page reload.','kashida-branch-locator'); ?></p></section>
            </div>

            <section class="bl-help-section">
                <div class="bl-help-section-head"><div><span class="bl-help-eyebrow"><?php esc_html_e('Troubleshooting','kashida-branch-locator'); ?></span><h2><?php esc_html_e('Common issues','kashida-branch-locator'); ?></h2></div></div>
                <div class="bl-help-faq">
                    <details><summary><?php esc_html_e('A branch does not appear on the map','kashida-branch-locator'); ?></summary><p><?php esc_html_e('Check that both Lat and Lng are valid decimal coordinates. Branches without valid coordinates remain available in non-map layouts but are skipped on the map.','kashida-branch-locator'); ?></p></details>
                    <details><summary><?php esc_html_e('The import preview shows an error','kashida-branch-locator'); ?></summary><p><?php esc_html_e('Review the row message in the Status column. The most common causes are a missing Name, invalid coordinates, or an incorrectly formatted Location value.','kashida-branch-locator'); ?></p></details>
                    <details><summary><?php esc_html_e('How do I combine branches or groups?','kashida-branch-locator'); ?></summary><p><?php esc_html_e('Select the required branches or groups in their table, choose Create Display Code, then configure the combined display in Display Builder.','kashida-branch-locator'); ?></p></details>
                    <details><summary><?php esc_html_e('Do map layouts require an API key?','kashida-branch-locator'); ?></summary><p><?php esc_html_e('No. The plugin uses OpenStreetMap tiles and does not require a Google Maps API key. Directions open Google Maps only after the visitor chooses the Directions button.','kashida-branch-locator'); ?></p></details>
                    <details><summary><?php esc_html_e('How does the table search work?','kashida-branch-locator'); ?></summary><p><?php esc_html_e('Click the search icon in the table header to open the search field. Search matches branch name, city, address, phone, and group. The field stays focused while you type and can be cleared with the × button.','kashida-branch-locator'); ?></p></details>
                    <details><summary><?php esc_html_e('How do I sort a table?','kashida-branch-locator'); ?></summary><p><?php esc_html_e('When sorting is enabled, click the Branch, City, or Group column heading. The arrow shows the active direction. There is no separate sorting control in the visitor interface.','kashida-branch-locator'); ?></p></details>
                </div>
            </section>

            <div class="bl-help-grid bl-help-grid-2">
                <section class="bl-help-section bl-help-compact"><h2><?php esc_html_e('Maps','kashida-branch-locator'); ?></h2><p><?php esc_html_e('Map and Map + Table require valid latitude and longitude values for each branch. Clicking a marker selects the matching branch; clicking a branch selects its marker. Map tiles are provided by OpenStreetMap.','kashida-branch-locator'); ?></p></section>
                <section class="bl-help-section bl-help-compact"><h2><?php esc_html_e('Import','kashida-branch-locator'); ?></h2><p><?php esc_html_e('For XLSX or CSV, use columns such as Name, City, Address, Phone, Lat, Lng, and Group. The downloadable XLSX template contains column instructions and no sample locations. Preview the import before running it.','kashida-branch-locator'); ?></p><div class="bl-help-actions"><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=kashida-branch-locator-import')); ?>"><?php esc_html_e('Open Import','kashida-branch-locator'); ?></a><a class="button" href="<?php echo esc_url(KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'samples/kashida-branch-locator-import-template.xlsx'); ?>" download><?php esc_html_e('Download sample file','kashida-branch-locator'); ?></a></div></section>
            </div>

            <?php Footer::render(); ?>
        </div>
        <?php
    }
}
