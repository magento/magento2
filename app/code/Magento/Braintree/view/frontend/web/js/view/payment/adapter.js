/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'braintreeClient'
], function ($, braintreeClient) {
    'use strict';

    return {
        apiClient: null,
        checkout: null,
        code: 'braintree',

        /**
         * Returns Braintree API client
         * @returns {Object}
         */
        getApiClient: function () {
            return braintreeClient.create({
                authorization: this.getClientToken()
            });
        },

        /**
         * Returns payment code
         *
         * @returns {String}
         */
        getCode: function () {
            return this.code;
        },

        /**
         * Returns client token
         *
         * @returns {String}
         * @private
         */
        getClientToken: function () {
            return window.checkoutConfig.payment[this.code].clientToken;
        }
    };
});
