<?php
declare(strict_types=1);
namespace Kashida\BranchLocator\Admin;
use Kashida\BranchLocator\Models\Branch;
use Kashida\BranchLocator\Models\Group;
if ( ! defined( 'ABSPATH' ) ) exit;

class Branches_Page {
    public function __construct() { $this->render(); }

    private function render(): void {
        $groups = Group::all();
        $cities = Branch::get_cities();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list filter; no state-changing action is performed.
        $search   = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list filter; no state-changing action is performed.
        $city_f   = sanitize_text_field( wp_unslash( $_GET['city'] ?? '' ) );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list filter; no state-changing action is performed.
        $group_f  = absint( wp_unslash( $_GET['group_id'] ?? 0 ) );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin pagination; no state-changing action is performed.
        $page_num = max(1, absint( wp_unslash( $_GET['paged'] ?? 1 ) ));
        $result   = Branch::query(['search'=>$search,'city'=>$city_f,'group_id'=>$group_f,'page'=>$page_num,'per_page'=>20]);
        $total    = $result['total'];
        $branches = $result['items'];
        $total_pages = ceil($total / 20);
        ?>
        <div class="wrap bl-admin-wrap">
            <div class="bl-admin-page-head">
                <div class="bl-admin-brand">
                    <div class="bl-admin-brand-logo"><img src="<?php echo esc_url( KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'images/kashida-mark.svg' ); ?>" alt="<?php esc_attr_e('Kashida Branch Locator','kashida-branch-locator'); ?>"></div>
                    <div>
                        <span class="bl-admin-eyebrow"><?php esc_html_e('Kashida Branch Locator','kashida-branch-locator'); ?></span>
                        <h1><?php esc_html_e('Branches','kashida-branch-locator'); ?></h1>
                        <p><?php esc_html_e('Manage locations, groups, coordinates and display codes.','kashida-branch-locator'); ?></p>
                    </div>
                </div>
                <button class="button button-primary bl-btn-add-branch"><?php esc_html_e('Add New Branch','kashida-branch-locator'); ?></button>
            </div>
            <div class="bl-bulk-toolbar" data-page-type="branches">
                <label class="bl-select-all-label"><input type="checkbox" class="bl-select-all"> <?php esc_html_e('Select all on this page','kashida-branch-locator'); ?></label>
                <span class="bl-selected-count"></span>
                <button type="button" class="button bl-bulk-display" disabled><?php esc_html_e('Create Display Code','kashida-branch-locator'); ?></button>
                <button type="button" class="button button-link-delete bl-bulk-delete" disabled><?php esc_html_e('Delete Selected','kashida-branch-locator'); ?></button>
            </div>
            <form method="get" class="bl-filter-form">
                <input type="hidden" name="page" value="kashida-branch-locator">
                <div class="bl-filters">
                    <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search branches…','kashida-branch-locator'); ?>" class="bl-search-input">
                    <select name="city">
                        <option value=""><?php esc_html_e('All Cities','kashida-branch-locator'); ?></option>
                        <?php foreach($cities as $c): ?>
                            <option value="<?php echo esc_attr($c); ?>" <?php selected($city_f,$c); ?>><?php echo esc_html($c); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="group_id">
                        <option value=""><?php esc_html_e('All Groups','kashida-branch-locator'); ?></option>
                        <?php foreach($groups as $g): ?>
                            <option value="<?php echo esc_attr($g->id); ?>" <?php selected($group_f,$g->id); ?>><?php echo esc_html($g->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="button"><?php esc_html_e('Filter','kashida-branch-locator'); ?></button>
                    <?php if($search||$city_f||$group_f): ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=kashida-branch-locator')); ?>" class="button"><?php esc_html_e('Clear','kashida-branch-locator'); ?></a>
                    <?php endif; ?>
                </div>
            </form>

            <p class="bl-results-count"><?php
                /* translators: %s: number of branches. */
                echo esc_html( sprintf( _n('%s branch','%s branches',$total,'kashida-branch-locator'), number_format_i18n($total) ) );
            ?></p>

            <table class="wp-list-table widefat fixed striped bl-branches-table">
                <thead>
                    <tr>
                        <th class="check-column"><span class="screen-reader-text"><?php esc_html_e('Select','kashida-branch-locator'); ?></span></th>
                        <th><?php esc_html_e('Name','kashida-branch-locator'); ?></th>
                        <th><?php esc_html_e('City','kashida-branch-locator'); ?></th>
                        <th><?php esc_html_e('Phone','kashida-branch-locator'); ?></th>
                        <th><?php esc_html_e('Group','kashida-branch-locator'); ?></th>
                        <th class="column-slug"><?php esc_html_e('Slug Code','kashida-branch-locator'); ?></th>
                        <th class="column-shortcode"><?php esc_html_e('Shortcode','kashida-branch-locator'); ?></th>
                        <th><?php esc_html_e('Coordinates','kashida-branch-locator'); ?></th>
                        <th><?php esc_html_e('Actions','kashida-branch-locator'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if(empty($branches)): ?>
                    <tr><td colspan="9" class="bl-empty"><?php esc_html_e('No branches found.','kashida-branch-locator'); ?></td></tr>
                <?php else: foreach($branches as $b): ?>
                    <tr data-id="<?php echo esc_attr($b->id); ?>" data-slug="<?php echo esc_attr($b->slug); ?>">
                        <?php
                        /* translators: %s: branch name. */
                        $branch_aria_label = sprintf( __( 'Select %s', 'kashida-branch-locator' ), $b->name );
                        ?>
                        <th scope="row" class="check-column"><input type="checkbox" class="bl-row-select" value="<?php echo esc_attr($b->id); ?>" data-slug="<?php echo esc_attr($b->slug); ?>" aria-label="<?php echo esc_attr( $branch_aria_label ); ?>"></th>
                        <td class="bl-branch-name-cell">
                            <div class="bl-branch-name-wrap">
                                <span class="bl-branch-pin" aria-hidden="true">●</span>
                                <div><strong><?php echo esc_html($b->name); ?></strong><br><small><?php echo esc_html($b->address); ?></small></div>
                            </div>
                        </td>
                        <td class="bl-branch-city-cell"><span class="bl-data-label"><?php esc_html_e('City','kashida-branch-locator'); ?></span><strong><?php echo esc_html($b->city); ?></strong></td>
                        <td class="bl-branch-phone-cell"><span class="bl-data-label"><?php esc_html_e('Phone','kashida-branch-locator'); ?></span><?php if($b->phone): ?><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/','',$b->phone)); ?>"><?php echo esc_html($b->phone); ?></a><?php else: ?><span class="bl-muted">—</span><?php endif; ?></td>
                        <td class="bl-branch-group-cell"><span class="bl-data-label"><?php esc_html_e('Group','kashida-branch-locator'); ?></span><?php if($b->group_name): ?><span class="bl-group-badge" style="background:<?php echo esc_attr($b->group_color); ?>"><?php echo esc_html($b->group_name); ?></span><?php else: ?><span class="bl-muted">—</span><?php endif; ?></td>
                        <td class="column-slug"><code><?php echo esc_html($b->slug); ?></code></td>
                        <td class="column-shortcode">
                            <?php $branch_shortcode = '[kashida_branch_locator branch="' . $b->slug . '"]'; ?>
                            <div class="bl-shortcode-cell">
                                <code class="bl-shortcode-code"><?php echo esc_html($branch_shortcode); ?></code>
                                <button type="button" class="button button-small bl-copy-shortcode" data-shortcode="<?php echo esc_attr($branch_shortcode); ?>" aria-label="<?php esc_attr_e('Copy shortcode','kashida-branch-locator'); ?>"><?php esc_html_e('Copy','kashida-branch-locator'); ?></button>
                            </div>
                        </td>
                        <td class="bl-branch-coords-cell"><span class="bl-data-label"><?php esc_html_e('Coordinates','kashida-branch-locator'); ?></span><code><?php echo esc_html($b->latitude . ', ' . $b->longitude); ?></code></td>
                        <td class="bl-row-actions">
                            <div class="bl-row-actions-wrap">
                            <button type="button" class="button button-primary button-small bl-open-display-builder" data-branch="<?php echo esc_attr($b->slug); ?>" data-branch-name="<?php echo esc_attr($b->name); ?>"><?php esc_html_e('Display','kashida-branch-locator'); ?></button>
                            <button class="button button-small bl-edit-branch"
                                data-id="<?php echo esc_attr($b->id); ?>"
                                data-name="<?php echo esc_attr($b->name); ?>"
                                data-city="<?php echo esc_attr($b->city); ?>"
                                data-address="<?php echo esc_attr($b->address); ?>"
                                data-phone="<?php echo esc_attr($b->phone); ?>"
                                data-lat="<?php echo esc_attr($b->latitude); ?>"
                                data-lng="<?php echo esc_attr($b->longitude); ?>"
                                data-group="<?php echo esc_attr($b->group_id); ?>">
                                <?php esc_html_e('Edit','kashida-branch-locator'); ?>
                            </button>
                            <button class="button button-small button-link-delete bl-delete-branch" data-id="<?php echo esc_attr($b->id); ?>">
                                <?php esc_html_e('Delete','kashida-branch-locator'); ?>
                            </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>

            <?php if($total_pages > 1): ?>
            <div class="bl-pagination tablenav">
                <div class="tablenav-pages">
                    <?php echo wp_kses_post( paginate_links(['base'=>add_query_arg('paged','%#%'),'format'=>'','current'=>$page_num,'total'=>$total_pages,'prev_text'=>'&laquo;','next_text'=>'&raquo;']) ); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Branch Modal -->
        <div id="bl-branch-modal" class="bl-modal" style="display:none">
            <div class="bl-modal-overlay"></div>
            <div class="bl-modal-box">
                <div class="bl-modal-header">
                    <h2 id="bl-modal-title"><?php esc_html_e('Add Branch','kashida-branch-locator'); ?></h2>
                    <button class="bl-modal-close">&times;</button>
                </div>
                <form id="bl-branch-form" class="bl-modal-form">
                    <input type="hidden" id="bl-branch-id" name="id" value="">
                    <div class="bl-form-row">
                        <label for="bl-f-name"><?php esc_html_e('Branch Name *','kashida-branch-locator'); ?></label>
                        <input type="text" id="bl-f-name" name="name" required>
                    </div>
                    <div class="bl-form-row bl-form-row-2col">
                        <div>
                            <label for="bl-f-city"><?php esc_html_e('City','kashida-branch-locator'); ?></label>
                            <input type="text" id="bl-f-city" name="city">
                        </div>
                        <div>
                            <label for="bl-f-phone"><?php esc_html_e('Phone','kashida-branch-locator'); ?></label>
                            <input type="text" id="bl-f-phone" name="phone">
                        </div>
                    </div>
                    <div class="bl-form-row">
                        <label for="bl-f-address"><?php esc_html_e('Address','kashida-branch-locator'); ?></label>
                        <textarea id="bl-f-address" name="address" rows="3"></textarea>
                    </div>
                    <div class="bl-form-row bl-form-row-2col">
                        <div>
                            <label for="bl-f-lat"><?php esc_html_e('Latitude','kashida-branch-locator'); ?></label>
                            <input type="number" id="bl-f-lat" name="latitude" step="0.0000001" min="-90" max="90" value="0">
                        </div>
                        <div>
                            <label for="bl-f-lng"><?php esc_html_e('Longitude','kashida-branch-locator'); ?></label>
                            <input type="number" id="bl-f-lng" name="longitude" step="0.0000001" min="-180" max="180" value="0">
                        </div>
                    </div>
                    <div class="bl-form-row">
                        <label for="bl-f-group"><?php esc_html_e('Group','kashida-branch-locator'); ?></label>
                        <select id="bl-f-group" name="group_id">
                            <option value=""><?php esc_html_e('— No Group —','kashida-branch-locator'); ?></option>
                            <?php foreach($groups as $g): ?>
                                <option value="<?php echo esc_attr($g->id); ?>"><?php echo esc_html($g->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="bl-modal-footer">
                        <button type="submit" class="button button-primary"><?php esc_html_e('Save','kashida-branch-locator'); ?></button>
                        <button type="button" class="button bl-modal-close"><?php esc_html_e('Cancel','kashida-branch-locator'); ?></button>
                    </div>
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
