/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'Magento_Paypal/js/view/payment/method-renderer/paypal-express-abstract',
    'Magento_Paypal/js/action/set-payment-method',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/model/messageList',
    'Magento_Paypal/js/in-context/express-checkout-smart-buttons',
    'Magento_Ui/js/lib/view/utils/async'
], function (
    $,
    _,
    Component,
    setPaymentMethodAction,
    additionalValidators,
    domObserver,
    customerData,
    messageList,
    checkoutSmartButtons
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Paypal/payment/paypal-express-in-context',
            validationElements: 'input'
        },

        /**
         * Listens element on change and validate it.
         *
         * @param {HTMLElement} context
         */
        initListeners: function (context) {
            $.async(this.validationElements, context, function (element) {
                $(element).on('change', function () {
                    this.validate();
                }.bind(this));
            }.bind(this));
        },

        /**
         *  Validates Smart Buttons
         */
        validate: function (actions) {
            this.clientConfig.buttonActions = actions || this.clientConfig.buttonActions;

            if (this.clientConfig.buttonActions) {
                additionalValidators.validate(true) ?
                    this.clientConfig.buttonActions.enable() :
                    this.clientConfig.buttonActions.disable();
            }
        },

        /**
         * Render PayPal buttons using checkout.js
         */
        renderPayPalButtons: function () {
            checkoutSmartButtons(this.prepareClientConfig(), '#' + this.getButtonId());
        },

        /**
         * @returns {String}
         */
        getButtonId: function () {
            return this.inContextId;
        },

        /**
         * @returns {String}
         */
        getAgreementId: function () {
            return this.inContextId + '-agreement';
        },

        /**
         * Populate client config with all required data
         *
         * @return {Object}
         */
        prepareClientConfig: function () {
            this.clientConfig.quoteId = window.checkoutConfig.quoteData['entity_id'];
            this.clientConfig.formKey = window.checkoutConfig.formKey;
            this.clientConfig.customerId = window.customerData.id;
            this.clientConfig.button = 0;
            this.clientConfig.merchantId = this.merchantId;
            this.clientConfig.client = {};
            this.clientConfig.client[this.clientConfig.environment] = this.merchantId;
            this.clientConfig.additionalAction = setPaymentMethodAction;
            this.clientConfig.rendererComponent = this;
            this.clientConfig.messageContainer = this.messageContainer;
            this.clientConfig.commit = true;

            return this.clientConfig;
        },

        /**
         * Adding logic to be triggered onClick action for smart buttons component
         */
        onClick: function() {
            additionalValidators.validate();
            this.selectPaymentMethod();
        },

        /**
         * Adds error message
         *
         * @param {string} message
         */
        addError: function(message) {
            messageList.addErrorMessage({
                message: message
            });
        }
    });
});
