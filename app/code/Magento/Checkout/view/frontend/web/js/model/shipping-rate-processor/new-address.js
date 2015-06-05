/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Ui/js/model/errorlist'
    ],
    function (resourceUrlManager, quote, storage, shippingService, rateRegistry, errorList) {
        "use strict";
        return {
            getRates: function(address) {
                var cache = rateRegistry.get(address.getCacheKey());
                if (cache) {
                    shippingService.setShippingRates(cache);
                } else {
                    var serviceUrl = resourceUrlManager.getUrl(
                        'estimateShippingMethodsForNewAddress',
                        {'guest': {quoteId: quote.getQuoteId()}}
                    );
                    storage.post(
                        serviceUrl,
                        JSON.stringify({
                                address: {
                                    country_id: address.countryId,
                                    region_id: address.regionId,
                                    region: address.region,
                                    postcode: address.postcode

                                }
                            }
                        )
                    ).done(
                        function (result) {
                            rateRegistry.set(address.getCacheKey(), result);
                            shippingService.setShippingRates(result);
                        }
                    ).fail(
                        function (response) {
                            var error = JSON.parse(response.responseText);
                            errorList.add(error);
                            shippingService.setShippingRates([])
                        }
                    );
                }
            }

        };
    }
);