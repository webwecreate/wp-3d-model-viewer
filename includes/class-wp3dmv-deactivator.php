<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/includes
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP3DMV_Deactivator {

    /**
     * Plugin deactivation handler.
     *
     * Note: We intentionally do NOT delete options here.
     * Options are only removed on uninstall (see uninstall.php).
     */
    public static function deactivate() {
        flush_rewrite_rules();
    }
}
