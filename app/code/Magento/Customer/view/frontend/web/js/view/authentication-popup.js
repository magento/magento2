/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/form',
    'Magento_Customer/js/action/login',
    'Magento_Customer/js/customer-data',
    'Magento_Customer/js/model/authentication-popup',
    'mage/translate'
], function ($, ko, Component, loginAction, customerData, authenticationPopup) {
    'use strict';

    return Component.extend({
        registerUrl: window.authenticationPopup.customerRegisterUrl,
        forgotPasswordUrl: window.authenticationPopup.customerForgotPasswordUrl,
        autocomplete: window.authenticationPopup.autocomplete,
        modalWindow: null,
        isLoading: ko.observable(false),

        defaults: {
            template: 'Magento_Customer/authentication-popup'
        },

        /**
         * Init
         */
        initialize: function () {
            this._super();

            loginAction.registerLoginCallback(function () {
                this.isLoading(false);
            }.bind(this));
        },

        /** Init popup login window */
        setModalElement: function (element) {
            if (authenticationPopup.modalWindow == null) {
                authenticationPopup.createPopUp(element);
            }
        },

        /** Is login form enabled for current customer */
        isActive: function () {
            var customer = customerData.get('customer');

            return customer() == false; //eslint-disable-line eqeqeq
        },

        /** Show login popup window */
        showModal: function () {
            if (this.modalWindow) {
                $(this.modalWindow).modal('openModal');
            } else {
                require([
                    'mage/translate',
                    'Magento_Ui/js/modal/alert'
                ], function ($t, alert) {
                    alert({
                        content: $t('Guest checkout is disabled.')
                    });
                });
            }
        },

        /**
         * Provide login action
         *
         * @return {Boolean}
         */
        login: function (formUiElement, event) {
            var loginData = {},
                formElement = $(event.currentTarget),
                formDataArray = formElement.serializeArray();

            event.stopPropagation();
            formDataArray.forEach(function (entry) {
                loginData[entry.name] = entry.value;
            });

            require(['mage/validation'], function () {
                if (formElement.validation() &&
                    formElement.validation('isValid')
                ) {
                    this.isLoading(true);
                    loginAction(loginData);
                }
            });

            return false;
        }
    });
});
