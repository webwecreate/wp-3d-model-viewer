/**
 * WP 3D Model Viewer — Orbit Controls Wrapper
 *
 * Wraps THREE.OrbitControls and applies plugin default settings
 * as defined in Master Architecture Section 7.3.
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/public/js
 * @version    1.0.0
 * @since      1.0.0
 *
 * Dependencies (must be enqueued before this file):
 *   - three.min.js      (THREE global)
 *   - OrbitControls.js  (THREE.OrbitControls — Three.js r158 legacy build)
 */

(function (window, THREE) {
    'use strict';

    if (!THREE) {
        console.error('WP3DMV Controls: THREE.js is not loaded.');
        return;
    }

    /**
     * WP3DMV_Controls
     * Thin factory wrapper around THREE.OrbitControls.
     * Applies plugin defaults from Master Architecture §7.3 and viewer settings.
     */
    var WP3DMV_Controls = {

        /**
         * Create and configure an OrbitControls instance.
         *
         * Settings applied (Master Architecture §7.3):
         *   enableDamping:    true
         *   dampingFactor:    0.05
         *   enableZoom:       from settings.enable_zoom
         *   minDistance:      1
         *   maxDistance:      10
         *   autoRotate:       from settings.auto_rotate
         *   autoRotateSpeed:  from settings.rotation_speed
         *   enablePan:        false (disabled — prevents accidental panning)
         *
         * Mobile touch is supported natively by THREE.OrbitControls (built-in).
         *
         * @param  {THREE.Camera}       camera    PerspectiveCamera from viewer
         * @param  {HTMLCanvasElement}  canvas    renderer.domElement
         * @param  {Object}             settings  Merged viewer settings object
         * @return {THREE.OrbitControls|null}
         */
        createControls: function (camera, canvas, settings) {
            if (!THREE.OrbitControls) {
                console.error(
                    'WP3DMV Controls: THREE.OrbitControls is not available. ' +
                    'Ensure OrbitControls.js (Three.js r158) is enqueued before wp3dmv-controls.js.'
                );
                return null;
            }

            var controls = new THREE.OrbitControls(camera, canvas);

            /* ---- §7.3 Damping ------------------------------------------ */
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;

            /* ---- §7.3 Zoom ---------------------------------------------- */
            controls.enableZoom  = (settings.enable_zoom !== false);
            controls.minDistance = 1;
            controls.maxDistance = 10;

            /* ---- §7.3 Auto-Rotate -------------------------------------- */
            controls.autoRotate      = (settings.auto_rotate !== false);
            controls.autoRotateSpeed = parseFloat(settings.rotation_speed) || 1.0;

            /* ---- §7.3 Pan (disabled) ----------------------------------- */
            controls.enablePan = false;

            return controls;
        }
    };

    // ── Expose globally ───────────────────────────────────────────────────
    window.WP3DMV_Controls = WP3DMV_Controls;

}(window, window.THREE));
