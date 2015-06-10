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
        'mage/translate'
    ],
    function (ko, Component, customer, addressList, quote, createBillingAddress, selectBillingAddress, $t) {
        "use strict";

        var newAddressOption = {
            getAddressInline: function() {
                return $t('New Address');
            },
            customerAddressId: null
        };
        var addressOptions = addressList().filter(function(address, index, addresses) {
            return address.getType() == 'customer-address';
        });
        addressOptions.push(newAddressOption);

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/billing-address'
            },

            initObservable: function () {
                this._super()
                    .observe({
                        selectedAddress: null,
                        isAddressDetailsVisible: quote.shippingAddress() != null,
                        isAddressDetailsEditable: false,
                        isAddressFormVisible: !customer.isLoggedIn() || addressOptions.length == 1,
                        isAddressSameAsShipping: quote.shippingAddress() != null && quote.shippingAddress() == quote.billingAddress()
                    });

                return this;
            },

            canUseShippingAddress: !quote.isVirtual(),

            saveInAddressBook: true,

            currentBillingAddress: quote.billingAddress,

            addressOptions: addressOptions,

            addressOptionsText: function(address) {
                return address.getAddressInline();
            },

            useShippingAddress: function () {
                if (this.isAddressSameAsShipping()) {
                    selectBillingAddress(quote.shippingAddress());
                    this.isAddressDetailsVisible(true);
                } else {
                    this.isAddressDetailsVisible(false);
                }
                return true;
            },

            updateAddress: function () {
                if (this.selectedAddress() && this.selectedAddress() != newAddressOption) {
                    selectBillingAddress(this.selectedAddress());
                    this.isAddressDetailsVisible(true);
                } else {
                    this.source.set('params.invalid', false);
                    // TODO Provide unique data provider for every billing form
                    this.source.trigger('shippingAddress.data.validate');
                    if (!this.source.get('params.invalid')) {
                        var addressData = this.source.get('shippingAddress');
                        addressData.save_in_address_book = this.saveInAddressBook;

                        // New address must be selected as a billing address
                        selectBillingAddress(createBillingAddress(addressData));
                        this.isAddressDetailsVisible(true);
                    }
                }
            },

            editAddress: function () {
                this.isAddressDetailsVisible(false);
            },

            cancelAddressEdit: function () {
                if (quote.billingAddress()) {
                    // restore 'Same As Shipping' checkbox state
                    this.isAddressSameAsShipping(
                        !quote.isVirtual() && (quote.shippingAddress() == quote.billingAddress())
                    );
                    this.isAddressDetailsVisible(true);
                }
            },

            onAddressChange: function (address) {
                this.isAddressFormVisible(address == newAddressOption);
            }
        });
    }
);
