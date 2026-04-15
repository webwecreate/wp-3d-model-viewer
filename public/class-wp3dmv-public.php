<?php
/**
 * Public-facing functionality of the plugin.
 *
 * Handles enqueuing of frontend scripts/styles and shortcode registration.
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/public
 * @version    1.0.2
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP3DMV_Public
 *
 * Manages all public-facing (frontend) concerns:
 * - Enqueue CSS
 * - Enqueue Three.js, OrbitControls, GLTFLoader, and viewer JS
 * - Register the [wp3dmv_viewer] shortcode
 */
class WP3DMV_Public {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * Constructor.
	 *
	 * @param string $version Plugin version string.
	 */
	public function __construct( string $version ) {
		$this->version = $version;
	}

	// -------------------------------------------------------------------------
	// Scripts & Styles
	// -------------------------------------------------------------------------

	/**
	 * Enqueue public CSS.
	 *
	 * Hook: wp_enqueue_scripts
	 */
	public function enqueue_styles(): void {
		wp_enqueue_style(
			'wp3dmv-public',
			WP3DMV_PLUGIN_URL . 'public/css/wp3dmv-public.css',
			[],
			$this->version
		);
	}

	/**
	 * Enqueue public JavaScript files.
	 *
	 * Load order (must be preserved — each script depends on the one above):
	 *   1. three.min.js        (no deps)
	 *   2. OrbitControls.js    (depends on three)
	 *   3. GLTFLoader.js       (depends on three)
	 *   4. wp3dmv-controls.js  (depends on three + orbit)
	 *   5. wp3dmv-loader.js    (depends on three + gltf)
	 *   6. wp3dmv-viewer.js    (depends on all of the above)
	 *
	 * Hook: wp_enqueue_scripts
	 */
	public function enqueue_scripts(): void {

		// 1. Three.js r158 core library.
		wp_enqueue_script(
			'wp3dmv-three',
			WP3DMV_PLUGIN_URL . 'assets/vendor/three/three.min.js',
			[],
			'147',
			true
		);

		// 2. OrbitControls addon (depends on Three.js).
		wp_enqueue_script(
			'wp3dmv-orbit',
			WP3DMV_PLUGIN_URL . 'assets/vendor/three/OrbitControls.js',
			[ 'wp3dmv-three' ],
			'147',
			true
		);

		// 3. GLTFLoader addon (depends on Three.js) — required for .glb / .gltf loading.
		wp_enqueue_script(
			'wp3dmv-gltf',
			WP3DMV_PLUGIN_URL . 'assets/vendor/three/GLTFLoader.js',
			[ 'wp3dmv-three' ],
			'147',
			true
		);

		// 4. Orbit controls wrapper (depends on Three.js + OrbitControls).
		wp_enqueue_script(
			'wp3dmv-controls',
			WP3DMV_PLUGIN_URL . 'public/js/wp3dmv-controls.js',
			[ 'wp3dmv-three', 'wp3dmv-orbit' ],
			$this->version,
			true
		);

		// 5. Model loader (depends on Three.js + GLTFLoader).
		wp_enqueue_script(
			'wp3dmv-loader',
			WP3DMV_PLUGIN_URL . 'public/js/wp3dmv-loader.js',
			[ 'wp3dmv-three', 'wp3dmv-gltf' ],
			$this->version,
			true
		);

		// 6. Main viewer controller (depends on all of the above).
		wp_enqueue_script(
			'wp3dmv-viewer',
			WP3DMV_PLUGIN_URL . 'public/js/wp3dmv-viewer.js',
			[ 'wp3dmv-three', 'wp3dmv-orbit', 'wp3dmv-gltf', 'wp3dmv-controls', 'wp3dmv-loader' ],
			$this->version,
			true
		);

		// Pass PHP settings to JavaScript.
		$settings = get_option( 'wp3dmv_settings', [] );
		wp_localize_script(
			'wp3dmv-viewer',
			'wp3dmvSettings',
			[
				'pluginUrl' => WP3DMV_PLUGIN_URL,
				'settings'  => $settings,
			]
		);
	}

	// -------------------------------------------------------------------------
	// Shortcodes
	// -------------------------------------------------------------------------

	/**
	 * Register all plugin shortcodes.
	 *
	 * Hook: init
	 */
	public function register_shortcodes(): void {
		add_shortcode( 'wp3dmv_viewer', [ $this, 'shortcode_viewer' ] );
	}

	/**
	 * Shortcode callback for [wp3dmv_viewer].
	 *
	 * Accepted attributes:
	 *   url        — URL to the .glb / .gltf file (required).
	 *   height     — Viewer height in px (default: 400).
	 *   bg         — Background colour hex (default: #f5f5f5).
	 *   autorotate — "true" / "false" (default: from plugin settings).
	 *
	 * Example:
	 *   [wp3dmv_viewer url="https://example.com/model.glb" height="500" bg="#ffffff" autorotate="true"]
	 *
	 * @param array|string $atts    Shortcode attributes.
	 * @param string|null  $content Inner content (unused).
	 * @return string              Rendered HTML or error message.
	 */
	public function shortcode_viewer( $atts, ?string $content = null ): string {

		// Normalise + apply defaults.
		$atts = shortcode_atts(
			[
				'url'        => '',
				'height'     => '',
				'bg'         => '',
				'autorotate' => '',
			],
			$atts,
			'wp3dmv_viewer'
		);

		// A model URL is required.
		if ( empty( $atts['url'] ) ) {
			return '<p class="wp3dmv-error">'
				. esc_html__( '[wp3dmv_viewer] requires a "url" attribute.', 'wp3dmv' )
				. '</p>';
		}

		// Map shortcode attributes → WP3DMV_Viewer::render() $args array.
		$args = [
			'url'         => $atts['url'],
			'height'      => $atts['height'],
			'bg_color'    => $atts['bg'],
			'auto_rotate' => $atts['autorotate'],
		];

		// Delegate rendering to the Viewer class.
		if ( ! class_exists( 'WP3DMV_Viewer' ) ) {
			return '<!-- WP3DMV_Viewer class not loaded -->';
		}

		return WP3DMV_Viewer::render( $args );
	}
}
