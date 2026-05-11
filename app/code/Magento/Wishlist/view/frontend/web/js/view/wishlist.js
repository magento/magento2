/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'underscore'
], function (Component, customerData, _) {
    'use strict';

    var wishlistReloaded = false;

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.wishlist = customerData.get('wishlist');
            this.company = customerData.get('company');
            if (!wishlistReloaded
                && !_.isEmpty(this.wishlist())
                // Expired section names are reloaded on page load.
                && _.indexOf(customerData.getExpiredSectionNames(), 'wishlist') === -1
                && window.checkout
                && window.checkout.storeId
                && (window.checkout.storeId !== this.wishlist().storeId || this.company().is_enabled)
            ) {
                //set count to 0 to prevent "Wishlist products" blocks and count to show with wrong count and items
                this.wishlist().counter = 0;
                customerData.invalidate(['wishlist']);
                customerData.reload(['wishlist'], false);
                wishlistReloaded = true;
            }
        }
    });
});
