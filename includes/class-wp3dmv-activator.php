<?php
/**
 * Fired during plugin activation.
 *
 * Creates default options, verifies requirements, and sets up
 * anything needed before the plugin runs for the first time.
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/includes
 * @version    1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP3DMV_Activator {

    /**
     * Default plugin settings (matches Section 4 of Master Architecture).
     *
     * @var array
     */
    private static $default_settings = array(
        'default_bg_color'        => '#f5f5f5',
        'default_height'          => 400,
        'default_auto_rotate'     => true,
        'default_rotation_speed'  => 1.0,
        'enable_zoom'             => true,
        'enable_fullscreen'       => true,
        'show_controls_hint'      => true,
        'loading_text'            => 'กำลังโหลด...',
        'cache_duration'          => 3600,
        'lazy_load'               => true,
        'max_texture_size'        => 2048,
        'initial_camera_distance' => 3,
    );

    /**
     * Plugin activation handler.
     */
    public static function activate() {
        // ── 1. Check PHP Version ──────────────────────────────────────────────
        if ( version_compare( PHP_VERSION, WP3DMV_MIN_PHP, '<' ) ) {
            deactivate_plugins( plugin_basename( WP3DMV_PLUGIN_FILE ) );
            wp_die(
                sprintf(
                    /* translators: 1: required version, 2: current version */
                    esc_html__( 'WP 3D Model Viewer requires PHP %1$s or higher. You are running PHP %2$s.', 'wp3dmv' ),
                    esc_html( WP3DMV_MIN_PHP ),
                    esc_html( PHP_VERSION )
                ),
                esc_html__( 'Plugin Activation Error', 'wp3dmv' ),
                array( 'back_link' => true )
            );
        }

        // ── 2. Check WordPress Version ────────────────────────────────────────
        if ( version_compare( get_bloginfo( 'version' ), WP3DMV_MIN_WP, '<' ) ) {
            deactivate_plugins( plugin_basename( WP3DMV_PLUGIN_FILE ) );
            wp_die(
                sprintf(
                    /* translators: 1: required WP version, 2: current WP version */
                    esc_html__( 'WP 3D Model Viewer requires WordPress %1$s or higher. You are running WordPress %2$s.', 'wp3dmv' ),
                    esc_html( WP3DMV_MIN_WP ),
                    esc_html( get_bloginfo( 'version' ) )
                ),
                esc_html__( 'Plugin Activation Error', 'wp3dmv' ),
                array( 'back_link' => true )
            );
        }

        // ── 3. Create Default Options (only if not already set) ───────────────
        if ( false === get_option( 'wp3dmv_settings' ) ) {
            add_option( 'wp3dmv_settings', self::$default_settings );
        }

        // ── 4. Store Activation Version ───────────────────────────────────────
        update_option( 'wp3dmv_version', WP3DMV_VERSION );
        update_option( 'wp3dmv_activated_at', current_time( 'mysql' ) );

        // ── 5. Flush Rewrite Rules ────────────────────────────────────────────
        flush_rewrite_rules();
    }

    /**
     * Return the default settings array.
     * Used by WP3DMV_Settings when resetting to defaults.
     *
     * @return array
     */
    public static function get_default_settings() {
        return self::$default_settings;
    }
}
