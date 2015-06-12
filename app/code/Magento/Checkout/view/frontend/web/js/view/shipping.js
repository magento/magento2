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
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
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
        formPopUpState,
        shippingService,
        selectShippingMethodAction,
        rateRegistry,
        setShippingInformation
    ) {
        'use strict';

        var rates = window.checkoutConfig.shippingRates.data;
        var rateKey = window.checkoutConfig.shippingRates.key;
        if (rateKey) {
            rateRegistry.set(rateKey, rates);
        }
        selectShippingMethodAction(window.checkoutConfig.selectedShippingMethod);
        shippingService.setShippingRates(rates);

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping',
                visible: true
            },
            isCustomerLoggedIn: customer.isLoggedIn,
            isFormPopUpVisible: formPopUpState.isVisible,
            isFormInline: addressList().length == 0,
            isNewAddressAdded: ko.observable(false),
            saveInAddressBook: true,

            initialize: function () {
                this._super();
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
                if (rates.length == 1) {
                    selectShippingMethodAction(rates[0])
                }
                return this;
            },

            initElement: function(element) {
                //@todo refactor this condition
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
            },

            /** Shipping Method View **/
            rates: shippingService.getSippingRates(),
            isLoading: shippingService.isLoading,
            isSelected: ko.computed(function () {
                    return quote.shippingMethod()
                        ? quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code
                        : null;
                }
            ),

            selectShippingMethod: function(shippingMethod) {
                selectShippingMethodAction(shippingMethod);
                return true;
            },

            setShippingInformation: function () {
                if (this.isFormInline) {
                    this.source.set('params.invalid', false);
                    this.source.trigger('shippingAddress.data.validate');
                    if (this.source.get('params.invalid')
                        || !quote.shippingMethod()
                        || !quote.shippingMethod().method_code
                        || !quote.shippingMethod().carrier_code
                    ) {
                        return false;
                    }

                    var shippingAddress = quote.shippingAddress();
                    var addressData = addressConverter.formAddressDataToQuoteAddress(
                        this.source.get('shippingAddress')
                    );

                    //Copy form data to quote shipping address object
                    for (var field in addressData) {
                        if (addressData.hasOwnProperty(field)
                            && shippingAddress.hasOwnProperty(field)
                            && typeof addressData[field] != 'function'
                        ) {
                            shippingAddress[field] = addressData[field];
                        }
                    }
                    selectShippingAddress(shippingAddress);
                }
                setShippingInformation();
            }
        });
    }
);
