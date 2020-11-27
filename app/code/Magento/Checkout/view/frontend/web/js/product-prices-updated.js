/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/customer-data'], function ($, customerData) {
    'use strict';

    return function () {
        var cacheKey = 'cart-cache-updated',
            cartData = customerData.get('cart'),
            localStorage = $.initNamespaceStorage('mage-cache-storage').localStorage,
            cartCacheUpdated = localStorage.get(cacheKey);

        customerData.getInitCustomerData().done(function () {
            var cartUpdated = cartData().updated_at;
            if (!cartCacheUpdated || cartUpdated > cartCacheUpdated) {
                cartCacheUpdated = cartUpdated;
            }
            if (PRODUCTS_UPDATED_AT > cartCacheUpdated) {
                customerData.invalidate(['cart']);
                customerData.reload(['cart'], true);
                cartCacheUpdated = PRODUCTS_UPDATED_AT;
                localStorage.set(cacheKey, cartCacheUpdated);
            }
        });
    };
});
