/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    'Magento_Checkout/js/model/cart/totals-processor/default'
], function (quote, defaultProcessor, totalsDefaultProvider) {
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
            // update rates list when estimated address was set
            rateProcessors['default'] = defaultProcessor;
            rateProcessors[type] ?
                rateProcessors[type].getRates(quote.shippingAddress()) :
                rateProcessors['default'].getRates(quote.shippingAddress());

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
