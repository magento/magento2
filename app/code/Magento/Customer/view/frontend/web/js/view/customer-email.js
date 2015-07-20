/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'uiComponent',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/action/check-email-availability',
        'Magento_Customer/js/action/login',
        'Magento_Checkout/js/model/quote',
        'mage/validation'
    ],
    function ($, Component, ko, customer, checkEmailAvailability, loginAction, quote) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_Customer/customer-email',
                email: '',
                isLoading: false,
                isPasswordVisible: false
            },
            checkDelay: 2000,
            checkRequest: null,
            isEmailCheckComplete: null,
            isCustomerLoggedIn: customer.isLoggedIn,
            forgotPasswordUrl: window.checkoutConfig.forgotPasswordUrl,
            emailCheckTimeout: 0,

            initialize: function() {
                this._super();
                var self = this;
                this.email.subscribe(function() {
                    self.emailHasChanged();
                });
            },

            /** Initialize observable properties */
            initObservable: function () {
                this._super()
                    .observe(['email', 'isLoading', 'isPasswordVisible']);
                return this;
            },

            emailHasChanged: function () {
                var self = this;
                clearTimeout(this.emailCheckTimeout);
                if (self.validateEmail()) {
                    quote.guestEmail = self.email();
                }
                this.emailCheckTimeout = setTimeout(function () {
                    if (self.validateEmail()) {
                        self.checkEmailAvailability();
                    } else {
                        self.isPasswordVisible(false);
                    }
                }, self.checkDelay);

            },

            checkEmailAvailability: function() {
                var self = this;
                this.validateRequest();
                this.isEmailCheckComplete = $.Deferred();
                this.isLoading(true);
                this.checkRequest = checkEmailAvailability(this.isEmailCheckComplete, this.email());

                $.when(this.isEmailCheckComplete).done(function() {
                    self.isPasswordVisible(false);
                }).fail( function() {
                    self.isPasswordVisible(true);
                }).always(function () {
                    self.isLoading(false);
                });
            },

            validateRequest: function() {
                /*
                 * If request has been sent -> abort it.
                 * ReadyStates for request aborting:
                 * 1 - The request has been set up
                 * 2 - The request has been sent
                 * 3 - The request is in process
                 */
                if (this.checkRequest != null && $.inArray(this.checkRequest.readyState, [1, 2, 3])) {
                    this.checkRequest.abort();
                    this.checkRequest = null;
                }
            },

            validateEmail: function() {
                var loginFormSelector = 'form[data-role=email-with-possible-login]';
                $(loginFormSelector).validation();
                var validationResult = $(loginFormSelector + ' input[name=username]').valid();
                return Boolean(validationResult);
            },

            login: function(loginForm) {
                var loginData = {},
                    formDataArray = $(loginForm).serializeArray();

                formDataArray.forEach(function (entry) {
                    loginData[entry.name] = entry.value;
                });
                if (this.isPasswordVisible()
                    && $(loginForm).validation()
                    && $(loginForm).validation('isValid')
                ) {
                    loginAction(loginData);
                }
            }
        });
    }
);
