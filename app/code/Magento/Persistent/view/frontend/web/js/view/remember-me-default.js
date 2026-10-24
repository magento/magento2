/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

define(
    [
        'ko',
        'uiComponent',
        'Magento_Customer/js/customer-data'
    ],
    function (ko, Component, customerData) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Persistent/remember-me'
            },
            dataScope: 'global',
            isRememberMeCheckboxVisible: ko.observable(false),
            isRememberMeCheckboxChecked: ko.observable(false),

            /** @inheritdoc */
            initialize: function () {
                this._super();

                this.showElement();
            },

            /**
             * Show remember me checkbox on certain conditions
             */
            showElement: function () {
                let cart = customerData.get('cart'),
                    persistenceConfig = window.rememberMeConfig.persistenceConfig;

                if (cart().isGuestCheckoutAllowed !== false) {
                    persistenceConfig.isRememberMeCheckboxVisible = false;
                } else {
                    cart.subscribe(function (cartData) {
                        if (cartData.isGuestCheckoutAllowed !== false) {
                            persistenceConfig.isRememberMeCheckboxVisible = false;
                        }
                    }, this);
                }

                this.isRememberMeCheckboxChecked = ko.observable(persistenceConfig.isRememberMeCheckboxChecked);
                this.isRememberMeCheckboxVisible = ko.observable(persistenceConfig.isRememberMeCheckboxVisible);
            }
        });
    }
);
