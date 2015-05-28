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
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Ui/js/model/errorlist'
    ],
    function (urlBuilder, quote, storage, shippingService, errorList) {
        "use strict";
        return {
            getRates: function(address) {
                storage.post(
                    urlBuilder.createUrl('/carts/mine/estimate-shipping-methods-by-address-id', {}),
                    JSON.stringify({
                        addressId:  address.customerAddressId
                    })
                ).done(
                    function(result) {
                        shippingService.setShippingRates(result);
                    }

                ).fail(
                    function(response) {
                        var error = JSON.parse(response.responseText);
                        errorList.add(error);
                        shippingService.setShippingRates([])
                    }

                );
            }
        };
    }
);