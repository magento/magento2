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
        '../model/addresslist'
    ],
    function($, Component, ko, selectShippingAddress, customer, quote, navigator, addressList) {
        'use strict';
        var stepName = 'shippingAddress';
        var newAddressSelected = ko.observable(false);
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-address'
            },
            stepNumber: navigator.getStepNumber(stepName),
            addresses: customer.getShippingAddressList(),
            selectedAddressId: addressList.getAddresses()[0].id,
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
                if (!newAddressSelected()) {
                    selectShippingAddress(addressList.getAddressById(this.selectedAddressId), this.sameAsBilling());
                } else {
                    this.validate();
                    if (!this.source.get('params.invalid')) {
                        var addressData = this.source.get('shippingAddress');
                        if (quote.getCheckoutMethod()() !== 'register') {
                            var addressBookCheckBox =  $("input[name = 'shipping[save_in_address_book]']:checked");
                            addressData.save_in_address_book = addressBookCheckBox.val();
                        }
                        selectShippingAddress(addressData, this.sameAsBilling());
                    }
                }
            },
            sameAsBillingClick: function() {
                if (this.sameAsBilling()) {
                    var billingAddress = quote.getBillingAddress();
                    this.selectedAddressId = billingAddress().customerAddressId;
                    newAddressSelected(false);
                }
                return true;
            },
            onAddressChange: function() {
                var billingAddress = quote.getBillingAddress();
                if (this.selectedAddressId != billingAddress().customerAddressId) {
                    this.sameAsBilling(false);
                }
                if (this.selectedAddressId == null) {
                    newAddressSelected(true);
                } else {
                    newAddressSelected(false);
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
                if (!this.customerAddressCount) {
                    newAddressSelected(true);
                    return true;
                }
                return newAddressSelected();
            },
            validate: function() {
                this.source.set('params.invalid', false);
                this.source.trigger('shippingAddress.data.validate');
            },
            isCustomerLoggedIn: customer.isLoggedIn(),
            customerAddressCount: window.checkoutConfig.customerAddressCount
        });
    }
);
