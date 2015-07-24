/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(
    [
        'ko',
        'Magento_Ui/js/form/form',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-billing-address',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'mage/translate'
    ],
    function (
        ko,
        Component,
        customer,
        addressList,
        quote,
        createBillingAddress,
        selectBillingAddress,
        checkoutData,
        checkoutDataResolver,
        $t
    ) {
        'use strict';

        var lastSelectedBillingAddress = null,
            newAddressOption = {
            getAddressInline: function() {
                return $t('New Address');
            },
            customerAddressId: null
            },
            countryData = window.checkoutConfig.countryData;

        var addressOptions = addressList().filter(function(address) {
            return address.getType() == 'customer-address';
        });
        addressOptions.push(newAddressOption);

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/billing-address'
            },

            initialize: function () {
                this._super();
                quote.paymentMethod.subscribe(function() {
                    checkoutDataResolver.resolveBillingAddress();
                }, this);
            },

            initObservable: function () {
                this._super()
                    .observe({
                        selectedAddress: null,
                        isAddressDetailsVisible: quote.billingAddress() != null,
                        isAddressFormVisible: !customer.isLoggedIn() || addressOptions.length == 1,
                        isAddressSameAsShipping: false,
                        saveInAddressBook: true
                    });

                quote.billingAddress.subscribe(function(newAddress) {
                    this.isAddressSameAsShipping(
                        newAddress != null
                            && newAddress.getCacheKey() == quote.shippingAddress().getCacheKey()
                            && !quote.isVirtual()
                    );
                    if (newAddress != null && newAddress.saveInAddressBook !== undefined) {
                        this.saveInAddressBook(newAddress.saveInAddressBook);
                    }
                    this.isAddressDetailsVisible(true);
                }, this);

                return this;
            },

            canUseShippingAddress: ko.computed(function(){
                return !quote.isVirtual() && quote.shippingAddress()
                    && quote.shippingAddress().canUseForBilling();
            }),

            currentBillingAddress: quote.billingAddress,

            addressOptions: addressOptions,

            customerHasAddresses: addressOptions.length > 1,

            addressOptionsText: function(address) {
                return address.getAddressInline();
            },

            useShippingAddress: function () {
                if (this.isAddressSameAsShipping()) {
                    selectBillingAddress(quote.shippingAddress());
                    this.isAddressDetailsVisible(true);
                } else {
                    lastSelectedBillingAddress = quote.billingAddress();
                    quote.billingAddress(null);
                    this.isAddressDetailsVisible(false);
                }
                checkoutData.setSelectedBillingAddress(null);
                return true;
            },

            updateAddress: function () {
                if (this.selectedAddress() && this.selectedAddress() != newAddressOption) {
                    selectBillingAddress(this.selectedAddress());
                    checkoutData.setSelectedBillingAddress(this.selectedAddress().getKey());
                } else {
                    this.source.set('params.invalid', false);
                    this.source.trigger(this.dataScopePrefix + '.data.validate');
                    if (!this.source.get('params.invalid')) {
                        var addressData = this.source.get(this.dataScopePrefix),
                            newBillingAddress = createBillingAddress(addressData);
                        if (this.isCustomerLoggedIn && !this.customerHasAddresses) {
                            this.saveInAddressBook(true);
                        }
                        addressData.save_in_address_book = this.saveInAddressBook();

                        // New address must be selected as a billing address
                        selectBillingAddress(newBillingAddress);
                        checkoutData.setSelectedBillingAddress(newBillingAddress.getKey());
                        checkoutData.setNewCustomerBillingAddress(addressData);
                    }
                }
            },

            editAddress: function () {
                lastSelectedBillingAddress = quote.billingAddress();
                quote.billingAddress(null);
                this.isAddressDetailsVisible(false);
            },

            cancelAddressEdit: function () {
                this.restoreBillingAddress();
                if (quote.billingAddress()) {
                    // restore 'Same As Shipping' checkbox state
                    this.isAddressSameAsShipping(
                        quote.billingAddress() != null
                            && quote.billingAddress().getCacheKey() == quote.shippingAddress().getCacheKey()
                            && !quote.isVirtual()
                    );
                    this.isAddressDetailsVisible(true);
                }
            },

            restoreBillingAddress: function () {
                if (lastSelectedBillingAddress != null) {
                    selectBillingAddress(lastSelectedBillingAddress);
                }
            },

            onAddressChange: function (address) {
                this.isAddressFormVisible(address == newAddressOption);
            },

            getCountryName: function(countryId) {
                return (countryData[countryId] != undefined) ? countryData[countryId].name : "";
            }
        });
    }
);
