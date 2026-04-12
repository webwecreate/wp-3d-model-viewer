<?php
/**
 * Admin Class — WP 3D Model Viewer
 *
 * Registers the admin menu, settings page link, and enqueues
 * admin-side scripts and styles. No WooCommerce dependencies.
 *
 * @package    WP3D_Model_Viewer
 * @subpackage WP3D_Model_Viewer/admin
 * @author     Webwecreate
 * @version    1.0.1
 * @since      1.0.0
 *
 * Changelog:
 *   1.0.1 — 2026-04-12 — Created fresh (Part 2). No WooCommerce references.
 *                          Resolves Pending item from CHANGELOG v1.1.1.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP3DMV_Admin
 *
 * Handles all WordPress admin hooks: menu registration,
 * admin asset enqueueing, and settings page rendering delegation.
 *
 * @since 1.0.0
 */
class WP3DMV_Admin {

	/**
	 * Plugin version.
	 *
	 * @since  1.0.0
	 * @var    string
	 */
	private string $version;

	/**
	 * Settings instance.
	 *
	 * @since  1.0.0
	 * @var    WP3DMV_Settings
	 */
	private WP3DMV_Settings $settings;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param string          $version  Current plugin version.
	 * @param WP3DMV_Settings $settings Settings instance.
	 */
	public function __construct( string $version, WP3DMV_Settings $settings ) {
		$this->version  = $version;
		$this->settings = $settings;
	}

	/**
	 * Register all admin hooks.
	 *
	 * Called by WP3DMV_Core. Wires all admin-facing WordPress hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init(): void {
		add_action( 'admin_menu',            [ $this, 'register_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter(
			'plugin_action_links_' . WP3DMV_PLUGIN_BASENAME,
			[ $this, 'add_settings_link' ]
		);
	}

	// =========================================================================
	// Menu Registration
	// =========================================================================

	/**
	 * Register the top-level admin menu and Settings sub-page.
	 *
	 * Creates "3D Model Viewer" in the WordPress admin sidebar
	 * with a "Settings" child item.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_admin_menu(): void {
		add_menu_page(
			__( 'WP 3D Model Viewer', 'wp-3d-model-viewer' ),
			__( '3D Model Viewer', 'wp-3d-model-viewer' ),
			'manage_options',
			'wp3dmv',
			[ $this, 'render_main_page' ],
			'dashicons-format-image',
			80
		);

		add_submenu_page(
			'wp3dmv',
			__( 'Settings — WP 3D Model Viewer', 'wp-3d-model-viewer' ),
			__( 'Settings', 'wp-3d-model-viewer' ),
			'manage_options',
			'wp3dmv-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	// =========================================================================
	// Page Renderers
	// =========================================================================

	/**
	 * Render the main admin dashboard page.
	 *
	 * Simple landing page directing users to Settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_main_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'wp-3d-model-viewer' ) );
		}
		?>
		<div class="wrap wp3dmv-admin-wrap">
			<h1><?php esc_html_e( 'WP 3D Model Viewer', 'wp-3d-model-viewer' ); ?></h1>
			<p><?php esc_html_e( 'Welcome! Configure your 3D viewer defaults on the Settings page.', 'wp-3d-model-viewer' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wp3dmv-settings' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Go to Settings', 'wp-3d-model-viewer' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Render the Settings page.
	 *
	 * Delegates rendering to WP3DMV_Settings::render_page().
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'wp-3d-model-viewer' ) );
		}

		$this->settings->render_page();
	}

	// =========================================================================
	// Asset Enqueueing
	// =========================================================================

	/**
	 * Enqueue admin stylesheets.
	 *
	 * CSS loaded only on plugin-owned admin pages.
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_styles( string $hook_suffix ): void {
		if ( ! $this->is_plugin_page( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_style(
			'wp3dmv-admin',
			WP3DMV_PLUGIN_URL . 'admin/css/wp3dmv-admin.css',
			[],
			$this->version
		);
	}

	/**
	 * Enqueue admin JavaScript.
	 *
	 * JS loaded only on plugin-owned admin pages, with localised data.
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_scripts( string $hook_suffix ): void {
		if ( ! $this->is_plugin_page( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_script(
			'wp3dmv-admin',
			WP3DMV_PLUGIN_URL . 'admin/js/wp3dmv-admin.js',
			[ 'jquery' ],
			$this->version,
			true
		);

		wp_localize_script(
			'wp3dmv-admin',
			'wp3dmv_admin',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wp3dmv_admin_nonce' ),
				'i18n'     => [
					'confirm_reset' => __( 'Reset all settings to defaults? This cannot be undone.', 'wp-3d-model-viewer' ),
				],
			]
		);
	}

	// =========================================================================
	// Plugin Action Links
	// =========================================================================

	/**
	 * Add a "Settings" link to the plugin row on the Plugins screen.
	 *
	 * @since 1.0.0
	 * @param array $links Existing plugin action links.
	 * @return array Modified links with Settings prepended.
	 */
	public function add_settings_link( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=wp3dmv-settings' ) ),
			esc_html__( 'Settings', 'wp-3d-model-viewer' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}

	// =========================================================================
	// Helpers
	// =========================================================================

	/**
	 * Determine whether the current page belongs to this plugin.
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix WordPress admin page hook suffix.
	 * @return bool True if on a plugin page.
	 */
	private function is_plugin_page( string $hook_suffix ): bool {
		$plugin_pages = [
			'toplevel_page_wp3dmv',
			'3d-model-viewer_page_wp3dmv-settings',
		];

		return in_array( $hook_suffix, $plugin_pages, true );
	}
}
