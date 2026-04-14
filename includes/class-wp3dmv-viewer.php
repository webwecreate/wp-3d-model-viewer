<?php
/**
 * Viewer render logic — generates the HTML for each 3D viewer instance.
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/includes
 * @version    1.0.0
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP3DMV_Viewer
 *
 * Responsible for:
 *   - Merging per-instance args with global plugin settings.
 *   - Sanitising all input values.
 *   - Generating a unique ID per viewer instance.
 *   - Loading viewer-template.php and returning the HTML string.
 */
class WP3DMV_Viewer {

	/**
	 * Counter used to create unique IDs within a single page load.
	 *
	 * @var int
	 */
	private static int $instance_counter = 0;

	// -------------------------------------------------------------------------
	// Public API
	// -------------------------------------------------------------------------

	/**
	 * Render a 3D viewer and return the HTML string.
	 *
	 * @param array $args {
	 *     Per-instance arguments.  All are optional except 'url'.
	 *
	 *     @type string $url         URL to the .glb / .gltf model file.
	 *     @type string $height      Viewer height in px, e.g. "400".
	 *     @type string $bg_color    Background colour hex, e.g. "#f5f5f5".
	 *     @type string $auto_rotate "true" | "false" | "" (use plugin default).
	 * }
	 * @return string Rendered HTML or error HTML.
	 */
	public static function render( array $args ): string {

		// 1. Load global settings from the database.
		$global_settings = self::get_global_settings();

		// 2. Merge: global defaults ← overridden by per-instance $args.
		$merged = self::merge_args( $args, $global_settings );

		// 3. Sanitise every value.
		$model_url = esc_url_raw( $merged['url'] );

		if ( empty( $model_url ) ) {
			return '<p class="wp3dmv-error">'
				. esc_html__( 'WP 3D Model Viewer: invalid or missing model URL.', 'wp3dmv' )
				. '</p>';
		}

		$settings = self::sanitize_settings( $merged );

		// 4. Generate unique ID for this instance.
		self::$instance_counter++;
		$unique_id = 'inst-' . self::$instance_counter . '-' . wp_generate_password( 6, false, false );

		// 5. Load template and capture output.
		$template_path = WP3DMV_PLUGIN_DIR . 'public/partials/viewer-template.php';

		if ( ! file_exists( $template_path ) ) {
			return '<!-- WP3DMV: viewer-template.php not found -->';
		}

		ob_start();
		include $template_path;
		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Retrieve and return the plugin's global settings array.
	 *
	 * Provides hardcoded fallbacks so the viewer works even before settings
	 * are saved for the first time.
	 *
	 * @return array
	 */
	private static function get_global_settings(): array {
		$saved = get_option( 'wp3dmv_settings', [] );

		$defaults = [
			'default_bg_color'      => '#f5f5f5',
			'default_height'        => 400,
			'default_auto_rotate'   => true,
			'default_rotation_speed'=> 1.0,
			'enable_zoom'           => true,
			'enable_fullscreen'     => true,
			'show_controls_hint'    => true,
			'loading_text'          => __( 'กำลังโหลด...', 'wp3dmv' ),
			'camera_distance'       => 3,
		];

		return wp_parse_args( $saved, $defaults );
	}

	/**
	 * Merge per-instance $args on top of $global_settings.
	 *
	 * Empty / unset per-instance values fall back to the global default.
	 *
	 * @param array $args            Per-instance arguments.
	 * @param array $global_settings Plugin-wide settings.
	 * @return array                 Merged array ready for sanitisation.
	 */
	private static function merge_args( array $args, array $global_settings ): array {

		// URL — no global fallback.
		$url = isset( $args['url'] ) ? trim( $args['url'] ) : '';

		// Height.
		$height = ( isset( $args['height'] ) && '' !== $args['height'] )
			? $args['height']
			: $global_settings['default_height'];

		// Background colour.
		$bg_color = ( isset( $args['bg_color'] ) && '' !== $args['bg_color'] )
			? $args['bg_color']
			: $global_settings['default_bg_color'];

		// Auto-rotate: accept "true"/"false" string or boolean.
		if ( isset( $args['auto_rotate'] ) && '' !== $args['auto_rotate'] ) {
			$auto_rotate = self::parse_bool( $args['auto_rotate'] );
		} else {
			$auto_rotate = (bool) $global_settings['default_auto_rotate'];
		}

		return [
			'url'             => $url,
			'height'          => $height,
			'bg_color'        => $bg_color,
			'auto_rotate'     => $auto_rotate,
			'rotation_speed'  => (float) $global_settings['default_rotation_speed'],
			'enable_zoom'     => (bool)  $global_settings['enable_zoom'],
			'enable_fullscreen' => (bool) $global_settings['enable_fullscreen'],
			'show_controls_hint'=> (bool) $global_settings['show_controls_hint'],
			'loading_text'    => $global_settings['loading_text'],
			'camera_distance' => (float) $global_settings['camera_distance'],
		];
	}

	/**
	 * Sanitise the merged settings array.
	 *
	 * @param array $merged Merged (unsanitised) settings.
	 * @return array        Sanitised settings — safe for HTML output.
	 */
	private static function sanitize_settings( array $merged ): array {
		return [
			'url'               => esc_url( $merged['url'] ),
			'height'            => absint( $merged['height'] ) ?: 400,
			'bg_color'          => sanitize_hex_color( $merged['bg_color'] ) ?: '#f5f5f5',
			'auto_rotate'       => (bool) $merged['auto_rotate'],
			'rotation_speed'    => min( max( (float) $merged['rotation_speed'], 0.1 ), 5.0 ),
			'enable_zoom'       => (bool) $merged['enable_zoom'],
			'enable_fullscreen' => (bool) $merged['enable_fullscreen'],
			'show_controls_hint'=> (bool) $merged['show_controls_hint'],
			'loading_text'      => sanitize_text_field( $merged['loading_text'] ),
			'camera_distance'   => min( max( (float) $merged['camera_distance'], 0.5 ), 20.0 ),
		];
	}

	/**
	 * Parse a value that might be a boolean or the strings "true" / "false".
	 *
	 * @param mixed $value The raw value.
	 * @return bool
	 */
	private static function parse_bool( $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}
		return in_array( strtolower( (string) $value ), [ 'true', '1', 'yes' ], true );
	}
}
