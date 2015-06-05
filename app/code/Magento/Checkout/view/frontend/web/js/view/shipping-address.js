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
        'underscore'
    ],
    function($, Component, ko, selectShippingAddress, customer, quote, navigator, addressList, _) {
        'use strict';
        var stepName = 'shippingAddress';
        var newAddressSelected = ko.observable(false);
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-address',
                visible: true,
                formVisible: customer.getShippingAddressList().length === 0
            },
            stepClassAttributes: function() {
                return navigator.getStepClassAttributes(stepName);
            },
            stepNumber: navigator.getStepNumber(stepName),
            addresses: function() {
                var newAddress = {
                        getAddressInline: function() {
                            return $.mage.__('New address');
                        },
                        customerAddressId: null
                    },
                    addresses = addressList.getAddresses();
                addresses.push(newAddress);
                return addresses;
            },
            selectedAddressId: ko.observable(
                addressList.getAddresses().length ? addressList.getAddresses()[0].customerAddressId : null
            ),
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
                var additionalFields,
                    addressData,
                    additionalData = {},
                    billingAddress = quote.getBillingAddress()();

                if (!billingAddress.customerAddressId || !this.visible()) {
                    /**
                     * All the the input fields that are not a part of the address but need to be submitted
                     * in the same request must have data-scope attribute set
                     */
                    additionalFields = $('input[data-scope="additionalAddressData"]').serializeArray();
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
                        addressData = this.source.get('shippingAddress');
                        selectShippingAddress(addressData, this.sameAsBilling(), additionalData);
                    }
                }
            },
            sameAsBillingClick: function() {
                var billingAddress,
                    shippingAddress,
                    property;

                addressList.isBillingSameAsShipping = !addressList.isBillingSameAsShipping;

                if (this.sameAsBilling()) {
                    billingAddress = quote.getBillingAddress()();

                    if (billingAddress.customerAddressId) {
                        this.selectedAddressId(billingAddress.customerAddressId);
                        newAddressSelected(false);

                    } else {
                        // copy billing address data to shipping address form if customer uses new address for billing
                        shippingAddress = this.source.get('shippingAddress');

                        for (property in billingAddress) {
                            if (billingAddress.hasOwnProperty(property) && shippingAddress.hasOwnProperty(property)) {
                                if (typeof billingAddress[property] === 'string') {
                                    this.source.set('shippingAddress.' + property, billingAddress[property]);
                                } else {
                                    this.source.set('shippingAddress.' + property, _.clone(billingAddress[property]));
                                }
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

                if (this.selectedAddressId() !== billingAddress().customerAddressId) {
                    this.sameAsBilling(false);
                }

                if (this.selectedAddressId() === null) {
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
