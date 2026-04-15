/**
 * WP 3D Model Viewer — Main Viewer Controller
 *
 * Manages Three.js scene, camera, renderer, and lights.
 * Supports multiple viewer instances on the same page.
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/public/js
 * @version    1.0.0
 * @since      1.0.0
 *
 * Dependencies (must be enqueued before this file):
 *   - three.min.js          (THREE global)
 *   - OrbitControls.js      (THREE.OrbitControls)
 *   - GLTFLoader.js         (THREE.GLTFLoader)
 *   - wp3dmv-controls.js    (WP3DMV_Controls global)
 *   - wp3dmv-loader.js      (WP3DMV_Loader global)
 */

(function (window, THREE) {
    'use strict';

    if (!THREE) {
        console.error('WP3DMV: THREE.js is not loaded. Viewer cannot initialize.');
        return;
    }

    /**
     * WP3DMV_Viewer
     * Central controller — creates and manages all viewer instances found on
     * the page. Each .wp3dmv-container element becomes one independent instance.
     */
    var WP3DMV_Viewer = {

        /** @type {Array} Active viewer instance objects */
        instances: [],

        /* ----------------------------------------------------------------
         * PUBLIC API
         * -------------------------------------------------------------- */

        /**
         * Initialize a single viewer from a container element.
         * Reads data-model-url and data-settings attributes.
         *
         * @param  {HTMLElement} container   .wp3dmv-container DOM node
         * @return {Object}                  Viewer instance object
         */
        init: function (container) {
            var modelUrl    = container.getAttribute('data-model-url') || '';
            var settingsRaw = container.getAttribute('data-settings')  || '{}';
            var settings    = {};

            try {
                settings = JSON.parse(settingsRaw);
            } catch (e) {
                console.warn('WP3DMV: Could not parse data-settings JSON — using defaults.', e);
            }

            // Apply defaults for any missing keys
            settings = Object.assign({
                bg_color:                '#f5f5f5',
                auto_rotate:             true,
                rotation_speed:          1.0,
                enable_zoom:             true,
                initial_camera_distance: 3
            }, settings);

            var instance = {
                container:   container,
                settings:    settings,
                modelUrl:    modelUrl,
                scene:       null,
                camera:      null,
                renderer:    null,
                controls:    null,
                animationId: null
            };

            // Build Three.js pipeline
            instance.scene    = this.createScene(instance);
            instance.camera   = this.createCamera(instance);
            instance.renderer = this.createRenderer(instance);
            this.createLights(instance);

            // OrbitControls (wp3dmv-controls.js)
            if (window.WP3DMV_Controls) {
                instance.controls = WP3DMV_Controls.createControls(
                    instance.camera,
                    instance.renderer.domElement,
                    settings
                );
            } else {
                console.warn('WP3DMV: WP3DMV_Controls not found — orbit controls disabled.');
            }

            // Load GLB/GLTF model (wp3dmv-loader.js)
            if (modelUrl && window.WP3DMV_Loader) {
                WP3DMV_Loader.loadModel(
                    modelUrl,
                    instance.scene,
                    container,
                    null,   /* onProgress — handled internally by WP3DMV_Loader */
                    null,   /* onLoaded   — handled internally by WP3DMV_Loader */
                    null    /* onError    — handled internally by WP3DMV_Loader */
                );
            } else if (!window.WP3DMV_Loader) {
                console.warn('WP3DMV: WP3DMV_Loader not found — model loading disabled.');
            }

            // Start render loop
            this.animate(instance);

            // Responsive resize (closure keeps reference to this instance only)
            var self = this;
            window.addEventListener('resize', function () {
                self.onWindowResize(instance);
            });

            this.instances.push(instance);
            return instance;
        },

        /**
         * Scan the page and initialize every .wp3dmv-container found.
         * Call this once on DOMContentLoaded (done automatically below).
         */
        initAll: function () {
            var containers = document.querySelectorAll('.wp3dmv-container');
            if (!containers.length) { return; }

            for (var i = 0; i < containers.length; i++) {
                this.init(containers[i]);
            }
        },

        /* ----------------------------------------------------------------
         * THREE.JS COMPONENT BUILDERS
         * -------------------------------------------------------------- */

        /**
         * Create THREE.Scene with background colour from settings.
         *
         * @param  {Object}      instance
         * @return {THREE.Scene}
         */
        createScene: function (instance) {
            var scene = new THREE.Scene();
            scene.background = new THREE.Color(instance.settings.bg_color);
            return scene;
        },

        /**
         * Create PerspectiveCamera — FOV 45°, near 0.1, far 1000.
         * Camera Z position = settings.initial_camera_distance.
         *
         * @param  {Object}               instance
         * @return {THREE.PerspectiveCamera}
         */
        createCamera: function (instance) {
            var container = instance.container;
            var width     = container.clientWidth  || 400;
            var height    = container.clientHeight || 400;
            var aspect    = width / height;
            var dist      = parseFloat(instance.settings.initial_camera_distance) || 3;

            var camera = new THREE.PerspectiveCamera(45, aspect, 0.1, 1000);
            camera.position.set(0, 0, dist);
            return camera;
        },

        /**
         * Create WebGLRenderer attached to .wp3dmv-canvas inside the container.
         * antialias: true, setPixelRatio(window.devicePixelRatio)
         *
         * @param  {Object}              instance
         * @return {THREE.WebGLRenderer}
         */
        createRenderer: function (instance) {
            var container = instance.container;
            var canvas    = container.querySelector('.wp3dmv-canvas');
            var width     = container.clientWidth  || 400;
            var height    = container.clientHeight || 400;

            var renderer = new THREE.WebGLRenderer({
                canvas:    canvas,
                antialias: true,
                alpha:     false
            });

            renderer.setPixelRatio(window.devicePixelRatio || 1);
            renderer.setSize(width, height);

            // Three.js r158: use outputColorSpace (outputEncoding is deprecated)
            if (THREE.SRGBColorSpace !== undefined) {
                renderer.outputColorSpace = THREE.SRGBColorSpace;
            }

            return renderer;
        },

        /**
         * Add default lights to the scene.
         *   - AmbientLight   intensity 0.6  (fills shadows uniformly)
         *   - DirectionalLight intensity 0.8 at position (5, 5, 5)
         *
         * @param {Object} instance
         */
        createLights: function (instance) {
            var ambient = new THREE.AmbientLight(0xffffff, 0.6);
            instance.scene.add(ambient);

            var dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
            dirLight.position.set(5, 5, 5);
            instance.scene.add(dirLight);
        },

        /* ----------------------------------------------------------------
         * RENDER LOOP & EVENTS
         * -------------------------------------------------------------- */

        /**
         * Start requestAnimationFrame render loop.
         * controls.update() is called each frame to apply damping / autoRotate.
         *
         * @param {Object} instance
         */
        animate: function (instance) {
            function loop() {
                instance.animationId = requestAnimationFrame(loop);

                if (instance.controls) {
                    instance.controls.update();
                }

                instance.renderer.render(instance.scene, instance.camera);
            }
            loop();
        },

        /**
         * Respond to window resize — update camera aspect and renderer size.
         * Guards against zero dimensions (e.g. hidden tabs / collapsed sections).
         *
         * @param {Object} instance
         */
        onWindowResize: function (instance) {
            var container = instance.container;
            var width     = container.clientWidth;
            var height    = container.clientHeight;

            if (!width || !height) { return; }

            instance.camera.aspect = width / height;
            instance.camera.updateProjectionMatrix();
            instance.renderer.setSize(width, height);
        }
    };

    // ── Expose globally ────────────────────────────────────────────────────
    window.WP3DMV_Viewer = WP3DMV_Viewer;

    // ── Auto-initialize on DOMContentLoaded ────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            WP3DMV_Viewer.initAll();
        });
    } else {
        // DOM already ready (e.g. script loaded with defer or at bottom of body)
        WP3DMV_Viewer.initAll();
    }

}(window, window.THREE));
