<?php
/**
 * AJAX Handler Class
 *
 * Handles all AJAX requests for the WP 3D Model Viewer plugin.
 * Covers nonce verification, input sanitization, URL validation,
 * and secure JSON responses.
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/includes
 * @version    1.0.0
 * @since      1.0.0
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP3DMV_AJAX
 *
 * Registers and handles AJAX actions for the plugin.
 * All public-facing and logged-in AJAX endpoints live here.
 *
 * Nonce handle : 'wp3dmv-ajax-nonce'
 * Nonce action : 'wp3dmv_nonce'
 */
class WP3DMV_AJAX {

	/**
	 * Allowed 3D model file extensions (whitelist).
	 *
	 * @since  1.0.0
	 * @var    string[]
	 */
	private static $allowed_extensions = array( 'glb', 'gltf' );

	/**
	 * Register WordPress AJAX hooks.
	 *
	 * Registers the action for both logged-in users (wp_ajax_*)
	 * and guests / non-logged-in visitors (wp_ajax_nopriv_*).
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_wp3dmv_get_model',        array( $this, 'get_model' ) );
		add_action( 'wp_ajax_nopriv_wp3dmv_get_model', array( $this, 'get_model' ) );
	}

	// =========================================================================
	// PUBLIC AJAX HANDLERS
	// =========================================================================

	/**
	 * AJAX handler: get_model
	 *
	 * Accepts either an attachment_id (integer) or a direct url (string)
	 * via $_POST.  Validates the nonce, sanitizes all input, resolves the
	 * final model URL, and returns it as a JSON response.
	 *
	 * POST params:
	 *   nonce         (string)  — wp3dmv_nonce value
	 *   attachment_id (int)     — WP Media Library attachment ID  [optional]
	 *   url           (string)  — direct URL to .glb / .gltf file [optional]
	 *
	 * One of attachment_id or url must be supplied; attachment_id takes priority.
	 *
	 * @since  1.0.0
	 * @return void  Terminates with wp_send_json_success() or wp_send_json_error().
	 */
	public function get_model() {

		// ── 1. Nonce verification ────────────────────────────────────────────
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'wp3dmv_nonce' ) ) {
			$this->send_error(
				__( 'Security check failed. Please refresh the page and try again.', 'wp3dmv' ),
				403
			);
		}

		// ── 2. Determine source: attachment_id or url ────────────────────────
		$attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
		$raw_url       = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';

		if ( empty( $attachment_id ) && empty( $raw_url ) ) {
			$this->send_error(
				__( 'No model source provided. Supply attachment_id or url.', 'wp3dmv' ),
				400
			);
		}

		// ── 3. Resolve URL ───────────────────────────────────────────────────
		$model_url = '';

		if ( ! empty( $attachment_id ) ) {
			// Path A — resolve from WordPress Media Library.
			$model_url = wp_get_attachment_url( $attachment_id );

			if ( false === $model_url ) {
				$this->send_error(
					__( 'Attachment not found.', 'wp3dmv' ),
					404
				);
			}
		} else {
			// Path B — use the supplied URL directly.
			$model_url = $raw_url;
		}

		// ── 4. Validate extension whitelist ──────────────────────────────────
		if ( ! $this->is_valid_model_url( $model_url ) ) {
			$this->send_error(
				__( 'Invalid file type. Only .glb and .gltf files are allowed.', 'wp3dmv' ),
				415
			);
		}

		// ── 5. Return success ────────────────────────────────────────────────
		$this->send_success(
			array(
				'url' => esc_url_raw( $model_url ),
			)
		);
	}

	// =========================================================================
	// PRIVATE HELPER METHODS
	// =========================================================================

	/**
	 * Validate a model URL.
	 *
	 * Checks that the URL:
	 *   (a) has a path that ends with an allowed extension (.glb or .gltf),
	 *   (b) is a valid, non-empty URL string.
	 *
	 * Does not block external URLs outright (the viewer may load from a CDN),
	 * but the caller should ensure the URL originates from a trusted source.
	 *
	 * @since  1.0.0
	 * @param  string $url  The URL to validate.
	 * @return bool         True if the URL passes validation, false otherwise.
	 */
	private function is_valid_model_url( $url ) {

		if ( empty( $url ) || ! is_string( $url ) ) {
			return false;
		}

		// Extract only the path component to ignore query strings / fragments.
		$parsed = wp_parse_url( $url );

		if ( empty( $parsed['path'] ) ) {
			return false;
		}

		$path      = strtolower( $parsed['path'] );
		$extension = pathinfo( $path, PATHINFO_EXTENSION );

		return in_array( $extension, self::$allowed_extensions, true );
	}

	/**
	 * Send a successful JSON response and terminate execution.
	 *
	 * Wraps wp_send_json_success() to ensure a consistent response shape
	 * and enforces wp_die() as per WordPress AJAX best practices.
	 *
	 * @since  1.0.0
	 * @param  array $data  Associative array of data to include in the response.
	 * @return void
	 */
	private function send_success( $data ) {
		wp_send_json_success( $data );
		die(); // Explicit die() — required after wp_send_json_* per WP best practices.
	}

	/**
	 * Send an error JSON response and terminate execution.
	 *
	 * Wraps wp_send_json_error() to ensure a consistent error shape
	 * and enforces wp_die() as per WordPress AJAX best practices.
	 *
	 * @since  1.0.0
	 * @param  string $message  Human-readable error message (already translatable).
	 * @param  int    $code     HTTP-style status code (e.g. 400, 403, 404, 415).
	 * @return void
	 */
	private function send_error( $message, $code = 400 ) {
		wp_send_json_error(
			array(
				'message' => $message,
				'code'    => $code,
			)
		);
		die(); // Explicit die() — required after wp_send_json_* per WP best practices.
	}
}
