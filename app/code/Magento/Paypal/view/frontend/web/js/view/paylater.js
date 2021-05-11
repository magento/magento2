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
            displayAmount: true,
            attributes: {}
        },
        amount: ko.observable(),
        qty: 1,
        price: 0,
        style: '',

        /**
         * Initialize
         *
         * @returns {*}
         */
        initialize: function () {
            var priceBox, qty;

            this._super();

            if (window.checkoutConfig && window.checkoutConfig.payment.paypalPayLater.enabled) {
                const config = window.checkoutConfig.payment.paypalPayLater.config;
                this.sdkUrl = config.sdkUrl;
                this.attributes = config.attributes;
                this.style = 'margin-bottom: 10px;';
            }

            if ( this.sdkUrl === '') {
                return this;
            }

            this.loadPayPalSdk(this.sdkUrl);

            if (this.displayAmount) {
                priceBox = $(this.priceBoxSelector);

                if (priceBox.priceBox('option') &&
                    priceBox.priceBox('option').prices
                ) {
                    this.price = priceBox.priceBox('option').prices.finalPrice.amount;
                    priceBox.on('priceUpdated', this._onPriceChange.bind(this));
                }

                qty = $(this.qtyFieldSelector);
                qty.on('change', this._onQtyChange.bind(this));

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
         *
         * @param {String} sdkUrl
         */
        loadPayPalSdk: function (sdkUrl) {
            paypalSdk(sdkUrl);
        }
    });
});
