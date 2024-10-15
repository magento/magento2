/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
            if (!wishlistReloaded
                && !_.isEmpty(this.wishlist())
                // Expired section names are reloaded on page load.
                && _.indexOf(customerData.getExpiredSectionNames(), 'wishlist') === -1
                && window.checkout
                && window.checkout.websiteId
                && window.checkout.websiteId !== this.wishlist().websiteId
            ) {
                //set count to 0 to prevent "wishlist" blocks and count to show with wrong count and items
                this.wishlist().counter = 0;
                customerData.invalidate(['wishlist']);
                customerData.reload(['wishlist'], false);
                wishlistReloaded = true;
            }
        }
    });
});
