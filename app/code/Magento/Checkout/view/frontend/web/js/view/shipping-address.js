/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        "jquery",
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Customer/js/model/customer',
        '../model/quote',
        'Magento_Checkout/js/model/step-navigator',
        '../model/addresslist',
        'mage/translate'
    ],
    function($, Component, ko, selectShippingAddress, customer, quote, navigator, addressList) {
        'use strict';
        var stepName = 'shippingAddress';
        var newAddressSelected = ko.observable(false);
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-address',
                visible: true,
                formVisible: customer.getShippingAddressList().length === 0
            },
            quoteObj: quote.getShippingAddress(),
            stepNumber: navigator.getStepNumber(stepName),
            isVisible: navigator.isStepVisible(stepName),
            isCustomerLoggedIn: customer.isLoggedIn(),
            customerAddressCount: window.checkoutConfig.customerAddressCount,

            stepClassAttributes: function() {
                return navigator.getStepClassAttributes(stepName);
            },

            /** Get all customer addresses  */
            addresses: function() {
                return addressList.getAddresses();
            },

            /** Initialize observable properties */
            initObservable: function () {
                this._super()
                    .observe('visible');
                return this;
            },

            /** Check if component is active */
            isActive: function() {
                if (quote.isVirtual()) {
                    navigator.setStepEnabled(stepName, false);
                }
                return !quote.isVirtual();
            },

            /** Set selected customer shipping address  */
            selectAddress: function(address) {
                quote.setShippingAddress(address);
            },

            /** Navigate to current step */
            navigateToCurrentStep: function() {
                if (!navigator.isStepVisible(stepName)()) {
                    navigator.goToStep(stepName);
                }
            }
        });
    }
);
