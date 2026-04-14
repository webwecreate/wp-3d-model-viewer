/**
 * WP 3D Model Viewer — Media Upload (WordPress Media Library)
 *
 * @package    WP3DModelViewer
 * @subpackage Admin/JS
 * @version    1.0.0
 * @since      1.0.0
 *
 * Depends on: jquery, media-upload, wp.media (wp_enqueue_media)
 * Loaded after: wp3dmv-admin.js
 *
 * Usage (HTML button markup):
 * ──────────────────────────────────────────────────────────────────
 * <div class="wp3dmv-media-wrap" data-field="wp3dmv_model_url">
 *
 *   <input  type="hidden"
 *           id="wp3dmv_model_url"
 *           name="wp3dmv_settings[model_url]"
 *           class="wp3dmv-url-field"
 *           value="" />
 *
 *   <button type="button"
 *           class="button wp3dmv-upload-btn">
 *     เลือกไฟล์ 3D Model
 *   </button>
 *
 *   <button type="button"
 *           class="button wp3dmv-remove-btn"
 *           style="display:none;">
 *     ✕ ลบไฟล์
 *   </button>
 *
 *   <div class="wp3dmv-file-preview" style="display:none;"></div>
 *
 * </div>
 * ──────────────────────────────────────────────────────────────────
 *
 * Accepted formats: .glb, .gltf
 * Attachment IDs are stored via  data-id-field  (optional) on the wrap.
 */

