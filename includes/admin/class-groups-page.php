<?php
declare(strict_types=1);
namespace Kashida\BranchLocator\Admin;
use Kashida\BranchLocator\Models\Group;
if ( ! defined( 'ABSPATH' ) ) exit;

class Groups_Page {
    public function __construct() {}
    public function render(): void {
        $groups = Group::all();
        ?>
        <div class="wrap bl-admin-wrap">
            <div class="bl-admin-page-head">
                <div class="bl-admin-brand">
                    <div class="bl-admin-brand-logo"><img src="<?php echo esc_url( KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'images/kashida-mark.svg' ); ?>" alt="<?php esc_attr_e('Kashida Branch Locator','kashida-branch-locator'); ?>"></div>
                    <div>
                        <span class="bl-admin-eyebrow"><?php esc_html_e('Kashida Branch Locator','kashida-branch-locator'); ?></span>
                        <h1><?php esc_html_e('Groups','kashida-branch-locator'); ?></h1>
                        <p><?php esc_html_e('Organize branches into groups and create reusable display codes.','kashida-branch-locator'); ?></p>
                    </div>
                </div>
                <button class="button button-primary bl-btn-add-group"><?php esc_html_e('Add New Group','kashida-branch-locator'); ?></button>
            </div>
            <div class="bl-bulk-toolbar" data-page-type="groups">
                <label class="bl-select-all-label"><input type="checkbox" class="bl-select-all"> <?php esc_html_e('Select all groups','kashida-branch-locator'); ?></label>
                <span class="bl-selected-count"></span>
                <button type="button" class="button bl-bulk-display" disabled><?php esc_html_e('Create Display Code','kashida-branch-locator'); ?></button>
                <button type="button" class="button button-link-delete bl-bulk-delete" disabled><?php esc_html_e('Delete Selected','kashida-branch-locator'); ?></button>
            </div>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr>
                    <th class="check-column"><span class="screen-reader-text"><?php esc_html_e('Select','kashida-branch-locator'); ?></span></th>
                    <th><?php esc_html_e('Color','kashida-branch-locator'); ?></th>
                    <th><?php esc_html_e('Name','kashida-branch-locator'); ?></th>
                    <th><?php esc_html_e('Slug','kashida-branch-locator'); ?></th>
                    <th class="column-shortcode"><?php esc_html_e('Shortcode','kashida-branch-locator'); ?></th>
                    <th><?php esc_html_e('Branches','kashida-branch-locator'); ?></th>
                    <th><?php esc_html_e('Actions','kashida-branch-locator'); ?></th>
                </tr></thead>
                <tbody>
                <?php if(empty($groups)): ?>
                    <tr><td colspan="7" class="bl-empty"><?php esc_html_e('No groups yet.','kashida-branch-locator'); ?></td></tr>
                <?php else: foreach($groups as $g): ?>
                    <?php $group_shortcode = '[kashida_branch_locator group="' . $g->slug . '"]'; ?>
                    <tr data-id="<?php echo esc_attr($g->id); ?>" data-slug="<?php echo esc_attr($g->slug); ?>">
                        <?php
                        /* translators: %s: group name. */
                        $group_aria_label = sprintf( __( 'Select %s', 'kashida-branch-locator' ), $g->name );
                        ?>
                        <th scope="row" class="check-column"><input type="checkbox" class="bl-row-select" value="<?php echo esc_attr($g->id); ?>" data-slug="<?php echo esc_attr($g->slug); ?>" aria-label="<?php echo esc_attr( $group_aria_label ); ?>"></th>
                        <td><span class="bl-color-swatch" style="background:<?php echo esc_attr($g->color); ?>"></span></td>
                        <td><strong><?php echo esc_html($g->name); ?></strong></td>
                        <td><code><?php echo esc_html($g->slug); ?></code></td>
                        <td class="column-shortcode">
                            <div class="bl-shortcode-cell">
                                <code class="bl-shortcode-code"><?php echo esc_html($group_shortcode); ?></code>
                                <button type="button" class="button button-small bl-copy-shortcode" data-shortcode="<?php echo esc_attr($group_shortcode); ?>" aria-label="<?php esc_attr_e('Copy shortcode','kashida-branch-locator'); ?>"><?php esc_html_e('Copy','kashida-branch-locator'); ?></button>
                            </div>
                        </td>
                        <td><?php echo (int)$g->branch_count; ?></td>
                        <td class="bl-actions-cell">
                            <button type="button" class="button button-primary button-small bl-open-display-builder" data-group="<?php echo esc_attr($g->slug); ?>" data-group-name="<?php echo esc_attr($g->name); ?>"><?php esc_html_e('Display','kashida-branch-locator'); ?></button>
                            <button type="button" class="button button-small bl-edit-group" data-id="<?php echo esc_attr($g->id); ?>" data-name="<?php echo esc_attr($g->name); ?>" data-color="<?php echo esc_attr($g->color); ?>"><?php esc_html_e('Edit','kashida-branch-locator'); ?></button>
                            <button type="button" class="button button-small button-link-delete bl-delete-group" data-id="<?php echo esc_attr($g->id); ?>"><?php esc_html_e('Delete','kashida-branch-locator'); ?></button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <div id="bl-group-modal" class="bl-modal" style="display:none">
            <div class="bl-modal-overlay"></div>
            <div class="bl-modal-box">
                <div class="bl-modal-header"><h2 id="bl-group-modal-title"><?php esc_html_e('Add Group','kashida-branch-locator'); ?></h2><button type="button" class="bl-modal-close">&times;</button></div>
                <form id="bl-group-form" class="bl-modal-form">
                    <input type="hidden" id="bl-group-id" name="id" value="">
                    <div class="bl-form-row"><label for="bl-g-name"><?php esc_html_e('Group Name *','kashida-branch-locator'); ?></label><input type="text" id="bl-g-name" name="name" required></div>
                    <div class="bl-form-row bl-color-field-row"><label for="bl-g-color"><?php esc_html_e('Color','kashida-branch-locator'); ?></label><div class="bl-color-field-controls"><input type="color" id="bl-g-color" name="color" value="<?php echo esc_attr( get_option( 'kashida_branch_locator_default_group_color', '#0a6b70' ) ); ?>" class="kbl-color-picker"><input type="text" id="bl-g-color-hex" class="kbl-color-hex" value="<?php echo esc_attr( get_option( 'kashida_branch_locator_default_group_color', '#0a6b70' ) ); ?>" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$" aria-label="<?php esc_attr_e('Color HEX value','kashida-branch-locator'); ?>" placeholder="#0A6B70"></div></div>
                    <div class="bl-modal-footer"><button type="submit" class="button button-primary"><?php esc_html_e('Save','kashida-branch-locator'); ?></button><button type="button" class="button bl-modal-close"><?php esc_html_e('Cancel','kashida-branch-locator'); ?></button></div>
                </form>
            </div>
        </div>

        <div id="bl-display-builder-modal" class="bl-modal" style="display:none" data-selection-type="group" data-selection-values="">
            <div class="bl-modal-overlay"></div>
            <div class="bl-modal-box bl-display-builder-box">
                <div class="bl-modal-header"><div><h2><?php esc_html_e('Display Builder','kashida-branch-locator'); ?></h2><p class="bl-builder-group-name"></p></div><button type="button" class="bl-modal-close">&times;</button></div>
                <div class="bl-display-builder">
                    <div class="bl-builder-section">
                        <h3><?php esc_html_e('Display Layout','kashida-branch-locator'); ?></h3>
                        <div class="bl-layout-options">
                            <label><input type="radio" name="bl-builder-layout" value="table" checked> <?php esc_html_e('Table','kashida-branch-locator'); ?></label>
                            <label><input type="radio" name="bl-builder-layout" value="cards"> <?php esc_html_e('Cards','kashida-branch-locator'); ?></label>
                            <label><input type="radio" name="bl-builder-layout" value="list"> <?php esc_html_e('List','kashida-branch-locator'); ?></label>
                            <label><input type="radio" name="bl-builder-layout" value="accordion"> <?php esc_html_e('Accordion','kashida-branch-locator'); ?></label>
                            <label><input type="radio" name="bl-builder-layout" value="map"> <?php esc_html_e('Map','kashida-branch-locator'); ?></label>
                            <label><input type="radio" name="bl-builder-layout" value="map-table"> <?php esc_html_e('Map + Table','kashida-branch-locator'); ?></label>
                        </div>
                    </div>
                    <div class="bl-builder-section">
                        <h3><?php esc_html_e('Information to Show','kashida-branch-locator'); ?></h3>
                        <div class="bl-field-options">
                            <label><input type="checkbox" data-builder-field="name" checked> <?php esc_html_e('Branch name','kashida-branch-locator'); ?></label>
                            <label><input type="checkbox" data-builder-field="city" checked> <?php esc_html_e('City','kashida-branch-locator'); ?></label>
                            <label><input type="checkbox" data-builder-field="address" checked> <?php esc_html_e('Address','kashida-branch-locator'); ?></label>
                            <label><input type="checkbox" data-builder-field="phone" checked> <?php esc_html_e('Phone','kashida-branch-locator'); ?></label>
                            <label><input type="checkbox" data-builder-field="group"> <?php esc_html_e('Group','kashida-branch-locator'); ?></label>
                        </div>
                    </div>
                    <div class="bl-builder-section">
                        <h3><?php esc_html_e('Options','kashida-branch-locator'); ?></h3>
                        <div class="bl-builder-options-grid">
                            <label class="bl-builder-check"><input type="checkbox" data-builder-option="search" checked> <?php esc_html_e('Search','kashida-branch-locator'); ?></label>
                            <label class="bl-builder-check"><input type="checkbox" data-builder-option="pagination" checked> <?php esc_html_e('Pagination','kashida-branch-locator'); ?></label>
                            <label class="bl-builder-check"><input type="checkbox" data-builder-option="sorting" checked> <?php esc_html_e('Sorting','kashida-branch-locator'); ?></label>
                            <label class="bl-builder-check"><input type="checkbox" data-builder-option="city_filter" checked> <?php esc_html_e('City filter','kashida-branch-locator'); ?></label>
                            <label class="bl-builder-check"><input type="checkbox" data-builder-option="group_filter" checked> <?php esc_html_e('Group filter','kashida-branch-locator'); ?></label>
                            <label class="bl-builder-check"><input type="checkbox" data-builder-option="call" checked> <?php esc_html_e('Call button','kashida-branch-locator'); ?></label>
                            <label class="bl-builder-check"><input type="checkbox" data-builder-option="directions" checked> <?php esc_html_e('Directions button','kashida-branch-locator'); ?></label>
                            <label><span><?php esc_html_e('Results per page','kashida-branch-locator'); ?></span><input type="number" min="1" max="100" value="15" data-builder-option="per_page"></label>
                            <label><span><?php esc_html_e('Map height','kashida-branch-locator'); ?></span><input type="number" min="300" max="1200" value="520" data-builder-option="height"></label>
                            <label><span><?php esc_html_e('Map zoom','kashida-branch-locator'); ?></span><input type="number" min="1" max="18" value="6" data-builder-option="zoom"></label>
                            <label><span><?php esc_html_e('Cards columns','kashida-branch-locator'); ?></span><input type="number" min="1" max="6" value="3" data-builder-option="columns"></label>
                            <label class="bl-builder-check"><input type="checkbox" data-builder-option="group_cards"> <?php esc_html_e('Group cards under group headings','kashida-branch-locator'); ?></label>
                            <label><span><?php esc_html_e('Call button color','kashida-branch-locator'); ?></span><input type="color" value="#12b76a" data-builder-option="call_color"></label>
                            <label><span><?php esc_html_e('Directions button color','kashida-branch-locator'); ?></span><input type="color" value="#1769e8" data-builder-option="directions_color"></label>
                            <label><span><?php esc_html_e('Website button color','kashida-branch-locator'); ?></span><input type="color" value="#d85b00" data-builder-option="website_color"></label>
                        </div>
                    </div>
                    <div class="bl-builder-preview">
                        <div class="bl-builder-output-head"><h3><?php esc_html_e('Live Preview','kashida-branch-locator'); ?></h3><span class="bl-builder-preview-note"><?php esc_html_e('Preview only — your selected settings will be used in the generated shortcode.','kashida-branch-locator'); ?></span></div>
                        <div id="bl-builder-preview" class="bl-builder-preview-canvas"></div>
                    </div>
                    <div class="bl-builder-output">
                        <div class="bl-builder-output-head"><h3><?php esc_html_e('Generated Shortcode','kashida-branch-locator'); ?></h3><button type="button" class="button button-primary bl-copy-generated-shortcode"><?php esc_html_e('Copy Shortcode','kashida-branch-locator'); ?></button></div>
                        <code id="bl-generated-shortcode" class="bl-generated-shortcode"></code>
                        <p class="bl-builder-note"><?php esc_html_e('Copy this shortcode and paste it into any WordPress page, post, or widget.','kashida-branch-locator'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php Footer::render(); ?>
        <?php
    }
}
