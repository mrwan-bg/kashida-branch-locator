<?php
declare(strict_types=1);
namespace Kashida\BranchLocator;
if ( ! defined( 'ABSPATH' ) ) exit;
final class Plugin {
    private static ?self $instance = null;
    public static function instance(): self {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }
    private function __construct() {}
    public function init(): void {
        new \Kashida\BranchLocator\Shortcodes\Branch_Locator_Shortcode();
        if ( is_admin() ) new \Kashida\BranchLocator\Admin\Admin();
    }
}
