/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/address-converter'
    ],
    function(Component, selectShippingAddress, addressConverter) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/cart/shipping-estimation'
            },
            isStateProvinceRequired: true,
            isCityActive: true,
            isCityRequired: true,
            isZipCodeRequired: true,

            getEstimationInfo: function (elem, event) {
                event.preventDefault();

                var addressFlat = {
                    'country': 'US',
                    'postcode': 11111
                };

                var address = addressConverter.formAddressDataToQuoteAddress(addressFlat);
                selectShippingAddress(address);
            }
        });
    }
);
