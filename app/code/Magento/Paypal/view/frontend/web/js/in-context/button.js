/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'Magento_Paypal/js/in-context/express-checkout-smart-buttons',
    'Magento_Customer/js/customer-data'
], function (Component, $, checkoutSmartButtons, customerData) {
    'use strict';

    return Component.extend({
        /**
         * @return {Object}
         */
        initialize: function (config, element) {
            this._super();
            this.prepareClientConfig();
            checkoutSmartButtons(this.prepareClientConfig(), element);

            return this;
        },

        /**
         *  Validates Smart Buttons
         */
        validate: function (actions) {
            this.clientConfig.buttonActions = actions || this.clientConfig.buttonActions;
            this.clientConfig.buttonActions.enable();
        },

        /**
         * Populate client config with all required data
         *
         * @return {Object}
         */
        prepareClientConfig: function () {
            this.clientConfig.client = {};
            this.clientConfig.client[this.clientConfig.environment] = this.clientConfig.merchantId;
            this.clientConfig.rendererComponent = this;
            this.clientConfig.formKey = $.mage.cookies.get('form_key');
            this.clientConfig.commit = false;

            return this.clientConfig;
        },

        /**
         * Adding logic to be triggered onClick action for smart buttons component
         */
        onClick: function () {},

        /**
         * Adds error message
         *
         * @param {String} message
         */
        addError: function (message) {
            customerData.set('messages', {
                messages: [{
                    type: 'error',
                    text: message
                }],
                'data_id': Math.floor(Date.now() / 1000)
            });
        }
    });
});
