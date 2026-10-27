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

                let updateTimer = null;

                const selector = '.wishlist .counter.qty, .customer-menu .wishlist .counter, .link.wishlist .counter',
                    wishlist = customerData.get('wishlist'),
                    clearPeriodicCounterUpdate = function () {
                        if (updateTimer) {
                            clearInterval(updateTimer);
                            updateTimer = null;
                        }
                    },
                    updateCounters = function (updatedWishlist) {
                        const counters = $(selector);

                        if (typeof updatedWishlist.counter !== 'undefined'
                            && updatedWishlist.counter !== null
                            && counters.length
                        ) {
                            const expectedText = updatedWishlist.counter.toString(),
                                currentText = counters.first().text().trim();

                            // Check if text is already correct
                            if (currentText === expectedText) {
                                clearPeriodicCounterUpdate();
                                return;
                            }

                            counters.text(updatedWishlist.counter);
                            counters.show();
                        }
                        if (updatedWishlist.counter === null) {
                            clearPeriodicCounterUpdate();
                            counters.hide();
                        }
                    };

                // Subscribe to future wishlist changes
                wishlist.subscribe(updateCounters);

                // Simple timer to periodically update counters
                updateTimer = setInterval(function () {
                    const wishlistData = wishlist();

                    if (wishlistData && typeof wishlistData.counter !== 'undefined') {
                        updateCounters(wishlistData);
                    }
                }, 1000);

                return this;
            }
        });
    };
});
