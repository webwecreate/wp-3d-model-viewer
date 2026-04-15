<?php
/**
 * HTML template for a single 3D viewer instance.
 *
 * Variables provided by WP3DMV_Viewer::render():
 *   @var string $model_url  Sanitised URL to the .glb / .gltf file.
 *   @var array  $settings   Sanitised settings array (height, bg_color, …).
 *   @var string $unique_id  Unique string ID for this instance (e.g. "inst-1-aB3xYz").
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/public/partials
 * @version    1.0.1
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Build inline style for the container.
$container_style = sprintf(
	'height:%dpx; background-color:%s;',
	(int) $settings['height'],
	esc_attr( $settings['bg_color'] )
);

// Encode settings for data attribute (used by wp3dmv-viewer.js).
// Keys must match snake_case used by wp3dmv-viewer.js, wp3dmv-controls.js, wp3dmv-loader.js.
$data_settings = esc_attr(
	wp_json_encode( [
		'bg_color'                => (string) $settings['bg_color'],
		'auto_rotate'             => (bool) $settings['auto_rotate'],
		'rotation_speed'          => (float) $settings['rotation_speed'],
		'enable_zoom'             => (bool) $settings['enable_zoom'],
		'initial_camera_distance' => (float) $settings['camera_distance'],
	] )
);

$container_id   = 'wp3dmv-' . esc_attr( $unique_id );
$loading_text   = esc_html( $settings['loading_text'] );
$show_hint      = ! empty( $settings['show_controls_hint'] );
$show_fullscreen= ! empty( $settings['enable_fullscreen'] );
?>

<div class="wp3dmv-container"
     id="<?php echo esc_attr( $container_id ); ?>"
     data-model-url="<?php echo esc_url( $model_url ); ?>"
     data-settings="<?php echo $data_settings; ?>"
     style="<?php echo esc_attr( $container_style ); ?>"
     role="img"
     aria-label="<?php esc_attr_e( '3D Model Viewer', 'wp3dmv' ); ?>">

	<?php /* ── Loading overlay ── */ ?>
	<div class="wp3dmv-loading" aria-live="polite" aria-label="<?php esc_attr_e( 'Loading model', 'wp3dmv' ); ?>">
		<div class="wp3dmv-loading-bar">
			<span class="wp3dmv-loading-bar__fill"></span>
		</div>
		<p class="wp3dmv-loading-text"><?php echo $loading_text; ?></p>
	</div>

	<?php /* ── Error message (hidden until JS shows it) ── */ ?>
	<div class="wp3dmv-error-message" hidden aria-live="assertive">
		<p><?php esc_html_e( 'ไม่สามารถโหลดโมเดล 3D ได้', 'wp3dmv' ); ?></p>
	</div>

	<?php /* ── Three.js render canvas ── */ ?>
	<canvas class="wp3dmv-canvas"></canvas>

	<?php /* ── Controls hint ── */ ?>
	<?php if ( $show_hint ) : ?>
	<div class="wp3dmv-controls-hint" aria-hidden="true">
		<span class="wp3dmv-controls-hint__item">
			<?php esc_html_e( '🖱 ลาก เพื่อหมุน', 'wp3dmv' ); ?>
		</span>
		<span class="wp3dmv-controls-hint__item">
			<?php esc_html_e( '🔍 Scroll เพื่อซูม', 'wp3dmv' ); ?>
		</span>
	</div>
	<?php endif; ?>

	<?php /* ── Optional fullscreen button ── */ ?>
	<?php if ( $show_fullscreen ) : ?>
	<button class="wp3dmv-fullscreen-btn"
	        type="button"
	        aria-label="<?php esc_attr_e( 'เต็มจอ', 'wp3dmv' ); ?>"
	        title="<?php esc_attr_e( 'เต็มจอ', 'wp3dmv' ); ?>">
		<span class="wp3dmv-fullscreen-icon" aria-hidden="true">⛶</span>
	</button>
	<?php endif; ?>

</div><!-- /.wp3dmv-container -->
