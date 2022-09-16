/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/

define([
    'jquery',
    'uiElement',
    'uiRegistry',
    'domReady!'
], function (
    $,
    Component,
    registry
) {
    'use strict';

    return Component.extend({

        defaults: {
            tableWrapperSelector: '.table-wrapper.grouped',
            priceBoxSelector: '[data-role="priceBox"]',
            qtyFieldSelector: '.input-text.qty',
            amount: null
        },
        priceInfo: {},

        /**
         * Initialize
         *
         * @returns {*}
         */
        initialize: function () {
            var self = this;

            this._super();

            $('tbody tr', this.tableWrapperSelector).each(function (index, element) {
                var priceBox = $(self.priceBoxSelector, element),
                    qtyElement = $(self.qtyFieldSelector, element),
                    productId = priceBox.data('productId'),
                    priceElement = $('#product-price-' + productId);

                self.priceInfo[productId] = {
                    qty: self._getQty(qtyElement),
                    price: priceElement.data('priceAmount')
                };
            });

            $(this.qtyFieldSelector).on('change', this._onQtyChange.bind(this));

            this._updateAmount();

            return this;
        },

        /**
         * Get product quantity
         *
         * @param {jQuery.Element} element
         * @private
         */
        _getQty: function (element) {
            var qty = parseFloat(element.val());

            return !isNaN(qty) && qty ? qty : 0;
        },

        /**
         * Handle changed product quantity
         *
         * @param {jQuery.Event} event
         * @private
         */
        _onQtyChange: function (event) {
            var qtyElement = $(event.target),
                parent = qtyElement.parents('tr'),
                priceBox = $(this.priceBoxSelector, parent),
                productId = priceBox.data('productId');

            if (this.priceInfo[productId]) {
                this.priceInfo[productId].qty = this._getQty(qtyElement);
            }

            this._updateAmount();
        },

        /**
         * Calculate and update amount
         *
         * @private
         */
        _updateAmount: function () {
            var productId,
                amount = 0,
                payLater = registry.get(this.parentName);

            for (productId in this.priceInfo) {
                if (this.priceInfo.hasOwnProperty(productId)) {
                    amount += this.priceInfo[productId].price * this.priceInfo[productId].qty;
                }
            }

            payLater.amount(amount);
        }
    });
});
