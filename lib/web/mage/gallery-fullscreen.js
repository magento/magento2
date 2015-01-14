/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            "jquery",
            "jquery/ui",
            "mage/gallery"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    "use strict";

    /**
     * An auxiliary widget
     * Wraps gallery into dialog widget and opens the dialog in fullscreen mode
     */
    $.widget('mage.galleryFullScreen', {
        options: {
            selectors: {
                trigger: '[data-role=zoom-image], [data-role=zoom-track]'
            },
            fullscreenClass: 'zoom lightbox'
        },

        /**
         * Widget constructor
         * @protected
         */
        _create: function() {
            this._bind();
        },

        /**
         * Bind full screen handler
         * @protected
         */
        _bind: function() {
            var events = {};
            events['click ' + this.options.selectors.trigger] = '_fullScreen';
            this._on(events);
        },

        /**
         * Open gallery in dialog
         * @param {Object} e - event object
         */
        _fullScreen: function() {
            this.element
                .gallery('option', {showNotice: false, fullSizeMode: true, showButtons: true})
                .dialog({
                    resizable: false,
                    draggable: false,
                    modal: true,
                    dialogClass: this.options.fullscreenClass,
                    close: $.proxy(function() {
                        this.element
                            .gallery('option', {showNotice: true, fullSizeMode: false, showButtons: false})
                            .dialog('destroy').show();
                    }, this)
                });
        }
    });
    
    return $.mage.galleryFullScreen;
}));