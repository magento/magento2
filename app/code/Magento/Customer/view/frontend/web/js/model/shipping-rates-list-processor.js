/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/shipping-service'
    ],
    function (urlBuilder, quote, storage,shippingService) {
        "use strict";
        var serviceUrl;
            serviceUrl =  urlBuilder.createUrl('/carts/mine/estimate-shipping-methods-by-address-id', {});
        return {
            getRates: function(address) {
                var shippingRates = [];
                storage.post(
                    serviceUrl,
                    JSON.stringify({
                        addressId:  address.customerAddressId
                    })
                ).done(
                    function(result) {
                        shippingService.setShippingRates(result);
                        //shippingRates = result.shipping_methods
                    }

                ).fail(


                );
                return shippingRates;
            }
        };
    }
);