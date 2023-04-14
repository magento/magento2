/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiElement',
    'uiLayout',
    'Magento_Paypal/js/in-context/paypal-sdk',
    'domReady!'
], function (
    $,
    ko,
    Component,
    layout,
    paypalSdk
) {
    'use strict';

    return Component.extend({

        defaults: {
            template: 'Magento_Paypal/paylater',
            sdkUrl: '',
            attributes: {
                class: 'pay-later-message'
            },
            dataAttributes: {},
            refreshSelector: '',
            displayAmount: false,
            amountComponentConfig: {
                name: '${ $.name }.amountProvider',
                component: ''
            }
        },
        paypal: null,
        amount: null,

        /**
         * Initialize
         *
         * @returns {*}
         */
        initialize: function () {
            this._super()
                .observe(['amount']);

            if (this.displayAmount) {
                layout([this.amountComponentConfig]);
            }

            if (this.sdkUrl !== '') {
                this.loadPayPalSdk(this.sdkUrl, this.dataAttributes)
                    .then(this._setPayPalObject.bind(this));
            }

            if (this.refreshSelector) {
                $(this.refreshSelector).on('click', this._refreshMessages.bind(this));
            }

            return this;
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
         * @param {String} sdkUrl - the url of the PayPal SDK
         * @param {Array} dataAttributes - Array of the Attributes for PayPal SDK Script tag
         */
        loadPayPalSdk: function (sdkUrl, dataAttributes) {
            return paypalSdk(sdkUrl, dataAttributes);
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
