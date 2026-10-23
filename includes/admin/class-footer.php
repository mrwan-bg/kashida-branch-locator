<?php
declare(strict_types=1);
namespace Kashida\BranchLocator\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

class Footer {
    public static function render(): void {
        ?>
        <div class="bl-admin-footer">
            <a class="bl-admin-footer-logo" href="https://kashida.ly/" target="_blank" rel="noopener noreferrer" aria-label="Kashida Media Services">
                <img src="<?php echo esc_url( KASHIDA_BRANCH_LOCATOR_ASSETS_URL . 'images/kashida-logo.svg' ); ?>" width="150" height="30" alt="Kashida Media Services">
            </a>
            <div class="bl-admin-footer-credit">
                <span><?php esc_html_e('Developed by', 'kashida-branch-locator'); ?> <a href="https://kashida.ly/" target="_blank" rel="noopener noreferrer">Kashida Media Services</a> — <a href="https://www.behance.net/Mrwan_elgobee" target="_blank" rel="noopener noreferrer">Mrwan elGobee</a></span>
            </div>
        </div>
        <?php
    }
}
