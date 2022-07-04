/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery-ui-modules/widget',
    'mage/translate'
], function ($) {
    'use strict';

    $.widget('mage.relatedProducts', {
        options: {
            relatedCheckbox: '.related-checkbox', // Class name for a related product's input checkbox.
            relatedProductsCheckFlag: false, // Related products checkboxes are initially unchecked.
            relatedProductsField: '#related-products-field', // Hidden input field that stores related products.
            selectAllMessage: $.mage.__('select all'),
            unselectAllMessage: $.mage.__('unselect all'),
            selectAllLink: '[data-role="select-all"]',
            elementsSelector: '.item.product'
        },

        /**
         * Bind events to the appropriate handlers.
         * @private
         */
        _create: function () {
            $(this.options.selectAllLink, this.element).on('click', $.proxy(this._selectAllRelated, this));
            $(this.options.relatedCheckbox, this.element).on('click', $.proxy(this._addRelatedToProduct, this));

            if (this.element.data('shuffle')) {
                this._shuffle(this.element.find(this.options.elementsSelector));
            }
            this._showRelatedProducts(
                this.element.find(this.options.elementsSelector),
                this.element.data('limit'),
                this.element.data('shuffle-weighted')
            );
        },

        /**
         * This method either checks all checkboxes for a product's set of related products (select all)
         * or unchecks them (unselect all).
         * @private
         * @param {jQuery.Event} e - Click event on either the "select all" link or the "unselect all" link.
         * @return {Boolean} - Prevent default event action and event propagation.
         */
        _selectAllRelated: function (e) {
            var innerHTML = this.options.relatedProductsCheckFlag ?
                this.options.selectAllMessage : this.options.unselectAllMessage;

            $(e.target).html(innerHTML);
            $(this.options.relatedCheckbox + ':visible').attr(
                'checked',
                this.options.relatedProductsCheckFlag = !this.options.relatedProductsCheckFlag
            );
            this._addRelatedToProduct();

            return false;
        },

        /**
         * This method iterates through each checkbox for all related products and collects only those products
         * whose checkbox has been checked. The selected related products are stored in a hidden input field.
         * @private
         */
        _addRelatedToProduct: function () {
            $(this.options.relatedProductsField).val(
                $(this.options.relatedCheckbox + ':checked').map(function () {
                    return this.value;
                }).get().join(',')
            );
        },

        /* jscs:disable */
        /* eslint-disable */
        /**
         * Show related products according to limit. Shuffle if needed.
         * @param {*} elements
         * @param {*} limit
         * @param weightedRandom
         * @private
         */
        _showRelatedProducts: function (elements, limit, weightedRandom) {
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
         * @param {Array} elements
         * @returns {*}
         */
        _shuffle: function shuffle(elements) {
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

    return $.mage.relatedProducts;
});
