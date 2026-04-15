<?php
/**
 * Settings Class — WP 3D Model Viewer
 *
 * Registers, sanitises, and renders all plugin settings using
 * the WordPress Settings API. Three sections exactly as defined
 * in Master Architecture Section 8:
 *   — General
 *   — 3D Viewer Defaults
 *   — Performance
 *
 * No WooCommerce dependencies.
 *
 * @package    WP3D_Model_Viewer
 * @subpackage WP3D_Model_Viewer/admin
 * @author     Webwecreate
 * @version    1.0.1
 * @since      1.0.0
 *
 * Changelog:
 *   1.0.1 — 2026-04-12 — Created fresh (Part 2). Fields match Master Architecture
 *                          Section 8 exactly. No WooCommerce section.
 *                          Resolves Pending item from CHANGELOG v1.1.1.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP3DMV_Settings
 *
 * Handles Settings API registration, field callbacks, sanitisation,
 * and the settings page render. Stores all settings as a single
 * serialised array in wp_options under the key 'wp3dmv_settings'.
 *
 * Fields (Master Architecture Section 8):
 *
 * [General]
 *   default_bg_color       — colorpicker   default: #f5f5f5
 *   default_height         — number (px)   default: 400
 *   show_controls_hint     — checkbox      default: true
 *   enable_fullscreen      — checkbox      default: true
 *
 * [3D Viewer Defaults]
 *   auto_rotate            — checkbox      default: true
 *   rotation_speed         — range 0.1–5   default: 1.0
 *   enable_zoom            — checkbox      default: true
 *   camera_distance        — number        default: 3
 *
 * [Performance]
 *   lazy_load              — checkbox      default: true
 *   max_texture_size       — select        default: 1024 / 2048 / 4096
 *
 * @since 1.0.0
 */
class WP3DMV_Settings {

	/**
	 * WordPress option key — single serialised array.
	 *
	 * @since  1.0.0
	 * @var    string
	 */
	const OPTION_KEY = 'wp3dmv_settings';

	/**
	 * Settings page slug used when registering sections/fields.
	 *
	 * @since  1.0.0
	 * @var    string
	 */
	const PAGE_SLUG = 'wp3dmv-settings';

	/**
	 * Cached option values merged with defaults.
	 *
	 * @since  1.0.0
	 * @var    array
	 */
	private array $options = [];

