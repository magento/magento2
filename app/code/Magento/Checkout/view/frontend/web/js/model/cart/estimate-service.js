/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    'Magento_Checkout/js/model/cart/totals-processor/default',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/cart/cache',
    'Magento_Customer/js/customer-data'
], function (_, quote, defaultProcessor, totalsDefaultProvider, shippingService, cartCache, customerData) {
    'use strict';

    var rateProcessors = {},
        totalsProcessors = {},

        /**
         * Estimate totals for shipping address and update shipping rates.
         *
         * The shipping service loading spinner kicks in as soon as address inputs change
         * The spinner needs to be stopped when shipping rates are updated or not needed.
         * - In case of virtual quote or no active carriers: the spinner should be stopped
         * right away as shipping rates update is not needed
         * - In case shipping rates are retrieved from cache: the spinner should be stopped
         * right after setting the rates.
         * - In case shipping rates are retrieved from server: the spinner should be stopped
         * after rates have been updated which is handled in the shipping rate processor.
         *
         * @see Magento_Checkout/js/model/shipping-rates-validator
         */
        estimateTotalsAndUpdateRates = function () {
            var type = quote.shippingAddress().getType();

            if (
                quote.isVirtual() ||
                window.checkoutConfig.activeCarriers && window.checkoutConfig.activeCarriers.length === 0
            ) {
                // update totals block when estimated address was set
                totalsProcessors['default'] = totalsDefaultProvider;
                totalsProcessors[type] ?
                    totalsProcessors[type].estimateTotals(quote.shippingAddress()) :
                    totalsProcessors['default'].estimateTotals(quote.shippingAddress());
                shippingService.isLoading(false);
            } else {
                // check if user data not changed -> load rates from cache
                if (!cartCache.isChanged('address', quote.shippingAddress()) &&
                    !cartCache.isChanged('cartVersion', customerData.get('cart')()['data_id']) &&
                    cartCache.get('rates')
                ) {
                    shippingService.setShippingRates(cartCache.get('rates'));
                    shippingService.isLoading(false);
                    return;
                }

                // update rates list when estimated address was set
                rateProcessors['default'] = defaultProcessor;
                rateProcessors[type] ?
                    rateProcessors[type].getRates(quote.shippingAddress()) :
                    rateProcessors['default'].getRates(quote.shippingAddress());
            }
        },

        /**
         * Estimate totals for shipping address.
         */
        estimateTotalsShipping = _.debounce(function () {
            totalsDefaultProvider.estimateTotals(quote.shippingAddress());
        }, 50),

        /**
         * Estimate totals for billing address.
         */
        estimateTotalsBilling = function () {
            var type = quote.billingAddress().getType();

            if (quote.isVirtual()) {
                // update totals block when estimated address was set
                totalsProcessors['default'] = totalsDefaultProvider;
                totalsProcessors[type] ?
                    totalsProcessors[type].estimateTotals(quote.billingAddress()) :
                    totalsProcessors['default'].estimateTotals(quote.billingAddress());
            }
        };

    quote.shippingAddress.subscribe(estimateTotalsAndUpdateRates);
    quote.shippingMethod.subscribe(estimateTotalsShipping);
    quote.billingAddress.subscribe(estimateTotalsBilling);
    shippingService.getShippingRates().subscribe(function (rates) {
        // Check whether rates come from cache or not. If not - update cache and estimate totals
        if (cartCache.get('rates') !== rates) {
            cartCache.set('rates', rates);
            estimateTotalsShipping();
        }
    });
});
