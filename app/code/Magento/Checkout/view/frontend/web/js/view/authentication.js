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
            isRegistrationAllowed: window.checkoutConfig.isRegistrationAllowed,
            isMethodRegister: window.checkoutConfig.isMethodRegister,
            isCustomerLoginRequired: window.checkoutConfig.isCustomerLoginRequired,
            registerUrl: window.checkoutConfig.registerUrl,
            forgotPasswordUrl: '',
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
            isActive: function() {
                if (customer.isLoggedIn()()) {
                    navigator.setStepEnabled(stepName, false);
                }
                return !customer.isLoggedIn()();
            },
            isChecked: function() {
                if (isMethodRegister || !isGuestCheckoutAllowed) {
                    return 'register';
                }
                return false;
            },
            setCheckoutMethod: function() {
                var guestChecked    = $( '[data-role=checkout-method-guest]' ).is( ':checked' );
                var registerChecked = $( '[data-role=checkout-method-register]').is( ':checked' );
                if( !guestChecked && !registerChecked ){
                    alert('Please choose to register or to checkout as a guest.');
                    return false;
                }
                if (guestChecked) {
                    quote.setCheckoutMethod('guest');
                    $('[name="customerDetails.password"]').hide();
                    $('[name="customerDetails.confirm_password"]').hide();
                }
                if (registerChecked) {
                    quote.setCheckoutMethod('register');
                    $('[name="customerDetails.password"]').show();
                    $('[name="customerDetails.confirm_password"]').show();
                }
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