	/**
	 * Constructor.
	 *
	 * Loads saved options, merging with defaults so new fields
	 * always have a fallback value.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$saved         = get_option( self::OPTION_KEY, [] );
		$this->options = wp_parse_args( $saved, $this->get_defaults() );
	}

	/**
	 * Register admin_init hook.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init(): void {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	// =========================================================================
	// Registration
	// =========================================================================

	/**
	 * Register the plugin option, sections, and fields.
	 *
	 * Uses the WordPress Settings API with three sections:
	 *   wp3dmv_general, wp3dmv_viewer_defaults, wp3dmv_performance
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			self::PAGE_SLUG,
			self::OPTION_KEY,
			[
				'sanitize_callback' => [ $this, 'sanitize_options' ],
				'default'           => $this->get_defaults(),
			]
		);

		$this->register_general_section();
		$this->register_viewer_defaults_section();
		$this->register_performance_section();
	}

	/**
	 * Register the General section and its fields.
	 *
	 * Fields: default_bg_color, default_height,
	 *         show_controls_hint, enable_fullscreen
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_general_section(): void {
		add_settings_section(
			'wp3dmv_general',
			__( 'General', 'wp-3d-model-viewer' ),
			[ $this, 'render_general_section_desc' ],
			self::PAGE_SLUG
		);

		add_settings_field(
			'default_bg_color',
			__( 'Default Background Color', 'wp-3d-model-viewer' ),
			[ $this, 'render_default_bg_color_field' ],
			self::PAGE_SLUG,
			'wp3dmv_general'
		);

		add_settings_field(
			'default_height',
			__( 'Default Height (px)', 'wp-3d-model-viewer' ),
			[ $this, 'render_default_height_field' ],
			self::PAGE_SLUG,
			'wp3dmv_general'
		);

		add_settings_field(
			'show_controls_hint',
			__( 'Show Controls Hint', 'wp-3d-model-viewer' ),
			[ $this, 'render_show_controls_hint_field' ],
			self::PAGE_SLUG,
			'wp3dmv_general'
		);

		add_settings_field(
			'enable_fullscreen',
			__( 'Enable Fullscreen Button', 'wp-3d-model-viewer' ),
			[ $this, 'render_enable_fullscreen_field' ],
			self::PAGE_SLUG,
			'wp3dmv_general'
		);
	}

	/**
	 * Register the 3D Viewer Defaults section and its fields.
	 *
	 * Fields: auto_rotate, rotation_speed, enable_zoom, camera_distance
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_viewer_defaults_section(): void {
		add_settings_section(
			'wp3dmv_viewer_defaults',
			__( '3D Viewer Defaults', 'wp-3d-model-viewer' ),
			[ $this, 'render_viewer_defaults_section_desc' ],
			self::PAGE_SLUG
		);

		add_settings_field(
			'auto_rotate',
			__( 'Auto Rotate', 'wp-3d-model-viewer' ),
			[ $this, 'render_auto_rotate_field' ],
			self::PAGE_SLUG,
			'wp3dmv_viewer_defaults'
		);

		add_settings_field(
			'rotation_speed',
			__( 'Rotation Speed', 'wp-3d-model-viewer' ),
			[ $this, 'render_rotation_speed_field' ],
			self::PAGE_SLUG,
			'wp3dmv_viewer_defaults'
		);

		add_settings_field(
			'enable_zoom',
			__( 'Enable Zoom', 'wp-3d-model-viewer' ),
			[ $this, 'render_enable_zoom_field' ],
			self::PAGE_SLUG,
			'wp3dmv_viewer_defaults'
		);

		add_settings_field(
			'camera_distance',
			__( 'Initial Camera Distance', 'wp-3d-model-viewer' ),
			[ $this, 'render_camera_distance_field' ],
			self::PAGE_SLUG,
			'wp3dmv_viewer_defaults'
		);
	}

	/**
	 * Register the Performance section and its fields.
	 *
	 * Fields: lazy_load, max_texture_size
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_performance_section(): void {
		add_settings_section(
			'wp3dmv_performance',
			__( 'Performance', 'wp-3d-model-viewer' ),
			[ $this, 'render_performance_section_desc' ],
			self::PAGE_SLUG
		);

		add_settings_field(
			'lazy_load',
			__( 'Lazy Load', 'wp-3d-model-viewer' ),
			[ $this, 'render_lazy_load_field' ],
			self::PAGE_SLUG,
			'wp3dmv_performance'
		);

		add_settings_field(
			'max_texture_size',
			__( 'Max Texture Size', 'wp-3d-model-viewer' ),
			[ $this, 'render_max_texture_size_field' ],
			self::PAGE_SLUG,
			'wp3dmv_performance'
		);
	}

	// =========================================================================
	// Section Description Callbacks
	// =========================================================================

	/** @since 1.0.0 */
	public function render_general_section_desc(): void {
		echo '<p class="description">'
			. esc_html__( 'Global appearance and UI settings applied to all viewer instances.', 'wp-3d-model-viewer' )
			. '</p>';
	}

	/** @since 1.0.0 */
	public function render_viewer_defaults_section_desc(): void {
		echo '<p class="description">'
			. esc_html__( 'Default Three.js viewer behaviour. Can be overridden per-shortcode.', 'wp-3d-model-viewer' )
			. '</p>';
	}

	/** @since 1.0.0 */
	public function render_performance_section_desc(): void {
		echo '<p class="description">'
			. esc_html__( 'Options that affect rendering performance and asset loading.', 'wp-3d-model-viewer' )
			. '</p>';
	}

	// =========================================================================
	// Field Callbacks — General
	// =========================================================================

