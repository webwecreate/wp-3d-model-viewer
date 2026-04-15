<?php
/**
 * Elementor 3D Viewer Widget
 *
 * Provides a drag-and-drop Elementor widget that renders an interactive
 * 3D model viewer using Three.js via WP3DMV_Viewer::render().
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/elementor/widgets
 * @author     WP 3D Model Viewer
 * @license    GPL-2.0+
 * @link       https://github.com/webwecreate/wp-3d-model-viewer
 * @since      1.0.1
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Guard: Elementor must be active.
if ( ! class_exists( 'Elementor\Widget_Base' ) ) {
    return;
}

/**
 * Class WP3DMV_Widget_3D_Viewer
 *
 * Elementor widget that displays an interactive 3D model viewer.
 * Supports model upload via Media Library or external URL.
 * Compatible with Elementor 3.x.
 *
 * Controls:
 *  - Content tab: Model (source + URL/upload), Viewer Size, Controls
 *  - Style tab:   Background color, Border radius, Box shadow
 *
 * @since 1.0.0
 */
class WP3DMV_Widget_3D_Viewer extends \Elementor\Widget_Base {

    // -------------------------------------------------------------------------
    // Identity
    // -------------------------------------------------------------------------

    /**
     * Get widget name (unique slug).
     *
     * @since  1.0.0
     * @return string
     */
    public function get_name() {
        return 'wp3dmv-viewer';
    }

    /**
     * Get widget display title shown in the Elementor panel.
     *
     * @since  1.0.0
     * @return string
     */
    public function get_title() {
        return esc_html__( '3D Model Viewer', 'wp3dmv' );
    }

    /**
     * Get widget icon (Elementor icon class).
     *
     * @since  1.0.0
     * @return string
     */
    public function get_icon() {
        return 'eicon-product-images';
    }

    /**
     * Get widget categories (must match registered category slug).
     *
     * @since  1.0.0
     * @return string[]
     */
    public function get_categories() {
        return array( 'wp3dmv' );
    }

    // -------------------------------------------------------------------------
    // Controls Registration
    // -------------------------------------------------------------------------

