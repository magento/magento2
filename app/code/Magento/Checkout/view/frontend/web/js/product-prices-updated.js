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
            localStorageSupported = isLocalStorageSupported(),
            cartCacheUpdated = localStorageSupported ? window.localStorage.getItem(cacheKey) : 0;

        customerData.getInitCustomerData().done(function () {
            var cartUpdated = cartData().updatedAt;
            debugger;
            if (!cartCacheUpdated || cartUpdated > cartCacheUpdated) {
                cartCacheUpdated = cartUpdated;
            }

            if (data.productsUpdatedTime > cartCacheUpdated) {
                customerData.invalidate(['cart']);
                customerData.reload(['cart'], true);
                cartCacheUpdated = data.productsUpdatedTime;
                if (localStorageSupported) {
                    window.localStorage.setItem(cacheKey, cartCacheUpdated);
                }
            }
        });

        function isLocalStorageSupported() {
            var key = '_storageSupported';

            try {
                window.localStorage.setItem(key, 'true');

                if (window.localStorage.getItem(key) === 'true') {
                    window.localStorage.removeItem(key);

                    return true;
                }

                return false;
            } catch (e) {
                return false;
            }
        };
    };
});
