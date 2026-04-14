/**
 * WP 3D Model Viewer — Admin Scripts
 *
 * @package    WP3DModelViewer
 * @subpackage Admin/JS
 * @version    1.0.0
 * @since      1.0.0
 *
 * Depends on: jquery, wp3dmv-media-upload (loaded after this file)
 * Localized:  wp3dmv_admin  (via WP3DMV_Admin::enqueue_scripts)
 *             {
 *               ajax_url : admin-ajax.php URL,
 *               nonce    : wp_create_nonce('wp3dmv_admin'),
 *               i18n     : { ... }
 *             }
 */

( function ( $ ) {
    'use strict';

    /**
     * WP3DMV_Admin
     * Main admin controller — bootstraps all admin sub-modules.
     */
    var WP3DMV_Admin = {

        /**
         * init()
         * Called on DOM ready. Boots all admin features.
         */
        init: function () {
            this.initTabs();
            this.initColorPickers();
            this.initRangeSliders();
            this.initMediaUploadButtons();
            this.bindSaveNotice();
        },

        /* ------------------------------------------------------------------ */
        /*  Settings Page — Tab navigation                                     */
        /* ------------------------------------------------------------------ */

        /**
         * initTabs()
         * Activates tab-based navigation on the settings page.
         * Tabs use  data-tab="tab-id"  buttons and  .wp3dmv-tab-panel  sections.
         */
        initTabs: function () {
            var $nav = $( '.wp3dmv-tab-nav' );
            if ( ! $nav.length ) {
                return;
            }

            $nav.on( 'click', '.wp3dmv-tab-btn', function () {
                var target = $( this ).data( 'tab' );

                // Active button
                $nav.find( '.wp3dmv-tab-btn' ).removeClass( 'is-active' );
                $( this ).addClass( 'is-active' );

                // Active panel
                $( '.wp3dmv-tab-panel' ).removeClass( 'is-active' );
                $( '#' + target ).addClass( 'is-active' );

                // Persist active tab in URL hash (without page jump)
                if ( history.replaceState ) {
                    history.replaceState( null, null, '#' + target );
                }
            } );

            // Restore tab from URL hash on load
            var hash = window.location.hash.replace( '#', '' );
            if ( hash && $( '#' + hash ).length ) {
                $nav.find( '[data-tab="' + hash + '"]' ).trigger( 'click' );
            } else {
                $nav.find( '.wp3dmv-tab-btn' ).first().trigger( 'click' );
            }
        },

        /* ------------------------------------------------------------------ */
        /*  Color Pickers                                                      */
        /* ------------------------------------------------------------------ */

        /**
         * initColorPickers()
         * Initialises WordPress iris color picker on every .wp3dmv-color-picker.
         */
        initColorPickers: function () {
            $( '.wp3dmv-color-picker' ).each( function () {
                if ( $.fn.wpColorPicker ) {
                    $( this ).wpColorPicker( {
                        change: function ( event, ui ) {
                            // Broadcast change so live preview can react
                            $( document ).trigger( 'wp3dmv:color-change', [
                                $( event.target ).attr( 'name' ),
                                ui.color.toString()
                            ] );
                        }
                    } );
                }
            } );
        },

        /* ------------------------------------------------------------------ */
        /*  Range Sliders (rotation speed, camera distance, …)                */
        /* ------------------------------------------------------------------ */

        /**
         * initRangeSliders()
         * Syncs the number display next to every  input[type=range].
         */
        initRangeSliders: function () {
            $( '.wp3dmv-range-wrap' ).each( function () {
                var $wrap  = $( this );
                var $range = $wrap.find( 'input[type="range"]' );
                var $label = $wrap.find( '.wp3dmv-range-value' );

                if ( ! $range.length || ! $label.length ) {
                    return;
                }

                // Set initial display
                $label.text( $range.val() );

                $range.on( 'input change', function () {
                    $label.text( $( this ).val() );
                } );
            } );
        },

        /* ------------------------------------------------------------------ */
        /*  Media Upload Buttons                                               */
        /* ------------------------------------------------------------------ */

        /**
         * initMediaUploadButtons()
         * Finds every  .wp3dmv-upload-btn  and wires it to the WP Media Library.
         * Delegates to WP3DMV_MediaUpload (wp3dmv-media-upload.js).
         */
        initMediaUploadButtons: function () {
            if ( typeof WP3DMV_MediaUpload === 'undefined' ) {
                return;
            }
            WP3DMV_MediaUpload.init();
        },

        /* ------------------------------------------------------------------ */
        /*  Settings-saved admin notice                                       */
        /* ------------------------------------------------------------------ */

        /**
         * bindSaveNotice()
         * Auto-dismisses the WP settings-updated notice after 3 s.
         */
        bindSaveNotice: function () {
            var $notice = $( '.notice-success.is-dismissible' );
            if ( $notice.length ) {
                setTimeout( function () {
                    $notice.fadeTo( 400, 0, function () {
                        $( this ).slideUp( 200 );
                    } );
                }, 3000 );
            }
        }

    }; // end WP3DMV_Admin

    /* ---------------------------------------------------------------------- */
    /*  Boot on DOM ready                                                       */
    /* ---------------------------------------------------------------------- */
    $( document ).ready( function () {
        WP3DMV_Admin.init();
    } );

    // Expose globally so other scripts can call WP3DMV_Admin.initMediaUploadButtons() etc.
    window.WP3DMV_Admin = WP3DMV_Admin;

} )( jQuery );
