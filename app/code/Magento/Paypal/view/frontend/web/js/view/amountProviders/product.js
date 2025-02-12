/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/

define([
    'jquery',
    'uiElement',
    'uiRegistry',
    'priceBox',
    'domReady!'
], function (
    $,
    Component,
    registry
) {
    'use strict';

    return Component.extend({

        defaults: {
            priceBoxSelector: '.price-box',
            qtyFieldSelector: '#product_addtocart_form [name="qty"]',
            amount: null
        },
        qty: 1,
        price: 0,
        priceType: '',

        /**
         * Initialize
         *
         * @returns {*}
         */
        initialize: function () {
            var priceBox;

            this._super();

            priceBox = $(this.priceBoxSelector);
            priceBox.on('priceUpdated', this._onPriceChange.bind(this));

            if (priceBox.priceBox('option') &&
                priceBox.priceBox('option').prices &&
                (priceBox.priceBox('option').prices.finalPrice || priceBox.priceBox('option').prices.basePrice)
            ) {
                this.priceType = priceBox.priceBox('option').prices.finalPrice ? 'finalPrice' : 'basePrice';
                this.price = priceBox.priceBox('option').prices[this.priceType].amount;
            }

            $(this.qtyFieldSelector).on('change', this._onQtyChange.bind(this));

            priceBox.trigger('updatePrice');

            return this;
        },

        /**
         * Handle changed product qty
         *
         * @param {jQuery.Event} event
         * @private
         */
        _onQtyChange: function (event) {
            var qty = parseFloat($(event.target).val());

            this.qty = !isNaN(qty) && qty ? qty : 1;
            this._updateAmount();
        },

        /**
         * Handle product price change
         *
         * @param {jQuery.Event} event
         * @param {Object} data
         * @private
         */
        _onPriceChange: function (event, data) {
            this.price = data[this.priceType].amount;
            this._updateAmount();
        },

        /**
         * Calculate and update amount
         *
         * @private
         */
        _updateAmount: function () {
            var amount = this.price * this.qty,
                payLater = registry.get(this.parentName);

            if (amount !== 0) {
                payLater.amount(amount);
            }
        }
    });
});
