/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.upsellProducts', {
        options: {
            elementsSelector: '.item.product'
        },

        /**
         * Bind events to the appropriate handlers.
         * @private
         */
        _create: function () {
            this._showUpsellProducts(
                this.element.find(this.options.elementsSelector),
                this.element.data('limit'),
                this.element.data('shuffle')
            );
        },

        /**
         * Show upsell products according to limit. Shuffle if needed.
         * @param {*} elements
         * @param {Number} limit
         * @param {Boolean} shuffle
         * @private
         */
        _showUpsellProducts: function (elements, limit, shuffle) {
            var index;

            if (shuffle) {
                this._shuffle(elements);
            }

            if (limit === 0) {
                limit = elements.length;
            }

            for (index = 0; index < limit; index++) {
                $(this.element).find(elements[index]).show();
            }
        },

        /* jscs:disable */
        /* eslint-disable */
        /**
         * Shuffle an array
         * @param o
         * @returns {*}
         */
        _shuffle: function shuffle(o){ //v1.0
            for (var j, x, i = o.length; i; j = Math.floor(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
            return o;
        }

        /* jscs:disable */
        /* eslint:disable */
    });

    return $.mage.upsellProducts;
});
