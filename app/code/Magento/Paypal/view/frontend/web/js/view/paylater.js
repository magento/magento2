/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiElement',
    'Magento_Paypal/js/in-context/paypal-sdk',
    'priceBox',
    'domReady!'
], function (
    $,
    ko,
    Component,
    paypalSdk
) {
    'use strict';

    return Component.extend({

        defaults: {
            template: 'Magento_Paypal/paylater',
            sdkUrl: '',
            priceBoxSelector: '.price-box',
            qtyFieldSelector: '#product_addtocart_form [name="qty"]',
            refreshSelector: '',
            displayAmount: true,
            attributes: {}
        },
        amount: ko.observable(),
        qty: 1,
        price: 0,
        paypal: null,

        /**
         * Initialize
         *
         * @returns {*}
         */
        initialize: function () {
            var priceBox;

            this._super();
            this.loadPayPalSdk()
                .then(this._setPayPalObject.bind(this));

            if (this.displayAmount) {
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
            }

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
         * Get attribute value from configuration
         *
         * @param {String} attributeName
         * @returns {*|null}
         */
        getAttribute: function (attributeName) {
            return typeof this.attributes[attributeName] !== 'undefined' ?
                this.attributes[attributeName] : null;
        },

        /**
         * Load PP SDK with preconfigured options
         */
        loadPayPalSdk: function () {
            return paypalSdk(this.sdkUrl);
        },

        /**
         * Set reference to paypal Sdk object
         *
         * @param {Object} paypal
         * @private
         */
        _setPayPalObject: function (paypal) {
            this.paypal = paypal;
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
