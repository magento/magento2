/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        '../model/address-converter',
        '../model/quote',
        '../action/select-shipping-address',
        '../model/shipping-rates-validator',
        'mage/translate'
    ],
    function(
        $,
        Component,
        ko,
        customer,
        addressList,
        addressConverter,
        quote,
        selectShippingAddress,
        shippingRatesValidator
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-address',
                visible: true
            },
            isVisible: ko.observable(true),
            isCustomerLoggedIn: customer.isLoggedIn(),
            isFormPopUpVisible: ko.observable(false),
            isFormInline: addressList().length == 0,
            isNewAddressAdded: ko.observable(false),
            saveInAddressBook: true,

            initElement: function(element) {
                if (element.index == 'shipping-address-fieldset') {
                    shippingRatesValidator.bindChangeHandlers(element.elems());
                }
            },

            /** Initialize observable properties */
            initObservable: function () {
                this._super()
                    .observe('visible');
                return this;
            },

            /** Check if component is active */
            isActive: function() {
                return !quote.isVirtual();
            },

            /** Show address form popup */
            showFormPopUp: function() {
                this.isFormPopUpVisible(true);
            },

            /** Hide address form popup */
            hideFormPopUp: function() {
                this.isFormPopUpVisible(false);
            },

            /** Save new shipping address */
            saveNewAddress: function() {
                this.source.set('params.invalid', false);
                this.source.trigger('shippingAddress.data.validate');

                if (!this.source.get('params.invalid')) {
                    var addressData = this.source.get('shippingAddress');
                    addressData.save_in_address_book = this.saveInAddressBook;

                    var newAddress = addressConverter.formAddressDataToQuoteAddress(addressData);
                    var isUpdated = addressList().some(function(address, index, addresses) {
                        if (address.getKey() == newAddress.getKey()) {
                            addresses[index] = newAddress;
                            return true;
                        }
                        return false;
                    });
                    if (!isUpdated) {
                        addressList.push(newAddress);
                    } else {
                        addressList.valueHasMutated();
                    }
                    // New address must be selected as a shipping address
                    selectShippingAddress(newAddress);
                    this.isFormPopUpVisible(false);
                    this.isNewAddressAdded(true);
                }
            }
        });
    }
);
