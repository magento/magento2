/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiElement',
    'Magento_Paypal/js/in-context/paypal-sdk',
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
            attributes: {},
            amount: ko.observable(),
            style: ''
        },
        paypal: null,

        /**
         * Initialize
         *
         * @returns {*}
         */
        initialize: function () {
            this._super();

            if (this.sdkUrl !== '') {
                this.loadPayPalSdk(this.sdkUrl)
                    .then(this._setPayPalObject.bind(this));
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
         * @param {String} sdkUrl
         */
        loadPayPalSdk: function (sdkUrl) {
            return paypalSdk(sdkUrl);
        },

        /**
         * Set reference to paypal Sdk object
         *
         * @param {Object} paypal
         * @private
         */
        _setPayPalObject: function (paypal) {
            this.paypal = paypal;
        }
    });
});
