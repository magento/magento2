/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery-ui-modules/widget'
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
            if (this.element.data('shuffle')) {
                this._shuffle(this.element.find(this.options.elementsSelector));
            }
            this._showUpsellProducts(
                this.element.find(this.options.elementsSelector),
                this.element.data('limit'),
                this.element.data('shuffle-weighted')
            );
        },

        /* jscs:disable */
        /* eslint-disable */
        /**
         * Show upsell products according to limit. Shuffle if needed.
         * @param {*} elements
         * @param {Number} limit
         * @param {Boolean} weightedRandom
         * @private
         */
        _showUpsellProducts: function (elements, limit, weightedRandom) {
            var index, weights = [], random = [], weight = 2, shown = 0, $element, currentGroup, prevGroup;

            if (limit === 0) {
                limit = elements.length;
            }

            if (weightedRandom && limit > 0 && limit < elements.length) {
                for (index = 0; index < limit; index++) {
                    $element = $(elements[index]);
                    if ($element.data('shuffle-group') !== '') {
                        break;
                    }
                    $element.show();
                    shown++;
                }
                limit -= shown;
                for (index = elements.length - 1; index >= 0; index--) {
                    $element = $(elements[index]);
                    currentGroup = $element.data('shuffle-group');
                    if (currentGroup !== '') {
                        weights.push([index, Math.log(weight)]);
                        if (typeof prevGroup !== 'undefined' && prevGroup !== currentGroup) {
                            weight += 2;
                        }
                        prevGroup = currentGroup;
                    }
                }

                if (weights.length === 0) {
                    return;
                }

                for (index = 0; index < weights.length; index++) {
                    random.push([weights[index][0], Math.pow(Math.random(), 1 / weights[index][1])]);
                }

                random.sort(function(a, b) {
                    a = a[1];
                    b = b[1];
                    return a < b ? 1 : (a > b ? -1 : 0);
                });
                index = 0;
                while (limit) {
                    $(elements[random[index][0]]).show();
                    limit--;
                    index++
                }
                return;
            }

            for (index = 0; index < limit; index++) {
                $(elements[index]).show();
            }
        },

        /* jscs:disable */
        /* eslint-disable */
        /**
         * Shuffle an array
         * @param elements
         * @returns {*}
         */
        _shuffle: function shuffle(elements){ //v1.0
            var parent, child, lastSibling;
            if (elements.length) {
                parent = $(elements[0]).parent();
            }
            while (elements.length) {
                child = elements.splice(Math.floor(Math.random() *  elements.length), 1)[0];
                lastSibling = parent.find('[data-shuffle-group="' + $(child).data('shuffle-group') + '"]').last();
                lastSibling.after(child);
            }
        }

        /* jscs:disable */
        /* eslint:disable */
    });

    return $.mage.upsellProducts;
});
