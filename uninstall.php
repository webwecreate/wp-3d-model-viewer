<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Removes all plugin options and post meta.
 * This file is called automatically by WordPress on plugin deletion.
 *
 * @package WP3DModelViewer
 * @version 1.0.0
 */

// WordPress security check — must be called by WP uninstall process
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// ── Remove Plugin Options ─────────────────────────────────────────────────────
delete_option( 'wp3dmv_settings' );
delete_option( 'wp3dmv_version' );
delete_option( 'wp3dmv_activated_at' );

// ── Remove Post Meta ──────────────────────────────────────────────────────────
$meta_keys = array(
    '_wp3dmv_model_url',
    '_wp3dmv_model_id',
    '_wp3dmv_viewer_settings',
    '_wp3dmv_enabled',
    '_wp3dmv_position',
);

foreach ( $meta_keys as $key ) {
    delete_post_meta_by_key( $key );
}
