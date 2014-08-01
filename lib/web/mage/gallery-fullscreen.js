/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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
}));