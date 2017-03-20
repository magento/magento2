/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.float', {
        options: {
            productOptionsSelector: '#product-options-wrapper'
        },

        /**
         * Bind handlers to scroll event
         * @private
         */
        _create: function () {
            $(window).on('scroll', $.proxy(this._setTop, this));
        },

        /**
         * float bundleSummary on windowScroll
         * @private
         */
        _setTop: function () {
            var starTop, offset, maxTop, allowedTop;

            if (this.element.is(':visible')) {
                starTop = $(this.options.productOptionsSelector).offset().top;
                offset = $(document).scrollTop();
                maxTop = this.element.parent().offset().top;

                if (!this.options.top) {
                    this.options.top = this.element.position().top;
                    this.element.css('top', this.options.top);
                }

                if (starTop > offset) {
                    return false;
                }

                if (offset < this.options.top) {
                    offset = this.options.top;
                }

                allowedTop = this.options.top + offset - starTop;

                if (allowedTop < maxTop) {
                    this.element.css('top', allowedTop);
                }
            }
        }
    });
});
