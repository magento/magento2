/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (resourceUrlManager, quote, storage, shippingService, rateRegistry, errorProcessor) {
        'use strict';

        return {
            /**
             * Get shipping rates for specified address.
             * @param {Object} address
             */
            getRates: function (address) {
                shippingService.isLoading(true);
                var cache = rateRegistry.get(address.getCacheKey()),
                    serviceUrl = resourceUrlManager.getUrlForEstimationShippingMethodsForNewAddress(quote),
                    payload = JSON.stringify({
                            address: {
                                country_id: address.countryId,
                                region_id: address.regionId,
                                region: address.region,
                                postcode: address.postcode
                            }
                        }
                    );

                if (cache) {
                    shippingService.setShippingRates(cache);
                    shippingService.isLoading(false);
                } else {
                    storage.post(
                        serviceUrl, payload, false
                    ).done(
                        function (result) {
                            rateRegistry.set(address.getCacheKey(), result);
                            shippingService.setShippingRates(result);
                        }
                    ).fail(
                        function (response) {
                            shippingService.setShippingRates([]);
                            errorProcessor.process(response);
                        }
                    ).always(
                        function () {
                            shippingService.isLoading(false);
                        }
                    );
                }
            }
        };
    }
);
