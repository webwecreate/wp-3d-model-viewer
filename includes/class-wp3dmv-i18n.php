<?php
/**
 * Internationalization — loads the plugin text domain.
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/includes
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP3DMV_i18n {

    /**
     * Load the plugin text domain for translation.
     * Hooked to plugins_loaded action.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            WP3DMV_TEXT_DOMAIN,
            false,
            dirname( plugin_basename( WP3DMV_PLUGIN_FILE ) ) . '/languages/'
        );
    }
}
