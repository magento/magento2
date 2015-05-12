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
                template: 'Magento_Checkout/shipping-address',
                visible: true
            },
            stepClassAttributes: function() {
                return navigator.getStepClassAttributes(stepName);
            },
            stepNumber: navigator.getStepNumber(stepName),
            addresses: customer.getShippingAddressList(),
            selectedAddressId: ko.observable(addressList.getAddresses()[0].id),
            sameAsBilling: ko.observable(null),
            quoteHasBillingAddress: quote.getBillingAddress(),
            isVisible: navigator.isStepVisible(stepName),
            initObservable: function () {
                this._super()
                    .observe('visible');
                return this;
            },
            isActive: function() {
                if (quote.isVirtual()) {
                    navigator.setStepEnabled(stepName, false);
                }
                return !quote.isVirtual();
            },
            selectShippingAddress: function() {
                var additionalData = {};
                var billingAddress = quote.getBillingAddress()();
                if (!billingAddress.customerAddressId) {
                    /**
                     * All the the input fields that are not a part of the address but need to be submitted
                     * in the same request must have data-scope attribute set
                     */
                    var additionalFields = $('input[data-scope="additionalAddressData"]').serializeArray();
                    additionalFields.forEach(function (field) {
                        additionalData[field.name] = field.value;
                    });
                }
                if (!newAddressSelected()) {
                    selectShippingAddress(
                        addressList.getAddressById(this.selectedAddressId()),
                        this.sameAsBilling(),
                        additionalData
                    );
                } else {
                    if (this.visible()) {
                        this.validate();
                    }
                    if (!this.source.get('params.invalid')) {
                        var addressData = this.source.get('shippingAddress');
                        if (quote.getCheckoutMethod()() !== 'register') {
                            var addressBookCheckBox =  $("input[name = 'shipping[save_in_address_book]']:checked");
                            addressData.save_in_address_book = addressBookCheckBox.val();
                        }
                        selectShippingAddress(addressData, this.sameAsBilling(), additionalData);
                    }
                }
            },
            sameAsBillingClick: function() {
                if (this.sameAsBilling()) {
                    var billingAddress = quote.getBillingAddress()();
                    if (billingAddress.customerAddressId) {
                        this.selectedAddressId(billingAddress.customerAddressId);
                        newAddressSelected(false);
                    } else {
                        // copy billing address data to shipping address form if customer uses new address for billing
                        var shippingAddress = this.source.get('shippingAddress');
                        for (var property in billingAddress) {
                            if (billingAddress.hasOwnProperty(property) && shippingAddress.hasOwnProperty(property)) {
                                this.source.set('shippingAddress.' + property, billingAddress[property]);
                            }
                        }
                        this.selectedAddressId(null);
                        newAddressSelected(true);
                    }
                }
                return true;
            },
            onAddressChange: function() {
                var billingAddress = quote.getBillingAddress();
                if (this.selectedAddressId() != billingAddress().customerAddressId) {
                    this.sameAsBilling(false);
                }
                if (this.selectedAddressId() == null) {
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
