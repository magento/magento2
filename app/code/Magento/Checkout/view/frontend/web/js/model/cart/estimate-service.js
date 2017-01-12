/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-rate-processor/new-address',
        'Magento_Checkout/js/model/cart/totals-processor/default',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/cart/cache'
    ],
    function (quote, defaultProcessor, totalsDefaultProvider, shippingService, cartCache) {
        'use strict';

        var rateProcessors = [],
            totalsProcessors = [];

        quote.shippingAddress.subscribe(function () {
            var type = quote.shippingAddress().getType();
            if (quote.isVirtual()) {
                // update totals block when estimated address was set
                totalsProcessors['default'] = totalsDefaultProvider;
                totalsProcessors[type]
                    ? totalsProcessors[type].estimateTotals(quote.shippingAddress())
                    : totalsProcessors['default'].estimateTotals(quote.shippingAddress());
            } else {
                // check if user data not changed -> load rates from cache
                if (!cartCache.isAddressChanged(quote.shippingAddress())
                    && !cartCache.isCartVersionChanged()
                    && cartCache.getRatesCache()
                ) {
                    shippingService.setShippingRates(cartCache.getRatesCache());
                    console.log('Shipping rates loaded from cache.');
                    return;
                }

                // update rates list when estimated address was set
                rateProcessors['default'] = defaultProcessor;
                rateProcessors[type]
                    ? rateProcessors[type].getRates(quote.shippingAddress())
                    : rateProcessors['default'].getRates(quote.shippingAddress());

                // save rates to cache after load
                shippingService.getShippingRates().subscribe(function (rates) {
                    cartCache.setRatesCache(rates);
                    console.log('Shipping rates loaded from server.');
                });
            }
        });
        quote.shippingMethod.subscribe(function () {
            totalsDefaultProvider.estimateTotals(quote.shippingAddress());
        });
        quote.billingAddress.subscribe(function () {
            var type = quote.billingAddress().getType();
            if (quote.isVirtual()) {
                // update totals block when estimated address was set
                totalsProcessors['default'] = totalsDefaultProvider;
                totalsProcessors[type]
                    ? totalsProcessors[type].estimateTotals(quote.billingAddress())
                    : totalsProcessors['default'].estimateTotals(quote.billingAddress());
            }
        });
    }
);
