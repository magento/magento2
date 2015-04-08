/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global define*/
define(
    [
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Customer/js/model/customer',
        '../model/quote',
        'Magento_Checkout/js/model/step-navigator',
        '../model/addresslist'
    ],
    function(Component, ko, selectShippingAddress, customer, quote, navigator, addressList) {
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
                    selectShippingAddress(addressList.getAddressById(this.selectedAddressId()), this.sameAsBilling());
                } else {
                    this.validate();
                    if (!this.source.get('params.invalid')) {
                        var addressData = this.source.get('shippingAddress');
                        selectShippingAddress(addressData, this.sameAsBilling());
                    }
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
            },
            isNewAddressSelected: function() {
                return this.newAddressSelected();
            },
            isCustomerLoggedIn: customer.isLoggedIn(),
            customerHasAddresses: window.customerHasAddresses,

        });
    }
);