	/**
	 * Render: Default Background Color (colorpicker).
	 *
	 * @since 1.0.0
	 */
	public function render_default_bg_color_field(): void {
		$value = $this->get( 'default_bg_color' );
		?>
		<input
			type="color"
			id="wp3dmv_default_bg_color"
			name="<?php echo esc_attr( self::OPTION_KEY ); ?>[default_bg_color]"
			value="<?php echo esc_attr( $value ); ?>"
		/>
		<p class="description">
			<?php
			printf(
				/* translators: %s: current hex color value */
				esc_html__( 'Canvas background color. Current: %s', 'wp-3d-model-viewer' ),
				'<code>' . esc_html( $value ) . '</code>'
			);
			?>
		</p>
		<?php
	}

	/**
	 * Render: Default Height in px (number input).
	 *
	 * @since 1.0.0
	 */
	public function render_default_height_field(): void {
		$value = $this->get( 'default_height' );
		?>
		<input
			type="number"
			id="wp3dmv_default_height"
			name="<?php echo esc_attr( self::OPTION_KEY ); ?>[default_height]"
			value="<?php echo esc_attr( $value ); ?>"
			class="small-text"
			min="100"
			max="2000"
			step="10"
		/>
		<span class="description"><?php esc_html_e( 'px — height of the viewer canvas.', 'wp-3d-model-viewer' ); ?></span>
		<?php
	}

	/**
	 * Render: Show Controls Hint (checkbox).
	 *
	 * @since 1.0.0
	 */
	public function render_show_controls_hint_field(): void {
		$this->render_checkbox(
			'show_controls_hint',
			__( 'Show "drag to rotate / scroll to zoom" hint overlay', 'wp-3d-model-viewer' )
		);
	}

	/**
	 * Render: Enable Fullscreen Button (checkbox).
	 *
	 * @since 1.0.0
	 */
	public function render_enable_fullscreen_field(): void {
		$this->render_checkbox(
			'enable_fullscreen',
			__( 'Show fullscreen toggle button on the viewer', 'wp-3d-model-viewer' )
		);
	}

	// =========================================================================
	// Field Callbacks — 3D Viewer Defaults
	// =========================================================================

	/**
	 * Render: Auto Rotate (checkbox).
	 *
	 * @since 1.0.0
	 */
	public function render_auto_rotate_field(): void {
		$this->render_checkbox(
			'auto_rotate',
			__( 'Rotate model automatically on load', 'wp-3d-model-viewer' )
		);
	}

	/**
	 * Render: Rotation Speed (range slider 0.1–5, step 0.1).
	 *
	 * @since 1.0.0
	 */
	public function render_rotation_speed_field(): void {
		$value = $this->get( 'rotation_speed' );
		?>
		<input
			type="range"
			id="wp3dmv_rotation_speed"
			name="<?php echo esc_attr( self::OPTION_KEY ); ?>[rotation_speed]"
			value="<?php echo esc_attr( $value ); ?>"
			min="0.1"
			max="5"
			step="0.1"
			oninput="document.getElementById('wp3dmv_rotation_speed_display').textContent = this.value"
		/>
		<span id="wp3dmv_rotation_speed_display"><?php echo esc_html( $value ); ?></span>
		<p class="description"><?php esc_html_e( 'OrbitControls autoRotateSpeed (0.1 – 5). Default: 1.0', 'wp-3d-model-viewer' ); ?></p>
		<?php
	}

	/**
	 * Render: Enable Zoom (checkbox).
	 *
	 * @since 1.0.0
	 */
	public function render_enable_zoom_field(): void {
		$this->render_checkbox(
			'enable_zoom',
			__( 'Allow scroll-wheel / pinch-to-zoom (OrbitControls.enableZoom)', 'wp-3d-model-viewer' )
		);
	}

	/**
	 * Render: Initial Camera Distance (number).
	 *
	 * @since 1.0.0
	 */
	public function render_camera_distance_field(): void {
		$value = $this->get( 'camera_distance' );
		?>
		<input
			type="number"
			id="wp3dmv_camera_distance"
			name="<?php echo esc_attr( self::OPTION_KEY ); ?>[camera_distance]"
			value="<?php echo esc_attr( $value ); ?>"
			class="small-text"
			min="1"
			max="20"
			step="0.5"
		/>
		<p class="description"><?php esc_html_e( 'Starting camera distance from model centre (Three.js units). Default: 3', 'wp-3d-model-viewer' ); ?></p>
		<?php
	}

