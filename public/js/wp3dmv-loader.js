/**
 * WP 3D Model Viewer — GLB / GLTF Model Loader
 *
 * Wraps THREE.GLTFLoader with:
 *   - Loading-bar progress updates
 *   - Automatic model centering via Box3
 *   - User-friendly error display
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/public/js
 * @version    1.0.0
 * @since      1.0.0
 *
 * Dependencies (must be enqueued before this file):
 *   - three.min.js     (THREE global)
 *   - GLTFLoader.js    (THREE.GLTFLoader — Three.js r158 legacy build)
 */

(function (window, THREE) {
    'use strict';

    if (!THREE) {
        console.error('WP3DMV Loader: THREE.js is not loaded.');
        return;
    }

    /**
     * WP3DMV_Loader
     * Handles all model loading operations for the viewer.
     */
    var WP3DMV_Loader = {

        /* ----------------------------------------------------------------
         * PUBLIC API
         * -------------------------------------------------------------- */

        /**
         * Load a GLB or GLTF model from a URL into a THREE.Scene.
         *
         * Internal callbacks (onProgress, onLoaded, onError) run automatically
         * to update the loading bar UI, centre the model, and handle errors.
         * External callbacks are optional — pass null if not needed.
         *
         * @param {string}        url         Absolute URL to .glb / .gltf file
         * @param {THREE.Scene}   scene       Target scene to add the model to
         * @param {HTMLElement}   container   .wp3dmv-container (for UI updates)
         * @param {Function|null} onProgress  Optional external progress callback(xhr)
         * @param {Function|null} onLoaded    Optional external loaded callback(gltf)
         * @param {Function|null} onError     Optional external error callback(error)
         */
        loadModel: function (url, scene, container, onProgress, onLoaded, onError) {
            if (!THREE.GLTFLoader) {
                console.error(
                    'WP3DMV Loader: THREE.GLTFLoader is not available. ' +
                    'Ensure GLTFLoader.js (Three.js r158) is enqueued before wp3dmv-loader.js.'
                );
                return;
            }

            if (!url) {
                console.warn('WP3DMV Loader: No model URL provided.');
                return;
            }

            var self   = this;
            var loader = new THREE.GLTFLoader();

            loader.load(
                url,

                /* ── onLoad ─────────────────────────────────────────────── */
                function (gltf) {
                    self.onLoaded(gltf, scene, container);
                    if (typeof onLoaded === 'function') { onLoaded(gltf); }
                },

                /* ── onProgress ─────────────────────────────────────────── */
                function (xhr) {
                    self.onProgress(xhr, container);
                    if (typeof onProgress === 'function') { onProgress(xhr); }
                },

                /* ── onError ────────────────────────────────────────────── */
                function (error) {
                    self.onError(error, container);
                    if (typeof onError === 'function') { onError(error); }
                }
            );
        },

        /* ----------------------------------------------------------------
         * INTERNAL CALLBACKS
         * -------------------------------------------------------------- */

        /**
         * Update the .wp3dmv-loading-bar span width to reflect load percentage.
         * Called automatically by THREE.GLTFLoader's onProgress.
         *
         * @param {ProgressEvent} xhr
         * @param {HTMLElement}   container
         */
        onProgress: function (xhr, container) {
            if (!xhr.lengthComputable) { return; }

            var percent = Math.round((xhr.loaded / xhr.total) * 100);
            var bar     = container.querySelector('.wp3dmv-loading-bar span');

            if (bar) {
                bar.style.width = percent + '%';
            }

            var textEl = container.querySelector('.wp3dmv-loading-text');
            if (textEl) {
                textEl.textContent = 'กำลังโหลด... ' + percent + '%';
            }
        },

        /**
         * Centre the loaded model in the scene and hide the loading overlay.
         *
         * @param {Object}      gltf       GLTF result object from THREE.GLTFLoader
         * @param {THREE.Scene} scene      Target scene
         * @param {HTMLElement} container  .wp3dmv-container
         */
        onLoaded: function (gltf, scene, container) {
            var model = gltf.scene;

            // Centre the model at the scene origin
            this.centerModel(model);

            // Add to scene
            scene.add(model);

            // Hide loading overlay
            var loadingEl = container.querySelector('.wp3dmv-loading');
            if (loadingEl) {
                loadingEl.style.display = 'none';
            }
        },

        /**
         * Display a human-readable error message inside the container.
         * Replaces the loading overlay content.
         *
         * @param {Error|Event|string} error
         * @param {HTMLElement}        container
         */
        onError: function (error, container) {
            console.error('WP3DMV Loader: Failed to load 3D model.', error);

            var message = 'ไม่สามารถโหลด 3D model ได้';
            var detail  = '';

            if (error && error.message) {
                detail = error.message;
            } else if (typeof error === 'string') {
                detail = error;
            }

            var loadingEl = container.querySelector('.wp3dmv-loading');
            if (loadingEl) {
                loadingEl.style.display = '';    // Ensure it is visible
                loadingEl.innerHTML =
                    '<div class="wp3dmv-error" style="' +
                        'color:#c0392b;' +
                        'text-align:center;' +
                        'padding:1.5em 1em;' +
                        'font-size:0.9em;' +
                    '">' +
                        '<span style="font-size:2em;">⚠️</span><br>' +
                        '<strong>' + message + '</strong>' +
                        (detail ? '<br><small style="opacity:0.7;">' + detail + '</small>' : '') +
                    '</div>';
            }
        },

        /* ----------------------------------------------------------------
         * MODEL UTILITIES
         * -------------------------------------------------------------- */

        /**
         * Centre a 3D object at the scene origin using its bounding box.
         *
         * Computes the axis-aligned bounding box of the object,
         * finds its geometric centre, then offsets the object's position
         * so that centre maps to (0, 0, 0).
         *
         * @param {THREE.Object3D} object  The loaded model root object
         */
        centerModel: function (object) {
            var box    = new THREE.Box3().setFromObject(object);
            var center = new THREE.Vector3();
            box.getCenter(center);

            object.position.x -= center.x;
            object.position.y -= center.y;
            object.position.z -= center.z;
        }
    };

    // ── Expose globally ───────────────────────────────────────────────────
    window.WP3DMV_Loader = WP3DMV_Loader;

}(window, window.THREE));
