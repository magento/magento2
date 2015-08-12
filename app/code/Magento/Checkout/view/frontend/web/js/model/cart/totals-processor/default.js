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
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (resourceUrlManager, quote, storage, totalsService, errorProcessor) {
        'use strict';

        return {
            /**
             * Get shipping rates for specified address.
             * @param {Object} address
             */
            estimateTotals: function (address) {
                totalsService.isLoading(true);
                var serviceUrl = resourceUrlManager.getUrlForTotalsEstimationForNewAddress(quote),
                    payload = {
                            addressInformation: {
                                address: quote.shippingAddress()
                            }
                    };
                if (quote.shippingMethod() && quote.shippingMethod()['method_code']) {
                    payload.addressInformation.shipping_method_code = quote.shippingMethod()['method_code'];
                    payload.addressInformation.shipping_carrier_code = quote.shippingMethod()['carrier_code'];

                }

                storage.post(
                    serviceUrl, JSON.stringify(payload), false
                ).done(
                    function (result) {
                        quote.setTotals(result);
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                    }
                ).always(
                    function () {
                        totalsService.isLoading(false);
                    }
                );
            }
        };
    }
);
