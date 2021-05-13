/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'Magento_Paypal/js/view/paylater-default',
    'priceBox',
    'domReady!'
], function (
    $,
    ko,
    Component
) {
    'use strict';

    return Component.extend({

        defaults: {
            priceBoxSelector: '.price-box',
            qtyFieldSelector: '#product_addtocart_form [name="qty"]',
            refreshSelector: ''
        },
        qty: 1,
        price: 0,

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
                priceBox.priceBox('option').prices.finalPrice
            ) {
                this.price = priceBox.priceBox('option').prices.finalPrice.amount;
            }

            $(this.qtyFieldSelector).on('change', this._onQtyChange.bind(this));

            if (this.refreshSelector) {
                $(this.refreshSelector).on('click', this._refreshMessages.bind(this));
            }

            this._updateAmount();

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
            this.price = data.finalPrice.amount;
            this._updateAmount();
        },

        /**
         * Calculate and update amount
         *
         * @private
         */
        _updateAmount: function () {
            var amount = this.price * this.qty;

            if (amount !== 0) {
                this.amount(amount);
            }
        },

        /**
         * Render messages
         *
         * @private
         */
        _refreshMessages: function () {
            if (this.paypal) {
                this.paypal.Messages.render();
            }
        }
    });
});
