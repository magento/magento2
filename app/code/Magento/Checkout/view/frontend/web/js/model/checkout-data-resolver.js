/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global alert*/
/**
 * Checkout adapter for customer data storage
 */
define([
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/action/create-shipping-address'
], function (
    addressList,
    quote,
    selectShippingAddress,
    checkoutData,
    shippingService,
    selectShippingMethodAction,
    createShippingAddress
) {
    'use strict';

    return {
        resolveShippingAddress: function () {
            var shippingAddressData = checkoutData.getShippingAddressData();
            if (shippingAddressData) {
                createShippingAddress(shippingAddressData);
            }
            var shippingAddress = quote.shippingAddress();
            if (!shippingAddress) {
                var isShippingAddressInitialized = addressList.some(function (address) {
                    if (checkoutData.getSelectedShippingAddress() == address.getKey()) {
                        selectShippingAddress(address);
                        return true;
                    }
                    return false;
                });

                if (!isShippingAddressInitialized) {
                    isShippingAddressInitialized = addressList.some(function (address) {
                        if (address.isDefaultShipping()) {
                            selectShippingAddress(address);
                            return true;
                        }
                        return false;
                    });
                }
                if (!isShippingAddressInitialized && addressList().length == 1) {
                    selectShippingAddress(addressList()[0]);
                }
            }
        },

        resolveShippingRates: function () {
            var ratesData = shippingService.getSippingRates();
            var selectedShippingRate = checkoutData.getSelectedShippingRate();
            if (selectedShippingRate) {
                var rateIsAvailable = ratesData.some(function (rate) {
                    if (rate.carrier_code == selectedShippingRate.carrier_code
                        && rate.method_code == selectedShippingRate.method_code) {
                        return true;
                    }
                    return false;

                });
                if (rateIsAvailable) {
                    selectShippingMethodAction(selectedShippingRate);
                }
            }
        }
    }
});
