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
            cartCacheUpdated = null;

        try {
            cartCacheUpdated = window.localStorage.getItem(cacheKey);
        } catch (e) {
            console.error(e);
        }

        customerData.getInitCustomerData().done(function () {
            var cartUpdated = cartData().updatedAt;

            if (!cartCacheUpdated || cartUpdated > cartCacheUpdated) {
                cartCacheUpdated = cartUpdated;
            }

            if (data.productsUpdatedTime > cartCacheUpdated) {
                customerData.invalidate(['cart']);
                customerData.reload(['cart'], true);
                cartCacheUpdated = data.productsUpdatedTime;

                try {
                    window.localStorage.setItem(cacheKey, cartCacheUpdated);
                } catch (e) {
                    console.error(e);
                }
            }
        });
    };
});
