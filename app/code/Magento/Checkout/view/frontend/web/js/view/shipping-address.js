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
        '../action/create-shipping-address',
        '../action/select-shipping-address',
        '../model/shipping-rates-validator',
        '../model/shipping-address/form-popup-state',
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
        createShippingAddress,
        selectShippingAddress,
        shippingRatesValidator,
        formPopUpState
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-address',
                visible: true
            },
            isVisible: ko.observable(true),
            isCustomerLoggedIn: customer.isLoggedIn,
            isFormPopUpVisible: formPopUpState.isVisible,
            isFormInline: addressList().length == 0,
            isNewAddressAdded: ko.observable(false),
            saveInAddressBook: true,

            initialize: function () {
                this._super();

                this._initializeDefaultAddress();

                return this;
            },

            /**
             * Initialize default shipping address
             *
             * @private
             */
            _initializeDefaultAddress: function() {
                var shippingAddress = quote.shippingAddress();
                if (!shippingAddress) {
                    var isShippingAddressInitialized = addressList.some(function (address) {
                        if (address.isDefaultShipping()) {
                            selectShippingAddress(address);
                            return true;
                        }
                        return false;
                    });
                    if (!isShippingAddressInitialized && addressList().length == 1) {
                        selectShippingAddress(addressList()[0]);
                    }
                }
            },

            initElement: function(element) {
                if (this.isFormInline && element.index == 'shipping-address-fieldset') {
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

                    // New address must be selected as a shipping address
                    selectShippingAddress(createShippingAddress(addressData));
                    this.isFormPopUpVisible(false);
                    this.isNewAddressAdded(true);
                }
            }
        });
    }
);