( function ( $ ) {
    'use strict';

    /* Accepted 3D model file extensions */
    var ACCEPTED_EXTS = [ 'glb', 'gltf' ];

    /**
     * WP3DMV_MediaUpload
     * Manages one or more media-upload widgets on the same admin page.
     */
    var WP3DMV_MediaUpload = {

        /**
         * Holds one wp.media frame per .wp3dmv-media-wrap so frames are
         * reused on re-open (avoids duplicate event listeners).
         * Key = unique data-field value.
         */
        _frames: {},

        /* ------------------------------------------------------------------ */
        /*  Public API                                                         */
        /* ------------------------------------------------------------------ */

        /**
         * init()
         * Scans the DOM for .wp3dmv-media-wrap elements and wires them up.
         * Safe to call multiple times — skips already-initialised wraps.
         */
        init: function () {
            var self = this;

            $( '.wp3dmv-media-wrap' ).each( function () {
                var $wrap = $( this );
                if ( $wrap.data( 'wp3dmv-init' ) ) {
                    return; // already wired
                }
                self._bindWrap( $wrap );
                $wrap.data( 'wp3dmv-init', true );
            } );
        },

        /* ------------------------------------------------------------------ */
        /*  Private helpers                                                    */
        /* ------------------------------------------------------------------ */

        /**
         * _bindWrap( $wrap )
         * Wires the Upload and Remove buttons for a single media-wrap element.
         *
         * @param {jQuery} $wrap  The .wp3dmv-media-wrap container.
         */
        _bindWrap: function ( $wrap ) {
            var self     = this;
            var fieldKey = $wrap.data( 'field' ) || ( 'wp3dmv_field_' + Date.now() );

            var $urlField = $wrap.find( '.wp3dmv-url-field' );
            var $idField  = $wrap.find( '.wp3dmv-id-field' );     // optional
            var $uploadBtn = $wrap.find( '.wp3dmv-upload-btn' );
            var $removeBtn = $wrap.find( '.wp3dmv-remove-btn' );
            var $preview   = $wrap.find( '.wp3dmv-file-preview' );

            // Restore state on page load if a value already exists
            if ( $urlField.val() ) {
                self._showPreview( $wrap, $urlField.val() );
            }

            /* ---- Upload button ------------------------------------------ */
            $uploadBtn.on( 'click', function ( e ) {
                e.preventDefault();

                // Create or reuse the wp.media frame for this field
                if ( ! self._frames[ fieldKey ] ) {
                    self._frames[ fieldKey ] = self._createFrame();
                }

                var frame = self._frames[ fieldKey ];

                /* When user clicks "Insert into post / Select" */
                frame.off( 'select' ).on( 'select', function () {
                    var attachment = frame
                        .state()
                        .get( 'selection' )
                        .first()
                        .toJSON();

                    // Validate extension
                    if ( ! self._isValidModel( attachment ) ) {
                        self._showError(
                            $wrap,
                            wp3dmv_admin.i18n.invalid_file_type ||
                            'กรุณาเลือกเฉพาะไฟล์ .glb หรือ .gltf เท่านั้น'
                        );
                        return;
                    }

                    self._clearError( $wrap );

                    // Populate fields
                    $urlField.val( attachment.url ).trigger( 'change' );
                    if ( $idField.length ) {
                        $idField.val( attachment.id ).trigger( 'change' );
                    }

                    self._showPreview( $wrap, attachment.url, attachment.filename );

                    // Trigger custom event for external listeners
                    $wrap.trigger( 'wp3dmv:model-selected', [ attachment ] );
                } );

                frame.open();
            } );

            /* ---- Remove button ------------------------------------------ */
            $removeBtn.on( 'click', function ( e ) {
                e.preventDefault();
                self._clearField( $wrap );
                $wrap.trigger( 'wp3dmv:model-removed' );
            } );
        },

        /**
         * _createFrame()
         * Builds a new wp.media modal frame configured for 3D model selection.
         *
         * @returns {wp.media.view.MediaFrame}
         */
        _createFrame: function () {
            return wp.media( {
                title    : ( typeof wp3dmv_admin !== 'undefined' && wp3dmv_admin.i18n.media_title )
                                ? wp3dmv_admin.i18n.media_title
                                : 'เลือก 3D Model (.glb / .gltf)',
                button   : {
                    text : ( typeof wp3dmv_admin !== 'undefined' && wp3dmv_admin.i18n.media_button )
                                ? wp3dmv_admin.i18n.media_button
                                : 'ใช้ไฟล์นี้'
                },
                multiple : false,   // single file selection only
                library  : {
                    /*
                     * WordPress Media Library does not have a built-in MIME type
                     * for .glb / .gltf, so we allow all uploads and validate the
                     * extension ourselves in the `select` callback above.
                     * The `type` filter below restricts to application/* which
                     * covers most 3D model uploads registered as application/octet-stream.
                     */
                    type : ''       // show all files
                }
            } );
        },

        /**
         * _isValidModel( attachment )
         * Returns true if the attachment's filename ends in .glb or .gltf.
         *
         * @param  {Object}  attachment  Backbone model data from wp.media.
         * @returns {boolean}
         */
        _isValidModel: function ( attachment ) {
            var name = ( attachment.filename || attachment.url || '' ).toLowerCase();
            for ( var i = 0; i < ACCEPTED_EXTS.length; i++ ) {
                if ( name.endsWith( '.' + ACCEPTED_EXTS[ i ] ) ) {
                    return true;
                }
            }
            return false;
        },

        /* ------------------------------------------------------------------ */
        /*  UI helpers                                                         */
        /* ------------------------------------------------------------------ */

        /**
         * _showPreview( $wrap, url [, filename] )
         * Displays the file name / URL in the preview area and shows Remove btn.
         *
         * @param {jQuery} $wrap
         * @param {string} url
         * @param {string} [filename]
         */
        _showPreview: function ( $wrap, url, filename ) {
            var $preview   = $wrap.find( '.wp3dmv-file-preview' );
            var $removeBtn = $wrap.find( '.wp3dmv-remove-btn' );
            var $uploadBtn = $wrap.find( '.wp3dmv-upload-btn' );

            // Derive a display name from the URL if no filename supplied
            var display = filename || url.split( '/' ).pop() || url;

            // Detect extension for icon class
            var ext  = display.split( '.' ).pop().toLowerCase();
            var icon = ( ext === 'gltf' ) ? 'gltf' : 'glb';

            $preview
                .html(
                    '<span class="wp3dmv-file-icon wp3dmv-file-icon--' + icon + '">' + ext.toUpperCase() + '</span>' +
                    '<span class="wp3dmv-file-name" title="' + url + '">' + display + '</span>'
                )
                .show();

            $removeBtn.show();
            $uploadBtn.text(
                ( typeof wp3dmv_admin !== 'undefined' && wp3dmv_admin.i18n.change_file )
                    ? wp3dmv_admin.i18n.change_file
                    : 'เปลี่ยนไฟล์'
            );
        },

        /**
         * _clearField( $wrap )
         * Resets the hidden input, preview, and button labels.
         *
         * @param {jQuery} $wrap
         */
        _clearField: function ( $wrap ) {
            var $urlField  = $wrap.find( '.wp3dmv-url-field' );
            var $idField   = $wrap.find( '.wp3dmv-id-field' );
            var $preview   = $wrap.find( '.wp3dmv-file-preview' );
            var $removeBtn = $wrap.find( '.wp3dmv-remove-btn' );
            var $uploadBtn = $wrap.find( '.wp3dmv-upload-btn' );

            $urlField.val( '' ).trigger( 'change' );
            if ( $idField.length ) {
                $idField.val( '' ).trigger( 'change' );
            }

            $preview.hide().html( '' );
            $removeBtn.hide();
            $uploadBtn.text(
                ( typeof wp3dmv_admin !== 'undefined' && wp3dmv_admin.i18n.select_file )
                    ? wp3dmv_admin.i18n.select_file
                    : 'เลือกไฟล์ 3D Model'
            );
            this._clearError( $wrap );
        },

        /**
         * _showError( $wrap, message )
         * Displays a validation error below the upload buttons.
         *
         * @param {jQuery} $wrap
         * @param {string} message
         */
        _showError: function ( $wrap, message ) {
            var $err = $wrap.find( '.wp3dmv-upload-error' );
            if ( ! $err.length ) {
                $err = $( '<p class="wp3dmv-upload-error"></p>' );
                $wrap.append( $err );
            }
            $err.text( message ).show();
        },

        /**
         * _clearError( $wrap )
         * Removes any visible validation error from the wrap.
         *
         * @param {jQuery} $wrap
         */
        _clearError: function ( $wrap ) {
            $wrap.find( '.wp3dmv-upload-error' ).hide().text( '' );
        }

    }; // end WP3DMV_MediaUpload

    /* ---------------------------------------------------------------------- */
    /*  Expose globally                                                         */
    /* ---------------------------------------------------------------------- */
    window.WP3DMV_MediaUpload = WP3DMV_MediaUpload;

    /*
     * Self-init on DOM ready when this script is loaded standalone
     * (without WP3DMV_Admin orchestrating it).
     */
    $( document ).ready( function () {
        if ( typeof WP3DMV_Admin === 'undefined' ) {
            WP3DMV_MediaUpload.init();
        }
    } );

} )( jQuery );
