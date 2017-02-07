/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'jquery/ui'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    'use strict';

    $.widget('mage.gallery', {
        options: {
            minWidth: 300, // Minimum width of the gallery image.
            widthOffset: 90, // Offset added to the width of the gallery image.
            heightOffset: 210, // Offset added to the height of the gallery image.
            closeWindow: 'div.buttons-set a[role="close-window"]' // Selector for closing the gallery popup window.
        },

        /**
         * Bind click handler for closing the popup window and resize the popup based on the image size.
         * @private
         */
        _create: function () {
            $(this.options.closeWindow).on('click', function () {
                window.close();
            });
            this._resizeWindow();
        },

        /**
         * Resize the gallery image popup window based on the image's dimensions.
         * @private
         */
        _resizeWindow: function () {
            var img = this.element,
                width = img.width() < this.options.minWidth ? this.options.minWidth : img.width();

            window.resizeTo(width + this.options.widthOffset, img.height() + this.options.heightOffset);
        }
    });

    return $.mage.gallery;
}));
