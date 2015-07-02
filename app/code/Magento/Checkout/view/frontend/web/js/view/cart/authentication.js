/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'ko',
        'Magento_Ui/js/form/form',
        'Magento_Customer/js/action/login',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/cart/authentication-popup',
        'mage/translate',
        'mage/validation'
    ],
    function($, ko, Component, loginAction, customerData, authenticationPopup, $t) {
        'use strict';
        return Component.extend({
            registerUrl: window.checkout.customerRegisterUrl,
            forgotPasswordUrl: window.checkout.customerForgotPasswordUrl,
            modalWindow: null,
            isLoading: ko.observable(false),

            initialize: function() {
                var self = this;
                this._super();
                loginAction.registerLoginCallback(function() {
                    self.isLoading(false);
                });
            },

            /** Init popup login window */
            setModalElement: function (element) {
                authenticationPopup.createPopUp(element);
            },

            /** Is login form enabled for current customer */
            isActive: function() {
                var customer = customerData.get('customer');
                return customer() == false;
            },

            /** Show login popup window */
            showModal: function() {
                if (this.modalWindow) {
                    $(this.modalWindow).modal('openModal');
                } else {
                    alert($t('Guest checkout is disabled.'));
                }
            },

            /** Provide login action */
            login: function(loginForm) {
                var loginData = {},
                    formDataArray = $(loginForm).serializeArray();
                formDataArray.forEach(function (entry) {
                    loginData[entry.name] = entry.value;
                });

                if($(loginForm).validation()
                    && $(loginForm).validation('isValid')
                ) {
                    this.isLoading(true);
                    loginAction(loginData, null, false);
                }
            }
        });
    }
);
