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
/*jshint browser:true, jquery:true*/
(function (factory) {
    if (typeof define === "function" && define.amd) {
        define([
            "jquery",
            "jquery/ui"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    "use strict";

    $.widget('mage.gallery', {
        options: {
            minWidth: 300, // Minimum width of the gallery image.
            widthOffset: 90, // Offset added to the width of the gallery image.
            heightOffset: 210, // Offset added to the height of the gallery image.
            closeWindow: "div.buttons-set a[role='close-window']" // Selector for closing the gallery popup window.
        },

        /**
         * Bind click handler for closing the popup window and resize the popup based on the image size.
         * @private
         */
        _create: function() {
            $(this.options.closeWindow).on('click', function() { window.close(); });
            this._resizeWindow();
        },

        /**
         * Resize the gallery image popup window based on the image's dimensions.
         * @private
         */
        _resizeWindow: function() {
            var img = this.element,
                width = img.width() < this.options.minWidth ? this.options.minWidth : img.width();
            window.resizeTo(width + this.options.widthOffset, img.height() + this.options.heightOffset);
        }
    });
    
    return $.mage.gallery;
}));