	// =========================================================================
	// Field Callbacks — Performance
	// =========================================================================

	/**
	 * Render: Lazy Load (checkbox).
	 *
	 * @since 1.0.0
	 */
	public function render_lazy_load_field(): void {
		$this->render_checkbox(
			'lazy_load',
			__( 'Initialise viewer only when scrolled into view (IntersectionObserver)', 'wp-3d-model-viewer' )
		);
	}

	/**
	 * Render: Max Texture Size (select: 1024 / 2048 / 4096).
	 *
	 * @since 1.0.0
	 */
	public function render_max_texture_size_field(): void {
		$value = (int) $this->get( 'max_texture_size' );
		$sizes = [ 1024, 2048, 4096 ];
		?>
		<select
			id="wp3dmv_max_texture_size"
			name="<?php echo esc_attr( self::OPTION_KEY ); ?>[max_texture_size]"
		>
			<?php foreach ( $sizes as $size ) : ?>
				<option value="<?php echo esc_attr( $size ); ?>" <?php selected( $value, $size ); ?>>
					<?php echo esc_html( $size . ' px' ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Textures are downsampled to this limit. Lower = better on mobile. Default: 2048', 'wp-3d-model-viewer' ); ?></p>
		<?php
	}

	// =========================================================================
	// Page Render
	// =========================================================================

	/**
	 * Render the full settings page HTML.
	 *
	 * Uses settings_fields() / do_settings_sections() so all
	 * registered sections and fields are output automatically.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_page(): void {
		?>
		<div class="wrap wp3dmv-settings-wrap">
			<h1><?php esc_html_e( 'WP 3D Model Viewer — Settings', 'wp-3d-model-viewer' ); ?></h1>

			<?php //settings_errors( self::OPTION_KEY ); ?>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( self::PAGE_SLUG );
				do_settings_sections( self::PAGE_SLUG );
				submit_button( __( 'Save Settings', 'wp-3d-model-viewer' ) );
				?>
			</form>
		</div>
		<?php
	}

	// =========================================================================
	// Sanitisation
	// =========================================================================

	/**
	 * Sanitise all settings before saving to the database.
	 *
	 * Every field is explicitly sanitised to its expected type.
	 * Unknown keys are dropped; missing checkboxes default to '0'.
	 *
	 * @since 1.0.0
	 * @param array $raw Raw POST data from the settings form.
	 * @return array Sanitised settings array.
	 */
	public function sanitize_options( array $raw ): array {
		$clean    = [];
		$defaults = $this->get_defaults();

		// --- General ---
		$clean['default_bg_color'] = $this->sanitize_hex_color(
			$raw['default_bg_color'] ?? $defaults['default_bg_color']
		);

		$clean['default_height'] = $this->sanitize_int_range(
			$raw['default_height'] ?? $defaults['default_height'],
			100,
			2000,
			(int) $defaults['default_height']
		);

		$clean['show_controls_hint'] = isset( $raw['show_controls_hint'] ) ? '1' : '0';
		$clean['enable_fullscreen']  = isset( $raw['enable_fullscreen'] )  ? '1' : '0';

		// --- 3D Viewer Defaults ---
		$clean['auto_rotate'] = isset( $raw['auto_rotate'] ) ? '1' : '0';

		$clean['rotation_speed'] = $this->sanitize_float_range(
			$raw['rotation_speed'] ?? $defaults['rotation_speed'],
			0.1,
			5.0,
			(float) $defaults['rotation_speed']
		);

		$clean['enable_zoom'] = isset( $raw['enable_zoom'] ) ? '1' : '0';

		$clean['camera_distance'] = $this->sanitize_float_range(
			$raw['camera_distance'] ?? $defaults['camera_distance'],
			1.0,
			20.0,
			(float) $defaults['camera_distance']
		);

		// --- Performance ---
		$clean['lazy_load'] = isset( $raw['lazy_load'] ) ? '1' : '0';

		$allowed_sizes = [ 1024, 2048, 4096 ];
		$raw_size      = (int) ( $raw['max_texture_size'] ?? $defaults['max_texture_size'] );
		$clean['max_texture_size'] = in_array( $raw_size, $allowed_sizes, true )
			? $raw_size
			: (int) $defaults['max_texture_size'];

		return $clean;
	}

	// =========================================================================
	// Defaults & Public API
	// =========================================================================

	/**
	 * Return the plugin default settings.
	 *
	 * Single source of truth. Keys match Master Architecture
	 * Section 4 (Database Schema) and Section 8 (Settings Page).
	 *
	 * @since 1.0.0
	 * @return array Default settings array.
	 */
	public function get_defaults(): array {
		return [
			// General
			'default_bg_color'    => '#f5f5f5',
			'default_height'      => 400,
			'show_controls_hint'  => '1',
			'enable_fullscreen'   => '1',

			// 3D Viewer Defaults
			'auto_rotate'         => '1',
			'rotation_speed'      => '1.0',
			'enable_zoom'         => '1',
			'camera_distance'     => '3',

			// Performance
			'lazy_load'           => '1',
			'max_texture_size'    => 2048,
		];
	}

	/**
	 * Get a single option value by key.
	 *
	 * @since 1.0.0
	 * @param string $key     Option key.
	 * @param mixed  $default Fallback if key not present.
	 * @return mixed Option value or fallback.
	 */
	public function get( string $key, $default = null ) {
		return $this->options[ $key ] ?? ( $default ?? $this->get_defaults()[ $key ] ?? null );
	}

	/**
	 * Get the entire options array.
	 *
	 * @since 1.0.0
	 * @return array All current option values.
	 */
	public function get_all(): array {
		return $this->options;
	}

	// =========================================================================
	// Private Sanitisation Helpers
	// =========================================================================

	/**
	 * Sanitise a hex colour string.
	 *
	 * @since 1.0.0
	 * @param string $color    Raw input.
	 * @param string $fallback Default if invalid.
	 * @return string Valid lowercase hex colour.
	 */
	private function sanitize_hex_color( string $color, string $fallback = '#f5f5f5' ): string {
		$color = trim( $color );

		if ( preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color ) ) {
			return strtolower( $color );
		}

		return $fallback;
	}

