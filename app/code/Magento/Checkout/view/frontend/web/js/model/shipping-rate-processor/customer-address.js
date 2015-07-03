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
        'Magento_Ui/js/model/messageList'
    ],
    function (resourceUrlManager, quote, storage, shippingService, rateRegistry, messageList) {
        "use strict";
        return {
            getRates: function(address) {
                var cache = rateRegistry.get(address.getKey());
                if (cache) {
                    shippingService.setShippingRates(cache);
                } else {
                    shippingService.isLoading(true);
                    storage.post(
                        resourceUrlManager.getUrlForEstimationShippingMethodsByAddressId(),
                        JSON.stringify({
                            addressId: address.customerAddressId
                        }),
                        false
                    ).done(
                        function(result) {
                            rateRegistry.set(address.getKey(), result);
                            shippingService.setShippingRates(result);
                        }

                    ).fail(
                        function(response) {
                            var error = JSON.parse(response.responseText);
                            messageList.addErrorMessage(error);
                            shippingService.setShippingRates([])
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