    /**
     * Register all widget controls (Content + Style tabs).
     *
     * @since  1.0.0
     * @return void
     */
    protected function register_controls() {

        // ── Section: Model ────────────────────────────────────────────────────
        $this->start_controls_section(
            'section_model',
            array(
                'label' => esc_html__( 'Model', 'wp3dmv' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        // Model source selector: upload from Media Library or external URL.
        $this->add_control(
            'model_source',
            array(
                'label'   => esc_html__( 'Model Source', 'wp3dmv' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'upload',
                'options' => array(
                    'upload' => esc_html__( 'Upload File', 'wp3dmv' ),
                    'url'    => esc_html__( 'External URL', 'wp3dmv' ),
                ),
            )
        );

        // External URL — shown only when source = url.
        $this->add_control(
            'model_url',
            array(
                'label'         => esc_html__( 'Model URL (.glb / .gltf)', 'wp3dmv' ),
                'type'          => \Elementor\Controls_Manager::URL,
                'placeholder'   => 'https://example.com/model.glb',
                'show_external' => false,
                'condition'     => array(
                    'model_source' => 'url',
                ),
            )
        );

        // Media upload — shown only when source = upload.
        // Note: Elementor MEDIA control opens the WP Media Library.
        // GLB/GLTF mime-type filtering is enforced on the server via
        // wp3dmv_upload_mimes filter (registered in WP3DMV_Public).
        $this->add_control(
            'model_upload',
            array(
                'label'       => esc_html__( 'Upload 3D Model', 'wp3dmv' ),
                'type'        => \Elementor\Controls_Manager::MEDIA,
                'description' => esc_html__( 'Upload a .glb or .gltf file from your Media Library.', 'wp3dmv' ),
                'condition'   => array(
                    'model_source' => 'upload',
                ),
            )
        );

        $this->end_controls_section();

        // ── Section: Viewer Size ──────────────────────────────────────────────
        $this->start_controls_section(
            'section_viewer_size',
            array(
                'label' => esc_html__( 'Viewer Size', 'wp3dmv' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        // Height slider: 200–1000 px, default 400.
        $this->add_control(
            'viewer_height',
            array(
                'label'      => esc_html__( 'Viewer Height', 'wp3dmv' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array(
                    'px' => array(
                        'min'  => 200,
                        'max'  => 1000,
                        'step' => 10,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 400,
                ),
            )
        );

        // Width mode: full column width or custom percentage.
        $this->add_control(
            'viewer_width',
            array(
                'label'   => esc_html__( 'Viewer Width', 'wp3dmv' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'full',
                'options' => array(
                    'full'   => esc_html__( 'Full Width', 'wp3dmv' ),
                    'custom' => esc_html__( 'Custom %', 'wp3dmv' ),
                ),
            )
        );

        $this->end_controls_section();

        // ── Section: Controls ─────────────────────────────────────────────────
        $this->start_controls_section(
            'section_controls',
            array(
                'label' => esc_html__( 'Controls', 'wp3dmv' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'auto_rotate',
            array(
                'label'        => esc_html__( 'Auto Rotate', 'wp3dmv' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'wp3dmv' ),
                'label_off'    => esc_html__( 'No', 'wp3dmv' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        // Rotation speed slider — only relevant when auto-rotate is on.
        $this->add_control(
            'rotation_speed',
            array(
                'label'     => esc_html__( 'Rotation Speed', 'wp3dmv' ),
                'type'      => \Elementor\Controls_Manager::SLIDER,
                'range'     => array(
                    'px' => array(
                        'min'  => 0.1,
                        'max'  => 5.0,
                        'step' => 0.1,
                    ),
                ),
                'default'   => array(
                    'size' => 1.0,
                ),
                'condition' => array(
                    'auto_rotate' => 'yes',
                ),
            )
        );

        $this->add_control(
            'enable_zoom',
            array(
                'label'        => esc_html__( 'Enable Zoom', 'wp3dmv' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'wp3dmv' ),
                'label_off'    => esc_html__( 'No', 'wp3dmv' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_hint',
            array(
                'label'        => esc_html__( 'Show Controls Hint', 'wp3dmv' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'wp3dmv' ),
                'label_off'    => esc_html__( 'No', 'wp3dmv' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // ── Section: Style (Style tab) ────────────────────────────────────────
        $this->start_controls_section(
            'section_style',
            array(
                'label' => esc_html__( 'Style', 'wp3dmv' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'bg_color',
            array(
                'label'   => esc_html__( 'Background Color', 'wp3dmv' ),
                'type'    => \Elementor\Controls_Manager::COLOR,
                'default' => '#f5f5f5',
            )
        );

        $this->add_responsive_control(
            'border_radius',
            array(
                'label'      => esc_html__( 'Border Radius', 'wp3dmv' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%', 'em' ),
                'selectors'  => array(
                    '{{WRAPPER}} .wp3dmv-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'box_shadow',
                'label'    => esc_html__( 'Box Shadow', 'wp3dmv' ),
                'selector' => '{{WRAPPER}} .wp3dmv-container',
            )
        );

        $this->end_controls_section();
    }

    // -------------------------------------------------------------------------
    // Rendering
    // -------------------------------------------------------------------------

    /**
     * Render widget HTML output on the frontend and in the Elementor editor.
     *
     * Reads settings via get_settings_for_display(), resolves the model URL,
     * then delegates to WP3DMV_Viewer::render() for consistent output
     * identical to the [wp3dmv_viewer] shortcode.
     *
     * @since  1.0.0
     * @return void
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        // ── Resolve model URL ─────────────────────────────────────────────────
        $model_url = '';
        if ( 'url' === $settings['model_source'] ) {
            $model_url = ! empty( $settings['model_url']['url'] )
                ? esc_url_raw( $settings['model_url']['url'] )
                : '';
        } elseif ( 'upload' === $settings['model_source'] ) {
            $model_url = ! empty( $settings['model_upload']['url'] )
                ? esc_url_raw( $settings['model_upload']['url'] )
                : '';
        }

        // In editor: show placeholder if no model is set.
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
        if ( empty( $model_url ) && ! $is_editor ) {
            return;
        }

        // ── Build args array for WP3DMV_Viewer::render() ──────────────────────
        $args = array(
            'url'            => $model_url,
            'height'         => ! empty( $settings['viewer_height']['size'] )
                                    ? absint( $settings['viewer_height']['size'] )
                                    : 400,
            'bg_color'             => ! empty( $settings['bg_color'] )
                                    ? sanitize_hex_color( $settings['bg_color'] )
                                    : '#f5f5f5',
            'autorotate'     => ( 'yes' === $settings['auto_rotate'] ) ? 'true' : 'false',
            'rotation_speed' => ! empty( $settings['rotation_speed']['size'] )
                                    ? floatval( $settings['rotation_speed']['size'] )
                                    : 1.0,
            'enable_zoom'    => ( 'yes' === $settings['enable_zoom'] ) ? 'true' : 'false',
            'show_hint'      => ( 'yes' === $settings['show_hint'] ) ? 'true' : 'false',
        );

        // ── Delegate to shared render class ───────────────────────────────────
        if ( class_exists( 'WP3DMV_Viewer' ) ) {
            echo WP3DMV_Viewer::render( $args );
        } else {
            // Fallback: render an empty container while plugin loads.
            printf(
                '<div class="wp3dmv-container" style="height:%dpx; background:%s;"></div>',
                absint( $args['height'] ),
                esc_attr( $args['bg'] )
            );
        }
    }

    /**
     * Render widget JavaScript template for the Elementor editor live preview.
     *
     * Uses Elementor's Backbone.js template syntax (#, {{}}).
     * Renders a preview container that mirrors the PHP render() output.
     * The full Three.js viewer is not initialised inside the template;
     * scripts are triggered via elementor/frontend/init in wp3dmv-viewer.js.
     *
     * @since  1.0.0
     * @return void
     */
    protected function content_template() {
        ?>
        <#
        // Resolve model URL from current panel settings.
        var modelUrl = '';
        if ( 'url' === settings.model_source && settings.model_url && settings.model_url.url ) {
            modelUrl = settings.model_url.url;
        } else if ( 'upload' === settings.model_source && settings.model_upload && settings.model_upload.url ) {
            modelUrl = settings.model_upload.url;
        }

        var height     = ( settings.viewer_height && settings.viewer_height.size ) ? settings.viewer_height.size : 400;
        var bgColor    = settings.bg_color ? settings.bg_color : '#f5f5f5';
        var autoRotate = ( 'yes' === settings.auto_rotate );
        var rotSpeed   = ( settings.rotation_speed && settings.rotation_speed.size ) ? settings.rotation_speed.size : 1.0;
        var enableZoom = ( 'yes' === settings.enable_zoom );
        var showHint   = ( 'yes' === settings.show_hint );

        var dataSettings = JSON.stringify({
            autoRotate    : autoRotate,
            rotationSpeed : rotSpeed,
            enableZoom    : enableZoom,
            showHint      : showHint,
            bgColor       : bgColor
        });
        #>

        <div class="wp3dmv-container elementor-widget-wp3dmv-viewer"
             data-model-url="{{ modelUrl }}"
             data-settings="{{ dataSettings }}"
             style="height: {{ height }}px; background-color: {{ bgColor }};">

            <# if ( ! modelUrl ) { #>
                <div style="display:flex; align-items:center; justify-content:center;
                            height:100%; color:#aaa; flex-direction:column; gap:10px;
                            font-family:sans-serif;">
                    <span style="font-size:48px; line-height:1;">&#x2B21;</span>
                    <span style="font-size:13px;">
                        <?php echo esc_html__( 'Set a 3D model in the panel to preview.', 'wp3dmv' ); ?>
                    </span>
                </div>
            <# } else { #>
                <div class="wp3dmv-loading">
                    <div class="wp3dmv-loading-bar"><span style="width:0%"></span></div>
                    <p class="wp3dmv-loading-text">
                        <?php echo esc_html__( 'Loading...', 'wp3dmv' ); ?>
                    </p>
                </div>

                <canvas class="wp3dmv-canvas"></canvas>

                <# if ( showHint ) { #>
                    <div class="wp3dmv-controls-hint">
                        <span><?php echo esc_html__( '🖱 Drag to rotate', 'wp3dmv' ); ?></span>
                        <span><?php echo esc_html__( '🔍 Scroll to zoom', 'wp3dmv' ); ?></span>
                    </div>
                <# } #>
            <# } #>

        </div>
        <?php
    }
}
