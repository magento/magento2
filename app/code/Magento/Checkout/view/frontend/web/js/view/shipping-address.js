/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Ui/js/form/component',
        'ko',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Customer/js/model/customer',
        '../model/quote',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function(Component, ko, selectShippingAddress, customer, quote, navigator) {
        var stepName = 'shippingAddress';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-address'
            },
            stepNumber: navigator.getStepNumber(stepName),
            addresses: customer.getShippingAddressList(),
            selectedAddressId: ko.observable(null),
            sameAsBilling: ko.observable(null),
            quoteHasBillingAddress: quote.getBillingAddress(),
            isVisible: navigator.isStepVisible(stepName),
            isActive: function() {
                if (quote.isVirtual()) {
                    navigator.setStepEnabled(stepName, false);
                }
                return !quote.isVirtual();
            },
            selectShippingAddress: function() {
                selectShippingAddress(this.selectedAddressId(), this.sameAsBilling());
            },
            sameAsBillingClick: function() {
                if (this.sameAsBilling()) {
                    var billingAddress = quote.getBillingAddress();
                    this.selectedAddressId(billingAddress().customerAddressId);
                }
                return true;
            },
            onAddressChange: function() {
                var billingAddress = quote.getBillingAddress();
                if (this.selectedAddressId() != billingAddress().customerAddressId) {
                    this.sameAsBilling(false);
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
