/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/form/form',
    'Magento_Customer/js/action/login',
    'Magento_Customer/js/model/customer',
    'mage/validation',
    'Magento_Checkout/js/model/authentication-messages',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, Component, loginAction, customer, validation, messageContainer, fullScreenLoader) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return Component.extend({
        isGuestCheckoutAllowed: checkoutConfig.isGuestCheckoutAllowed,
        isCustomerLoginRequired: checkoutConfig.isCustomerLoginRequired,
        registerUrl: checkoutConfig.registerUrl,
        forgotPasswordUrl: checkoutConfig.forgotPasswordUrl,
        autocomplete: checkoutConfig.autocomplete,
        defaults: {
            template: 'Magento_Checkout/authentication'
        },

        /**
         * Is login form enabled for current customer.
         *
         * @return {Boolean}
         */
        isActive: function () {
            return !customer.isLoggedIn();
        },

        /**
         * Provide login action.
         *
         * @param {HTMLElement} loginForm
         */
        login: function (loginForm) {
            var loginData = {},
                formDataArray = $(loginForm).serializeArray();

            formDataArray.forEach(function (entry) {
                loginData[entry.name] = entry.value;
            });

            if ($(loginForm).validation() &&
                $(loginForm).validation('isValid')
            ) {
                fullScreenLoader.startLoader();
                loginAction(loginData, checkoutConfig.checkoutUrl, undefined, messageContainer).always(function () {
                    fullScreenLoader.stopLoader();
                });
            }
        }
    });
});
