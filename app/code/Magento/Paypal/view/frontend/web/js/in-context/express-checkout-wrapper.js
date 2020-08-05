/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/translate',
    'Magento_Customer/js/customer-data',
    'Magento_Paypal/js/in-context/express-checkout-smart-buttons',
    'Magento_Ui/js/modal/alert',
    'mage/cookies'
], function ($, $t, customerData, checkoutSmartButtons, alert) {
    'use strict';

    return {
        defaults: {
            paymentActionError: $t('Something went wrong with your request. Please try again later.'),
            signInMessage: $t('To check out, please sign in with your email address.')
        },

        /**
         * Render PayPal buttons using checkout.js
         */
        renderPayPalButtons: function (element) {
            checkoutSmartButtons(this.prepareClientConfig(), element);
        },

        /**
         * Validate payment method
         *
         * @param {Object} actions
         */
        validate: function (actions) {
            this.actions = actions || this.actions;
        },

        /**
         * Execute logic on Paypal button click
         */
        onClick: function () {},

        /**
         * Before payment execute
         *
         * @param {Function} resolve
         * @param {Function} reject
         * @return {*}
         */
        beforePayment: function (resolve, reject) { //eslint-disable-line no-unused-vars
            return $.Deferred().resolve();
        },

        /**
         * After payment execute
         *
         * @param {Object} res
         * @param {Function} resolve
         * @param {Function} reject
         *
         * @return {*}
         */
        afterPayment: function (res, resolve, reject) {

            if (res.success) {
                return resolve(res.token);
            }

            return reject(new Error(res['error_message']));
        },

        /**
         * Catch payment
         *
         * @param {Error} err
         * @param {Function} resolve
         * @param {Function} reject
         */
        catchPayment: function (err, resolve, reject) {
            this.addAlert(this.paymentActionError);
            reject(err);
        },

        /**
         * Before onAuthorize execute
         *
         * @param {Function} resolve
         * @param {Function} reject
         * @param {Object} actions
         *
         * @return {jQuery.Deferred}
         */
        beforeOnAuthorize: function (resolve, reject, actions) { //eslint-disable-line no-unused-vars
            //display loading widget.
            $('body').trigger('processStart');

            return $.Deferred().resolve();
        },

        /**
         * After onAuthorize execute
         *
         * @param {Object} res
         * @param {Function} resolve
         * @param {Function} reject
         * @param {Object} actions
         *
         * @return {*}
         */
        afterOnAuthorize: function (res, resolve, reject, actions) {
            $('body').trigger('processStop');

            if (res.success) {
                resolve();

                return actions.redirect(res.redirectUrl);
            }

            return reject(new Error(res['error_message']));
        },

        /**
         * Catch payment
         *
         * @param {Error} err
         * @param {Function} resolve
         * @param {Function} reject
         */
        catchOnAuthorize: function (err, resolve, reject) {
            $('body').trigger('processStop');
            this.addAlert(this.paymentActionError);
            reject(err);
        },

        /**
         * Process cancel action
         *
         * @param {Object} data
         * @param {Object} actions
         */
        onCancel: function (data, actions) {
            $('body').trigger('processStop');
            actions.redirect(this.clientConfig.onCancelUrl);
        },

        /**
         * Process errors
         *
         * @param {Error} err
         */
        onError: function (err) { //eslint-disable-line no-unused-vars
            // Uncaught error isn't displayed in the console
        },

        /**
         * Adds error message
         *
         * @param {String} message
         * @param {String} [type]
         */
        addError: function (message, type) {
            type = type || 'error';
            customerData.set('messages', {
                messages: [{
                    type: type,
                    text: message
                }],
                'data_id': Math.floor(Date.now() / 1000)
            });
        },

        /**
         * Add alert message
         *
         * @param {String} message
         */
        addAlert: function (message) {
            alert({
                content: message
            });
        },

        /**
         * @returns {String}
         */
        getButtonId: function () {
            return this.inContextId;
        },

        /**
         * Populate client config with all required data
         *
         * @return {Object}
         */
        prepareClientConfig: function () {
            this.clientConfig.rendererComponent = this;
            this.clientConfig.formKey = $.mage.cookies.get('form_key');

            return this.clientConfig;
        }
    };
});
