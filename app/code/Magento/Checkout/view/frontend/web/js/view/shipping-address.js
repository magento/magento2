/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'uiComponent',
        'ko',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Customer/js/model/customer',
        '../model/quote',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function(Component, ko, selectShippingAddress, customer, quote, navigator) {
        'use strict';
        var stepName = 'shippingAddress';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-address'
            },
            stepNumber: navigator.getStepNumber(stepName),
            addresses: customer.getShippingAddressList(),
            selectedAddressId: ko.observable(1),
            sameAsBilling: ko.observable(null),
            quoteHasBillingAddress: quote.getBillingAddress(),
            newAddressSelected: ko.observable(false),
            isVisible: navigator.isStepVisible(stepName),
            isActive: function() {
                if (quote.isVirtual()) {
                    navigator.setStepEnabled(stepName, false);
                }
                return !quote.isVirtual();
            },
            selectShippingAddress: function() {
                if (!this.newAddressSelected()) {
                    selectShippingAddress(this.selectedAddressId(), this.sameAsBilling());
                } else {
                    alert('save new address');
                }
            },
            sameAsBillingClick: function() {
                if (this.sameAsBilling()) {
                    var billingAddress = quote.getBillingAddress();
                    this.selectedAddressId(billingAddress().customerAddressId);
                    this.newAddressSelected(false);
                }
                return true;
            },
            onAddressChange: function() {
                var billingAddress = quote.getBillingAddress();
                if (this.selectedAddressId() != billingAddress().customerAddressId) {
                    this.sameAsBilling(false);
                }
                if (this.selectedAddressId() == null) {
                    this.newAddressSelected(true);
                } else {
                    this.newAddressSelected(false);
                }
            },
            // Checkout step navigation
            backToBilling: function() {
                navigator.setCurrent(stepName).goBack();
            },
            navigateToCurrentStep: function() {
                if (!navigator.isStepVisible(stepName)()) {
                    navigator.goToStep(stepName);
                }
            }
        });
    }
);
