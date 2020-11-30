/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/customer-data'], function ($, customerData) {
    'use strict';

    return function (data) {
        var cacheKey = 'cart-cache-updated',
            cartData = customerData.get('cart'),
            cartCacheUpdated = window.localStorage.getItem(cacheKey);

        customerData.getInitCustomerData().done(function () {
            var cartUpdated = cartData().updated_at;
            if (!cartCacheUpdated || cartUpdated > cartCacheUpdated) {
                cartCacheUpdated = cartUpdated;
            }
            if (data.productsUpdatedTime > cartCacheUpdated) {
                customerData.invalidate(['cart']);
                customerData.reload(['cart'], true);
                cartCacheUpdated = data.productsUpdatedTime;
                window.localStorage.setItem(cacheKey, cartCacheUpdated);
            }
        });
    };
});
