/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        "jquery",
        'ko',
        'Magento_Ui/js/form/form',
        'Magento_Customer/js/action/login',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/step-navigator',
        '../model/quote',
        'mage/validation'
    ],
    function($, ko, Component, login, customer, navigator, quote) {
        "use strict";
        var stepName = 'authentication';
        return Component.extend({
            stepNumber: navigator.getStepNumber(stepName),
            isGuestCheckoutAllowed: window.checkoutConfig.isGuestCheckoutAllowed,
            isCustomerLoginRequired: window.checkoutConfig.isCustomerLoginRequired,
            registerUrl: window.checkoutConfig.registerUrl,
            forgotPasswordUrl: window.checkoutConfig.forgotPasswordUrl,
            username: '',
            password: '',
            isVisible: navigator.isStepVisible(stepName),
            defaults: {
                template: 'Magento_Checkout/authentication'
            },
            login: function(loginForm) {
                var loginData = {};
                var formDataArray = $(loginForm).serializeArray();
                var loginFormSelector = 'form[data-role=login]';
                formDataArray.forEach(function (entry) {
                    loginData[entry.name] = entry.value;
                });
                if($(loginFormSelector).validation() && $(loginFormSelector).validation('isValid')) {
                    login(loginData);
                }
            },
            stepClassAttributes: function() {
                return navigator.getStepClassAttributes(stepName);
            },
            isActive: function() {
                if (customer.isLoggedIn()()) {
                    navigator.setStepEnabled(stepName, false);
                }
                return !customer.isLoggedIn()();
            },
            isChecked: function() {
                if (!isGuestCheckoutAllowed) {
                    return 'register';
                }
                return false;
            },
            setCheckoutMethod: function() {
                quote.setCheckoutMethod('guest');
                $('[name="customerDetails.password"]').hide();
                $('[name="customerDetails.confirm_password"]').hide();
                $('[name*=".save_in_address_book"]').hide();
                navigator.setCurrent('authentication').goNext();
            },
            navigateToCurrentStep: function() {
                if (!navigator.isStepVisible(stepName)()) {
                    navigator.goToStep(stepName);
                }
            }
        });
    }
);
