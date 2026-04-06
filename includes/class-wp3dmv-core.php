<?php
/**
 * Core Plugin Class — bootstraps all modules.
 *
 * Follows a singleton pattern. Loads all sub-classes and registers
 * hooks through WP3DMV_Loader.
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/includes
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP3DMV_Core {

    // ─── Singleton ─────────────────────────────────────────────────────────────

    /**
     * @var WP3DMV_Core
     */
    private static $instance = null;

    /**
     * Returns the single instance of the class.
     *
     * @return WP3DMV_Core
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ─── Properties ────────────────────────────────────────────────────────────

    /** @var WP3DMV_Loader */
    protected $loader;

    /** @var string Plugin version */
    protected $version;

    // ─── Constructor ───────────────────────────────────────────────────────────

    /**
     * Private constructor — use instance() to get the object.
     */
    private function __construct() {
        $this->version = WP3DMV_VERSION;
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_woocommerce_hooks();
        $this->define_elementor_hooks();
        $this->loader->run();
    }

    // ─── Dependency Loading ────────────────────────────────────────────────────

    /**
     * Load all required class files.
     * Core files are already loaded by the main plugin file.
     * Here we load the feature modules.
     */
    private function load_dependencies() {
        // Admin
        $this->maybe_load( WP3DMV_PLUGIN_DIR . 'admin/class-wp3dmv-admin.php' );
        $this->maybe_load( WP3DMV_PLUGIN_DIR . 'admin/class-wp3dmv-settings.php' );
        $this->maybe_load( WP3DMV_PLUGIN_DIR . 'admin/class-wp3dmv-product-meta.php' );

        // Public
        $this->maybe_load( WP3DMV_PLUGIN_DIR . 'public/class-wp3dmv-public.php' );
        $this->maybe_load( WP3DMV_PLUGIN_DIR . 'includes/class-wp3dmv-viewer.php' );
        $this->maybe_load( WP3DMV_PLUGIN_DIR . 'includes/class-wp3dmv-ajax.php' );

        // WooCommerce
        $this->maybe_load( WP3DMV_PLUGIN_DIR . 'woocommerce/class-wp3dmv-woocommerce.php' );

        // Elementor
        $this->maybe_load( WP3DMV_PLUGIN_DIR . 'elementor/class-wp3dmv-elementor.php' );

        // Instantiate loader
        $this->loader = new WP3DMV_Loader();
    }

    /**
     * Load a file only if it exists (prevents fatal errors during development).
     *
     * @param string $file Absolute path.
     */
    private function maybe_load( $file ) {
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }

    // ─── Locale ───────────────────────────────────────────────────────────────

    /**
     * Set the locale for translations.
     */
    private function set_locale() {
        $plugin_i18n = new WP3DMV_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    // ─── Admin Hooks ──────────────────────────────────────────────────────────

    /**
     * Register all hooks related to the admin area.
     */
    private function define_admin_hooks() {
        if ( ! class_exists( 'WP3DMV_Admin' ) ) {
            return;
        }

        $plugin_admin = new WP3DMV_Admin( $this->version );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu',            $plugin_admin, 'add_plugin_admin_menu' );

        if ( class_exists( 'WP3DMV_Settings' ) ) {
            $plugin_settings = new WP3DMV_Settings();
            $this->loader->add_action( 'admin_init', $plugin_settings, 'register_settings' );
        }

        if ( class_exists( 'WP3DMV_Product_Meta' ) ) {
            $plugin_meta = new WP3DMV_Product_Meta();
            $this->loader->add_action( 'add_meta_boxes',                     $plugin_meta, 'add_metabox' );
            $this->loader->add_action( 'woocommerce_process_product_meta',   $plugin_meta, 'save_meta' );
        }
    }

    // ─── Public Hooks ─────────────────────────────────────────────────────────

    /**
     * Register all hooks related to the public-facing side.
     */
    private function define_public_hooks() {
        if ( ! class_exists( 'WP3DMV_Public' ) ) {
            return;
        }

        $plugin_public = new WP3DMV_Public( $this->version );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'init',               $plugin_public, 'register_shortcodes' );

        // AJAX
        if ( class_exists( 'WP3DMV_AJAX' ) ) {
            $plugin_ajax = new WP3DMV_AJAX();
            $this->loader->add_action( 'wp_ajax_wp3dmv_get_model',        $plugin_ajax, 'get_model' );
            $this->loader->add_action( 'wp_ajax_nopriv_wp3dmv_get_model', $plugin_ajax, 'get_model' );
        }
    }

    // ─── WooCommerce Hooks ────────────────────────────────────────────────────

    /**
     * Register WooCommerce-specific hooks.
     */
    private function define_woocommerce_hooks() {
        if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WP3DMV_WooCommerce' ) ) {
            return;
        }

        $plugin_wc = new WP3DMV_WooCommerce();

        $this->loader->add_action(
            'woocommerce_before_single_product_summary',
            $plugin_wc,
            'render_viewer_position_a',
            20
        );
        $this->loader->add_action(
            'woocommerce_after_single_product_summary',
            $plugin_wc,
            'render_viewer_position_b',
            5
        );
        $this->loader->add_filter(
            'woocommerce_product_tabs',
            $plugin_wc,
            'add_3d_tab'
        );
    }

    // ─── Elementor Hooks ──────────────────────────────────────────────────────

    /**
     * Register Elementor widget.
     */
    private function define_elementor_hooks() {
        if ( ! did_action( 'elementor/loaded' ) || ! class_exists( 'WP3DMV_Elementor' ) ) {
            // Register on elementor/loaded in case it fires after us
            add_action( 'elementor/loaded', array( $this, 'init_elementor' ) );
            return;
        }
        $this->init_elementor();
    }

    /**
     * Callback for elementor/loaded hook.
     */
    public function init_elementor() {
        if ( ! class_exists( 'WP3DMV_Elementor' ) ) {
            return;
        }
        $plugin_elementor = new WP3DMV_Elementor();
        $this->loader->add_action(
            'elementor/widgets/register',
            $plugin_elementor,
            'register_widgets'
        );
        $this->loader->add_action(
            'elementor/elements/categories_registered',
            $plugin_elementor,
            'add_category'
        );
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /**
     * @return WP3DMV_Loader
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * @return string
     */
    public function get_version() {
        return $this->version;
    }
}
