/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/shipping-rate-service',
        'Magento_Checkout/js/model/shipping-service',
        'mage/validation'
    ],
    function($, ko, Component, selectShippingAddress, addressConverter, shippingRateService, shippingService) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/cart/shipping-estimation'
            },
            isStateProvinceRequired: true,
            isCityActive: true,
            isCityRequired: true,
            isZipCodeRequired: true,
            isLoading: shippingService.isLoading,
            shippingRates: shippingService.getShippingRates(),

            getEstimationInfo: function (form, event) {
                var addressForm = $(form),
                    addressData = {},
                    formDataArray = addressForm.serializeArray();

                if (event) {
                    event.preventDefault();
                }

                if (addressForm.validation() && addressForm.validation('isValid')) {
                    formDataArray.forEach(function (entry) {
                       addressData[entry.name] = entry.value;
                    });
                    selectShippingAddress(addressConverter.formAddressDataToQuoteAddress(addressData));
                }
            }
        });
    }
);
