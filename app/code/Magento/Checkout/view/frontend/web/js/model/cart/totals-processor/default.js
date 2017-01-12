/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'underscore',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/cart/cache'
    ],
    function (_, resourceUrlManager, quote, storage, totalsService, errorProcessor, cartCache) {
        'use strict';

        var loadFromServer = function (address) {
            var serviceUrl,
                payload;

            // Start loader for totals block
            totalsService.isLoading(true);
            serviceUrl = resourceUrlManager.getUrlForTotalsEstimationForNewAddress(quote);
            payload = {
                addressInformation: {
                    address: _.pick(address, cartCache.requiredFields)
                }
            };

            if (quote.shippingMethod() && quote.shippingMethod()['method_code']) {
                payload.addressInformation['shipping_method_code'] = quote.shippingMethod()['method_code'];
                payload.addressInformation['shipping_carrier_code'] = quote.shippingMethod()['carrier_code'];
            }

            storage.post(
                serviceUrl, JSON.stringify(payload), false
            ).done(
                function (result) {
                    quote.setTotals(result);
                    cartCache.saveCartDataToCache(address, result);
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            ).always(
                function () {
                    // Stop loader for totals block
                    totalsService.isLoading(false);
                }
            );
        };

        return {
            /**
             * Get shipping rates for specified address.
             */
            estimateTotals: function (address) {

                if (!cartCache.isCartVersionChanged()
                    && !cartCache.isShippingMethodCodeChanged()
                    && !cartCache.isShippingCarrierCodeChanged()
                    && !cartCache.isAddressChanged(address)
                    && cartCache.getTotalsCache()
                ) {
                    quote.setTotals(cartCache.getTotalsCache());
                    console.log('Totals loaded from cache.');
                } else {
                    loadFromServer(address);
                    console.log('Totals loaded from server.');
                }
            }
        };
    }
);
