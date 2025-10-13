/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

define(['jquery', 'Magento_Customer/js/customer-data'], function ($, customerData) {
    'use strict';

    return function (WishlistComponent) {
        return WishlistComponent.extend({
            initialize: function () {
                this._super();

                const selector = '.wishlist .counter.qty, .customer-menu .wishlist .counter, .link.wishlist .counter',
                    wishlist = customerData.get('wishlist');

                wishlist.subscribe(function (updatedWishlist) {
                    const counters = $(selector);

                    if (typeof updatedWishlist.counter !== 'undefined'
                        && updatedWishlist.counter !== null
                        && counters.length
                    ) {
                        counters.text(updatedWishlist.counter);
                    }
                    if (updatedWishlist.counter === null) {
                        counters.hide();
                    }
                });

                return this;
            }
        });
    };
});
