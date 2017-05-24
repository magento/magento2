/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    'Magento_Checkout/js/model/cart/totals-processor/default',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/cart/cache',
    'Magento_Customer/js/customer-data'
], function (quote, defaultProcessor, totalsDefaultProvider, shippingService, cartCache, customerData) {
    'use strict';

    var rateProcessors = [],
        totalsProcessors = [];

    quote.shippingAddress.subscribe(function () {
        var type = quote.shippingAddress().getType();

        if (quote.isVirtual()) {
            // update totals block when estimated address was set
            totalsProcessors['default'] = totalsDefaultProvider;
            totalsProcessors[type] ?
                totalsProcessors[type].estimateTotals(quote.shippingAddress()) :
                totalsProcessors['default'].estimateTotals(quote.shippingAddress());
        } else {
            // check if user data not changed -> load rates from cache
            if (!cartCache.isChanged('address', quote.shippingAddress()) &&
                !cartCache.isChanged('cartVersion', customerData.get('cart')()['data_id']) &&
                cartCache.get('rates')
            ) {
                shippingService.setShippingRates(cartCache.get('rates'));

                return;
            }

            // update rates list when estimated address was set
            rateProcessors['default'] = defaultProcessor;
            rateProcessors[type] ?
                rateProcessors[type].getRates(quote.shippingAddress()) :
                rateProcessors['default'].getRates(quote.shippingAddress());

            // save rates to cache after load
            shippingService.getShippingRates().subscribe(function (rates) {
                cartCache.set('rates', rates);
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
            totalsProcessors[type] ?
                totalsProcessors[type].estimateTotals(quote.billingAddress()) :
                totalsProcessors['default'].estimateTotals(quote.billingAddress());
        }
    });
});
