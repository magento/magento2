/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(
    [
        "jquery",
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        '../action/select-billing-address',
        '../model/step-navigator',
        '../model/quote',
        '../model/addresslist'
    ],
    function ($, Component, ko, customer, selectBillingAddress, navigator, quote, addressList) {
        "use strict";
        var stepName = 'billingAddress';
        var newAddressSelected = ko.observable(false);
        var billingFormSelector = '#co-billing-form';

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/billing-address'
            },
            initObservable: function () {
                this._super().observe('useForShipping');
                return this;
            },
            stepClassAttributes: function() {
                return navigator.getStepClassAttributes(stepName);
            },
            stepNumber: navigator.getStepNumber(stepName),
            billingAddresses: function() {
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
            selectedBillingAddressId: ko.observable(
                addressList.getAddresses().length ? addressList.getAddresses()[0].customerAddressId : null
            ),
            isVisible: navigator.isStepVisible(stepName),
            useForShipping: "1",
            quoteIsVirtual: quote.isVirtual(),
            billingAddressesOptionsText: function(item) {
                return item.getAddressInline();
            },
            checkUseForShipping: function(useForShipping) {
                var additionalData = {};
                if (useForShipping() instanceof Object) {
                    additionalData = useForShipping().getAdditionalData();
                    useForShipping('1');
                }
                return additionalData;
            },
            submitBillingAddress: function() {
                var additionalData = this.checkUseForShipping(this.useForShipping);
                if (this.selectedBillingAddressId()) {
                    selectBillingAddress(
                        addressList.getAddressById(this.selectedBillingAddressId()),
                        this.useForShipping,
                        additionalData
                    );
                } else {
                    this.validate();
                    if (!this.source.get('params.invalid')) {
                        var addressData = this.source.get('billingAddress');
                        /**
                         * All the the input fields that are not a part of the address (e. g. CAPTCHA) but need to be
                         * submitted in the same request must have data-scope attribute set
                         */
                        var additionalFields = $('input[data-scope="additionalAddressData"]').serializeArray();
                        additionalFields.forEach(function (field) {
                            additionalData[field.name] = field.value;
                        });
                        if (quote.getCheckoutMethod()() && !customer.isLoggedIn()()) {
                            addressData.email = this.source.get('customerDetails.email');
                        }
                        if($(billingFormSelector).validation() && $(billingFormSelector).validation('isValid')) {
                            selectBillingAddress(addressData, this.useForShipping, additionalData);
                        }
                    }
                }
            },
            navigateToCurrentStep: function() {
                if (!navigator.isStepVisible(stepName)()) {
                    navigator.goToStep(stepName);
                }
            },
            isNewAddressSelected: function() {
                if (!this.customerAddressCount) {
                    return true;
                }
                return newAddressSelected();
            },
            onAddressChange: function (value) {
                value() === null ? newAddressSelected(true) : newAddressSelected(false);
            },
            validate: function() {
                var fields = $(billingFormSelector).find('input, select');

                this.source.set('params.invalid', false);
                fields.trigger('change');
                this.source.trigger('billingAddress.data.validate');
                if (!customer.isLoggedIn()()) {
                    this.source.trigger('customerDetails.data.validate');
                }
                this.validateAdditionalAddressFields();
            },
            validateAdditionalAddressFields: function() {
                $(billingFormSelector).validation();
                $(billingFormSelector + ' input[data-scope="additionalAddressData"]').each(function(key, item) {
                    $(item).valid();
                });
            },
            isCustomerLoggedIn: customer.isLoggedIn(),
            customerAddressCount: window.checkoutConfig.customerAddressCount,
            hideExtraFields: function() {
                if (!quote.getCheckoutMethod()() && customer.isLoggedIn()()) {
                    $('[name="customerDetails.email"]').hide();
                }
            }
        });
    }
);
