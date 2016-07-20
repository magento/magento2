/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'braintree',
    'Magento_Ui/js/model/messageList',
    'mage/translate'
], function ($, braintree, globalMessageList, $t) {
    'use strict';

    return {
        apiClient: null,
        config: {},
        checkout: null,

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
         * Set configuration
         * @param {Object} config
         */
        setConfig: function (config) {
            this.config = config;
        },

        /**
         * Setup Braintree SDK
         */
        setup: function () {
            if (!this.getClientToken()) {
                this.showError($t('Sorry, but something went wrong.'));
            }

            braintree.setup(this.getClientToken(), 'custom', this.config);
        },

        /**
         * Get payment name
         * @returns {String}
         */
        getCode: function () {
            return 'braintree';
        },

        /**
         * Get client token
         * @returns {String|*}
         */
        getClientToken: function () {

            return window.checkoutConfig.payment[this.getCode()].clientToken;
        },

        /**
         * Show error message
         *
         * @param {String} errorMessage
         */
        showError: function (errorMessage) {
            globalMessageList.addErrorMessage({
                message: errorMessage
            });
        },

        /**
         * May be triggered on Braintree SDK setup
         */
        onReady: function () {}
    };
});
