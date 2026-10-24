/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/shipping-rate-registry'
], function (quote, selectShippingAddress, rateRegistry) {
    'use strict';

    return function () {
        var shippingAddress = null;

        if (!quote.isVirtual()) {
            shippingAddress = quote.shippingAddress();

            rateRegistry.set(shippingAddress.getCacheKey(), null);
            selectShippingAddress(shippingAddress);
        }
    };
});
