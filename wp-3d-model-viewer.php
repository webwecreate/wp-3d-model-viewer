<?php
/**
 * Plugin Name:       WP 3D Model Viewer
 * Plugin URI:        https://github.com/webwecreate/wp-3d-model-viewer
 * Description:       แสดง 3D Model แบบ interactive บน WooCommerce product page และ Elementor widget รองรับการหมุน 360° ด้วย mouse/touch drag
 * Version:           1.0.0
 * Author:            webwecreate
 * Author URI:        https://webwecreate.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp3dmv
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 *
 * @package WP3DModelViewer
 * @version 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─── Plugin Constants ──────────────────────────────────────────────────────────
define( 'WP3DMV_VERSION',        '1.0.0' );
define( 'WP3DMV_PLUGIN_DIR',     plugin_dir_path( __FILE__ ) );
define( 'WP3DMV_PLUGIN_URL',     plugin_dir_url( __FILE__ ) );
define( 'WP3DMV_PLUGIN_FILE',    __FILE__ );
define( 'WP3DMV_PLUGIN_SLUG',    'wp-3d-model-viewer' );
define( 'WP3DMV_TEXT_DOMAIN',    'wp3dmv' );
define( 'WP3DMV_MIN_PHP',        '7.4' );
define( 'WP3DMV_MIN_WP',         '6.0' );

// ─── PHP Version Check ─────────────────────────────────────────────────────────
if ( version_compare( PHP_VERSION, WP3DMV_MIN_PHP, '<' ) ) {
    add_action( 'admin_notices', 'wp3dmv_php_version_notice' );
    return;
}

function wp3dmv_php_version_notice() {
    echo '<div class="notice notice-error"><p>';
    printf(
        /* translators: 1: required PHP version, 2: current PHP version */
        esc_html__( 'WP 3D Model Viewer requires PHP %1$s or higher. You are running PHP %2$s.', 'wp3dmv' ),
        esc_html( WP3DMV_MIN_PHP ),
        esc_html( PHP_VERSION )
    );
    echo '</p></div>';
}

// ─── Load Core Classes ─────────────────────────────────────────────────────────
require_once WP3DMV_PLUGIN_DIR . 'includes/class-wp3dmv-loader.php';
require_once WP3DMV_PLUGIN_DIR . 'includes/class-wp3dmv-activator.php';
require_once WP3DMV_PLUGIN_DIR . 'includes/class-wp3dmv-deactivator.php';
require_once WP3DMV_PLUGIN_DIR . 'includes/class-wp3dmv-i18n.php';
require_once WP3DMV_PLUGIN_DIR . 'includes/class-wp3dmv-core.php';

// ─── Activation / Deactivation Hooks ─────────────────────────────────────────
register_activation_hook( WP3DMV_PLUGIN_FILE, array( 'WP3DMV_Activator', 'activate' ) );
register_deactivation_hook( WP3DMV_PLUGIN_FILE, array( 'WP3DMV_Deactivator', 'deactivate' ) );

// ─── Boot Plugin ───────────────────────────────────────────────────────────────
/**
 * Returns the main instance of WP3DMV_Core.
 *
 * @return WP3DMV_Core
 */
function wp3dmv() {
    return WP3DMV_Core::instance();
}

// Kick off
wp3dmv();
