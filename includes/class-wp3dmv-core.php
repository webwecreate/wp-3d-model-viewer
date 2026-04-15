<?php
/**
 * Core Plugin Class — bootstraps all modules.
 *
 * Follows a singleton pattern. Loads all sub-classes and registers
 * hooks through WP3DMV_Loader.
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/includes
 * @version    1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP3DMV_Core {

    // ─── Singleton ─────────────────────────────────────────────────────────────

    /** @var WP3DMV_Core */
    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ─── Properties ────────────────────────────────────────────────────────────

    /** @var WP3DMV_Loader */
    protected $loader;

    /** @var string */
    protected $version;

    // ─── Constructor ───────────────────────────────────────────────────────────

    private function __construct() {
        $this->version = WP3DMV_VERSION;
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_elementor_hooks();
        $this->loader->run();
    }

    // ─── Dependency Loading ────────────────────────────────────────────────────

    private function load_dependencies() {
        // Admin
        $this->maybe_load( WP3DMV_PLUGIN_DIR . 'admin/class-wp3dmv-admin.php' );
        $this->maybe_load( WP3DMV_PLUGIN_DIR . 'admin/class-wp3dmv-settings.php' );

        // Public
        $this->maybe_load( WP3DMV_PLUGIN_DIR . 'public/class-wp3dmv-public.php' );
        $this->maybe_load( WP3DMV_PLUGIN_DIR . 'includes/class-wp3dmv-viewer.php' );
        $this->maybe_load( WP3DMV_PLUGIN_DIR . 'includes/class-wp3dmv-ajax.php' );

        // Elementor
        $this->maybe_load( WP3DMV_PLUGIN_DIR . 'elementor/class-wp3dmv-elementor.php' );

        // Instantiate loader
        $this->loader = new WP3DMV_Loader();
    }

    private function maybe_load( $file ) {
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }

    // ─── Locale ───────────────────────────────────────────────────────────────

    private function set_locale() {
        $plugin_i18n = new WP3DMV_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    // ─── Admin Hooks ──────────────────────────────────────────────────────────

    private function define_admin_hooks() {
        if ( ! class_exists( 'WP3DMV_Admin' ) ) {
            return;
        }

        // Settings must be instantiated first — Admin constructor requires it.
        $plugin_settings = class_exists( 'WP3DMV_Settings' ) ? new WP3DMV_Settings() : null;

        if ( ! $plugin_settings ) {
            return;
        }

        $plugin_admin = new WP3DMV_Admin( $this->version, $plugin_settings );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin,    'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin,    'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu',            $plugin_admin,    'register_admin_menu' );
        $this->loader->add_action( 'admin_init',            $plugin_settings, 'register_settings' );
    }

    // ─── Public Hooks ─────────────────────────────────────────────────────────

    private function define_public_hooks() {
        if ( ! class_exists( 'WP3DMV_Public' ) ) {
            return;
        }

        $plugin_public = new WP3DMV_Public( $this->version );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'init',               $plugin_public, 'register_shortcodes' );

        if ( class_exists( 'WP3DMV_AJAX' ) ) {
            $plugin_ajax = new WP3DMV_AJAX();
            $this->loader->add_action( 'wp_ajax_wp3dmv_get_model',        $plugin_ajax, 'get_model' );
            $this->loader->add_action( 'wp_ajax_nopriv_wp3dmv_get_model', $plugin_ajax, 'get_model' );
        }
    }

    // ─── Elementor Hooks ──────────────────────────────────────────────────────

    private function define_elementor_hooks() {
        if ( ! did_action( 'elementor/loaded' ) || ! class_exists( 'WP3DMV_Elementor' ) ) {
            add_action( 'elementor/loaded', array( $this, 'init_elementor' ) );
            return;
        }
        $this->init_elementor();
    }

    public function init_elementor() {
        if ( ! class_exists( 'WP3DMV_Elementor' ) ) {
            return;
        }
        $plugin_elementor = new WP3DMV_Elementor();
        $this->loader->add_action( 'elementor/widgets/register',              $plugin_elementor, 'register_widgets' );
        $this->loader->add_action( 'elementor/elements/categories_registered', $plugin_elementor, 'add_category' );
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function get_loader()  { return $this->loader; }
    public function get_version() { return $this->version; }
}