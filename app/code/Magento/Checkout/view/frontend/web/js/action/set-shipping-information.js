/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define,alert*/
define(
    [
        '../model/quote',
        '../model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/payment-service'
    ],
    function (quote, resourceUrlManager, storage, paymentService) {
        "use strict";
        return function (customOptions, callbacks) {
            var proceed = true;
            _.each(callbacks, function (callback) {
                proceed = proceed && callback();
            });

            if (proceed) {
                var payload = {
                    addressInformation: {
                        shipping_address: quote.shippingAddress(),
                        shipping_method_code: quote.shippingMethod().method_code,
                        shipping_carrier_code: quote.shippingMethod().carrier_code
                    }
                };

                storage.post(
                    resourceUrlManager.getUrlForSetShippingInformation(quote),
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        console.log(response);
                        paymentService.setPaymentMethods(response.payment_methods);
                        quote.setTotals(response.totals)
                    }
                ).fail(
                    function (response) {
                        var error = JSON.parse(response.responseText);
                        console.log(error);
                        //errorList.add(error);
                    }
                );

                //console.log(customOptions);
                //console.log(quote.shippingAddress());
                //console.log(quote.shippingMethod());

                //var shippingMethodCode = code.split("_"),
                //    shippingRate = shippingService.getRateByCode(shippingMethodCode)[0];
                //quote.setShippingCustomOptions(customOptions);
                //quote.setCollectedTotals('shipping', shippingRate.amount);
            }
        };
    }
);
