<?php
/**
 * Elementor Manager Class
 *
 * Handles registration of custom Elementor widget category and widget.
 * Hooks into Elementor's widget registration and category registration APIs.
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/elementor
 * @author     WP 3D Model Viewer
 * @license    GPL-2.0+
 * @link       https://github.com/webwecreate/wp-3d-model-viewer
 * @since      1.0.0
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class WP3DMV_Elementor
 *
 * Manages Elementor integration for WP 3D Model Viewer.
 * Registers the "WP3D" widget category and the 3D Viewer widget.
 *
 * Hooks used:
 *   - elementor/widgets/register         → register_widgets()
 *   - elementor/elements/categories_registered → add_category()
 *
 * @since 1.0.0
 */
class WP3DMV_Elementor {

    /**
     * Register custom Elementor widgets.
     *
     * Requires the widget class file and registers
     * WP3DMV_Widget_3D_Viewer with Elementor's widget manager.
     *
     * @since  1.0.0
     * @param  \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager instance.
     * @return void
     */
    public function register_widgets( $widgets_manager ) {
        require_once WP3DMV_PLUGIN_DIR . 'elementor/widgets/class-widget-3d-viewer.php';
        $widgets_manager->register( new WP3DMV_Widget_3D_Viewer() );
    }

    /**
     * Add custom "WP3D" category to the Elementor widget panel.
     *
     * @since  1.0.0
     * @param  \Elementor\Elements_Manager $elements_manager Elementor elements manager instance.
     * @return void
     */
    public function add_category( $elements_manager ) {
        $elements_manager->add_category(
            'wp3dmv',
            array(
                'title' => esc_html__( 'WP3D', 'wp3dmv' ),
                'icon'  => 'fa fa-cube',
            )
        );
    }
}
