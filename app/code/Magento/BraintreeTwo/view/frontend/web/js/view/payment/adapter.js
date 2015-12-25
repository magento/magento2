/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'braintree'
], function ($, braintree) {
    'use strict';

    return {
        apiClient: null,

        /**
         * Get Braintree api client
         * @returns {Object}
         */
        getApiClient: function () {
            if (!this.apiClient) {
                this.apiClient = new braintree.api.Client({
                    clientToken: this.getClientToken()
                });
            }

            return this.apiClient;
        },

        /**
         * Get Braintree SDK client
         * @returns {Object}
         */
        getSdkClient: function () {
            return braintree;
        },

        /**
         * Get payment name
         * @returns {String}
         */
        getCode: function () {
            return 'braintreetwo';
        },

        /**
         * Get client token
         * @returns {String|*}
         */
        getClientToken: function () {

            return window.checkoutConfig.payment[this.getCode()].clientToken;
        }
    };
});
