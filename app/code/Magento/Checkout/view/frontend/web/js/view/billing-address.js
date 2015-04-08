/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(
    [
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        '../action/select-billing-address',
        'Magento_Checkout/js/model/step-navigator',
        '../model/quote',
        '../model/addresslist'
    ],
    function (Component, ko,  customer, selectBillingAddress, navigator, quote, addressList) {
        "use strict";
        var stepName = 'billingAddress';
        var newAddressSelected = ko.observable(false);
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/billing-address'
            },
            stepNumber: navigator.getStepNumber(stepName),
            billingAddresses: customer.getBillingAddressList(),
            selectedBillingAddressId: "1",
            isVisible: navigator.isStepVisible(stepName),
            useForShipping: "1",
            quoteIsVirtual: quote.isVirtual(),
            billingAddressesOptionsText: function(item) {
                return item.getFullAddress();
            },
            submitBillingAddress: function() {
                if (this.selectedBillingAddressId) {
                    selectBillingAddress(
                        addressList.getAddressById(this.selectedBillingAddressId),
                        this.useForShipping
                    );
                } else {
                    this.validate();
                    if (!this.source.get('params.invalid')) {
                        var addressData = this.source.get('billingAddress');
                        selectBillingAddress(addressData, this.useForShipping);
                    }
                }
                if (this.source.get('customerDetails')) {
                    customer.setDetails('email', this.source.get('customerDetails.email'));
                    customer.setDetails('password', this.source.get('customerDetails.password'));
                    customer.setDetails('confirm_password', this.source.get('customerDetails.confirm_password'));
                }
            },
            navigateToCurrentStep: function() {
                if (!navigator.isStepVisible(stepName)()) {
                    navigator.goToStep(stepName);
                }
            },
            isNewAddressSelected: function() {
                return newAddressSelected();
            },
            onAddressChange: function (value) {
                if (value === null) {
                    newAddressSelected(true);
                } else {
                    newAddressSelected(false);
                }
            },
            isCustomerLoggedIn: customer.isLoggedIn(),
            customerHasAddresses: window.customerHasAddresses

        });
    }
);
