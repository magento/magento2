define([
    'jquery',
    'ko',
    'Magento_Paypal/js/in-context/paypal-sdk',
    'domReady!'
], function (
    $,
    ko,
    paypalSdk
) {
    'use strict';

    return {
        attributes: {},

        /**
         * Initialize PayLater
         *
         * @param {string} sdkUrl
         * @param {array} attributes
         */
        init: function (sdkUrl, attributes) {
            this.attributes = attributes;
            this.loadPayPalSdk(sdkUrl);
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
    };
});