	/**
	 * Sanitise a float within a min/max range.
	 *
	 * @since 1.0.0
	 * @param mixed $value    Raw input.
	 * @param float $min      Minimum.
	 * @param float $max      Maximum.
	 * @param float $fallback Default if out of range.
	 * @return float Sanitised value.
	 */
	private function sanitize_float_range( $value, float $min, float $max, float $fallback ): float {
		$float = (float) $value;

		if ( $float < $min || $float > $max ) {
			return $fallback;
		}

		return $float;
	}

	/**
	 * Sanitise an integer within a min/max range.
	 *
	 * @since 1.0.0
	 * @param mixed $value    Raw input.
	 * @param int   $min      Minimum.
	 * @param int   $max      Maximum.
	 * @param int   $fallback Default if out of range.
	 * @return int Sanitised value.
	 */
	private function sanitize_int_range( $value, int $min, int $max, int $fallback ): int {
		$int = (int) $value;

		if ( $int < $min || $int > $max ) {
			return $fallback;
		}

		return $int;
	}

	/**
	 * Render a reusable checkbox field.
	 *
	 * @since 1.0.0
	 * @param string $key   Option key.
	 * @param string $label Label text.
	 * @return void
	 */
	private function render_checkbox( string $key, string $label ): void {
		$value = $this->get( $key );
		?>
		<label for="wp3dmv_<?php echo esc_attr( $key ); ?>">
			<input
				type="checkbox"
				id="wp3dmv_<?php echo esc_attr( $key ); ?>"
				name="<?php echo esc_attr( self::OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]"
				value="1"
				<?php checked( '1', $value ); ?>
			/>
			<?php echo esc_html( $label ); ?>
		</label>
		<?php
	}
}
