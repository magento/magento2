/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define,alert*/
define(
    [
        "jquery",
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        '../action/select-billing-address',
        'Magento_Checkout/js/model/step-navigator',
        '../model/quote',
        '../model/addresslist',
        '../action/check-email-availability'
    ],
    function ($, Component, ko, customer, selectBillingAddress, navigator, quote, addressList, checkEmailAvailability) {
        "use strict";
        var stepName = 'billingAddress';
        var newAddressSelected = ko.observable(false);
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/billing-address'
            },
            stepNumber: navigator.getStepNumber(stepName),
            billingAddresses: customer.getBillingAddressList(),
            selectedBillingAddressId: addressList.getAddresses()[0].id,
            isVisible: navigator.isStepVisible(stepName),
            useForShipping: "1",
            quoteIsVirtual: quote.isVirtual(),
            isEmailCheckComplete: null,
            billingAddressesOptionsText: function(item) {
                return item.getFullAddress();
            },
            submitBillingAddress: function() {
                this.isEmailCheckComplete = $.Deferred();
                if (quote.getCheckoutMethod()() === 'register') {
                    customer.customerData.email = this.source.get('billingAddress.email');
                    customer.customerData.firstname = this.source.get('billingAddress.firstname');
                    customer.customerData.lastname = this.source.get('billingAddress.lastname');
                    customer.setDetails('password', this.source.get('customerDetails.password'));
                }
                if (this.selectedBillingAddressId) {
                    selectBillingAddress(
                        addressList.getAddressById(this.selectedBillingAddressId),
                        this.useForShipping
                    );
                } else {
                    var that = this;
                    this.validate();
                    $.when(this.isEmailCheckComplete).done( function() {
                        if (!that.source.get('params.invalid')) {
                            var addressData = that.source.get('billingAddress');
                            addressData['save_in_address_book'] = $( "input[name = 'billing[save_in_address_book]']:checked" ).val();
                            if (quote.getCheckoutMethod()() && !customer.isLoggedIn()())
                            {
                                addressData['email'] = this.source.get('customerDetails.email')
                            }
                            selectBillingAddress(addressData, that.useForShipping);
                        }
                    }).fail( function() {
                        alert(
                            "There is already a registered customer using this email address. " +
                            "Please log in using this email address or enter a different email address " +
                            "to register your account."
                        );
                    });
                }
            },
            navigateToCurrentStep: function() {
                if (!navigator.isStepVisible(stepName)()) {
                    navigator.goToStep(stepName);
                }
            },
            isNewAddressSelected: function() {
                if (!this.customerHasAddresses) {
                    return true;
                }
                return newAddressSelected();
            },
            onAddressChange: function (value) {
                if (value === null) {
                    newAddressSelected(true);
                } else {
                    newAddressSelected(false);
                }
            },
            validate: function() {
                this.source.set('params.invalid', false);
                this.source.trigger('billingAddress.data.validate');
                if (quote.getCheckoutMethod()() === 'register') {
                    this.source.trigger('customerDetails.data.validate');
                    checkEmailAvailability(this.isEmailCheckComplete);
                } else {
                    this.isEmailCheckComplete.resolve();
                }
            },
            isCustomerLoggedIn: customer.isLoggedIn(),
            customerHasAddresses: window.customerHasAddresses,
            hideExtraFields: function() {
                if (!quote.getCheckoutMethod()() && customer.isLoggedIn()()) {
                    $('[name="customerDetails.email"]').hide();
                    $('[name="customerDetails.password"]').hide();
                    $('[name="customerDetails.confirm_password"]').hide();
                }
            }
        });
    }
);
