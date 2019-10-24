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
            this._showRelatedProducts(
                this.element.find(this.options.elementsSelector),
                this.element.data('limit'),
                this.element.data('shuffle')
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
            $(this.options.relatedCheckbox).attr(
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

        /**
         * Show related products according to limit. Shuffle if needed.
         * @param {*} elements
         * @param {*} limit
         * @param {*} shuffle
         * @private
         */
        _showRelatedProducts: function (elements, limit, shuffle) {
            var index;

            if (shuffle) {
                this._shuffle(elements);
            }

            if (limit === 0) {
                limit = elements.length;
            }

            for (index = 0; index < limit; index++) {
                $(elements[index]).show();
            }
        },

        /* jscs:disable */
        /* eslint-disable */
        /**
         * Shuffle an array
         * @param {Array} o
         * @returns {*}
         */
        _shuffle: function shuffle(o) { //v1.0
            for (var j, x, i = o.length; i; j = Math.floor(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
            return o;
        }

        /* jscs:disable */
        /* eslint:disable */
    });

    return $.mage.relatedProducts;
});
