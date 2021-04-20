/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'uiElement',
    'Magento_Paypal/js/in-context/paypal-sdk'
], function (
    ko,
    Component,
    paypalSdk
) {
    'use strict';

    return Component.extend({

        defaults: {
            template: 'Magento_Paypal/paylater',
            sdkUrl: '',
            attributes: {
                'data-pp-amount': '',
                'data-pp-style-logo-position': 'right'
            }
        },

        /**
         * Initialize
         *
         * @returns {*}
         */
        initialize: function () {
            this._super();
            this.loadPayPalSdk(this.sdkUrl);

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
            paypalSdk(sdkUrl);
        }
    });
});
